<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Encaminhamentos extends Model
{
    protected $table = 'encaminhamentos';
    protected $fillable = ['title', 'start', 'end', 'acolhimento', 'tipo', 'motivo', 'local', 'cep', 'endereco', 'num', 'bairro', 'cidade', 'uf', 'telefone', 'celular', 'observacoes', 'custo', 'usuario', 'status', 'category'];
    public $timestamps = true;
}
