<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Pas extends Model
{
    protected $table = 'pas';
    protected $fillable = ['acolhido', 'acolhimento', 'anexo_1', 'anexo_2', 'anexo_3', 'last_metas', 'last_atividades', 'last_evolucao', 'status', 'data_abertura', 'usuario'];
    public $timestamps = true;
}
