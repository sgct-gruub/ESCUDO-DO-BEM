<?php

namespace App\Models\Cantina;


use  Illuminate\Database\Eloquent\Model;

class BaixaLancamentos extends Model
{
    protected $table = 'cantina_baixa_lancamentos';
    protected $fillable = ['lancamento', 'data_pagamento', 'valor_pagamento', 'observacoes', 'usuario'];
    public $timestamps = true;
}
