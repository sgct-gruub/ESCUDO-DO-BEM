<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Mensalidades extends Model
{
    protected $table = 'mensalidades';
    protected $fillable = ['acolhimento', 'parcela', 'valor', 'data_vencimento', 'data_pagamento', 'status'];
    public $timestamps = true;
}
