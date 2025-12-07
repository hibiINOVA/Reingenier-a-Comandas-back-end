<?php

namespace App\Services;
use Config\Database\Methods as db;
use Config\Jwt\Jwt;
use Config\Utils\Utils;
use Config\Utils\CustomException as excep;

class AuthService
{
    public static function sign_in(string $name, string $password){
        $query = (object)[
            "query" => "SELECT * FROM users WHERE name = ? and rol NOT IN (5);",
            "params" => [$name]
        ];
        $res = db::query_one($query);
        if ($res->error) throw new excep("004");
        $msj = $res->msg;
        if(!Utils::verify($password, $msj->password)) throw new excep("005");

        return (object)["error"=>false, "msg"=>$msj];
    }
    public static function sign_up(
        string $name,
        string $password,
        string $id,
        string $phone,
        int $rol
    ){
        $query = (object)[
            "query" => "INSERT INTO users 
                (idusers, name, password, phone, rol, actual_order) 
                VALUES (?, ?, ?, ?, ?, ?)",
            "params" => [
                $id,
                $name,
                $password,
                $phone,
                $rol,
                null
            ]
        ];

        return db::save($query);
    }
}
