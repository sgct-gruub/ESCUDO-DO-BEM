<?php

namespace App\Models\Pas;


use  Illuminate\Database\Eloquent\Model;

class Atividades extends Model
{
    protected $table = 'pas_atividades';
    protected $fillable = ['acolhimento', 'pas', 'ano', 'mes', 'atividade', 'usuario', 'status'];
    public $timestamps = true;
}
