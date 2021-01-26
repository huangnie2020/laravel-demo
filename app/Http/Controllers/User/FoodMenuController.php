<?php

namespace App\Http\Controllers\User;

use App\Services\Mr\MenuService;

use App\Http\Controllers\Controller;

class FoodMenuController extends Controller
{

    public function index(MenuService $m)
    {

        $result = $m->create(
            $weight = -2,
            $height = -2,
            MenuService::PLAN_REDUCE_WEIGHT_MORE
        );
        print_r($result);
        
        $result = $m->update(
                     array (
                        'breakfast' => array (
                            'meat' => 120,
                            'water' => 666,
                            'text' => '6-10点：鸡蛋1、牛奶/酸奶/豆浆250ml、蒸包子1个-2个【适量即可】
                            上午喝水：666ml'
                        ),
                        'lunch' => array (
                            'meat' => 120,
                            'water' => 888,
                            'text' => '11点-13点：米饭小半碗--1碗【适量即可】、白菜+菠菜150g-200g、牛肉120g左右，做饭清炒即可
                            下午：苹果200-300g，女生拳头大小
                            下午喝水： 888ml',
                        ),
                        'dinner' => array (
                            'meat' => 120,
                            'water' => 444,
                            'text' => '6-10点：鸡蛋1、牛奶/酸奶/豆浆250ml、蒸包子1个-2个【适量即可】
                            上午喝水：666ml',
                        ),
                    ),
                    -200,
                    MenuService::PLAN_REDUCE_WEIGHT_LESS,
                );
        print_r($result);
    }

}