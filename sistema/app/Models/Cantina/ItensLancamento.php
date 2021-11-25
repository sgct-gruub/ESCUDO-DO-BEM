<?php

namespace App\Models\Cantina;


use  Illuminate\Database\Eloquent\Model;

class ItensLancamento extends Model
{
    protected $table = 'cantina_itens_lancamento';
    protected $fillable = ['lancamento', 'produto', 'quantidade', 'valor_unitario'];
    public $timestamps = false;
}
