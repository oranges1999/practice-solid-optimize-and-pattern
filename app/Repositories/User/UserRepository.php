<?php

namespace App\Repositories\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserRepository implements UserRepositoryInterface
{
    public function getAllUser($perPage, $page)
    {
        return User::paginate($perPage, ['*'], 'page', $page);
    }

    public function massEditUser($data)
    {   
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

        return 1;
    }

    public function massDeleteUser($data)
    {
        $userIds = [];
        foreach($data['userCheck'] as $key => $userIdsPage){
            if($userIdsPage == null || $userIdsPage == []) continue;
            $userIds = array_merge($userIds, $userIdsPage);
        }
        User::whereIn('id', $userIds)->delete();
        return 1;
    }
}
