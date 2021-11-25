<?php

namespace App\Models\Cantina;


use  Illuminate\Database\Eloquent\Model;

class Lancamentos extends Model
{
    protected $table = 'cantina_lancamentos';
    protected $fillable = ['acolhido', 'data', 'valor_total', 'observacoes', 'status', 'usuario'];
    public $timestamps = true;
}
