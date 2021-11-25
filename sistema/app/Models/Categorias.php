<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Categorias extends Model
{
    protected $table = 'financeiro_categorias';
    protected $fillable = ['nome', 'tipo'];
    public $timestamps = true;
}
