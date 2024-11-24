<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndicesValores extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $table = 'indices_valores';

    public function indice()
    {
        return $this->belongsTo(Indices::class,'id_indice');
    }
}
