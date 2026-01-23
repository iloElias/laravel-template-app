<?php

namespace App\Services;

use App\Models\Hr\User;

class UserQueryService
{
    /**
     * Returns the first user that matches the search criteria.
     *
     * @param array $query Search data (id, telephone, name)
     *
     * @return null|User
     */
    public function getUser(array $query)
    {
        $userQuery = User::query();

        if (!empty($query['id'])) {
            $userQuery->where('id', $query['id']);
        } elseif (!empty($query['telephone'])) {
            $userQuery->where('number', $query['telephone']);
        } elseif (!empty($query['name'])) {
            $userQuery->where('name', 'like', '%' . $query['name'] . '%');
        }

        return $userQuery->first();
    }

    /**
     * Returns summarized user information.
     *
     * @return null|User
     */
    public function getInfo(string $uuid)
    {
        return User::where(['uuid' => $uuid])->first();
    }

    /**
     * Checks if a user exists by number.
     *
     * @return null|User
     */
    public function exists(string $number)
    {
        return User::where('number', $number)->first();
    }
}
