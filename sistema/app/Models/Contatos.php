<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Contatos extends Model
{
    protected $table = 'contatos_acolhidos';
    protected $fillable = ['acolhido', 'nome', 'grau_parentesco', 'rg', 'cpf', 'data_nascimento', 'telefone', 'celular', 'email', 'cep', 'endereco', 'num', 'complemento', 'bairro', 'cidade', 'uf', 'status'];
    public $timestamps = true;
}
