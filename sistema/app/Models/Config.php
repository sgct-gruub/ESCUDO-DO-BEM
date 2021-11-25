<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $table = 'configuracoes';
    protected $fillable = ['nome_fantasia', 'razao_social', 'cnpj', 'tempo_tratamento', 'cep', 'endereco', 'num', 'complemento', 'bairro', 'cidade', 'uf', 'logo', 'timbre'];
    public $timestamps = false;
}
