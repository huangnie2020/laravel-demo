<?php

namespace Tests\Feature\Mr;

use Tests\TestCase;

use App\Services\Mr\MenuService;

class MenuTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample2()
    {
        $height = 176;
        $weight = [76, 70, 63];

        $menu = [];
        $is = false;
        foreach($weight as $w) {

            if (!$is) {
                $menu = (new MenuService)->create($w, $height, 1);
        
                print_r([
                    'a'=> 'create',
                    'w' => $w,
                    'm' => $menu['lunch']['meat'],
                    'mt' => $menu['lunch']['text']
                ]);

                $is = true;

            } else {

                $menu = (new MenuService)->update($menu, 6, 1);
        
                print_r([
                    'a'=> 'update',
                    'w' => $w,
                    'm' => $menu['lunch']['meat'],
                    'mt' => $menu['lunch']['text']
                ]);
            }

        }

        $this->assertTrue(true);
    }
}