<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Oficios extends Model
{
    protected $table = 'oficios';
    protected $fillable = ['nome', 'atividades', 'acolhimentos', 'responsavel'];
    public $timestamps = false;
}
