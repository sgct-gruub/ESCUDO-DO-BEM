<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Acolhimentos extends Model
{
    protected $table = 'acolhimentos_novo';
    protected $fillable = ['numero_manual', 'acolhido', 'tecnico_referencia', 'tipo_acolhimento', 'voluntario', 'terceirizado', 'convenio', 'valor_material_didatico', 'forma_pgto_material_didatico', 'valor_mensalidade', 'previsao_saida', 'unidade', 'quarto', 'fase', 'pertences', 'data_inicio', 'data_inicio_convenio', 'data_saida', 'motivo', 'status'];
    public $timestamps = true;
}
