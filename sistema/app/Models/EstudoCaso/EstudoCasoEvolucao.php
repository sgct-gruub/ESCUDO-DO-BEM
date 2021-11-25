<?php

namespace App\Models\EstudoCaso;


use  Illuminate\Database\Eloquent\Model;

class EstudoCasoEvolucao extends Model
{
    protected $table = 'estudo_caso_evolucao';
    protected $fillable = ['acolhimento', 'estudo_caso', 'mes', 'ano', 'descricao', 'usuario'];
    public $timestamps = true;
}
