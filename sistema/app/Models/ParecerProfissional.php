<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class ParecerProfissional extends Model
{
    protected $table = 'parecer_profissional';
    protected $fillable = ['usuario', 'acolhido', 'acolhimento', 'data', 'registro'];
    public $timestamps = true;
}
