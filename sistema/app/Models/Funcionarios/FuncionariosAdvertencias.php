<?php

namespace App\Models\Funcionarios;


use  Illuminate\Database\Eloquent\Model;

class FuncionariosAdvertencias extends Model
{
    protected $table = 'funcionarios_advertencias';
    protected $fillable = ['funcionario', 'usuario', 'quem_aplicou', 'condicao_quem_aplicou', 'data', 'motivo'];
    public $timestamps = true;
}
