<?php

namespace App\Services;

use App\Enums\AppConst;
use App\Enums\UserTypeExportEnum;
use App\Events\ExportRequestReceived;
use App\Events\FileReceived;
use App\Http\Requests\ImportUserRequest;
use App\Http\Requests\UserCreateRequest;
use App\Jobs\ExportUsersToXlsx;
use App\Jobs\ImportExcelJob;
use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Facades\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
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
         * REMOVE MEILISEARCH
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

    public function updateSpecificUser($user, $data)
    {
        try {
            if (array_key_exists('avatar', $data) && $data['avatar'] instanceof UploadedFile) {
                $path = storeFile("avatars/$user->id", $data['avatar']);
                $data['avatar'] = getFileUrl($path);
            }
            if ($user->avatar && Storage::disk('s3')->exists(getFilePath($user->avatar))) {
                deleteFile('s3', getFilePath($user->avatar));
            }
            $this->userRepository->updateSpecificUser($user, $data);
            return 1;
        } catch (\Throwable $th) {
            Log::info($th);
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
            if (array_key_exists('avatar', $data) && $data['avatar']) {
                $image = array_pop($data);
            }
            $user = $this->userRepository->createUser($data);
            if ($image) {
                $path = storeFile('avatars/' . $user->id, $image);
                $avatar = getFileUrl($path);
                $this->updateSpecificUser($user, ['avatar' => $avatar]);
            }
            return $user->refresh();
        } catch (\Throwable $th) {
            Log::info($th);
        }
    }

    public function import($data)
    {
        $currentUser = Auth::user();
        FileReceived::dispatch($currentUser);
        $path = Storage::disk('public')
            ->putFileAs('Imports', $data['file'], uniqid() . '_' . $data['file']->getClientOriginalName());
        ImportExcelJob::dispatch($currentUser, $path);
    }

    public function getSampleFilePath()
    {
        return public_path('/sample/sample_file.xlsx');
    }

    public function exportUsers($userType, $exportType, $userIds)
    {
        $currentUser = Auth::user();
        ExportRequestReceived::dispatch($currentUser);
        ExportUsersToXlsx::dispatch($currentUser, $userType, $exportType, $userIds);
    }
}
