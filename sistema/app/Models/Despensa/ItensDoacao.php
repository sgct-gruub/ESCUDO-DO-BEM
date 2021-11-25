<?php

namespace App\Models\Despensa;


use  Illuminate\Database\Eloquent\Model;

class ItensDoacao extends Model
{
    protected $table = 'itens_doacao';
    protected $fillable = ['doacao', 'produto', 'quantidade', 'valor_unitario'];
    public $timestamps = false;
}
