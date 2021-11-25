<?php

namespace App\Models\Funcionarios;


use  Illuminate\Database\Eloquent\Model;

class FuncionariosTimeline extends Model
{
    protected $table = 'funcionarios_timeline';
    protected $fillable = ['funcionario', 'usuario', 'titulo', 'descricao', 'color', 'icon'];
    public $timestamps = true;
}
