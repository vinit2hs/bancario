<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jobs extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $table = 'jobs';

    public function auditoria()
    {
        return $this->belongsTo(Auditorias::class,'id_auditoria');
    }

    public function bam()
    {
        return $this->belongsTo(BamProcessados::class,'id_bam');
    }

    public function dms()
    {
        return $this->belongsTo(DmsProcessador::class,'id_dms');
    }

    public function cosif()
    {
        return $this->belongsTo(Cosifs::class,'id_cosif');
    }

    public function indice()
    {
        return $this->hasMany(IndicesValores::class,'id_indice','id_indice');
    }

    public function cidade()
    {
        return $this->belongsTo(Cidades::class,'id_cidade');
    }

}

