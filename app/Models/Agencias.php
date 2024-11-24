<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agencias extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $table = "agencias";

    public function banco()
    {
        return $this->belongsTo(Bancos::class,'id_banco');
    }

    public function cidade()
    {
        return $this->belongsTo(Cidades::class,'id_cidade');
    }
}
