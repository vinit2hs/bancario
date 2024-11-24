<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estiban extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $table = 'estban';

    public function auditoria()
    {
        return $this->belongsTo(Auditorias::class,'id_auditoria');
    }
}
