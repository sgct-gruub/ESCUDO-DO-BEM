<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Arquivos extends Model
{
    protected $table = 'arquivos_acolhidos';
    protected $fillable = ['acolhido', 'arquivo', 'descricao'];
    public $timestamps = true;
}
