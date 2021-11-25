<?php

namespace App\Models\Cantina;


use  Illuminate\Database\Eloquent\Model;

class Produtos extends Model
{
    protected $table = 'cantina_produtos';
    protected $fillable = ['nome', 'valor_unitario', 'status'];
    public $timestamps = false;
}
