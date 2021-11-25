<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class EvolucaoPas extends Model
{
    protected $table = 'pas_evolucao';
    protected $fillable = ['acolhimento', 'pas', 'ano', 'mes', 'descricao', 'usuario'];
    public $timestamps = true;
}
