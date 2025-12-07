<?php

    namespace Config\Database;
    //Protocolo para la conexion a la bd que nos ayuda a que no nos hagan inyecciones
    use PDO;
    use \PDOException;
    class Connection
    {
        private static $host = "127.0.0.1:3307";
        private static $db_name = "db_restaurant";
        private static $user_name = "root";
        private static $password = "root";

        public static function conection()
        {
            try
            {
                return new PDO('mysql:host='.self::$host.';dbname='.self::$db_name, self::$user_name, self::$password);
            }
            catch(PDOException $e)
            {
                return null;
            }
        }
    }
?>