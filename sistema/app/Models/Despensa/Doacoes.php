<?php

namespace App\Models\Despensa;


use  Illuminate\Database\Eloquent\Model;

class Doacoes extends Model
{
    protected $table = 'doacoes';
    protected $fillable = ['data_doacao', 'doador', 'acolhido'];
    public $timestamps = false;
}
