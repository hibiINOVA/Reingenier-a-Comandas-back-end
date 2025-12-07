<?php

namespace App\Controllers;

use App\Services\FavoritosService;
use Config\Utils\CustomException as ex;

class FavoritosController
{
    public function toggle($req)
{
    try {
        // 🛑 CORRECCIÓN: Acceder directamente a las propiedades del objeto $req
        $userId    = $req->user_id    ?? null;
        $productId = $req->product_id ?? null;

        // Validación básica
        if (!$userId || !$productId) {
            // Asegúrate de usar la clase de excepción correcta si es 'ex' o 'exc'
            throw new ex("001"); // missing_data_or_empty_body
        }

        // 1. Llamar al servicio (El servicio ya está verificado)
        $res = FavoritosService::toggle($userId, $productId);

        // 2. Retornar la respuesta (res es un objeto: {favorite: bool, msg: string})
        return [
            "error"    => false,
            "favorite" => $res->favorite,
            "msg"      => $res->msg
        ];

    } catch (ex $e) {
        return $e->response();
    }
}

    public function list($req)
    {
        try {
            $userId = $req->params->id ?? null;
            
            if (!$userId) {
                throw new ex("007"); 
            }

            $items = FavoritosService::listByUser($userId);

            return [
                "error" => false,
                "favorites" => $items
            ];

        } catch (ex $e) {
            return $e->response();
        }
    }
}
