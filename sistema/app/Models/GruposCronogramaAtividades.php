<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class GruposCronogramaAtividades extends Model
{
    protected $table = 'grupos_cronograma_atividades';
    protected $fillable = ['nome', 'periodo'];
    public $timestamps = false;
}
