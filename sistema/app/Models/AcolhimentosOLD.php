<?php

namespace App\Models;


use  Illuminate\Database\Eloquent\Model;

class Acolhimentos extends Model
{
    protected $table = 'acolhimentos';
    protected $fillable = ['numero_manual', 'acolhido', 'tipo_acolhimento', 'convenio', 'valor_material_didatico', 'forma_pgto_material_didatico', 'valor_mensalidade', 'previsao_saida', 'religiao', 'pratica_religiao', 'estado_civil', 'nome_conjuge', 'data_nascimento_conjuge', 'profissao_conjuge', 'profissao', 'codificacao_profissao', 'estuda', 'escolaridade', 'estrato_social', 'resultado_estrato_social', 'dependencia_quimica', 'saude', 'observacoes_do_entrevistador', 'fatores_de_protecao', 'fatores_de_risco', 'chance_abandono_previsao_permanencia', 'indicacao_urgencia', 'unidade', 'quarto', 'data_inicio', 'data_inicio_convenio', 'data_saida', 'status'];
    public $timestamps = true;
}
