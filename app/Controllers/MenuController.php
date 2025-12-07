<?php

namespace App\Controllers;

use App\Services\MenuService;
use Config\Utils\CustomException as ex; // Asumiendo que usas tu CustomException

class MenuController{
    
    private $service;

    public function __construct() {
        $this->service = new MenuService();
    }
    
    public function viewIngredients(){
        try {
            // Llama directamente al Servicio, que tiene la lógica
            $res = $this->service->viewIngredients();

            // 🚨 Retornamos el array de datos. El router lo serializará a JSON.
            // Esto evita la advertencia de 'devolvió NULL'.
            return $res;

        } catch (ex $e) {
            // Manejar excepciones si el servicio las lanza
            return $e->response();
        } catch (\Exception $e) {
             // Manejar otras excepciones
             error_log("Error fatal en MenuController::viewIngredients: " . $e->getMessage());
             return ["error" => true, "msg" => "server_error", "error_code" => "500"];
        }
    }
}