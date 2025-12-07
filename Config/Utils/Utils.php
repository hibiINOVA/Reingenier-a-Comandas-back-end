<?php

namespace Config\Utils;

use Ramsey\Uuid\Uuid as uuid;

class Utils
{
    //Funcion para crear los id con la librería
    public static function uuid()
    {
        return uuid::uuid4();
    }
    //funcion para encriptar la contraseña
    public static function hash(string $password)
    {
        return password_hash($password, PASSWORD_DEFAULT, ['cost' => 8]);
    }
    //funcion que verifica la contraseña
    public static function verify(string $pass_orign, string $pass_hash)
    {
        return password_verify($pass_orign, $pass_hash);
    }
    public static function hasEmptyParamsAlt(array $params)
    {
        return count(array_filter($params, function($param) {
            return $param === null || $param === '' || !isset($param);
        })) > 0;
    }
    //Obtener la ip
    public static function get_ip()
    {
        $mainIp = '';
        if (getenv('HTTP_CLIENT_IP'))
            $mainIp = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $mainIp = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $mainIp = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $mainIp = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $mainIp = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $mainIp = getenv('REMOTE_ADDR');
        else
            $mainIp = 'UNKNOWN';
        return $mainIp;
    }
}
