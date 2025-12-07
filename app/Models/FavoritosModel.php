<?php

namespace App\Models;

use Config\Database\Methods;
use Exception;
use stdClass;

class FavoritosModel
{
    public static function isFavorite($userId, $productId)
    {
        $sql = (object)[
            // ✅ Usar los nombres de columna correctos
            "query" => "SELECT idfavorite FROM favorites WHERE users_idusers = :uid AND products_idproducts = :pid",
            "params" => [
                ":uid" => $userId, 
                ":pid" => $productId
            ]
        ];
        
        // 🛑 Usar query_one (o el método que devuelve una sola fila)
        $res = \Config\Database\Methods::query_one($sql); 
        
        // Si hay error en la DB, query_one devuelve {error: true, msg: "DB_ERROR:..."}
        return $res;
    }

    // app/Models/FavoritosModel.php

    public static function add($userId, $productId)
    {
        $id = self::uuid();
        $sql         = new stdClass();
        // 🛑 CORRECCIÓN: Cambiado 'user_id' a 'users_idusers' y 'product_id' a 'products_idproducts'
        $sql->query  = "INSERT INTO favorites (idfavorite, users_idusers, products_idproducts) VALUES (:id, :uid, :pid)";
        $sql->params = [
            ":id"  => $id,
            ":uid" => $userId,
            ":pid" => $productId
        ];
        return Methods::save($sql);
    }

    public static function delete($userId, $productId)
    {
        $sql         = new stdClass();
        // 🛑 CORRECCIÓN: Cambiado 'user_id' a 'users_idusers' y 'product_id' a 'products_idproducts'
        $sql->query  = "DELETE FROM favorites WHERE users_idusers = :uid AND products_idproducts = :pid";
        $sql->params = [":uid" => $userId, ":pid" => $productId];
        return Methods::save($sql);
    }

    private static function uuid()
    {
        return bin2hex(random_bytes(16));
    }
}
