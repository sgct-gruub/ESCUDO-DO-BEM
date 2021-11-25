<?php

namespace App\Models\Doacoes;


use  Illuminate\Database\Eloquent\Model;

class Redes extends Model
{
    protected $table = 'rede_doadores';
    protected $fillable = ['nome', 'users', 'link'];
    public $timestamps = true;
}
