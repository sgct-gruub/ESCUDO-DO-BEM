<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class ArquivosAcolhimentos extends Model
{
    protected $table = 'arquivos_acolhimentos';
    protected $fillable = ['acolhido', 'acolhimento', 'arquivo', 'descricao'];
    public $timestamps = true;
}
