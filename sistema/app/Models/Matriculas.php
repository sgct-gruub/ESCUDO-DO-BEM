<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Matriculas extends Model
{
    protected $table = 'matriculas';
    protected $fillable = ['acolhimento', 'valor', 'data_vencimento', 'data_pagamento', 'status'];
    public $timestamps = true;
}
