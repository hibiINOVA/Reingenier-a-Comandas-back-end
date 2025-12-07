<?php

namespace App\Services;

use App\Models\FavoritosModel;
use Exception;

class FavoritosService
{
    public static function toggle($userId, $productId)
    {
        // Ver si ya existe
        $exists = FavoritosModel::isFavorite($userId, $productId);

        if ($exists->error) {
            throw new Exception("error_checking_favorite");
        }

        // Si ya existe → eliminar
        if ($exists->msg !== null) {
            $res = FavoritosModel::delete($userId, $productId);

            if (is_object($res) && $res->error) {
                throw new Exception("error_removing_favorite");
            }

            return (object)[
                "favorite" => false,
                "msg"      => "removed"
            ];
        }

        // Si no existe → agregar
        $res = FavoritosModel::add($userId, $productId);

        if (is_object($res) && $res->error) {
            throw new Exception("error_adding_favorite");
        }

        return (object)[
            "favorite" => true,
            "msg"      => "added"
        ];
    }

    public static function listByUser($userId)
    {
        $sql         = (object)[
            // ✅ CORRECCIÓN FINAL: Usando products_idproducts y users_idusers
            "query"  => "SELECT products_idproducts FROM favorites WHERE users_idusers = :id",
            "params" => [":id" => $userId]
        ];

        $res = \Config\Database\Methods::query($sql);

        if ($res->error) {
            $db_error_message = is_string($res->msg) ? $res->msg : "Unknown DB Error";
            
            throw new Exception("error_fetching_favorites: " . $db_error_message);
        }
        
        // Devuelve el array de objetos con los IDs de productos favoritos
        return $res->msg;
    }
}
