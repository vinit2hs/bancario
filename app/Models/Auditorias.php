<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditorias extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $table = 'auditorias';

    public function agencia()
    {
        return $this->belongsTo(Agencias::class,'id_agencia');
    }
}
