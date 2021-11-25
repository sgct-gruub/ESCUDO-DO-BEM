<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Unidades extends Model
{
    protected $table = 'unidades';
    protected $fillable = ['nome', 'publico', 'vagas'];
    public $timestamps = true;
}
