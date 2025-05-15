<?php

namespace App\Jobs;

use App\Enums\ExportTypeEnum;
use App\Enums\MailTypeEnum;
use App\Enums\UserTypeExportEnum;
use App\Mail\ExportMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ExportUsersToXlsx implements ShouldQueue
{
    use Queueable;

    private $userType;
    private $exportType;
    private $userIds;
    private $currentUser;

    public $timesout = 180;

    /**
     * Create a new job instance.
     */
    public function __construct($currentUser, $userType, $exportType, $userIds)
    {
        $this->currentUser = $currentUser;
        $this->userType = $userType;
        $this->exportType = $exportType;
        $this->userIds = $userIds;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $query = User::where('id', '!=', $this->currentUser->id);
        if($this->userType == UserTypeExportEnum::SELECTED->value){
            $query = $query->whereIn('id', $this->userIds);
        } 

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $header = ['Name', 'Email', 'Description', 'Type', 'Avatar'];
        $fileContent[] = [$header,];
        $query->chunk(500, function($users) use (&$fileContent) {
            foreach($users as $key => $user){
                $fileContent[] = [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'description' => $user['description'],
                    'type' => $user['type'],
                    'avatar' => $user['avatar'],
                ];
            }
        });
        $filePath = exportFile($fileContent, $sheet, $spreadsheet, $this->currentUser);
        SendMailAndDeleteFileJob::dispatch(
            $this->currentUser->email, 
            MailTypeEnum::ExportMail->value, 
            $filePath
        );
        if($this->exportType == ExportTypeEnum::EXPORT_AND_DELETE->value){
            if($this->userType == UserTypeExportEnum::SELECTED->value){
                User::whereIn('id', $this->userIds)->delete();
            }
            if($this->userType == UserTypeExportEnum::ALL->value){
                User::where('id', '!=', $this->currentUser->id)->delete();
            }
        }
        // dd($users);
    }
}
