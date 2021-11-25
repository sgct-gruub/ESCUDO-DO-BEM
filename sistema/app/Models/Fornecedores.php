<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Fornecedores extends Model
{
    protected $table = 'fornecedores';
    protected $fillable = ['nome_fantasia', 'razao_social', 'cnpj', 'tipo', 'inscricao_estadual', 'inscricao_municipal', 'email', 'cep', 'endereco', 'num', 'complemento', 'bairro', 'cidade', 'uf', 'responsavel', 'telefone', 'celular', 'dados_pagamento', 'observacoes'];
    public $timestamps = true;
}
