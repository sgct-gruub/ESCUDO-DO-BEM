<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class MetasPas extends Model
{
    protected $table = 'pas_metas';
    protected $fillable = ['acolhimento', 'pas', 'ano', 'mes', 'arquivo', 'descricao', 'usuario'];
    public $timestamps = true;
}
