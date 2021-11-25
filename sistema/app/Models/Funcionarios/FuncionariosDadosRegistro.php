<?php

namespace App\Models\Funcionarios;


use  Illuminate\Database\Eloquent\Model;

class FuncionariosDadosRegistro extends Model
{
    protected $table = 'funcionarios_dados_registro';
    protected $fillable = ['funcionario', 'unidade', 'cnpj', 'data_admissao', 'exame_admissional', 'tipo_contrato', 'vale_transporte', 'salario', 'horario_trabalho', 'funcao', 'descricao_funcao', 'status'];
    public $timestamps = true;
}
