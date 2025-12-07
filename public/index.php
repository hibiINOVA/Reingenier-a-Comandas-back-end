<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Incluye el archivo de carga automática de clases desde la carpeta 'vendor'
require_once __DIR__ . '/../vendor/autoload.php';

// Importa la clase Router desde el namespaces 'Router'
use Router\Router;

// Establece los Headers
header("Access-Control-Allow-Origin: *");
header("Content-type: application/json; charset=utf-8");
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

// Obtiene todas los headers de la solicitud actual
$HEADERS = getallheaders();

// Obtiene la URI de la solicitud y el método HTTP utilizado
$requestUri = rute(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$httpMethod = $_SERVER['REQUEST_METHOD'];

// Manejo de preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    http_response_code(200);
    exit();
}

// Llama al método estático 'handle' de la clase 'Router' para manejar la solicitud
Router::handle($httpMethod, $requestUri, $HEADERS);

//Función para analizar la URL y extraer parte de ella.
function rute (String $url) {
    // Divide la URL en segmentos usando '/' como separador
    $parts = explode('/', $url);
    
    // Busca el índice del segmento 'public' en la URL
    $publicIndex = array_search('public', $parts);
    
    // Verifica si se encuentra 'public' y si hay un segmento siguiente después de 'public'
    if ($publicIndex !== false) {
        $routeParts = array_slice($parts, $publicIndex + 1);
         return implode('/', $routeParts);
    }

// Si no existe "public", devolvemos la ruta completa real:
    return trim($url, '/');

}

?>