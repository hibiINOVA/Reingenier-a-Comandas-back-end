<?php

namespace App\Services;

use Config\Database\Methods as db;
use Config\Utils\Utils;

class OrderService{
    
    public static function createOrder(float $total, $origin, $comments, string $client, string $users_idusers, $order_details){
        $idOrder = Utils::uuid();
        $query[] = (object)[
            "query" => "INSERT INTO `order`(`idorder`, `total`, `origin`, `comments`, `client`, `users_idusers`) VALUES (?,?,?,?,?,?)",
            "params" =>[
                $idOrder,
                $total,
                $origin,
                $comments,
                $client,
                $users_idusers
            ]
        ];
        foreach ($order_details as $value) {
            $idOrderDetails = Utils::uuid();
            $query[] = (object)[
                "query" => "INSERT INTO `order_details`(`idorderdetail`, `unit_price`, `order_type`, `comments`, `order_idorder`, `products_idproducts`) VALUES (?,?,?,?,?,?)",
                "params" =>[
                    $idOrderDetails,
                    $value->unit_price,
                    $value->order_type,
                    $value->comments,
                    $idOrder,
                    $value->products_idproducts
                ]
            ];
            
            if (isset($value->not_ingredient) && is_array($value->not_ingredient) && count($value->not_ingredient) > 0) {
                foreach ($value->not_ingredient as $ingredient) {
                    $query[] = (object)[
                        "query" => "INSERT INTO `not_ingredient`(`ingredients_idingredients`, `order_details_idorderdetail`, `type`) VALUES (?,?,?)",
                        "params" =>[
                            $ingredient->ingredients_idingredients,
                            $idOrderDetails,
                            $ingredient->type,
                        ]
                    ];
                }
            }
        }
        $res = db::save_transaction($query);
        if($res['error'] == false){
            return ["error"=>false,
            "msg"=>$idOrder];
        }else{
            return $res;
        }
    }
    
    public static function viewOrders(){
        // ✅ CORRECCIÓN: Faltaba cerrar comillas en 'active'
        $query = (object)[
            "query" => "SELECT o.idorder, o.client, o.total, o.status, o.comments FROM `order` AS o WHERE o.active='1' ORDER BY o.date ASC",
            "params" => []
        ];
        return db::query($query);
    }
    
    public static function viewOrder(string $idOrder){
        $query = (object)[
            "query" => "SELECT o.idorder, o.client, o.total, o.status, o.comments, MONTH(o.date) as mes, od.idorderdetail, 
            od.order_type, od.comments as comments_product, p.name product, c.name category FROM `order` AS o 
            JOIN order_details od ON od.order_idorder = o.idorder 
            JOIN products p ON p.idproducts=od.products_idproducts 
            JOIN category c ON c.idcategory=p.category_idcategory WHERE o.idorder=?",
            "params" => [$idOrder]
        ];
        $res = db::query($query);

        // Validación de seguridad
        if($res->error) return $res;
        $msg = $res->msg;
        if(!is_array($msg)) return (object)["error"=>true, "msg"=>"Orden vacía"];

        foreach ($msg as $key => $value) {
            $query = (object)[
                "query" => "SELECT ni.ingredients_idingredients, ni.type, i.name FROM not_ingredient ni 
                JOIN ingredients i ON ni.ingredients_idingredients = i.idingredients WHERE order_details_idorderdetail=?",
                "params" => [$value->idorderdetail]
            ];
            $res2 = db::query($query);
            $msg[$key]->ingredients=$res2->msg;
        }
        return (object)["error"=>false, "msg"=>$msg];
    }
    
    public static function updateStatus(int $status, string $idOrder, string $users_idusers){
        $query = []; // ✅ Inicializar array vacío
        
        switch ($status) {
            case 1:
                $query[] = (object)[
                    "query" => "UPDATE `order` SET status=?, start_order=CURRENT_TIMESTAMP() WHERE idorder=?",
                    "params" => [$status, $idOrder]
                ];
                $query[] = (object)[
                    "query" => "UPDATE `users` SET actual_order=? WHERE idusers=?",
                    "params" => [$idOrder, $users_idusers]
                ];
                break;
            case 2:
                $query[] = (object)[
                    "query" => "UPDATE `order` SET status=?, finish_order=CURRENT_TIMESTAMP() WHERE idorder=?",
                    "params" => [$status, $idOrder]
                ];
                $query[] = (object)[
                    "query" => "UPDATE `users` SET actual_order=NULL WHERE idusers=?",
                    "params" => [$users_idusers]
                ];
                break;
            case 3:
                $query[] = (object)[
                    "query" => "UPDATE `order` SET status=? WHERE idorder=?",
                    "params" => [$status, $idOrder]
                ];
                break;
            case 4:
                $query[] = (object)[
                    "query" => "UPDATE `order` SET status=? WHERE idorder=?",
                    "params" => [$status, $idOrder]
                ];
                break;
        }
        return db::save_transaction($query);
    }
    
    public static function lastOrder(string $iduser){
        $query = (object)[
            "query" => "SELECT u.actual_order, o.status FROM users u JOIN `order` o ON o.idorder=u.actual_order WHERE u.idusers=?",
            "params" => [$iduser]
        ];
        $res = db::query_one($query);
        if(!$res->error && isset($res->msg->status) && $res->msg->status == 1){
            return self::viewOrder($res->msg->actual_order);
        }
        return ['error'=>false,'msg'=>null];
    }
}
?>