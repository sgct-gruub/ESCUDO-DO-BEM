<?php

namespace App\Models\Pas;


use  Illuminate\Database\Eloquent\Model;

class Evolucao extends Model
{
    protected $table = 'pas_evolucao';
    protected $fillable = ['acolhimento', 'pas', 'ano', 'mes', 'descricao', 'usuario'];
    public $timestamps = true;
}
