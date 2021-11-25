<?php

namespace App\Controllers;

use Slim\Views\Twig as View;
use App\Models\Acolhidos;
use App\Models\Acolhimentos;
use App\Models\Unidades;
use App\Models\Quartos;
use App\Models\Convenios;
use App\Models\Ressocializacao;
use App\Models\Mensalidades;
use App\Models\Funcionarios\Funcionarios;


class HomeController extends Controller
{

    public function index ($request, $response)
    {
        $mensagem = $this->flash->getMessages();

        $total_acolhidos = Acolhidos::count();
        $total_acolhimentos = Acolhimentos::count();
        $vagas_ocupadas = Acolhimentos::whereIn('status', [0, 1])->count();

        $total_vagas = Quartos::sum('vagas');
        $vagas_disponiveis = $total_vagas - $vagas_ocupadas;

        $alta_terapeutica = Acolhimentos::where('status', 11)->count();
        $alta_solicitada = Acolhimentos::where('status', 12)->count();
        $alta_administrativa = Acolhimentos::where('status', 13)->count();
        $alta_evasao = Acolhimentos::where('status', 14)->count();

        $total_convenios = Convenios::count();
        $acolhimentos_convenio = Acolhimentos::where('tipo_acolhimento', 0)->whereIn('status', [0, 1])->count();
        $acolhimentos_particular = Acolhimentos::where('tipo_acolhimento', 1)->whereIn('status', [0, 1])->count();
        $acolhimentos_social = Acolhimentos::where('tipo_acolhimento', 2)->whereIn('status', [0, 1])->count();
        $moradores_assistidos = Acolhimentos::where('tipo_acolhimento', 3)->whereIn('status', [0, 1])->count();

        $acolhimentos_previsao_saida_amanha = Acolhimentos::whereIn('status', [0,1])->where('previsao_saida', date('Y-m-d', strtotime('+1 day')))->get()->toArray();
        for ($i=0; $i < count($acolhimentos_previsao_saida_amanha) ; $i++) { 
            $id_acolhimento = $acolhimentos_previsao_saida_amanha[$i]['id'];
            $acolhido_amanha[$id_acolhimento] = Acolhidos::find($acolhimentos_previsao_saida_amanha[$i]['acolhido']);
            
            $this->view->offsetSet("acolhido_amanha", $acolhido_amanha);
        }
        $acolhimentos_previsao_saida = Acolhimentos::whereIn('status', [0, 1])->where('previsao_saida', date('Y-m-d'))->get()->toArray();
        for ($i=0; $i < count($acolhimentos_previsao_saida) ; $i++) { 
            $id_acolhimento = $acolhimentos_previsao_saida[$i]['id'];
            $acolhido[$id_acolhimento] = Acolhidos::find($acolhimentos_previsao_saida[$i]['acolhido']);
            
            $this->view->offsetSet("acolhido", $acolhido);
        }

        $aniversariantes_mes = Acolhidos::where('status', 1)->whereMonth('data_nascimento', date('m'))->get()->toArray();
        for($i = 0; $i < count($aniversariantes_mes); $i++){
            $id_acolhido = $aniversariantes_mes[$i]['id'];
            $verifica_acolhimento[$id_acolhido] = Acolhimentos::whereIn('status', [0, 1])->where('acolhido', $id_acolhido)->count();
                
            $this->view->offsetSet("verifica_acolhimento", $verifica_acolhimento);
        }

        $aniversariantes_dia = Acolhidos::where('status', 1)->whereDay('data_nascimento', date('d'))->whereMonth('data_nascimento', date('m'))->get()->toArray();
        for($i = 0; $i < count($aniversariantes_dia); $i++){
            $id_acolhido = $aniversariantes_dia[$i]['id'];
            $verifica_acolhimento[$id_acolhido] = Acolhimentos::whereIn('status', [0, 1])->where('acolhido', $id_acolhido)->count();
                
            $this->view->offsetSet("verifica_acolhimento", $verifica_acolhimento);
        }

        $funcionarios_aniversariantes_mes = Funcionarios::whereMonth('data_nascimento', date('m'))->get()->toArray();


        // mensalidades
        if($this->acesso_usuario['financeiro']['mensalidades']['r'] == 'on'){
            $mensalidades_a_receber_hoje = Mensalidades::whereYear('data_vencimento', date('Y'))->whereMonth('data_vencimento', date('m'))->whereDay('data_vencimento', date('d'))->where('status', 0)->count();
            $this->view->offsetSet("mensalidades_a_receber_hoje", $mensalidades_a_receber_hoje);
        }
        
        return $this->view->render($response, 'index.html', [
            'Titulo_Pagina' => 'Dashboard',
            'titulo' => 'Dashboard',
            'flash' => $mensagem,
            'total_acolhidos' => $total_acolhidos,
            'total_acolhimentos' => $total_acolhimentos,
            'vagas_ocupadas' => $vagas_ocupadas,
            'total_vagas' => $total_vagas,
            'vagas_disponiveis' => $vagas_disponiveis,
            'alta_terapeutica' => $alta_terapeutica,
            'alta_solicitada' => $alta_solicitada,
            'alta_administrativa' => $alta_administrativa,
            'alta_evasao' => $alta_evasao,
            'total_convenios' => $total_convenios,
            'acolhimentos_convenio' => $acolhimentos_convenio,
            'acolhimentos_particular' => $acolhimentos_particular,
            'acolhimentos_social' => $acolhimentos_social,
            'moradores_assistidos' => $moradores_assistidos,
            'aniversariantes_mes' => $aniversariantes_mes,
            'funcionarios_aniversariantes_mes' => $funcionarios_aniversariantes_mes,
            'aniversariantes_dia' => $aniversariantes_dia,
            'ressocializacoes' => $ressocializacoes,
            'acolhimentos_previsao_saida_amanha' => $acolhimentos_previsao_saida_amanha,
            'acolhimentos_previsao_saida' => $acolhimentos_previsao_saida,
        ]);
    }

}
