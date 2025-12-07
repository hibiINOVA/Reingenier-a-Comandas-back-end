<?php

namespace Config\Database;

use Config\Database\Connection as con;

//Excepciones específicas
use Exception;
//Excepciones en general
use Throwable;
use PDO;

class Methods
{

    //Metodo para ejecutar una consulta de varias columnas
public static function query(Object $sql)
    {
        $results = []; // Inicializar results para evitar warnings si no hay filas
        try {

            $db = con::conection();
            $stmt = $db->prepare($sql->query);
            $stmt->execute($sql->params);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            
            // Usar fetchAll directamente es más eficiente para obtener todos los resultados
            // Si quieres fetchObject, asegúrate de inicializar $results
            $results = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            // Si quieres mantener el while loop, debe ser así:
            /*
            while ($row = $stmt->fetchObject()) {
                $results[] = $row;
            }
            */
            
        } catch (Throwable $th) {
            // 🛑 CORRECCIÓN CLAVE: Devolver el mensaje de la excepción de la DB
            error_log("DB_QUERY_ERROR: " . $th->getMessage()); // Loguear el error real
            return (object) [
                "error" => true, 
                "msg" => "DB_ERROR: " . $th->getMessage(), // Devolver el error real
                "error_code" => $th->getCode()
            ];
        }
        
        // Cerrar conexion con la bd (Esto es correcto)
        $db = null;
        
        // Si no se encontraron resultados, $results será un array vacío, lo cual es correcto.
        return (object) ["error" => false, "msg" => $results];
    }
    //Metodo para ejecutar una consulta de un solo resultado    
    public static function query_one(Object $obj)
    {
        try {

            $db = con::conection();
            $stmt = $db->prepare($obj->query);
            $stmt->execute($obj->params);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $results = $stmt->fetchObject();
        } catch (Throwable $th) {
            return (object) ["error" => true, "msg" => "error_query_one", "error_code" => $th->getCode()];
        }

        if ($results === false) $results = null;

        $db = null;
        return (object) ["error" => false, "msg" => $results];
    }

    //Metodo para guardar los datos en la base de datos
    public static function save(Object $obj)
    {
        $array = [];
        try {
            $db = con::conection();
            $stmt = $db->prepare($obj->query);
            $stmt->execute($obj->params);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            if ($stmt->fetchColumn()) {
                throw new Exception("query_error");
            }
            $db = null;
            $array = ["error" => false, "msg" => "querys_executed"];
        } catch (Throwable $th) {
            return (object) ["error" => true, "msg" => "error_save", "error_code" => $th->getCode()];
        }
        return $array;
    }

    //Metodo que ejecuta una transacción de consultas en una base de datos utilizando una lista de consultas preparadas
    public static function save_transaction(array $querys)
    {
        try {
            $db = con::conection();
            $db->beginTransaction();
            foreach ($querys as $obj) {
                $stmt = $db->prepare($obj->query);
                $stmt->execute($obj->params);
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                $array[] = $stmt->fetchColumn();
                if (in_array(true, $array)) {
                    throw new Exception("error_in_one_of_the_queries");
                }
            }
            $db->commit();
            $db = null;
            $array = ["error" => false, "msg" => "querys_executed"];
        } catch (Throwable $th) {
            $array = (object) ["error" => true, "msg" => "error_save_transaction", "error_code" => $th->getCode()];
        }
        return $array;
    }
}
