<?php

namespace App\Models\EstudoCaso;


use  Illuminate\Database\Eloquent\Model;

class EstudoCasoMetas extends Model
{
    protected $table = 'estudo_caso_metas';
    protected $fillable = ['acolhimento', 'estudo_caso', 'mes', 'ano', 'descricao', 'usuario'];
    public $timestamps = true;
}
