<?php

namespace App\Models\Despensa;


use  Illuminate\Database\Eloquent\Model;

class Produtos extends Model
{
    protected $table = 'produtos';
    protected $fillable = ['nome', 'unidade', 'estoque', 'estoque_minimo'];
    public $timestamps = false;
}
