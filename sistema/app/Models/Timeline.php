<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Timeline extends Model
{
    protected $table = 'timeline';
    protected $fillable = ['acolhimento', 'usuario', 'titulo', 'descricao', 'color', 'icon'];
    public $timestamps = true;
}
