<?php

namespace App\Models\Financeiro;


use  Illuminate\Database\Eloquent\Model;

class SubCategorias extends Model
{
    protected $table = 'financeiro_subcategorias';
    protected $fillable = ['nome', 'categoria'];
    public $timestamps = true;
}
