<?php

namespace App\Models\Despensa;


use  Illuminate\Database\Eloquent\Model;

class Estoque extends Model
{
    protected $table = 'estoque';
    protected $fillable = ['produto', 'quantidade'];
    public $timestamps = false;
}
