<?php

namespace App\Services;

use App\Enums\AppConst;
use App\Http\Requests\ImportUserRequest;
use App\Http\Requests\UserCreateRequest;
use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Facades\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UserService
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getUser($data, $currentUser)
    {  
        $users = $this->userRepository->getAllUser($data, $currentUser);
        return collect($users);

        /**
         * 
         * REMOVE MEILISEARCH
         * 
         */

        // if (!empty($data['key_word'])) {
        //     $query = User::search($data['key_word']);
        //     $filters = [];
        //     if (!empty($data['advance_search'])) {
        //         if (!empty($data['created_from'])) {
        //             $timestampFrom = strtotime($data['created_from']);
        //             $filters[] = 'created_at >= ' . $timestampFrom;
        //         }
        //         if (!empty($data['created_to'])) {
        //             $timestampTo = strtotime($data['created_to']);
        //             $filters[] = 'created_at <= ' . $timestampTo;
        //         }
        //     }
        //     if (!empty($filters)) {
        //         $query->filter(implode(' AND ', $filters));
        //     }
        //     $users = $query->paginate($this->perPage, 'page', (int)$this->page);
        // } else {
        //     $users = User::where('id', '!=', $currentUser->id)
        //         ->paginate($this->perPage, ['*'], 'page', $this->page);
        // }
        // if(array_key_exists('key_word', $data) && $data['key_word']){
        //     $query = User::search($data['key_word']);
        //     if($data['advance_search']){
        //         if(array_key_exists('created_from', $data) && $data['created_from']){
        //             $timeStampFrom = strtotime($data['created_from']);
        //             $filters[] = 'created_at > '.$timeStampFrom;
        //         }
        //         if(array_key_exists('created_to', $data) && $data['created_to']){
        //             $timeStampTo = strtotime($data['created_to']);
        //             $filters[] = 'created_at < '.$timeStampTo;
        //         }
        //         if(!empty($filters)){
        //             $query->filter(implode(' AND ', $filters));
        //         }
        //         $users = $query->paginate($this->perPage, 'page', $this->page);
        //     }
        // } else {
        //     $users = User::where('id', '!=', $currentUser->id)
        //         ->paginate($this->perPage, ['*'], 'page', $this->page);
        // }
        // $query = User::whereNotIn('id', [$currentUser->id]);;
        // if(array_key_exists('key_word', $data) && $data['key_word']){
        //     $query = $this->searchByKeyWord($data['key_word'], $query);
        // }
        // if($data['advance_search'] == true){
        //     if(array_key_exists('created_from', $data) && $data['created_from']){
        //         $query = $this->filterByCreateFrom($data['created_from'], $query);
        //     }
        //     if(array_key_exists('created_to', $data) && $data['created_to']){
        //         $query = $this->filterByCreateTo($data['created_to'], $query);
        //     }
        // }
        // $users = $query->paginate(
        //     $this->perPage,  // $perPage
        //     ['*'],           // $columns
        //     'page',          // $pageName
        //     $this->page      // $page
        // );
    }

    public function massUpdateUser($data)
    {
        $this->userRepository
            ->massEditUser($data);
        return 1;
    }

    public function massDeleteUser($data)
    {
        $this->userRepository
            ->massDeleteUser($data);
        return 1;
    }

    public function updateSpecificUser($user,$data)
    {
        try {
            if(array_key_exists('avatar', $data) && $data['avatar'] instanceof UploadedFile){
                $path = storeFile("avatars/$user->id", $data['avatar']);
                $data['avatar'] = getFileUrl($path);
            }
            $this->userRepository->updateSpecificUser($user, $data);
            return 1;
        } catch (\Throwable $th) {
            throw $th;
        }
        
    }

    public function deleteSpecificUser($user)
    {
        $this->userRepository
            ->deleteSpecificUser($user);
        return 1;
    }

    public function createNewUser($data)
    {
        try {
            if(array_key_exists('avatar', $data) && $data['avatar']){
                $path = storeFile('avatar', $data['avatar']);
                $data['avatar'] = getFileUrl($path);
            }
            return $this->userRepository->createUser($data);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function loadUserFromFile($file)
    {
        $path = $file->getRealPath();
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        return array_filter($sheet->toArray(), function($data){
            foreach($data as $field){
                if(trim($field) != ''){
                    return true;
                } else {
                    return false;
                }
            }
        });
    }

    public function import($data)
    {
        $isError = false;
        $header = ['Name', 'Email', 'Description', 'Type'];
        $fileContent = [$header,];
        $users = json_decode($data['user']);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        try {
            foreach ($users as $key => $user) {
                if($key == 0) continue;
                $rules = (new ImportUserRequest())->rules();
                $userData = $this->sampleData($user);
                $validator = Validator::make($userData, $rules);
                if(!$validator->fails()){
                    $this->userRepository->createUser($userData);
                } else {
                    $isError = true;
                    $user[] = trim($this->formatValidateMessage($validator));
                    $fileContent[] = $user;
                }
            }
            if($isError){
                $fullPath = $this->exportErrorFile($fileContent, $sheet, $spreadsheet);
                return $fullPath;
            } else {
                return null;
            }
        } catch (\Throwable $th) {
            throw $th;
            return false;
        }
    }

    public function getSampleFilePath()
    {
        return public_path('/sample/sample_file.xlsx');
    }

    private function exportErrorFile($fileContent, $sheet, $spreadsheet)
    {
        $userId = Auth::user()->id;
        $fullPath = storage_path('app/public/temp/'.$userId.'/error_file.xlsx');
        $row = 1;
        foreach ($fileContent as $record) {
            $sheet->fromArray($record, NULL, 'A' . $row++);
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save($fullPath);
        
        return $fullPath;
    }

    private function sampleData($user)
    {
        return [
            'name' => $user[0],
            'email' => $user[1],
            'description' => $user[2],
            'type' => $user[3],
            'avatar' => AppConst::DEFAULT_AVATAR->value,
            'password' => '123456789',
        ];
    }

    private function formatValidateMessage($validator)
    {
        $errorContent = '';
        foreach ($validator->errors()->toArray() as $value) {
            foreach($value as $error){
                $errorContent .= "$error\n";
            }
        }
        return $errorContent;
    }
}
