<?php

namespace App\Models\EstudoCaso;


use  Illuminate\Database\Eloquent\Model;

class EstudoCaso extends Model
{
    protected $table = 'estudo_caso';
    protected $fillable = ['acolhido', 'acolhimento', 'encaminhamento_responsavel', 'pertences', 'padrao_uso_droga', 'local_residencia', 'condicoes_financeiras', 'vinculo_familiar', 'saude_fisica', 'comprometimentos', 'acompanhamentos_encaminhamentos', 'historico_profissional', 'historia_de_vida', 'critica_consciencia_dq', 'tratamentos_anteriores', 'avaliacao_psicologa', 'avaliacao_conselheiros', 'avaliacao_servico_social', 'relato_familiar', 'rede_de_apoio', 'autocuidado', 'autoconhecimento', 'autonomia', 'capacitacoes_propostas', 'atcs_propostas', 'last_metas', 'last_evolucao', 'data_abertura', 'usuario', 'status'];
    public $timestamps = true;
}
