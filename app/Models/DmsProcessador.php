<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DmsProcessador extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $table = 'dms_processados';
}
