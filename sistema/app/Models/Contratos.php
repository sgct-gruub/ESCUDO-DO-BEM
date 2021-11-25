<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Contratos extends Model
{
    protected $table = 'modelos_contratos';
    protected $fillable = ['tipo', 'voluntario', 'terceirizado', 'convenio', 'titulo', 'conteudo', 'status'];
    public $timestamps = true;
}
