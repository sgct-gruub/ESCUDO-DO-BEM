<?php

namespace App\Models\Despensa;


use  Illuminate\Database\Eloquent\Model;

class ItensCompra extends Model
{
    protected $table = 'itens_compra';
    protected $fillable = ['compra', 'produto', 'quantidade', 'valor_unitario'];
    public $timestamps = false;
}
