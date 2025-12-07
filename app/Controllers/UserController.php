<?php

namespace App\Controllers;

use App\Services\UserServices;
use Config\Utils\CustomException as ex;

class UserController
{
    private $service;

    public function __construct()
    {
        $this->service = new UserServices();
    }
    /* ============================================================
        OBTENER TODOS
    ============================================================ */
    public function getAll()
    {
        try {
            $res = $this->service->getAll();

            return [
                "error" => false,
                "msg" => $res["msg"]
            ];
        } catch (ex $e) {
            return $e->response();
        }
    }


    /* ============================================================
        OBTENER POR ID
    ============================================================ */
    public function getById($req)
    {
        try {
            $id = $req->params->id ?? null;
            if (!$id) throw new ex("001");

            $res = $this->service->getById($id);

            if ($res["error"]) throw new ex("002");

            return [
                "error" => false,
                "msg"   => $res["msg"] ?? "Usuario obtenido",
                "data"  => $res["data"] ?? null
            ];

        } catch (ex $e) {
            return $e->response();
        }
    }




    /* ============================================================
        OBTENER POR ROL
    ============================================================ */
    public function getByRole($req)
    {
        try {
            $role = $req->params["role"] ?? null;
            if ($role === null) throw new ex("001");

            $res = $this->service->getByRole((int)$role);

            return [
                "error" => false,
                "msg" => $res["msg"]
            ];
        } catch (ex $e) {
            return $e->response();
        }
    }


    /* ============================================================
        ACTUALIZAR
    ============================================================ */
    public function update($req)
    {
        try {
            // params siempre está en ->params (stdClass)
            $id = $req->params->id ?? null;

            // El body NO está en $req->body... está DIRECTO:
            // O sea: name, phone, rol están en las propiedades raíz de $req
            $data = [
                "name"  => $req->name  ?? null,
                "phone" => $req->phone ?? null,
                "rol"   => $req->rol   ?? null
            ];

            // Limpia los nulos
            $data = array_filter($data, fn($v) => $v !== null);

            if (!$id || empty($data)) throw new ex("001");

            $res = $this->service->update($id, $data);

            return [
                "error" => false,
                "msg" => $res["msg"]
            ];

        } catch (ex $e) {
            return $e->response();
        }
    }

    /* ============================================================
        ELIMINAR
    ============================================================ */
    public function delete($req)
    {
        try {
            // Extrae 'id' desde el body
            $id = $req->id ?? null; 
            
            if (!$id) {
                throw new ex("001"); // ID faltante
            }

            $res = $this->service->delete($id);

            return [
                "error" => false,
                "msg" => $res["msg"]
            ];
        } catch (ex $e) {
            return $e->response();
        }
    }
}
