<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class FichaEntrevista extends Model
{
    protected $table = 'ficha_entrevista';
    protected $fillable = ['data', 'acolhido', 'acolhimento', 'naturalidade', 'uf_naturalidade', 'cidade_encaminhamento', 'uf_encaminhamento', 'religiao', 'pratica_religiao', 'estado_civil', 'nome_conjuge', 'data_nascimento_conjuge', 'profissao_conjuge', 'profissao', 'codificacao_profissao', 'estuda', 'escolaridade', 'estrato_social', 'resultado_estrato_social', 'dependencia_quimica', 'saude', 'trabalha', 'status', 'usuario'];
    public $timestamps = true;
}
