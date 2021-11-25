<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Quartos extends Model
{
    protected $table = 'quartos';
    protected $fillable = ['unidade', 'nome', 'vagas'];
    public $timestamps = true;
}
