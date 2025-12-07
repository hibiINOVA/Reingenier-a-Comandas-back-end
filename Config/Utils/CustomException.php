<?php
    namespace Config\Utils;
    
    use Exception;

    // Hereda lo que tiene la liberia de php
    class CustomException extends Exception
    {

    // Opcion predeterminada para mensaje de error.
    private $_options = "unknown_error";

    // Opcion predeterminada para mensaje de error.
    private $_error_code = "000";

    // Constructor de la clase $errorCode Código(s) de error para buscar en el catálogo de mensajes.
    public function __construct(...$errorCode)
    {
        // El constructor necesita llamar a parent::__construct()
        // o manejar el código/mensaje según la lógica del framework.
        // Aquí solo ajustamos la lógica de mensaje:
        
        if (!isset($errorCode[0])) return;
        if (!array_key_exists($errorCode[0], self::MESSAGE_CATALOGUE)) return;
        
        // El mensaje de la excepción de PHP se establece aquí:
        parent::__construct(self::MESSAGE_CATALOGUE[$errorCode[0]], $errorCode[0]);
        
        $this->_options = self::MESSAGE_CATALOGUE[$errorCode[0]]; 
        $this->_error_code = $errorCode[0]; 
    }
    
    public function response()
    {
        return $this->GetOptions();
    }

    // Obtener opciones de mensaje de error. Un array con información sobre el error, incluyendo indicador de error y mensaje
    public function GetOptions() 
    { 
        return [
            "error" => true, 
            "msg" => $this->_options, 
            "error_code" => $this->_error_code
        ]; 
    }
    
    // Catálogo de mensajes de error.
    const MESSAGE_CATALOGUE = [
        // ✅ CORRECCIÓN: 001 ahora significa datos faltantes
        "001" => "missing_data_or_empty_body", 
        
        "002" => "incorrect_class",
        "003" => "method_not_exist",
        "006" => "not_token",
        
        // ⚠️ Nota: Si necesitas el mensaje "incorrect_request_method" para routing, 
        // deberías asignarlo a un código de error de routing, por ejemplo, "010".
        "010" => "incorrect_request_method", 

        "007" => "missing_required_parameter", // Ajustado de empty_params
        
        // Creat Order
        "008" => "incorrect_insert",
        "009" => "incorrect_update",
        // Login
        "004" => "no_user",
        "005" => "invalid_credentials"
    ];
    }
?>