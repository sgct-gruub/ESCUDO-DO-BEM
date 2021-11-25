<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Ressocializacao extends Model
{
    protected $table = 'ressocializacao';
    protected $fillable = ['title', 'start', 'end', 'acolhimento', 'data_saida', 'data_retorno', 'status', 'observacoes', 'category'];
    public $timestamps = true;
}
