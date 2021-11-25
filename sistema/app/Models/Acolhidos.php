<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Acolhidos extends Model
{
    protected $table = 'acolhidos';
    protected $fillable = ['nome', 'rg', 'cpf', 'data_nascimento', 'naturalidade', 'uf_naturalidade', 'cad_unico', 'cartao_sus', 'pis', 'cor_raca', 'possui_filhos', 'filhos', 'nome_pai', 'data_nascimento_pai', 'profissao_pai', 'nome_mae', 'data_nascimento_mae', 'profissao_mae', 'pais_separados', 'situacao_rua', 'cep', 'endereco', 'num', 'complemento', 'bairro', 'cidade', 'uf', 'contatos', 'limite_cantina', 'status'];
    public $timestamps = true;
}
