<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class CronogramaAtividades extends Model
{
    protected $table = 'cronograma_atividades';
    protected $fillable = ['nome', 'periodo', 'grupo'];
    public $timestamps = false;
}
