<?php

namespace App\Models;

use Config\Database\Methods as db;
use Config\Utils\Utils as util;
use Config\Utils\CustomException as excep;

class UserModel
{
    /* ============================================================
        OBTENER TODOS
    ============================================================ */
    public static function get_all()
    {
        $query = (object) [
            "query" => "SELECT idusers, name, phone, rol, actual_order, created_at, updated_at 
                        FROM users;",
            "params" => []
        ];

        $res = db::query($query);
        if ($res->error) throw new excep("004");

        return [
            "error" => false,
            "msg" => $res->msg
        ];
    }


    /* ============================================================
        OBTENER POR ID
    ============================================================ */
    public static function get_by_id(string $id)
    {
        $query = (object) [
            "query" => "SELECT idusers, name, phone, rol, actual_order, created_at, updated_at
                        FROM users WHERE idusers = ?;",
            "params" => [$id]
        ];

        $res = db::query_one($query);
        if ($res->error) throw new excep("004");

        if (!$res->msg) throw new excep("006"); // no encontrado

        return [
            "error" => false,
            "msg" => $res->msg
        ];
    }


    /* ============================================================
        OBTENER POR ROL
    ============================================================ */
    public static function get_by_rol(int $rol)
    {
        $query = (object) [
            "query" => "SELECT idusers, name, phone, rol, actual_order, created_at, updated_at
                        FROM users WHERE rol = ?;",
            "params" => [$rol]
        ];

        $res = db::query($query);
        if ($res->error) throw new excep("004");

        return [
            "error" => false,
            "msg" => $res->msg
        ];
    }


    /* ============================================================
        ACTUALIZAR
    ============================================================ */
public static function update(string $id, array $data)
    {
        // Construcción dinámica (solo actualiza lo que mandas)
        $fields = [];
        $params = [];

        // ✅ Se añade el campo 'password' aquí y se aplica el hash
        if (isset($data["password"])) {
            // Usa tu función util::hash() para encriptar la nueva contraseña
            $fields[] = "password = ?";
            $params[] = util::hash($data["password"]);
        }
        // Fin de la corrección para la contraseña

        if (isset($data["name"])) {
            $fields[] = "name = ?";
            $params[] = $data["name"];
        }

        if (isset($data["phone"])) {
            $fields[] = "phone = ?";
            $params[] = $data["phone"];
        }

        if (isset($data["rol"])) {
            $fields[] = "rol = ?";
            $params[] = $data["rol"];
        }

        if (isset($data["actual_order"])) {
            $fields[] = "actual_order = ?";
            $params[] = $data["actual_order"];
        }

        if (count($fields) === 0) {
            throw new excep("001"); // nada que actualizar
        }

        $params[] = $id;

        $query = (object) [
            "query" => "UPDATE users SET " . implode(", ", $fields) . " WHERE idusers = ?;",
            "params" => $params
        ];

        $res = db::save($query);
        if ($res["error"]) throw new excep("003");

        return ["error" => false, "msg" => "Usuario actualizado"];
    }

    /* ============================================================
        ELIMINAR
    ============================================================ */
    public static function delete(string $id)
    {
        $query = (object) [
            "query" => "DELETE FROM users WHERE idusers = ?;",
            "params" => [$id]
        ];

        $res = db::save($query);
        if ($res["error"]) throw new excep("003");

        return ["error" => false, "msg" => "Usuario eliminado"];
    }
}

