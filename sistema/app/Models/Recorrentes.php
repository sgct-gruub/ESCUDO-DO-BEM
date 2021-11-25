<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Recorrentes extends Model
{
    protected $table = 'movimentos_recorrentes';
    protected $fillable = ['movimento', 'parcela', 'valor', 'data_vencimento', 'data_pagamento', 'status'];
    public $timestamps = true;
}
