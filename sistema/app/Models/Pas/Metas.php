<?php

namespace App\Models\Pas;


use  Illuminate\Database\Eloquent\Model;

class Metas extends Model
{
    protected $table = 'pas_metas';
    protected $fillable = ['acolhimento', 'pas', 'ano', 'mes', 'arquivo', 'descricao', 'usuario'];
    public $timestamps = true;
}
