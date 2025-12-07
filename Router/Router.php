<?php

namespace Router;

use Config\Utils\CustomException as exc;
use Config\Jwt\Jwt;
use App\Controllers\AuthController;
use App\Controllers\GraphicsController;
use App\Controllers\MenuController;
use App\Controllers\OrderController;
use App\Controllers\UserController;
use App\Controllers\FavoritosController;

class Router
{
    private static $routes = [
        "GET" => [
            // RUTAS ACTUALIZADAS PARA USAR PARÁMETROS DINÁMICOS
            "menu/viewIngredients" =>[MenuController::class, "viewIngredients", 1],
            "order/viewOrders" => [OrderController::class, "viewOrders", 1],
            "order/viewOrder/{id}" => [OrderController::class, "viewOrder", 1],
            "order/lastOrder" => [OrderController::class, "lastOrder", 1],
            "graphics/totalSales" => [GraphicsController::class, "totalSales", 1],
            "graphics/bestSeller" => [GraphicsController::class, "bestSeller", 1],
            "graphics/bestClient" => [GraphicsController::class, "bestClient", 1],
            "graphics/sales" => [GraphicsController::class, "sales", 1],
            "graphics/avgTime" => [GraphicsController::class, "avgTime", 1],

            "user/getAll" => [UserController::class, "getAll", 1],
            "user/getById/{id}" => [UserController::class, "getById", 1],
            "user/getByRol/{rol}" => [UserController::class, "getByRole", 1],
            "favorites/list/{id}" => [FavoritosController::class, "list", 1],
        ],
        "POST" => [
            "auth/signin" => [AuthController::class, "sign_in", 0],
            "auth/signup" => [AuthController::class, "sign_up", 0],
            "order/createOrder" => [OrderController::class, "createOrder", 1],
            "favorites/toggle" => [FavoritosController::class, "toggle", 1],
        ],
        "PUT" => [
            "order/updateStatus" => [OrderController::class, 'updateStatus', 1],
            "user/update/{id}" => [UserController::class, "update", 1],
        ],
        "DELETE" => [
            "user/delete" => [UserController::class, "delete", 1],

        ],
    ];

    /**
     * Intenta encontrar la ruta, manejando rutas estáticas y dinámicas.
     * Si encuentra una ruta dinámica, devuelve la información de la ruta y los parámetros capturados.
     * @return array [routeInfo, params]
     * @throws \Exception
     */
    private static function findRoute(String $method, String $uri): array
    {
        $uriSegments = explode('/', $uri);
        
        // 1. Intentar coincidencia estática exacta
        if (isset(self::$routes[$method][$uri])) {
            return [self::$routes[$method][$uri], new \stdClass()];
        }

        // 2. Intentar coincidencia dinámica
        foreach (self::$routes[$method] as $routePattern => $routeInfo) {
            $patternSegments = explode('/', $routePattern);
            
            // Si el número de segmentos no coincide, ignorar
            if (count($uriSegments) !== count($patternSegments)) {
                continue;
            }

            $params = new \stdClass();
            $match = true;

            // Comparar segmento por segmento
            foreach ($patternSegments as $index => $segment) {
                if (preg_match('/^\{(\w+)\}$/', $segment, $matches)) {
                    // Es un placeholder dinámico como {id} o {rol}
                    $paramName = $matches[1];
                    $params->{$paramName} = $uriSegments[$index]; // Capturar el valor
                } elseif ($segment !== $uriSegments[$index]) {
                    // No coincide con segmento estático
                    $match = false;
                    break;
                }
            }

            if ($match) {
                // Éxito: Se encontró la ruta dinámica
                error_log("ÉXITO 1: Ruta dinámica '$routePattern' coincidente. Parámetros capturados: " . json_encode($params));
                return [$routeInfo, $params];
            }
        }

        // Si no se encuentra nada
        throw new \Exception("Ruta no encontrada: " . $uri, 404);
    }


    public static function handle(String $method, String $uri, array $HEADERS)
    {
        $HEADERS = array_change_key_case($HEADERS, CASE_LOWER);
        
        error_log("=====================================");
        error_log("=== INICIO DEL MANEJO DE RUTA ===");
        error_log("Método HTTP: $method | URI recibida: '$uri'");

        try {
            // 1. VERIFICAR RUTA EN EL MAPA (ahora usa findRoute)
            list($routeInfo, $uriParams) = self::findRoute($method, $uri);
            
            $type_auth = $routeInfo[2];

            if (is_null($type_auth)) {
                 error_log("ERROR 001: Falta tipo de autenticación.");
                 throw new exc("001");
            }

            // 2. PROCESAR AUTENTICACIÓN (Lógica JWT y Simple, sin cambios)
            if ($type_auth === 0) {
                // Autenticación Simple (0)
                $expectedPass = 'd5e4ff7d77e5a8b3303ef5b48a9150f0';
                
                if (!isset($HEADERS['simple']) || $HEADERS['simple'] !== $expectedPass) {
                    error_log("ERROR 006: 'simple' header no encontrado o inválido.");
                    throw new exc('006'); // not_token o invalid auth
                }
                error_log("ÉXITO 2: Autenticación Simple (0) validada.");
            } else {
                // Autenticación JWT (1)
                if (!isset($HEADERS['authorization']) || !Jwt::Check(@$HEADERS['authorization'])) {
                    error_log("ERROR 006: JWT de autorización faltante o inválido.");
                    throw new exc('006'); // not_token
                }
                error_log("ÉXITO 2: Autenticación JWT (1) validada.");
            }

            $controllerClass = $routeInfo[0];
            $methodName = $routeInfo[1];
            
            error_log("⚙️  CLASE/MÉTODO: Clase: $controllerClass | Método: $methodName");


            // 3. VERIFICAR CLASE
            if (!class_exists($controllerClass)) {
                error_log("ERROR 002: Clase '$controllerClass' no encontrada. (PSR-4 no configurado para esta clase).");
                throw new exc("002"); // incorrect_class
            }
            error_log("ÉXITO 3: Clase del controlador existe.");

            $controllerInstance = new $controllerClass();

            // 4. VERIFICAR MÉTODO
            if (!method_exists($controllerInstance, $methodName)) {
                error_log("ERROR 003: Método '$methodName' no existe en la clase '$controllerClass'.");
                throw new exc("003"); // method_not_exist
            }
            error_log("ÉXITO 4: Método del controlador existe.");

            // 5. OBTENER DATOS Y EJECUTAR
            $requestData = self::getRequestData($method);
            
            // ANEXAR parámetros de la URL (si existen) al objeto $requestData
            $requestData->params = $uriParams;
            
            error_log("⚙️  REQUEST DATA FINAL: " . json_encode($requestData));
            error_log("⚙️  Ejecutando: $controllerClass::$methodName()...");

            $response = call_user_func([$controllerInstance, $methodName], $requestData);
            
            if ($response) {
                error_log("ÉXITO 5: Respuesta del controlador recibida. Enviando JSON al cliente.");
                echo json_encode($response);
            } else {
                error_log("⚠️ ADVERTENCIA: El controlador devolvió NULL o vacío. No se envió respuesta.");
            }
            error_log("=== FIN DEL MANEJO DE RUTA ===");


        } catch (exc $e) {
            // Excepciones controladas por CustomException
            error_log("🛑 EXCEPCIÓN CONTROLADA: Código: {$e->GetOptions()['error_code']} | Mensaje: {$e->GetOptions()['msg']}");
            echo json_encode($e->GetOptions());
        } catch (\Throwable $th) {
            // Excepciones no controladas (errores de código PHP, DB, etc.)
            error_log("💥 EXCEPCIÓN CRÍTICA NO CONTROLADA: Código: {$th->getCode()} | Mensaje: {$th->getMessage()} | Archivo: {$th->getFile()} | Línea: {$th->getLine()}");
            http_response_code(500);
            echo json_encode(["error" => true, "msg" => "Error interno del servidor: " . $th->getMessage(), "error_code" => 500]);
        }
    }
    
    // Función sin cambios (lee el body para POST/PUT/DELETE, vacío para GET)
    private static function getRequestData(String $REQUEST_METHOD)
    {
        $requestData = new \stdClass();

        // POST, PUT, DELETE, PATCH: leer body crudo
        if (in_array($REQUEST_METHOD, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $rawInput = file_get_contents("php://input");

            // Si el body viene vacío, devuelve un objeto vacío para evitar errores
            if (empty($rawInput)) {
                error_log("Input vacío, retornando objeto vacío");
                return $requestData;
            }

            $decoded = json_decode($rawInput, false);

            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Error JSON: " . json_last_error_msg());
                return $requestData;
            }

            error_log("Data decodificada del body correctamente");
            
            // Retorna el objeto decodificado, o un objeto vacío si es NULL/false
            return $decoded ?: $requestData;
        }

        // GET u otros métodos sin body
        return $requestData;
    }

}