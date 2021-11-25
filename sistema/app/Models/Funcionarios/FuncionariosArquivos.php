<?php

namespace App\Models\Funcionarios;


use  Illuminate\Database\Eloquent\Model;

class FuncionariosArquivos extends Model
{
    protected $table = 'funcionarios_arquivos';
    protected $fillable = ['funcionario', 'arquivo', 'descricao'];
    public $timestamps = true;
}
