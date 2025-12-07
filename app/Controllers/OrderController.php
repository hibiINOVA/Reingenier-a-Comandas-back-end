<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Services\OrderService;

class OrderController
{
    public function createOrder($data){
        echo json_encode(OrderModel::createOrder($data->total, $data->origin, $data->comments, $data->client, $data->users_idusers, $data->order_details));
    }
    
    public function viewOrders(){
        echo json_encode(OrderModel::viewOrders());
    }
    
    public function viewOrder($data)
    {
        // validar params
        if (!isset($data->params->id)) {
            echo json_encode(["error" => true, "msg" => "Falta id en la ruta"]);
            return;
        }

        $idOrder = $data->params->id;

        $response = OrderService::viewOrder($idOrder);
        echo json_encode($response);
    }

    
    public function updateStatus($data){
        // ✅ FORZAR salida de logs a pantalla temporalmente
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        
        // ✅ DEBUGGING COMPLETO
        $debug = [
            'tipo_data' => gettype($data),
            'es_objeto' => is_object($data),
            'es_array' => is_array($data),
            'data_completa' => $data,
            'status_existe' => isset($data->status),
            'status_valor' => $data->status ?? 'NO EXISTE',
            'status_tipo' => gettype($data->status ?? null),
        ];
        
        error_log("==================== DEBUG updateStatus ====================");
        error_log(json_encode($debug, JSON_PRETTY_PRINT));
        error_log("============================================================");
        
        // ✅ VALIDACIÓN: Verificar que status exista
        if (!isset($data->status)) {
            error_log("ERROR: status no está definido en data");
            echo json_encode(['error' => true, 'msg' => 'Status no enviado']);
            return;
        }
        
        // ✅ SOLUCIÓN NUCLEAR: Convertir AGRESIVAMENTE
        if (!isset($data->status)) {
            echo json_encode(['error' => true, 'msg' => 'Status no enviado', 'debug' => $debug]);
            return;
        }
        
        // Probar TODAS las formas posibles de conversión
        $status_raw = $data->status;
        
        // Intentar conversión múltiple
        if (is_string($status_raw)) {
            error_log("STATUS ES STRING, convirtiendo...");
            $status = (int)$status_raw;
        } elseif (is_numeric($status_raw)) {
            error_log("STATUS ES NUMÉRICO, convirtiendo...");
            $status = (int)$status_raw;
        } else {
            error_log("STATUS YA ES INT");
            $status = $status_raw;
        }
        
        error_log("Status convertido: $status (tipo: " . gettype($status) . ")");
        
        // ✅ VALIDACIÓN
        if (!in_array($status, [1, 2, 3, 4], true)) {
            echo json_encode([
                'error' => true, 
                'msg' => "Status inválido",
                'debug' => $debug,
                'status_convertido' => $status,
                'tipo_convertido' => gettype($status)
            ]);
            return;
        }
        
        error_log("✅ Status VÁLIDO: $status");
        error_log("===========================================================");
        
        // ✅ FORZAR tipo INT explícitamente antes de pasar al modelo
        $statusInt = (int)$status;
        $idorder = (string)$data->idorder;
        $users_idusers = (string)$data->users_idusers;
        
        error_log("Llamando OrderModel::updateStatus con status=$statusInt (tipo: " . gettype($statusInt) . ")");
        
        try {
            $result = OrderModel::updateStatus($statusInt, $idorder, $users_idusers);
            echo json_encode($result);
        } catch (\TypeError $e) {
            error_log("TypeError capturado: " . $e->getMessage());
            echo json_encode([
                'error' => true,
                'msg' => $e->getMessage(),
                'debug' => $debug,
                'statusInt' => $statusInt,
                'tipo_statusInt' => gettype($statusInt)
            ]);
        }
    }
    
    
    public function lastOrder($data){
        echo json_encode(OrderModel::lastOrder($data->iduser));
    }
}
?>