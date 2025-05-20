<?php

namespace App\Jobs;

use App\Enums\AppConst;
use App\Enums\MailTypeEnum;
use App\Events\FileEmpty;
use App\Events\FileLimit;
use App\Events\FileReceived;
use App\Events\FileWarning;
use App\Events\ImportSuccess;
use App\Http\Requests\ImportUserRequest;
use App\Mail\ImportMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ImportExcelJob implements ShouldQueue
{
    use Queueable;

    private $user;
    private $path;

    public $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct($user, $path)
    {
        $this->user = $user;
        $this->path = $path;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $isError = false;
            $header = ['Name', 'Email', 'Description', 'Type'];
            $fileContent = [$header,];
            $users = $this->loadUserFromFile($this->path);
            $usersNumber = count($users) - 1;
            deleteFile('public', $this->path);
            if ($usersNumber <= 0) {
                FileEmpty::dispatch($this->user);
                return;
            }
            if ($usersNumber >= 5001) {
                FileLimit::dispatch($this->user);
                return;
            }

            $rules = (new ImportUserRequest())->rules();
            $validData = [];
            foreach ($users as $key => $user) {
                if ($key == 0) {
                    continue;
                }
                $userData = $this->sampleData($user);
                $validator = Validator::make($userData, $rules);
                if (!$validator->fails()) {
                    $validData[] = $userData;
                } else {
                    $isError = true;
                    $user[] = trim($this->formatValidateMessage($validator));
                    $fileContent[] = $user;
                }
            }

            if (!empty($validData)) {
                $this->processDataValid($validData);
            }

            if ($isError) {
                FileWarning::dispatch($this->user);

                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $fullPath = exportFile($fileContent, $sheet, $spreadsheet, $this->user);

                SendMailAndDeleteFileJob::dispatch(
                    $this->user->email,
                    MailTypeEnum::ImportMail->value,
                    $fullPath
                );
            } else {
                ImportSuccess::dispatch($this->user);
            }
        } catch (\Throwable $th) {
            Log::info($th);
        }
    }

    private function sampleData($user)
    {
        return [
           'name' => $user[0],
           'email' => $user[1],
           'description' => $user[2],
           'type' => $user[3],
           'avatar' => AppConst::DEFAULT_AVATAR->value,
           'password' => AppConst::DEFAULT_PASSWORD->value,
        ];
    }

    private function formatValidateMessage($validator)
    {
        $errorContent = '';
        foreach ($validator->errors()->toArray() as $value) {
            foreach ($value as $error) {
                $errorContent .= "$error\n";
            }
        }
        return $errorContent;
    }

    private function loadUserFromFile($path)
    {
        $path = storage_path('app/public/' . $path);
        $reader = new Xlsx();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();

        $rows = [];
        foreach ($sheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = trim($cell->getValue());
            }
            if (collect($rowData)->filter()->isEmpty()) {
                continue;
            }
            $rows[] = $rowData;
        }

        return $rows;
    }

    private function processDataValid($validData)
    {
        foreach (array_chunk($validData, 1000) as $user) {
            DB::table('users')->insert($user);
        }
    }
}
