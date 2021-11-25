<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Convenios extends Model
{
    protected $table = 'convenios';
    protected $fillable = ['nome', 'vagas', 'valor'];
    public $timestamps = true;
}
