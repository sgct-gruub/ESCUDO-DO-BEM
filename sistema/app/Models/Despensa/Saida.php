<?php

namespace App\Models\Despensa;


use  Illuminate\Database\Eloquent\Model;

class Saida extends Model
{
    protected $table = 'saida';
    protected $fillable = ['produto', 'quantidade', 'data'];
    public $timestamps = false;
}
