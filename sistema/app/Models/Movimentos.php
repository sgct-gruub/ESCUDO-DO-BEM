<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Movimentos extends Model
{
    protected $table = 'movimentos';
    protected $fillable = ['num_nf', 'descricao', 'categoria', 'data_prevista', 'data_efetuada', 'valor_previsto', 'valor_efetuado', 'conta_bancaria', 'forma_pagamento', 'dados_pagamento', 'observacoes', 'acolhido', 'acolhimento', 'fornecedor', 'compra', 'cantina_lancamento', 'funcionario', 'tipo', 'recorrente', 'ciclos', 'repetir_a_cada', 'tipo_repeticao', 'status'];
    public $timestamps = true;
}