<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cidades extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $table = "cidades";

    public function indice()
    {
        return $this->belongsTo(Indices::class,'id_indice');
    }
}
