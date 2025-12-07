<?php

namespace App\Services;

use App\Models\UserModel;

class UserServices
{

    public function getAll()
    {
        return UserModel::get_all();
    }

    public function getById(string $id)
{
    return UserModel::get_by_id($id);
}

    public function getByRole(int $role)
    {
        return UserModel::get_by_rol($role);
    }

    public function update(string $id, array $data)
    {
        return UserModel::update($id, $data);
    }

    public function delete(string $id)
    {
        return UserModel::delete($id);
    }
}
