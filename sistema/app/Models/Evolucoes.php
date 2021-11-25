<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Evolucoes extends Model
{
    protected $table = 'ficha_evolucao';
    protected $fillable = ['usuario', 'acolhido', 'acolhimento', 'data', 'registro'];
    public $timestamps = true;
}
