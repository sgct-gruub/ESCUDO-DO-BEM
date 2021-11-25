<?php

namespace App\Models\Funcionarios;


use  Illuminate\Database\Eloquent\Model;

class Funcionarios extends Model
{
    protected $table = 'funcionarios';
    protected $fillable = ['nome', 'rg', 'cpf', 'data_nascimento', 'naturalidade', 'uf_naturalidade', 'pais', 'cep', 'endereco', 'num', 'complemento', 'bairro', 'cidade', 'uf', 'telefone', 'celular', 'estado_civil', 'nome_conjuge', 'cabelos', 'olhos', 'altura', 'peso', 'deficiente_fisico', 'tipo_deficiencia', 'cor_raca', 'escolaridade', 'rg_dt_expedicao', 'pis', 'reservista', 'cnh', 'titulo_eleitor', 'titulo_eleitor_zona', 'titulo_eleitor_sessao', 'ctps', 'ctps_serie', 'possui_filhos', 'filhos', 'status'];
    public $timestamps = true;
}
