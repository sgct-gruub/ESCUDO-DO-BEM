<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class ContasBancarias extends Model
{
    protected $table = 'financeiro_contas_bancarias';
    protected $fillable = ['nome', 'agencia', 'conta', 'dv', 'op', 'favorecido', 'saldo_inicial', 'saldo_atual'];
    public $timestamps = true;
}
