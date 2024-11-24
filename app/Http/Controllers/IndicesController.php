<?php

namespace App\Http\Controllers;

use App\Classes\Indices;

class IndicesController extends Controller
{
    public function index()
    {
        /*$indice = new Indices('ipca');
        $indice->updateData();*/
        echo Indices::getFatorIndiceBacen('ipca','03/2019','12/2023');

    }
}
