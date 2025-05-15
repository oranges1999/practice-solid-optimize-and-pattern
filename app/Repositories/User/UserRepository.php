<?php

namespace App\Repositories\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserRepository implements UserRepositoryInterface
{
    public function getAllUser($data, $user, $perPage = 20, $page = 0)
    {
        $query = User::where('id', '!=', $user->id);
        if($data['advance_search']){
            if(array_key_exists('created_from', $data) && $data['created_from']){
                $query = $this->filterByCreatedAt($data['created_from'], null, $query);
            }
            if(array_key_exists('created_to', $data) && $data['created_to']){
                $query = $this->filterByCreatedAt(null, $data['created_to'], $query);
            }
        }
        if(array_key_exists('key_word', $data) && $data['key_word']){
            $query = $this->filterByKeyword($data['key_word'], $query);
        }
        return $query->paginate($perPage, ['*'], 'page', $page);
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
            User::whereIn('id', $data['userCheck'])->delete();
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

    public function createUser($data)
    {
        DB::beginTransaction();
        try {
            $user = User::create($data);
            DB::commit();
            return $user;
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }

    public function getUser($currentUser, $userIds = null)
    {
        $query = User::where('id', '!=',  $currentUser->id);
        if($userIds){
            $query = $query->whereIn('id', $userIds);
        }
        return $query->get();
    }

    public function insertUsers($users)
    {
        DB::table('users')->insert($users);
    }

    private function filterByKeyword($keyWord, $query)
    {
        return $query->where(function($q) use ($keyWord){
            $q->whereLike('name', "%$keyWord%")
            ->orWhereLike('email', "%$keyWord%")
            ->orWhereLike('description', "%$keyWord%"); 
        });
    }

    private function filterByCreatedAt($createdFrom = null, $createdTo = null, $query)
    {
        return $query->when($createdFrom, function($q) use ($createdFrom){
                $q->where('created_at', '>=', $createdFrom);
            })
            ->when($createdTo, function($q) use ($createdTo){
                $q->where('created_at', '<=', $createdTo);
            });
    }
}