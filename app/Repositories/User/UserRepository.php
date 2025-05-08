<?php

namespace App\Repositories\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserRepository implements UserRepositoryInterface
{
    public function getAllUser($perPage, $page, $user)
    {
        return User::where('id', '!=', $user->id)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function massEditUser($data)
    {   
        DB::beginTransaction();
        try {
            // Solution 1
            // Before index: : 1.82s
            // After index: 1.74s
            User::where('type', $data['account_type'])
            ->update([
                'description' => $data['description'],
            ]);

            
            // Solution 2
            // Before index: 1.76s
            // After index: 1.68s
            // DB::table('users')
            //     ->where('type', $data['account_type'])
            //     ->update(['description' => $data['description']]);
            DB::commit();
            return 1;
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }

    public function massDeleteUser($data)
    {
        DB::beginTransaction();
        try {
            $userIds = [];
            foreach($data['userCheck'] as $key => $userIdsPage){
                if($userIdsPage == null || $userIdsPage == []) continue;
                $userIds = array_merge($userIds, $userIdsPage);
            }
            User::whereIn('id', $userIds)->delete();
            DB::commit();
            return 1;
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }

    public function updateSpecificUser($user, $data)
    {
        DB::beginTransaction();
        try {
            $user->update($data);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }
    
    public function deleteSpecificUser($user)
    {
        DB::beginTransaction();
        try {
            $user->delete();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }
}