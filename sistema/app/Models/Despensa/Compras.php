<?php

namespace App\Models\Despensa;


use  Illuminate\Database\Eloquent\Model;

class Compras extends Model
{
    protected $table = 'compras';
    protected $fillable = ['data_compra', 'fornecedor', 'numero_pedido', 'vendedor', 'tipo_frete', 'valor_frete', 'previsao_entrega', 'forma_pagamento', 'dados_pagamento', 'valor_total', 'conta_bancaria', 'observacoes'];
    public $timestamps = true;
}
