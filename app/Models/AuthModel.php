<?php
namespace App\Models;

use Config\Jwt\Jwt;
use Config\Utils\Utils as util;
use App\Services\AuthService;
use Config\Utils\CustomException as excep;

class AuthModel{

    public static function sign_in(string $name, string $password){
        $res = AuthService::sign_in($name, $password);
        if($res->error) throw new excep("004");

        $msg = $res->msg;
        unset($msg->password);

        return [
            "error" => false,
            "msg" =>[
                "idusers"=>$msg->idusers,
                "name"=>$msg->name,
                "token"=>Jwt::SignIn($msg),
                "phone"=>$msg->phone,
                "rol"=>$msg->rol,
                "actual_order"=>$msg->actual_order
            ]
        ];
    }

    public static function sign_up(string $name, string $password, string $phone, int $rol){
        $id = util::uuid();
        $pass_hash = util::hash($password);

        $res = AuthService::sign_up($name, $pass_hash, $id, $phone, $rol);

        if ($res["error"]) throw new excep("003");

        return [
            "error" => false,
            "msg" => "Usuario registrado correctamente",
            "data" => [
                "id" => $id,
                "name" => $name,
                "phone" => $phone,
                "rol" => $rol
            ]
        ];
    }
}
?>
