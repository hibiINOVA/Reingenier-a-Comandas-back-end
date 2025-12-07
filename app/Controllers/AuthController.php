<?php

namespace App\Controllers;

use App\Models\AuthModel;
use Config\Utils\CustomException as excep;

class AuthController
{
    public function sign_in($data){
        return AuthModel::sign_in($data->name, $data->password);
    }
    
    public function sign_up($data)
    {
        if (!isset($data->name) || !isset($data->password) || !isset($data->phone) || !isset($data->rol)) {
            throw new excep("001");
        }

        return AuthModel::sign_up(
            $data->name,
            $data->password,
            $data->phone,
            $data->rol
        );
    }

}
