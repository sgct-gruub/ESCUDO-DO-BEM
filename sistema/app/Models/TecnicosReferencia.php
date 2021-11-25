<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class TecnicosReferencia extends Model
{
    protected $table = 'tecnicos_referencia';
    protected $fillable = ['referencia', 'acolhimentos', 'ligacoes'];
    public $timestamps = false;
}
