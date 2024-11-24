<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditoriasProcessamentos extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $table = "auditorias_processamento";

    public function bam()
    {
        return $this->belongsTo(BamProcessados::class,'id_bam');
    }

    public function dms()
    {
        return $this->belongsTo(DmsProcessador::class,'id_dms');
    }
}
