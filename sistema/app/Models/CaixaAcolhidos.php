<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class CaixaAcolhidos extends Model
{
    protected $table = 'caixa_acolhidos';
    protected $fillable = ['acolhido', 'data', 'tipo', 'descricao', 'valor', 'usuario'];
    public $timestamps = true;
}
