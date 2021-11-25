<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class AtividadesPas extends Model
{
    protected $table = 'pas_atividades';
    protected $fillable = ['acolhimento', 'pas', 'ano', 'mes', 'atividade', 'usuario', 'status'];
    public $timestamps = true;
}
