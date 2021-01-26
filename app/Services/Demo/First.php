<?php
namespace App\Services\Demo;

use App\Contracts\Demo\FirstInterface;

class First implements FirstInterface
{
    function getUsername()
    {

        return "huangnie";
    }
}