<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Doadores extends Model
{
    protected $table = 'doadores';
    protected $fillable = ['nome', 'cpf', 'data_nascimento', 'telefone', 'celular', 'email', 'cep', 'endereco', 'num', 'complemento', 'bairro', 'cidade', 'uf', 'rede', 'status', 'observacoes'];
    public $timestamps = true;
}
