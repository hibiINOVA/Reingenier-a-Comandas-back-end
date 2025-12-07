<?php

namespace App\Models;

use App\Services\MenuService;

class MenuModel{
    public static function viewIngredients(){
        return MenuService::viewIngredients();
    }
}

?>