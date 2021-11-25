<?php

namespace App\Controllers;

use \Datetime;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Controllers\Controller;
use Slim\Views\Twig as View;
use App\Models\Acolhidos;
use App\Models\Acolhimentos;
use App\Models\Snapshots;
use App\Models\Contatos;
use App\Models\ArquivosAcolhimentos;
use App\Models\Unidades;
use App\Models\Quartos;
use App\Models\Convenios;
use App\Models\Timeline;
use App\Models\Ressocializacao;
use App\Models\Mensalidades;
use App\Models\Matriculas;
use App\Models\CronogramaAtividades;
use App\Models\GruposCronogramaAtividades;
use App\Models\FichaEntrevista;
use App\Models\Evolucoes;
use App\Models\ParecerProfissional;
use App\Models\TecnicosReferencia;
use App\Models\Pas\Pas;
use App\Models\Pas\Atividades;
use App\Models\Pas\Evolucao;
use App\Models\Pas\Metas;
use App\Models\EstudoCaso\EstudoCaso;
use App\Models\EstudoCaso\EstudoCasoMetas;
use App\Models\EstudoCaso\EstudoCasoEvolucao;
use App\Models\Users;
use App\Models\Config;

class AcolhimentosController extends Controller
{

##### ACOLHIMENTOS #####
    // Exibe listagem dos acolhimentos
    public function listar ($request, $response, $args)
    {   
        if($this->acesso_usuario['acolhimentos']['index']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        // SE TIVER ALGUMA UNIDADE SELECIONADA
        if(isset($_SESSION['Unidade'])){
            // FILTRA PELO STATUS
            if(!isset($args['status']) OR $args['status'] == 'andamento'){
                $item = Acolhimentos::where('status', 0)->where('unidade', $_SESSION['Unidade'])->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'ressocializacao'){
                $item = Acolhimentos::where('status', 1)->where('unidade', $_SESSION['Unidade'])->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'alta-terapeutica'){
                $item = Acolhimentos::where('status', 11)->where('unidade', $_SESSION['Unidade'])->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'alta-solicitada'){
                $item = Acolhimentos::where('status', 12)->where('unidade', $_SESSION['Unidade'])->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'alta-administrativa'){
                $item = Acolhimentos::where('status', 13)->where('unidade', $_SESSION['Unidade'])->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'evasao'){
                $item = Acolhimentos::where('status', 14)->where('unidade', $_SESSION['Unidade'])->get()->toArray();
            }

            $count_0 = Acolhimentos::where('status', 0)->where('unidade', $_SESSION['Unidade'])->count();
            $count_1 = Acolhimentos::where('status', 1)->where('unidade', $_SESSION['Unidade'])->count();
            $count_11 = Acolhimentos::where('status', 11)->where('unidade', $_SESSION['Unidade'])->count();
            $count_12 = Acolhimentos::where('status', 12)->where('unidade', $_SESSION['Unidade'])->count();
            $count_13 = Acolhimentos::where('status', 13)->where('unidade', $_SESSION['Unidade'])->count();
            $count_14 = Acolhimentos::where('status', 14)->where('unidade', $_SESSION['Unidade'])->count();

            // PUXA OS ACOLHIDOS DA UNIDADE SELECIONADA
            $acolhidos = Acolhidos::get()->toArray();
            
            // PUXAR O NOME DA UNIDADE SELECIONADA
            $unid = Unidades::find($_SESSION['Unidade']);
            $unidades = Unidades::where('id', $_SESSION['Unidade'])->get()->toArray();

            $titulo = 'Listagem dos acolhimentos - ' . $unid['nome'];
            $subtitulo = 'Todos os acolhimentos que estão em andamento ou já passaram por esta unidade';
        } else { // SE NÃO TIVER NENHUMA UNIDADE SELECIONADA
            // PEGA TODAS AS UNIDADES
            $unidades = Unidades::get()->toArray();

            // PEGA TODOS OS ACOLHIDOS QUE NÃO ESTÃO EM ACOLHIMENTO
            $acolhidos = Acolhidos::where('status', 0)->get()->toArray();

            // FILTRA PELO STATUS
            if(!isset($args['status']) OR $args['status'] == 'andamento'){
                $item = Acolhimentos::where('status', 0)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'ressocializacao'){
                $item = Acolhimentos::where('status', 1)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'alta-terapeutica'){
                $item = Acolhimentos::where('status', 11)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'alta-solicitada'){
                $item = Acolhimentos::where('status', 12)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'alta-administrativa'){
                $item = Acolhimentos::where('status', 13)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'evasao'){
                $item = Acolhimentos::where('status', 14)->get()->toArray();
            }

            $count_0 = Acolhimentos::where('status', 0)->count();
            $count_1 = Acolhimentos::where('status', 1)->count();
            $count_11 = Acolhimentos::where('status', 11)->count();
            $count_12 = Acolhimentos::where('status', 12)->count();
            $count_13 = Acolhimentos::where('status', 13)->count();
            $count_14 = Acolhimentos::where('status', 14)->count();

            $titulo = 'Listagem de todos os acolhimentos';
            $subtitulo = 'Todos os acolhimentos que estão em andamento ou já passaram por sua instituição';
        }

        // SE RECEBER O CONVÊNIO, EXIBE A LISTAGEM DOS ACOLHIMENTOS PARA O MESMO
        if(isset($args['convenio']) && $args['convenio'] != 'todos'){
            // FILTRA PELO STATUS
            if(!isset($args['status']) OR $args['status'] == 'andamento'){
                $item = Acolhimentos::where('status', 0)->where('convenio', $args['convenio'])->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'ressocializacao'){
                $item = Acolhimentos::where('status', 1)->where('convenio', $args['convenio'])->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'alta-terapeutica'){
                $item = Acolhimentos::where('status', 11)->where('convenio', $args['convenio'])->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'alta-solicitada'){
                $item = Acolhimentos::where('status', 12)->where('convenio', $args['convenio'])->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'alta-administrativa'){
                $item = Acolhimentos::where('status', 13)->where('convenio', $args['convenio'])->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'evasao'){
                $item = Acolhimentos::where('status', 14)->where('convenio', $args['convenio'])->get()->toArray();
            }
            
            $sel_convenio = Convenios::find($args['convenio']);

            $count_0 = Acolhimentos::where('status', 0)->where('convenio', $args['convenio'])->count();
            $count_1 = Acolhimentos::where('status', 1)->where('convenio', $args['convenio'])->count();
            $count_11 = Acolhimentos::where('status', 11)->where('convenio', $args['convenio'])->count();
            $count_12 = Acolhimentos::where('status', 12)->where('convenio', $args['convenio'])->count();
            $count_13 = Acolhimentos::where('status', 13)->where('convenio', $args['convenio'])->count();
            $count_14 = Acolhimentos::where('status', 14)->where('convenio', $args['convenio'])->count();

            $titulo = 'Listagem dos acolhimentos - ' . $sel_convenio['nome'];
            $subtitulo = 'Todos os acolhimentos que estão em andamento ou já passaram por este convênio';

            $id_convenio = $convenio['id']; // ????? SE RETIRAR ESSA LINHA, DÁ ERRO 502 - BAD GATWAY PARA O CONVÊNIO "2", MAS O RESTANTE FUNCIONA... lOl
        }

        // FILTRAR ACOLHIMENTOS POR TODOS OS CONVÊNIOS
        if(isset($args['convenio']) && $args['convenio'] == 'todos'){
            // FILTRA PELO STATUS
            if(!isset($args['status']) OR $args['status'] == 'andamento'){
                $item = Acolhimentos::where('status', 0)->where('convenio', '!=', null)->where('convenio', '!=', 0)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'ressocializacao'){
                $item = Acolhimentos::where('status', 1)->where('convenio', '!=', null)->where('convenio', '!=', 0)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'alta-terapeutica'){
                $item = Acolhimentos::where('status', 11)->where('convenio', '!=', null)->where('convenio', '!=', 0)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'alta-solicitada'){
                $item = Acolhimentos::where('status', 12)->where('convenio', '!=', null)->where('convenio', '!=', 0)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'alta-administrativa'){
                $item = Acolhimentos::where('status', 13)->where('convenio', '!=', null)->where('convenio', '!=', 0)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'evasao'){
                $item = Acolhimentos::where('status', 14)->where('convenio', '!=', null)->where('convenio', '!=', 0)->get()->toArray();
            }

            $count_0 = Acolhimentos::where('status', 0)->where('convenio', '!=', null)->where('convenio', '!=', 0)->count();
            $count_1 = Acolhimentos::where('status', 1)->where('convenio', '!=', null)->where('convenio', '!=', 0)->count();
            $count_11 = Acolhimentos::where('status', 11)->where('convenio', '!=', null)->where('convenio', '!=', 0)->count();
            $count_12 = Acolhimentos::where('status', 12)->where('convenio', '!=', null)->where('convenio', '!=', 0)->count();
            $count_13 = Acolhimentos::where('status', 13)->where('convenio', '!=', null)->where('convenio', '!=', 0)->count();
            $count_14 = Acolhimentos::where('status', 14)->where('convenio', '!=', null)->where('convenio', '!=', 0)->count();

            $titulo = 'Listagem dos acolhimentos - Todos os convênios';
            $subtitulo = 'Todos os acolhimentos que estão em andamento ou já passaram por um convênio';

            $id_convenio = $convenio['id']; // ????? SE RETIRAR ESSA LINHA, DÁ ERRO 502 - BAD GATWAY PARA O CONVÊNIO "2", MAS O RESTANTE FUNCIONA... lOl
        }

        // FILTRAR ACOLHIMENTOS POR PARTICULAR
        if(isset($args['tipo']) && $args['tipo'] == 'particular'){
            // FILTRA PELO STATUS
            if(!isset($args['status']) OR $args['status'] == 'andamento'){
                $item = Acolhimentos::where('status', 0)->where('tipo_acolhimento', 1)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'ressocializacao'){
                $item = Acolhimentos::where('status', 1)->where('tipo_acolhimento', 1)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'alta-terapeutica'){
                $item = Acolhimentos::where('status', 11)->where('tipo_acolhimento', 1)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'alta-solicitada'){
                $item = Acolhimentos::where('status', 12)->where('tipo_acolhimento', 1)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'alta-administrativa'){
                $item = Acolhimentos::where('status', 13)->where('tipo_acolhimento', 1)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'evasao'){
                $item = Acolhimentos::where('status', 14)->where('tipo_acolhimento', 1)->get()->toArray();
            }

            $count_0 = Acolhimentos::where('status', 0)->where('tipo_acolhimento', 1)->count();
            $count_1 = Acolhimentos::where('status', 1)->where('tipo_acolhimento', 1)->count();
            $count_11 = Acolhimentos::where('status', 11)->where('tipo_acolhimento', 1)->count();
            $count_12 = Acolhimentos::where('status', 12)->where('tipo_acolhimento', 1)->count();
            $count_13 = Acolhimentos::where('status', 13)->where('tipo_acolhimento', 1)->count();
            $count_14 = Acolhimentos::where('status', 14)->where('tipo_acolhimento', 1)->count();
            $titulo = 'Listagem dos acolhimentos - Particular';
            $subtitulo = 'Todos os acolhimentos que estão em andamento ou já passaram pela instituição';
        }

        // FILTRAR ACOLHIMENTOS POR VAGA SOCIAL
        if(isset($args['tipo']) && $args['tipo'] == 'vaga-social'){
            // FILTRA PELO STATUS
            if(!isset($args['status']) OR $args['status'] == 'andamento'){
                $item = Acolhimentos::where('status', 0)->where('tipo_acolhimento', 2)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'ressocializacao'){
                $item = Acolhimentos::where('status', 1)->where('tipo_acolhimento', 2)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'alta-terapeutica'){
                $item = Acolhimentos::where('status', 11)->where('tipo_acolhimento', 2)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'alta-solicitada'){
                $item = Acolhimentos::where('status', 12)->where('tipo_acolhimento', 2)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'alta-administrativa'){
                $item = Acolhimentos::where('status', 13)->where('tipo_acolhimento', 2)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'evasao'){
                $item = Acolhimentos::where('status', 14)->where('tipo_acolhimento', 2)->get()->toArray();
            }

            $count_0 = Acolhimentos::where('status', 0)->where('tipo_acolhimento', 2)->count();
            $count_1 = Acolhimentos::where('status', 1)->where('tipo_acolhimento', 2)->count();
            $count_11 = Acolhimentos::where('status', 11)->where('tipo_acolhimento', 2)->count();
            $count_12 = Acolhimentos::where('status', 12)->where('tipo_acolhimento', 2)->count();
            $count_13 = Acolhimentos::where('status', 13)->where('tipo_acolhimento', 2)->count();
            $count_14 = Acolhimentos::where('status', 14)->where('tipo_acolhimento', 2)->count();
            $titulo = 'Listagem dos acolhimentos - Vaga Social';
            $subtitulo = 'Todos os acolhimentos que estão em andamento ou já passaram pela instituição';
        }

        // FILTRAR ACOLHIMENTOS MORADORES ASSISTIDOS
        if(isset($args['tipo']) && $args['tipo'] == 'morador-assistido'){
            // FILTRA PELO STATUS
            if(!isset($args['status']) OR $args['status'] == 'andamento'){
                $item = Acolhimentos::where('status', 0)->where('tipo_acolhimento', 3)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'ressocializacao'){
                $item = Acolhimentos::where('status', 1)->where('tipo_acolhimento', 3)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'alta-terapeutica'){
                $item = Acolhimentos::where('status', 11)->where('tipo_acolhimento', 3)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'alta-solicitada'){
                $item = Acolhimentos::where('status', 12)->where('tipo_acolhimento', 3)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'alta-administrativa'){
                $item = Acolhimentos::where('status', 13)->where('tipo_acolhimento', 3)->get()->toArray();
            }

            if(isset($args['status']) && $args['status'] == 'evasao'){
                $item = Acolhimentos::where('status', 14)->where('tipo_acolhimento', 3)->get()->toArray();
            }

            $count_0 = Acolhimentos::where('status', 0)->where('tipo_acolhimento', 3)->count();
            $count_1 = Acolhimentos::where('status', 1)->where('tipo_acolhimento', 3)->count();
            $count_11 = Acolhimentos::where('status', 11)->where('tipo_acolhimento', 3)->count();
            $count_12 = Acolhimentos::where('status', 12)->where('tipo_acolhimento', 3)->count();
            $count_13 = Acolhimentos::where('status', 13)->where('tipo_acolhimento', 3)->count();
            $count_14 = Acolhimentos::where('status', 14)->where('tipo_acolhimento', 3)->count();
            $titulo = 'Listagem dos acolhimentos - Moradores Asssistidos';
            $subtitulo = 'Todos os acolhimentos que estão em andamento ou já passaram pela instituição';
        }

        // FAZ O LOOP PARA CADA ACOLHIMENTO
        for($i = 0; $i < count($item); $i++){
            $id_acolhido = $item[$i]['acolhido'];
            $id_unidade = $item[$i]['unidade'];
            $id_quarto = $item[$i]['quarto'];
            $id_convenio = $item[$i]['convenio'];

            $unidade[$id_unidade] = Unidades::find($id_unidade);
            $quarto[$id_quarto] = Quartos::find($id_quarto);
            $convenio[$id_convenio] = Convenios::find($id_convenio);
            $acolhido[$id_acolhido] = Acolhidos::find($id_acolhido);

            $data_atual = new DateTime( date("Y-m-d") );
            $data_inicio = new DateTime( $item[$i]['data_inicio'] );
            $data_inicio_convenio = new DateTime( $item[$i]['data_inicio_convenio'] );

            if($item[$i]['tipo_acolhimento'] == 0){
                $intervalo_dia = $data_atual->diff( $data_inicio_convenio );
                $intervalo_mes = $data_atual->diff( $data_inicio_convenio );
                $intervalo_ano = $data_atual->diff( $data_inicio_convenio );
            } else {
                $intervalo_dia = $data_atual->diff( $data_inicio );
                $intervalo_mes = $data_atual->diff( $data_inicio );
                $intervalo_ano = $data_atual->diff( $data_inicio );
            }

            if($intervalo_ano->y > 1)
            {
                $total_ano[$item[$i]['id']] = $intervalo_ano->y . " anos ";
            }
            elseif($intervalo_ano->y > 0)
            {
                $total_ano[$item[$i]['id']] = $intervalo_ano->y . " ano ";
            }
            elseif($intervalo_ano->y == 0){
                $total_ano[$item[$i]['id']] = '';
            }

            if($intervalo_mes->m > 1)
            {
                $total_mes[$item[$i]['id']] = $intervalo_mes->m . " meses ";
            }
            elseif($intervalo_mes->m > 0)
            {
                $total_mes[$item[$i]['id']] = $intervalo_mes->m . " mes ";
            }
            elseif($intervalo_mes->m == 0){
                $total_mes[$item[$i]['id']] = '';
            }

            if($intervalo_dia->d > 1)
            {
                $total_dia[$item[$i]['id']] = $intervalo_dia->d . " dias ";
            }
            elseif($intervalo_dia->d > 0)
            {
                $total_dia[$item[$i]['id']] = $intervalo_dia->d . " dia ";
            }

            $diferenca = strtotime(date('Y-m-d')) - strtotime($item[$i]['data_inicio']);
            $dia[$item[$i]['id']] = floor($diferenca / (60 * 60 * 24));

            // $dia[$item[$i]['id']] = $intervalo_dia->d;
            // $mes[$item[$i]['id']] = $intervalo_mes->m;

            // VERIFICA SE JÁ TEM UM CONTATO PRINCIPAL
            $contato_principal = Contatos::where('acolhido', $item[$i]['acolhido'])->where('status', 1)->count();
            if($contato_principal >= 1){
                $this->view->offsetSet("tem_contato_principal", true);
            }

            // VERIFICA SE JÁ FOI PREENCHIDO A FICHA DE ENTREVISTA
            $ficha_entrevista[$item[$i]['id']] = FichaEntrevista::where('acolhimento', $item[$i]['id'])->where('status', 1)->count();
            $this->view->offsetSet("ficha_entrevista", $ficha_entrevista);

            // VERIFICA SE O ACOLHIMENTO TEM MAIS DE 30 DIAS
            $dias_em_acolhimento[$item[$i]['id']] = $data_atual->diff($data_inicio)->format('%a');
            if($dias_em_acolhimento > 30){
                // VERIFICA SE O PAS JÁ FOI ABERTO/INICIADO
                $verifica_pas = Pas::where('acolhido', $item[$i]['acolhido'])->where('acolhimento', $item[$i]['id'])->where('status', 1)->count();
                if($verifica_pas >= 1){
                    $this->view->offsetSet("pas_aberto", 1);
                } else {
                    $this->view->offsetSet("pas_aberto", 0);
                }

                // VERIFICA SE O ESTUDO DE CASO JÁ FOI ABERTO/INICIADO
                $verifica_estudo_caso = EstudoCaso::where('acolhido', $item[$i]['acolhido'])->where('acolhimento', $item[$i]['id'])->where('status', 1)->count();
                if($verifica_estudo_caso >= 1){
                    $this->view->offsetSet("estudo_caso_aberto", 1);
                } else {
                    $this->view->offsetSet("estudo_caso_aberto", 0);
                }

                $this->view->offsetSet("dias_em_acolhimento", $dias_em_acolhimento);
            }

            // $this->view->offsetSet("mes", $mes);
            // $this->view->offsetSet("dia", $dia);
            $this->view->offsetSet("periodo_ano", $total_ano);
            $this->view->offsetSet("periodo_mes", $total_mes);
            $this->view->offsetSet("periodo_dia", $total_dia);
            $this->view->offsetSet("acolhido", $acolhido);
            $this->view->offsetSet("convenio", $convenio);
            $this->view->offsetSet("unidade", $unidade);
            $this->view->offsetSet("quarto", $quarto);
            
            if(isset($args['status'])){
                $this->view->offsetSet("status", $args['status']);
            }
            if(isset($args['convenio'])){
                $this->view->offsetSet("id_convenio_sel", $args['convenio']);
            }
        }

        // PUXA TODOS OS CONVÊNIOS
        $convenios = Convenios::get()->toArray();

        // PUXA TODOS OS TÉCNICOS DE REFERÊNCIA
        $tecnicos_referencia = TecnicosReferencia::get()->toArray();

        // $unidades = Unidades::orderBy('id','ASC')->get()->toArray();
        $quartos = Quartos::orderBy('id','ASC')->get()->toArray();

        for($i = 0; $i <= (count($quartos)); $i++){
            $id_quarto = $quartos[$i]['id'];
            $vagas_ocupadas[$id_quarto] = Acolhimentos::whereIn('status', [0, 1])->where('quarto', $id_quarto)->count();
            $this->view->offsetSet("vagas_ocupadas", $vagas_ocupadas);
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'acolhimentos/listar.html', [
            'Titulo_Pagina' => 'Acolhimentos',
            'titulo'    => $titulo,
            'subtitulo' => $subtitulo,
            'view'      => 'listar',
            'flash'     => $mensagem,
            'itens'     => $item,
            'unidades'  => $unidades,
            'quartos'  => $quartos,
            'acolhidos' => $acolhidos,
            'count_0' => $count_0,
            'count_1' => $count_1,
            'count_11' => $count_11,
            'count_12' => $count_12,
            'count_13' => $count_13,
            'count_14' => $count_14,
            'status' => $args['status'],
            'tipo' => $args['tipo'],
            'convenios' => $convenios,
            'tecnicos_referencia' => $tecnicos_referencia,
        ]);
    }

    // Exibe formulário de cadastro
    public function getCadastrar ($request, $response)
    {   
        if($this->acesso_usuario['acolhimentos']['index']['c'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        // if(isset($_SESSION['Unidade'])){
        //     $unidade = Unidades::find($_SESSION['Unidade']);

        //     $acolhidos = Acolhidos::where('unidade', $_SESSION['Unidade'])->get()->toArray();

        //     $acolhimentos = Acolhimentos::where('unidade', $_SESSION['Unidade'])->whereIn('status', [0, 1])->count();

        //     $convenios = Convenios::get()->toArray();

        //     $verifica_vagas_unidade = $unidade['vagas'] - $acolhimentos;

        //     // VERIFICA SE HÁ VAGAS DISPONÍVEIS NA UNIDADE SELECIONADA
        //     if($verifica_vagas_unidade > 0){
        //         // SE O TIPO DE ACOLHIMENTO FOR CONVÊNIO
        //         if($request->getParam('tipo_acolhimento') == 0){
        //             // SELECIONA O CONVÊNIO
        //             $convenio = Convenios::find($request->getParam('convenio'));
        //             // SELECIONA OS ACOLHIMENTOS COM ESTE CONVENIO
        //             $acolhimentos_convenio = Acolhimentos::where('convenio', $convenio['id'])->whereIn('status', [0, 1])->count();

        //             $verifica_vagas_convenio = $convenio['vagas'] - $acolhimentos_convenio;

        //             // VERIFICA SE HÁ VAGAS DISPONÍVEIS NA UNIDADE E CONVÊNIO SELECIONADOS
        //             if($verifica_vagas_convenio <= 0){
        //                 $this->flash->addMessage('error', 'Este convênio não possui mais vagas disponíveis!');
                    
        //                 return $response->withRedirect($this->router->pathFor('acolhimentos'));
        //             }
        //         }
        //     } else {
        //         $this->flash->addMessage('error', 'Esta unidade não possui mais vagas disponíveis!');
                
        //         return $response->withRedirect($this->router->pathFor('acolhimentos'));
        //     }
        // } else {
        //     $this->flash->addMessage('error', 'Você precisa selecionar uma unidade para iniciar um acolhimento!');

        //     return $response->withRedirect($this->router->pathFor('acolhimentos'));
        // }

        $unidade = Unidades::find($request->getParam('unidade'));
        $quarto = Quartos::find($request->getParam('quarto'));
        $acolhidos = Acolhidos::get()->toArray();
        $acolhimentos = Acolhimentos::where('unidade', $unidade['id'])->where('quarto', $quarto['id'])->whereIn('status', [0, 1])->count();
        $convenios = Convenios::get()->toArray();
        $tecnicos_referencia = TecnicosReferencia::get()->toArray();
        $verifica_vagas_unidade = $quarto['vagas'] - $acolhimentos;

        // VERIFICA SE HÁ VAGAS DISPONÍVEIS NA UNIDADE SELECIONADA
        if($verifica_vagas_unidade > 0){
            // SE O TIPO DE ACOLHIMENTO FOR CONVÊNIO
            if($request->getParam('tipo_acolhimento') == 0){
                // SELECIONA O CONVÊNIO
                $convenio = Convenios::find($request->getParam('convenio'));
                // SELECIONA OS ACOLHIMENTOS COM ESTE CONVENIO
                $acolhimentos_convenio = Acolhimentos::where('convenio', $convenio['id'])->whereIn('status', [0, 1])->count();
                
                $verifica_vagas_convenio = $convenio['vagas'] - $acolhimentos_convenio;

                // VERIFICA SE HÁ VAGAS DISPONÍVEIS NA UNIDADE E CONVÊNIO SELECIONADOS
                if($verifica_vagas_convenio <= 0){
                    $this->flash->addMessage('error', 'Este convênio não possui mais vagas disponíveis!');
                    return $response->withRedirect($this->router->pathFor('acolhimentos'));
                }
            }
        } else {
            $this->flash->addMessage('error', 'Esta unidade não possui mais vagas disponíveis!');
            return $response->withRedirect($this->router->pathFor('acolhimentos'));
        }

        return $this->view->render($response, 'acolhimentos/form.html', [
            'Titulo_Pagina' => 'Novo Registro',
            'titulo'    => 'Iniciar novo acolhimento',
            'subtitulo' => 'Preencha o formulário abaixo para iniciar um novo acolhimento para a unidade - ' . $unidade['nome'],
            'view'      => 'cadastrar',
            'acolhidos' => $acolhidos,
            'convenios' => $convenios,
            'tecnicos_referencia' => $tecnicos_referencia,
        ]);
    }

    // Cadastra um acolhimento no banco de dados
    public function postCadastrar ($request, $response, $args)
    {   
        $estrato_social =
        [
            'nome_chefe_familia' => $request->getParam('nome_chefe_familia'), 
            'chefe_familia' => $request->getParam('chefe_familia'), 
            'banheiros' => $request->getParam('banheiros'), 
            'empregados' => $request->getParam('empregados'),
            'automoveis' => $request->getParam('automoveis'), 
            'microcomputador' => $request->getParam('microcomputador'), 
            'lava_louca' => $request->getParam('lava_louca'), 
            'geladeira' => $request->getParam('geladeira'), 
            'freezer' => $request->getParam('freezer'), 
            'lava_roupas' => $request->getParam('lava_roupas'), 
            'dvd' => $request->getParam('dvd'), 
            'microondas' => $request->getParam('microondas'),
            'motocicleta' => $request->getParam('motocicleta'), 
            'secador_de_roupas' => $request->getParam('secador_de_roupas'), 
            'agua_encanada' => $request->getParam('agua_encanada'),
            'rua_pavimentada' => $request->getParam('rua_pavimentada')
        ];
            
        $estrato_social = serialize($estrato_social);
            
        if($request->getParam('lava_louca') == 61 OR $request->getParam('lava_louca') == 62)
        {
            $lava_louca = 6;
        }
            
        if($request->getParam('geladeira') == 51)
        {
            $geladeira = 5;
        }
            
        if($request->getParam('freezer') == 61)
        {
            $freezer = 6;
        }
            
        if($request->getParam('lava_roupas') == 61)
        {
            $lava_roupas = 6;
        }
            
        if($request->getParam('microondas') == 41 OR $request->getParam('microondas') == 42)
        {
            $microondas = 4;
        }
            
        if($request->getParam('motocicleta') == 31 OR $request->getParam('motocicleta') == 32)
        {
            $motocicleta = 3;
        }
            
        if($request->getParam('secador_de_roupas') == 21 OR $request->getParam('secador_de_roupas') == 22)
        {
            $secador_de_roupas = 2;
        }
            
        $resultado_estrato_social =
                $request->getParam('chefe_familia') + 
                $request->getParam('banheiros') + 
                $request->getParam('empregados') + 
                $request->getParam('automoveis') + 
                $request->getParam('microcomputador') + 
                $lava_louca + 
                $geladeira + 
                $freezer + 
                $lava_roupas + 
                $request->getParam('dvd') + 
                $microondas + 
                $motocicleta + 
                $secador_de_roupas + 
                $request->getParam('agua_encanada') + 
                $request->getParam('rua_pavimentada');

        $dependencia_quimica = serialize($request->getParam('DQ'));

        $saude = serialize($request->getParam('saude'));

        $indicacao_urgencia = serialize($request->getParam('indicacao_urgencia'));

        if($request->getParam('data_inicio') == "")
        {
            $data_inicio = date("Y-m-d H:i:s");
        } else {
            $data_inicio = explode("/", $request->getParam('data_inicio'));
            $data_inicio = $data_inicio[2] . "-" . $data_inicio[1] . "-" . $data_inicio[0] . ' ' . date('H:i:s');
            // $data_inicio = date("Y-m-d", strtotime($data_inicio));
        }

        if($request->getParam('convenio') == "")
        {
            $data_inicio_convenio = '';
        } else {
            $data_inicio_convenio = explode("/", $request->getParam('data_inicio_convenio'));
            $data_inicio_convenio = $data_inicio_convenio[2] . "-" . $data_inicio_convenio[1] . "-" . $data_inicio_convenio[0];
        }

        if($request->getParam('data_nascimento_conjuge') != null)
        {
            $data_nascimento_conjuge = explode("/", $request->getParam('data_nascimento_conjuge'));
            $data_nascimento_conjuge = $data_nascimento_conjuge[2]."-".$data_nascimento_conjuge[1]."-".$data_nascimento_conjuge[0];
        }

        $previsao_saida = explode("/", $request->getParam('previsao_saida'));
        $previsao_saida = $previsao_saida[2]."-".$previsao_saida[1]."-".$previsao_saida[0];
        
        $numero_manual = $request->getParam('numero_manual');
        
        $item = Acolhimentos::create([
            'numero_manual' => $numero_manual, 
            'acolhido' => $request->getParam('acolhido'), 
            'tecnico_referencia' => $request->getParam('tecnico_referencia'), 
            'tipo_acolhimento' => $request->getParam('tipo_acolhimento'), 
            'convenio' => $request->getParam('convenio'), 
            'valor_material_didatico' => $request->getParam('valor_material_didatico'), 
            'forma_pgto_material_didatico' => $request->getParam('forma_pgto_material_didatico'), 
            'valor_mensalidade' => $request->getParam('valor_mensalidade'), 
            'previsao_saida' => $previsao_saida, 
            'religiao' => $request->getParam('religiao'), 
            'pratica_religiao' => $request->getParam('pratica_religiao'), 
            'estado_civil' => $request->getParam('estado_civil'), 
            'nome_conjuge' => $request->getParam('nome_conjuge'), 
            'data_nascimento_conjuge' => $data_nascimento_conjuge, 
            'profissao_conjuge' => $request->getParam('profissao_conjuge'), 
            'profissao' => $request->getParam('profissao'), 
            'codificacao_profissao' => $request->getParam('codificacao_profissao'), 
            'estuda' => $request->getParam('estuda'), 
            'escolaridade' => $request->getParam('escolaridade'), 
            'estrato_social' => $estrato_social, 
            'resultado_estrato_social' => $resultado_estrato_social, 
            'dependencia_quimica' => $dependencia_quimica, 
            'saude' => $saude, 
            'observacoes_do_entrevistador' => $request->getParam('observacoes_do_entrevistador'), 
            'fatores_de_protecao' => $request->getParam('fatores_de_protecao'), 
            'fatores_de_risco' => $request->getParam('fatores_de_risco'), 
            'chance_abandono_previsao_permanencia' => $request->getParam('chance_abandono_previsao_permanencia'), 
            'indicacao_urgencia' => $indicacao_urgencia, 
            'unidade' => $request->getParam('unidade'), 
            'quarto' => $request->getParam('quarto'), 
            'data_inicio' => $data_inicio, 
            'data_inicio_convenio' => $data_inicio_convenio, 
            'data_saida' => '', 
            'status' => 0
        ]);

        if($item)
        {
            $atualiza_status_acolhido = Acolhidos::find($request->getParam('acolhido'));
            $atualiza_status_acolhido->update([
                'status' => 1
            ]);

            // se o acolhimento for particular
            if($request->getParam('tipo_acolhimento') == 1){
                for($x = 0; $x < count($request->getParam('data_vencimento')); $x++)
                {
                    $data_vencimento = $request->getParam('data_vencimento')[$x];
                    $nova_data_vencimento = explode('/', $data_vencimento);
                    $nova_data_vencimento = $nova_data_vencimento[2] .'-'. $nova_data_vencimento[1] .'-'. $nova_data_vencimento[0];
                    $parcela = $x + 1;
                    $mensalidade = Mensalidades::create([
                        'acolhimento' => $item->id, 
                        'parcela' => $parcela, 
                        'valor' => $request->getParam('valor_mensalidade'), 
                        'data_vencimento' => $nova_data_vencimento,
                        'status' => 0
                    ]);
                }
                
                // se for a vista
                if($request->getParam('forma_pgto_material_didatico') == 1){
                    $matricula = Matriculas::create([
                        'acolhimento' => $item->id, 
                        'valor' => $request->getParam('valor_material_didatico'), 
                        'data_vencimento' => date('Y-m-d'),
                        'status' => 0
                    ]);
                }

                // se for 1+1
                if($request->getParam('forma_pgto_material_didatico') == 2){

                    $valor_material_didatico = $request->getParam('valor_material_didatico') / 2;

                    $matricula = Matriculas::create([
                        'acolhimento' => $item->id, 
                        'valor' => $valor_material_didatico, 
                        'data_vencimento' => date('Y-m-d'),
                        'status' => 0
                    ]);

                    $matricula = Matriculas::create([
                        'acolhimento' => $item->id, 
                        'valor' => $valor_material_didatico, 
                        'data_vencimento' => date('Y-m-d', strtotime("+1 month")),
                        'status' => 0
                    ]);
                }

                // se for isento
                if($request->getParam('forma_pgto_material_didatico') == 3){
                    $matricula = Matriculas::create([
                        'acolhimento' => $item->id, 
                        'valor' => '0.00', 
                        'data_vencimento' => date('Y-m-d'),
                        'status' => 0
                    ]);
                }
            }
            return true;
        }
        else
        {
            return false;
        }
    }

    // Exibe formulário de edição dos acolhimentos
    public function getEditar ($request, $response, $args)
    {   
        if($this->acesso_usuario['acolhimentos']['index']['u'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $this->view->offsetSet("flash", $this->flash->getMessages());

        $item = Acolhimentos::find($args['id']);
        $unidade = Unidades::find($item['unidade']);
        $unidades = Unidades::get()->toArray();
        $acolhido = Acolhidos::find($item['acolhido']);
        $acolhidos = Acolhidos::get()->toArray();
        $convenios = Convenios::get()->toArray();
        $tecnicos_referencia = TecnicosReferencia::get()->toArray();
        $timeline = Timeline::where('acolhimento', $args['id'])->orderBy('id', 'DESC')->get()->toArray();
        $ressocializacao = Ressocializacao::where('acolhimento', $args['id'])->orderBy('id', 'DESC')->get()->toArray();
        $mensalidades = Mensalidades::where('acolhimento', $args['id'])->get()->toArray();

        /* puxa o cronograma de atividades para ser inserido no PAS */
        $atividades = CronogramaAtividades::orderBy('id', 'asc')->get()->toArray();
        $grupos = GruposCronogramaAtividades::orderBy('id', 'asc')->get()->toArray();

        $snapshots = Snapshots::orderBy('id','desc')->where('acolhido', $item['acolhido'])->get()->toArray();
        $contatos = Contatos::orderBy('id','desc')->where('acolhido', $item['acolhido'])->get()->toArray();
        $arquivos = ArquivosAcolhimentos::orderBy('id','desc')->where('acolhido', $item['acolhido'])->get()->toArray();
        
        $estrato_social = unserialize($item['estrato_social']);
        $dependencia_quimica = unserialize($item['dependencia_quimica']);
        $saude = unserialize($item['saude']);
        $indicacao_urgencia = unserialize($item['indicacao_urgencia']);

        // VERIFICA SE JÁ TEM UM CONTATO PRINCIPAL
        $contato_principal = Contatos::where('acolhido', $acolhido['id'])->where('status', 1)->count();
        if($contato_principal >= 1){
            $this->view->offsetSet("tem_contato_principal", true);
        }

        // VERIFICA SE O PAS JÁ FOI ABERTO/INICIADO
        $verifica_pas = Pas::where('acolhido', $acolhido['id'])->where('acolhimento', $args['id'])->where('status', 1)->count();
        if($verifica_pas >= 1){
            $this->view->offsetSet("pas_aberto", 1);
            
            // PEGA AS INFORMAÇÕES DO PAS
            $pas = Pas::where('acolhido', $acolhido['id'])->where('acolhimento', $args['id'])->where('status', 1)->first();
            $this->view->offsetSet("pas", $pas);
            $this->view->offsetSet("pas_anexo_1", unserialize($pas['anexo_1']));
            $this->view->offsetSet("pas_anexo_2", unserialize($pas['anexo_2']));

            // PUXA AS METAS DO PAS
            $metas_pas = Metas::where('acolhimento', $args['id'])->get()->toArray();
            $this->view->offsetSet("metas_pas", $metas_pas);
        } else {
            $this->view->offsetSet("pas_aberto", 0);
        }

        // VERIFICA SE O ESTUDO DE CASO JÁ FOI ABERTO/INICIADO
        $verifica_estudo_caso = EstudoCaso::where('acolhido', $acolhido['id'])->where('acolhimento', $args['id'])->where('status', 1)->count();
        if($verifica_estudo_caso >= 1){
            $this->view->offsetSet("estudo_caso_aberto", 1);
            
            // PEGA AS INFORMAÇÕES DO ESTUDO DE CASO
            $estudo_caso = EstudoCaso::where('acolhido', $acolhido['id'])->where('acolhimento', $args['id'])->where('status', 1)->first();
            $this->view->offsetSet("estudo_caso", $estudo_caso);
            $this->view->offsetSet("pas_anexo_1", unserialize($pas['anexo_1']));
            $this->view->offsetSet("pas_anexo_2", unserialize($pas['anexo_2']));

            // PUXA AS METAS DO ESTUDO DE CASO
            $metas_estudo_caso = EstudoCasoMetas::where('acolhimento', $args['id'])->get()->toArray();
            $this->view->offsetSet("metas_estudo_caso", $metas_estudo_caso);

            // PUXA AS EVOLUÇÕES DO ESTUDO DE CASO
            $evolucoes_estudo_caso = EstudoCasoEvolucao::where('acolhimento', $args['id'])->get()->toArray();
            $this->view->offsetSet("evolucoes_estudo_caso", $evolucoes_estudo_caso);
        } else {
            $this->view->offsetSet("estudo_caso_aberto", 0);
        }

        $clone = clone $item;

        return $this->view->render($response, 'acolhimentos/form.html', [
            'Titulo_Pagina' => 'Editar Registro',
            'titulo'    => 'Editar acolhimento - ' . $acolhido['nome'],
            'subtitulo' => 'Preencha o formulário abaixo para editar este acolhimento na unidade - ' . $unidade['nome'],
            'view' => 'editar',
            'item' => $item,
            'clone' => $clone->getAttributes(),
            'acolhido' => $acolhido,
            'acolhidos' => $acolhidos,
            'convenios' => $convenios,
            'tecnicos_referencia' => $tecnicos_referencia,
            'es' => $estrato_social,
            'dq' => $dependencia_quimica,
            'saude' => $saude,
            'indicacao_urgencia' => $indicacao_urgencia,
            'snapshots' => $snapshots,
            'contatos' => $contatos,
            'arquivos' => $arquivos,
            'unidade' => $unidade,
            'unidades' => $unidades,
            'timeline' => $timeline,
            'ressocializacao' => $ressocializacao,
            'mensalidades' => $mensalidades,
            'atividades' => $atividades,
            'grupos' => $grupos,
        ]);
    }

    // Edita um acolhimento no banco de dados
    public function postEditar ($request, $response, $args)
    {
        $item = Acolhimentos::find($args['id']);
        $acolhido = Acolhidos::find($item['acolhido']);

        // se não for vaga social
        // se tiver convenio
        // se o convenio for diferente do atual convenio, faz a troca de convênio
        if($request->getParam('tipo_acolhimento_atual') != 2 and  $request->getParam('convenio') != '' and $request->getParam('convenio') != $item['convenio']){
            $convenio = $request->getParam('convenio');
            $convenio_atual = Convenios::find($item['convenio']);
            $convenio_troca = Convenios::find($convenio);

            $descricao = $acolhido['nome'] . ' trocou do convênio ' . $convenio_atual['nome'] . ' para o convênio ' . $convenio_troca['nome'];
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $args['id'],
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Troca de convênio',
                'descricao' => $descricao,
                'color' => $color,
                'icon' => 'mdi mdi-hospital',
            ]);
        } else {
            $convenio = $request->getParam('convenio');
        }

        // verifica se o acolhimento é do tipo vaga social
        // se o tipo de acolhimento for convenio, faz a troca
        if($request->getParam('tipo_acolhimento_atual') == 2 and $request->getParam('tipo_acolhimento') != $request->getParam('tipo_acolhimento_atual')){
            $convenio = Convenios::find($request->getParam('convenio'));

            $descricao = $acolhido['nome'] . ' trocou de vaga social para o convênio ' . $convenio['nome'] . ' com sua data de início em ' . $request->getParam('data_inicio_convenio');
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $args['id'],
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Alteração no tipo de acolhimento',
                'descricao' => $descricao,
                'color' => $color,
                'icon' => 'mdi mdi-hospital',
            ]);
        } else {
            $convenio = $request->getParam('convenio');
        }

        $estrato_social =
        [
            'nome_chefe_familia' => $request->getParam('nome_chefe_familia'), 
            'chefe_familia' => $request->getParam('chefe_familia'), 
            'banheiros' => $request->getParam('banheiros'), 
            'empregados' => $request->getParam('empregados'),
            'automoveis' => $request->getParam('automoveis'), 
            'microcomputador' => $request->getParam('microcomputador'), 
            'lava_louca' => $request->getParam('lava_louca'), 
            'geladeira' => $request->getParam('geladeira'), 
            'freezer' => $request->getParam('freezer'), 
            'lava_roupas' => $request->getParam('lava_roupas'), 
            'dvd' => $request->getParam('dvd'), 
            'microondas' => $request->getParam('microondas'),
            'motocicleta' => $request->getParam('motocicleta'), 
            'secador_de_roupas' => $request->getParam('secador_de_roupas'), 
            'agua_encanada' => $request->getParam('agua_encanada'),
            'rua_pavimentada' => $request->getParam('rua_pavimentada')
        ];
            
        $estrato_social = serialize($estrato_social);
            
        if($request->getParam('lava_louca') == 61 OR $request->getParam('lava_louca') == 62)
        {
            $lava_louca = 6;
        }
            
        if($request->getParam('geladeira') == 51)
        {
            $geladeira = 5;
        }
            
        if($request->getParam('freezer') == 61)
        {
            $freezer = 6;
        }
            
        if($request->getParam('lava_roupas') == 61)
        {
            $lava_roupas = 6;
        }
            
        if($request->getParam('microondas') == 41 OR $request->getParam('microondas') == 42)
        {
            $microondas = 4;
        }
            
        if($request->getParam('motocicleta') == 31 OR $request->getParam('motocicleta') == 32)
        {
            $motocicleta = 3;
        }
            
        if($request->getParam('secador_de_roupas') == 21 OR $request->getParam('secador_de_roupas') == 22)
        {
            $secador_de_roupas = 2;
        }
            
        $resultado_estrato_social =
                $request->getParam('chefe_familia') + 
                $request->getParam('banheiros') + 
                $request->getParam('empregados') + 
                $request->getParam('automoveis') + 
                $request->getParam('microcomputador') + 
                $lava_louca + 
                $geladeira + 
                $freezer + 
                $lava_roupas + 
                $request->getParam('dvd') + 
                $microondas + 
                $motocicleta + 
                $secador_de_roupas + 
                $request->getParam('agua_encanada') + 
                $request->getParam('rua_pavimentada');

        $dependencia_quimica = serialize($request->getParam('DQ'));

        $saude = serialize($request->getParam('saude'));

        $indicacao_urgencia = serialize($request->getParam('indicacao_urgencia'));

        if($request->getParam('data_inicio') == "")
        {
            $data_inicio = date("Y-m-d H:i:s");
        } else {
            $data_inicio = explode("/", $request->getParam('data_inicio'));
            $data_inicio = $data_inicio[2] . "-" . $data_inicio[1] . "-" . $data_inicio[0] . ' ' . date('H:i:s');
            // $data_inicio = date("Y-m-d", strtotime($data_inicio));
        }

        if($request->getParam('convenio') == "")
        {
            $data_inicio_convenio = '';
        } else {
            $data_inicio_convenio = explode("/", $request->getParam('data_inicio_convenio'));
            $data_inicio_convenio = $data_inicio_convenio[2] . "-" . $data_inicio_convenio[1] . "-" . $data_inicio_convenio[0];
        }

        if($request->getParam('data_nascimento_conjuge') != null)
        {
            $data_nascimento_conjuge = explode("/", $request->getParam('data_nascimento_conjuge'));
            $data_nascimento_conjuge = $data_nascimento_conjuge[2]."-".$data_nascimento_conjuge[1]."-".$data_nascimento_conjuge[0];
        }

        $previsao_saida = explode("/", $request->getParam('previsao_saida'));
        $previsao_saida = $previsao_saida[2]."-".$previsao_saida[1]."-".$previsao_saida[0];
        
        $numero_manual = $request->getParam('numero_manual');

        // se alterar a data de entrada e o acolhimento for particular
        if($item->tipo_acolhimento == 1){
            $data_inicio_old = $item->data_inicio;
        }

        $item->update([
            'numero_manual' => $numero_manual, 
            'acolhido' => $request->getParam('acolhido'), 
            'tipo_acolhimento' => $request->getParam('tipo_acolhimento'), 
            'convenio' => $request->getParam('convenio'), 
            'valor_material_didatico' => $request->getParam('valor_material_didatico'), 
            'forma_pgto_material_didatico' => $request->getParam('forma_pgto_material_didatico'), 
            'valor_mensalidade' => $request->getParam('valor_mensalidade'), 
            'previsao_saida' => $previsao_saida, 
            'religiao' => $request->getParam('religiao'), 
            'pratica_religiao' => $request->getParam('pratica_religiao'), 
            'estado_civil' => $request->getParam('estado_civil'), 
            'nome_conjuge' => $request->getParam('nome_conjuge'), 
            'data_nascimento_conjuge' => $data_nascimento_conjuge, 
            'profissao_conjuge' => $request->getParam('profissao_conjuge'), 
            'profissao' => $request->getParam('profissao'), 
            'codificacao_profissao' => $request->getParam('codificacao_profissao'), 
            'estuda' => $request->getParam('estuda'), 
            'escolaridade' => $request->getParam('escolaridade'), 
            'estrato_social' => $estrato_social, 
            'resultado_estrato_social' => $resultado_estrato_social, 
            'dependencia_quimica' => $dependencia_quimica, 
            'saude' => $saude, 
            'observacoes_do_entrevistador' => $request->getParam('observacoes_do_entrevistador'), 
            'fatores_de_protecao' => $request->getParam('fatores_de_protecao'), 
            'fatores_de_risco' => $request->getParam('fatores_de_risco'), 
            'chance_abandono_previsao_permanencia' => $request->getParam('chance_abandono_previsao_permanencia'), 
            'indicacao_urgencia' => $indicacao_urgencia, 
            'unidade' => $request->getParam('unidade'), 
            'data_inicio' => $data_inicio, 
            'data_inicio_convenio' => $data_inicio_convenio, 
        ]);

        // if($item)
        // {
        //     if($request->getParam('tipo_acolhimento') == 1){
        //         $config = Config::find(1);
        //         for($x = 0; $x < count($request->getParam('data_vencimento')); $x++)
        //         {
        //             if($request->getParam('data_inicio') != $item->data_inicio){
        //                 $data_vencimento = date('Y-m-d', strtotime("+".[$x]." months", strtotime($request->getParam('data_inicio'))));
        //                 // $nova_data_vencimento = explode('/', $data_vencimento);
        //                 // $nova_data_vencimento = $nova_data_vencimento[2] .'-'. $nova_data_vencimento[1] .'-'. $nova_data_vencimento[0];
        //             $parcela = $x + 1;
        //             $mensalidade = Mensalidades::where('id', $request->getParam('id_mensalidade')[$x]);
        //             $mensalidade->update([
        //                 'parcela' => $parcela, 
        //                 'valor' => $request->getParam('valor_mensalidade'), 
        //                 'data_vencimento' => $data_vencimento,
        //                 // 'status' => 0
        //             ]);
        //         }
        //     }

        //     return true;
        // }
        // else
        // {
        //     return false;
        // }
    }

    // Remove um acolhido
    public function getRemover ($request, $response, $args)
    {
        if($this->acesso_usuario['acolhimentos']['index']['d'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Acolhimentos::find($args['id']);

        if($item->delete())
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    // Remove uma foto do acolhido
    public function getRemoverFoto ($request, $response, $args)
    {
        $item = Snapshots::find($args['id']);

        if(unlink('public/uploads/acolhidos/fotos/'.$item['imagem'])){
            if($item->delete())
            {
                return true;
            }
            else
            {
                return false;
            }
        } else {
            return false;
        }
    }

    // NOVO
    public function novo ($request, $response, $args)
    {
        if($this->acesso_usuario['acolhimentos']['index']['u'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $this->view->offsetSet("flash", $this->flash->getMessages());

        $item = Acolhimentos::find($args['id']);
        $unidade = Unidades::find($item['unidade']);
        $quarto = Quartos::find($item['quarto']);
        $unidades = Unidades::get()->toArray();
        $acolhido = Acolhidos::find($item['acolhido']);
        $acolhidos = Acolhidos::get()->toArray();
        $convenio = Convenios::find($item['convenio']);
        $convenios = Convenios::get()->toArray();
        $timeline = Timeline::where('acolhimento', $args['id'])->orderBy('id', 'DESC')->get()->toArray();
        $ressocializacao = Ressocializacao::where('acolhimento', $args['id'])->orderBy('id', 'DESC')->get()->toArray();
        $mensalidades = Mensalidades::where('acolhimento', $args['id'])->get()->toArray();
        $tecnicos_referencia = TecnicosReferencia::get()->toArray();

        /* puxa o cronograma de atividades para ser inserido no PAS */
        $atividades = CronogramaAtividades::orderBy('id', 'asc')->get()->toArray();
        $grupos = GruposCronogramaAtividades::orderBy('id', 'asc')->get()->toArray();

        $snapshots = Snapshots::orderBy('id','desc')->where('acolhido', $item['acolhido'])->get()->toArray();
        $contatos = Contatos::orderBy('id','desc')->where('acolhido', $item['acolhido'])->get()->toArray();
        $arquivos = ArquivosAcolhimentos::orderBy('id','desc')->where('acolhido', $item['acolhido'])->get()->toArray();
        
        $estrato_social = unserialize($item['estrato_social']);
        $dependencia_quimica = unserialize($item['dependencia_quimica']);
        $saude = unserialize($item['saude']);
        $indicacao_urgencia = unserialize($item['indicacao_urgencia']);

        // VERIFICA SE JÁ TEM UM CONTATO PRINCIPAL
        $contato_principal = Contatos::where('acolhido', $acolhido['id'])->where('status', 1)->count();
        if($contato_principal >= 1){
            $this->view->offsetSet("tem_contato_principal", true);
        }

        // VERIFICA SE O PAS JÁ FOI ABERTO/INICIADO
        $verifica_pas = Pas::where('acolhido', $acolhido['id'])->where('acolhimento', $args['id'])->where('status', 1)->count();
        if($verifica_pas >= 1){
            $this->view->offsetSet("pas_aberto", 1);
            
            // PEGA AS INFORMAÇÕES DO PAS
            $pas = Pas::where('acolhido', $acolhido['id'])->where('acolhimento', $args['id'])->where('status', 1)->first();
            $this->view->offsetSet("pas", $pas);
            $this->view->offsetSet("pas_anexo_1", unserialize($pas['anexo_1']));
            $this->view->offsetSet("pas_anexo_2", unserialize($pas['anexo_2']));

            // PUXA AS METAS DO PAS
            $metas_pas = Metas::where('acolhimento', $args['id'])->get()->toArray();
            $this->view->offsetSet("metas_pas", $metas_pas);
        } else {
            $this->view->offsetSet("pas_aberto", 0);
        }

        // VERIFICA SE O ESTUDO DE CASO JÁ FOI ABERTO/INICIADO
        $verifica_estudo_caso = EstudoCaso::where('acolhido', $acolhido['id'])->where('acolhimento', $args['id'])->where('status', 1)->count();
        if($verifica_estudo_caso >= 1){
            $this->view->offsetSet("estudo_caso_aberto", 1);
            
            // PEGA AS INFORMAÇÕES DO ESTUDO DE CASO
            $estudo_caso = EstudoCaso::where('acolhido', $acolhido['id'])->where('acolhimento', $args['id'])->where('status', 1)->first();
            $this->view->offsetSet("estudo_caso", $estudo_caso);
            $this->view->offsetSet("pas_anexo_1", unserialize($pas['anexo_1']));
            $this->view->offsetSet("pas_anexo_2", unserialize($pas['anexo_2']));

            // PUXA AS METAS DO ESTUDO DE CASO
            $metas_estudo_caso = EstudoCasoMetas::where('acolhimento', $args['id'])->get()->toArray();
            $this->view->offsetSet("metas_estudo_caso", $metas_estudo_caso);

            // PUXA AS EVOLUÇÕES DO ESTUDO DE CASO
            $evolucoes_estudo_caso = EstudoCasoEvolucao::where('acolhimento', $args['id'])->get()->toArray();
            $this->view->offsetSet("evolucoes_estudo_caso", $evolucoes_estudo_caso);
        } else {
            $this->view->offsetSet("estudo_caso_aberto", 0);
        }

        // $clone = clone $item;

        return $this->view->render($response, 'acolhimentos/novo.html', [
            'Titulo_Pagina' => 'Editar Registro',
            'titulo'    => 'Editar acolhimento - ' . $acolhido['nome'],
            'subtitulo' => 'Preencha o formulário abaixo para editar este acolhimento na unidade - ' . $unidade['nome'],
            'view' => 'editar',
            'item' => $item,
            // 'clone' => $clone->getAttributes(),
            'acolhido' => $acolhido,
            'acolhidos' => $acolhidos,
            'convenio' => $convenio,
            'convenios' => $convenios,
            'es' => $estrato_social,
            'dq' => $dependencia_quimica,
            'saude' => $saude,
            'indicacao_urgencia' => $indicacao_urgencia,
            'snapshots' => $snapshots,
            'contatos' => $contatos,
            'arquivos' => $arquivos,
            'unidade' => $unidade,
            'quarto' => $quarto,
            'unidades' => $unidades,
            'timeline' => $timeline,
            'ressocializacao' => $ressocializacao,
            'mensalidades' => $mensalidades,
            'atividades' => $atividades,
            'grupos' => $grupos,
            'tecnicos_referencia' => $tecnicos_referencia,
        ]);
    }

    // Cadastra um acolhimento no banco de dados
    public function add_acolhimento ($request, $response, $args)
    {   
        if($request->getParam('data_inicio') == "")
        {
            $data_inicio = date("Y-m-d H:i:s");
        } else {
            $data_inicio = explode("/", $request->getParam('data_inicio'));
            $data_inicio = $data_inicio[2] . "-" . $data_inicio[1] . "-" . $data_inicio[0] . ' ' . date('H:i:s');
            // $data_inicio = date("Y-m-d", strtotime($data_inicio));
        }

        if($request->getParam('convenio') == "")
        {
            $data_inicio_convenio = '';
        } else {
            $data_inicio_convenio = explode("/", $request->getParam('data_inicio_convenio'));
            $data_inicio_convenio = $data_inicio_convenio[2] . "-" . $data_inicio_convenio[1] . "-" . $data_inicio_convenio[0];
        }

        $previsao_saida = explode("/", $request->getParam('previsao_saida'));
        $previsao_saida = $previsao_saida[2]."-".$previsao_saida[1]."-".$previsao_saida[0];
        
        $numero_manual = $request->getParam('numero_manual');
        
        $item = Acolhimentos::create([
            'numero_manual' => $numero_manual, 
            'acolhido' => $request->getParam('acolhido'), 
            'tipo_acolhimento' => $request->getParam('tipo_acolhimento'), 
            'convenio' => $request->getParam('convenio'), 
            'valor_material_didatico' => $request->getParam('valor_material_didatico'), 
            'forma_pgto_material_didatico' => $request->getParam('forma_pgto_material_didatico'), 
            'valor_mensalidade' => $request->getParam('valor_mensalidade'), 
            'previsao_saida' => $previsao_saida, 
            'unidade' => $request->getParam('unidade'), 
            'quarto' => $request->getParam('quarto'), 
            'data_inicio' => $data_inicio, 
            'data_inicio_convenio' => $data_inicio_convenio, 
            'pertences' => $request->getParam('pertences'), 
            'tecnico_referencia' => $request->getParam('tecnico_referencia'), 
            'voluntario' => $request->getParam('voluntario'), 
            'terceirizado' => $request->getParam('terceirizado'), 
            'fase' => 1, 
            'data_saida' => '', 
            'status' => 0
        ]);

        if($item)
        {
            // altera o status do ACOLHIDO para ALOCADO
            $atualiza_status_acolhido = Acolhidos::find($request->getParam('acolhido'));
            $atualiza_status_acolhido->update([
                'status' => 1
            ]);

            // se o acolhimento for particular
            if($request->getParam('tipo_acolhimento') == 1){
                for($x = 0; $x < count($request->getParam('data_vencimento')); $x++)
                {
                    $data_vencimento = $request->getParam('data_vencimento')[$x];
                    $nova_data_vencimento = explode('/', $data_vencimento);
                    $nova_data_vencimento = $nova_data_vencimento[2] .'-'. $nova_data_vencimento[1] .'-'. $nova_data_vencimento[0];
                    $parcela = $x + 1;
                    $mensalidade = Mensalidades::create([
                        'acolhimento' => $item->id, 
                        'parcela' => $parcela, 
                        'valor' => $request->getParam('valor_mensalidade'), 
                        'data_vencimento' => $nova_data_vencimento,
                        'status' => 0
                    ]);
                }
                
                // se for a vista
                if($request->getParam('forma_pgto_material_didatico') == 1){
                    $matricula = Matriculas::create([
                        'acolhimento' => $item->id, 
                        'valor' => $request->getParam('valor_material_didatico'), 
                        'data_vencimento' => date('Y-m-d'),
                        'status' => 0
                    ]);
                }

                // se for 1+1
                if($request->getParam('forma_pgto_material_didatico') == 2){

                    $valor_material_didatico = $request->getParam('valor_material_didatico') / 2;

                    $matricula = Matriculas::create([
                        'acolhimento' => $item->id, 
                        'valor' => $valor_material_didatico, 
                        'data_vencimento' => date('Y-m-d'),
                        'status' => 0
                    ]);

                    $matricula = Matriculas::create([
                        'acolhimento' => $item->id, 
                        'valor' => $valor_material_didatico, 
                        'data_vencimento' => date('Y-m-d', strtotime("+1 month")),
                        'status' => 0
                    ]);
                }

                // se for isento
                if($request->getParam('forma_pgto_material_didatico') == 3){
                    $matricula = Matriculas::create([
                        'acolhimento' => $item->id, 
                        'valor' => '0.00', 
                        'data_vencimento' => date('Y-m-d'),
                        'status' => 0
                    ]);
                }
            }

            $descricao = 'Acolhimento iniciado';
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $item->id,
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Acolhimento',
                'descricao' => $descricao,
                'color' => 'success',
                'icon' => 'mdi mdi-hotel',
            ]);
            $this->flash->addMessage('success', 'Acolhimento iniciado com sucesso!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao iniciar o acolhimento!');
        }
        return $response->withRedirect($this->router->pathFor('editar_acolhimento', ['id' => $item->id]));
    }

    // Editar acolhimento (novo)
    public function editar_acolhimento ($request, $response, $args)
    {   
        $item = Acolhimentos::find($args['id']);
        $config = Config::find(1);

        if($request->getParam('data_inicio') == "")
        {
            $data_inicio = date("Y-m-d H:i:s");
        } else {
            $data_inicio = explode("/", $request->getParam('data_inicio'));
            $data_inicio = $data_inicio[2] . "-" . $data_inicio[1] . "-" . $data_inicio[0] . ' ' . date('H:i:s');
            // $data_inicio = date("Y-m-d", strtotime($data_inicio));
        }

        if($request->getParam('convenio') == "")
        {
            $data_inicio_convenio = '';
        } else {
            $data_inicio_convenio = explode("/", $request->getParam('data_inicio_convenio'));
            $data_inicio_convenio = $data_inicio_convenio[2] . "-" . $data_inicio_convenio[1] . "-" . $data_inicio_convenio[0];
        }

        $previsao_saida = explode("/", $request->getParam('previsao_saida'));
        $previsao_saida = $previsao_saida[2]."-".$previsao_saida[1]."-".$previsao_saida[0];
        
        $convenio = $request->getParam('convenio');
        $convenio_nome = Convenios::find($convenio);
        $valor_mensalidade = $request->getParam('valor_mensalidade');

        // se o acolhimento for convenio
        if($request->getParam('tipo_acolhimento') == 0){
            $tipo_acolhimento = 'Convênio (' . $convenio_nome['nome'] . ')';
            $valor_mensalidade = '';
        }

        // se o acolhimento for vaga social
        if($request->getParam('tipo_acolhimento') == 2){
            $tipo_acolhimento = 'Vaga Social';
            $convenio = '';
            $valor_mensalidade = ''; 
            $data_inicio_convenio = '';
        }

        // se o acolhimento for morador assistido
        if($request->getParam('tipo_acolhimento') == 3){
            $tipo_acolhimento = 'Morador Assistido';
            $convenio = '';
            $data_inicio_convenio = '';
        }

        // se alterar a data de entrada e o acolhimento for particular
        if($item->tipo_acolhimento == 1){
            $data_inicio_old = $item->data_inicio;
        }

        $item->update([
            // 'acolhido' => $request->getParam('acolhido'), 
            'tipo_acolhimento' => $request->getParam('tipo_acolhimento'), 
            'convenio' => $convenio, 
            'valor_material_didatico' => $request->getParam('valor_material_didatico'), 
            'forma_pgto_material_didatico' => $request->getParam('forma_pgto_material_didatico'), 
            'valor_mensalidade' => $valor_mensalidade, 
            'previsao_saida' => $previsao_saida, 
            // 'unidade' => $request->getParam('unidade'), 
            // 'quarto' => $request->getParam('quarto'), 
            'data_inicio' => $data_inicio, 
            'data_inicio_convenio' => $data_inicio_convenio, 
            'pertences' => $request->getParam('pertences'), 
            'tecnico_referencia' => $request->getParam('tecnico_referencia'), 
            'voluntario' => $request->getParam('voluntario'), 
            'terceirizado' => $request->getParam('terceirizado'), 
        ]);

        if($item)
        {
            // altera o status do ACOLHIDO para ALOCADO
            // $atualiza_status_acolhido = Acolhidos::find($request->getParam('acolhido'));
            // $atualiza_status_acolhido->update([
            //     'status' => 1
            // ]);

            $descricao = 'Acolhimento editado';
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            // se mudar o tipo de acolhimento
            if($request->getParam('tipo_acolhimento_original') != $request->getParam('tipo_acolhimento')){

                if($request->getParam('tipo_acolhimento') == 0){
                    $tipo_acolhimento = 'Convênio';
                }

                if($request->getParam('tipo_acolhimento') == 1){
                    $tipo_acolhimento = 'Particular';
                }

                if($request->getParam('tipo_acolhimento') == 2){
                    $tipo_acolhimento = 'Vaga Social';
                }

                if($request->getParam('tipo_acolhimento') == 3){
                    $tipo_acolhimento = 'Morador Assistido';
                }

                if($request->getParam('tipo_acolhimento_original') == 0){
                    $tipo_acolhimento_original = 'Convênio';
                }

                if($request->getParam('tipo_acolhimento_original') == 1){
                    $tipo_acolhimento_original = 'Particular';
                }

                if($request->getParam('tipo_acolhimento_original') == 2){
                    $tipo_acolhimento_original = 'Vaga Social';
                }

                if($request->getParam('tipo_acolhimento_original') == 3){
                    $tipo_acolhimento_original = 'Morador Assistido';
                }

                $descricao .= "\n" . 'Alterado de: ' . $tipo_acolhimento_original . "\n" . 'para: ' . $tipo_acolhimento; 
            }

            // se o acolhimento for particular
            if($request->getParam('tipo_acolhimento') == 1){
                    // for($x = 0; $x < count($request->getParam('data_vencimento')); $x++)
                    // {
                    //     $data_vencimento = $request->getParam('data_vencimento')[$x];
                    //     $nova_data_vencimento = explode('/', $data_vencimento);
                    //     $nova_data_vencimento = $nova_data_vencimento[2] .'-'. $nova_data_vencimento[1] .'-'. $nova_data_vencimento[0];
                    //     $parcela = $x + 1;
                    //     $mensalidade = Mensalidades::create([
                    //         'acolhimento' => $item->id, 
                    //         'parcela' => $parcela, 
                    //         'valor' => $request->getParam('valor_mensalidade'), 
                    //         'data_vencimento' => $nova_data_vencimento,
                    //         'status' => 0
                    //     ]);
                    // }

                for($x = 0; $x < count($request->getParam('data_vencimento')); $x++)
                {
                    if($request->getParam('data_inicio') != $data_inicio_old){
                        $data_vencimento = $request->getParam('data_inicio');
                        $nova_data_vencimento = explode('/', $data_vencimento);
                        $nova_data_vencimento = $nova_data_vencimento[2] .'-'. $nova_data_vencimento[1] .'-'. $nova_data_vencimento[0];
                        // $nova_data_vencimento = date('Y-m-d', strtotime($nova_data_vencimento));
                        $parcela = $x + 1;
                        $nova_data_vencimento = date('Y-m-d', strtotime("+".$x." months", strtotime($nova_data_vencimento)));
                        $mensalidade = Mensalidades::where('id', $request->getParam('id_mensalidade')[$x]);
                        $mensalidade->update([
                            'parcela' => $parcela, 
                            'valor' => $request->getParam('valor_mensalidade'), 
                            'data_vencimento' => $nova_data_vencimento,
                            // 'status' => 0
                        ]);
                    }
                }
            }

            // se o acolhimento for morador assistido
            if($request->getParam('tipo_acolhimento') == 3){
                $data_hoje = new DateTime(date('Y-m-d'));
                $data_inicio = new DateTime($item->data_inicio);
                $intervalo = $data_inicio->diff($data_hoje);
                $total_meses = $intervalo->m + ($intervalo->y * 12);

                // limpa todas as outras mensalidades anteriores
                $mensalidade = Mensalidades::where('acolhimento', $item->id);
                $mensalidade->delete();

                for($x = 0; $x <= $total_meses; $x++){
                    $parcela = $x + 1;
                    $data_vencimento = $item->data_inicio;
                    $nova_data_vencimento = date('Y-m-d', strtotime("+".$x." months", strtotime($data_vencimento)));
                    $mensalidade = Mensalidades::create([
                        'acolhimento' => $item->id, 
                        'parcela' => $parcela, 
                        'valor' => $request->getParam('valor_mensalidade'), 
                        'data_vencimento' => $nova_data_vencimento,
                        'data_pagamento' => $nova_data_vencimento,
                        'status' => 1
                    ]);
                }
            }

            // gera o material didático/matrícula
            if($request->getParam('tipo_acolhimento') != 0){
                $matricula = Matriculas::where('acolhimento', $item->id)->count();
                
                //se não tiver material didatico no financeiro
                if($matricula = 0){

                    // se for a vista
                    if($request->getParam('forma_pgto_material_didatico') == 1){
                        $matricula = Matriculas::create([
                            'acolhimento' => $item->id, 
                            'valor' => $request->getParam('valor_material_didatico'), 
                            'data_vencimento' => date('Y-m-d'),
                            'status' => 0
                        ]);
                    }

                    // se for 1+1
                    if($request->getParam('forma_pgto_material_didatico') == 2){

                        $valor_material_didatico = $request->getParam('valor_material_didatico') / 2;

                        $matricula = Matriculas::create([
                            'acolhimento' => $item->id, 
                            'valor' => $valor_material_didatico, 
                            'data_vencimento' => date('Y-m-d'),
                            'status' => 0
                        ]);

                        $matricula = Matriculas::create([
                            'acolhimento' => $item->id, 
                            'valor' => $valor_material_didatico, 
                            'data_vencimento' => date('Y-m-d', strtotime("+1 month")),
                            'status' => 0
                        ]);
                    }

                    // se for isento
                    if($request->getParam('forma_pgto_material_didatico') == 3){
                        $matricula = Matriculas::create([
                            'acolhimento' => $item->id, 
                            'valor' => '0.00', 
                            'data_vencimento' => date('Y-m-d'),
                            'status' => 0
                        ]);
                    }

                }
            }

            $timeline = Timeline::create([
                'acolhimento' => $item->id,
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Acolhimento',
                'descricao' => $descricao,
                'color' => 'primary',
                'icon' => 'mdi mdi-hotel',
            ]);
            $this->flash->addMessage('success', 'Acolhimento editado com sucesso!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao editar o acolhimento!');
        }
        return $response->withRedirect($this->router->pathFor('editar_acolhimento', ['id' => $item->id]));
    }

    // FICHA DE ENTREVISTA
    public function ficha_entrevista ($request, $response, $args)
    {
        if($this->acesso_usuario['acolhimentos']['index']['u'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $this->view->offsetSet("flash", $this->flash->getMessages());

        $item = Acolhimentos::find($args['id']);
        $entrevista = FichaEntrevista::where('acolhimento', $args['id'])->first();
        $acolhido = Acolhidos::find($item['acolhido']);
        $unidade = Unidades::find($item['unidade']);
        $quarto = Quartos::find($item['quarto']);
        $convenio = Convenios::find($item['convenio']);
        $usuario = Users::find($entrevista['usuario']);

        $estrato_social = unserialize($entrevista['estrato_social']);
        $dependencia_quimica = unserialize($entrevista['dependencia_quimica']);
        $saude = unserialize($entrevista['saude']);

        // VERIFICA SE JÁ FOI PREENCHIDO A FICHA DE ENTREVISTA
        $ficha_entrevista = FichaEntrevista::where('acolhimento', $args['id'])->where('status', 1)->count();
        if($ficha_entrevista >= 1){
            $this->view->offsetSet("tem_ficha_entrevista", true);
        }

        // VERIFICA SE JÁ TEM UM CONTATO PRINCIPAL
        $contato_principal = Contatos::where('acolhido', $acolhido['id'])->where('status', 1)->count();
        if($contato_principal >= 1){
            $this->view->offsetSet("tem_contato_principal", true);
        }
        
        // for ($i=0; $i < count($evolucoes) ; $i++) { 
        //     $usuario[$evolucoes[$i]['id']] = Users::find($evolucoes[$i]['usuario']);
        //     $this->view->offsetSet("usuario", $usuario);
        // }

        return $this->view->render($response, 'acolhimentos/novo.html', [
            'Titulo_Pagina' => 'Ficha de Entrevista',
            'view' => 'editar',
            'item' => $item,
            'entrevista' => $entrevista,
            'acolhido' => $acolhido,
            'unidade' => $unidade,
            'quarto' => $quarto,
            'usuario' => $usuario,
            'convenio' => $convenio,
            'es' => $estrato_social,
            'dq' => $dependencia_quimica,
            'saude' => $saude,
            'view' => 'ficha-entrevista'
        ]);
    }

    // CADASTRAR FICHA DE ENTREVISTA
    public function add_ficha_entrevista ($request, $response, $args)
    {
        $estrato_social =
        [
            'nome_chefe_familia' => $request->getParam('nome_chefe_familia'), 
            'chefe_familia' => $request->getParam('chefe_familia'), 
            'banheiros' => $request->getParam('banheiros'), 
            'empregados' => $request->getParam('empregados'),
            'automoveis' => $request->getParam('automoveis'), 
            'microcomputador' => $request->getParam('microcomputador'), 
            'lava_louca' => $request->getParam('lava_louca'), 
            'geladeira' => $request->getParam('geladeira'), 
            'freezer' => $request->getParam('freezer'), 
            'lava_roupas' => $request->getParam('lava_roupas'), 
            'dvd' => $request->getParam('dvd'), 
            'microondas' => $request->getParam('microondas'),
            'motocicleta' => $request->getParam('motocicleta'), 
            'secador_de_roupas' => $request->getParam('secador_de_roupas'), 
            'agua_encanada' => $request->getParam('agua_encanada'),
            'rua_pavimentada' => $request->getParam('rua_pavimentada')
        ];
            
        $estrato_social = serialize($estrato_social);
            
        if($request->getParam('lava_louca') == 61 OR $request->getParam('lava_louca') == 62)
        {
            $lava_louca = 6;
        }
            
        if($request->getParam('geladeira') == 51)
        {
            $geladeira = 5;
        }
            
        if($request->getParam('freezer') == 61)
        {
            $freezer = 6;
        }
            
        if($request->getParam('lava_roupas') == 61)
        {
            $lava_roupas = 6;
        }
            
        if($request->getParam('microondas') == 41 OR $request->getParam('microondas') == 42)
        {
            $microondas = 4;
        }
            
        if($request->getParam('motocicleta') == 31 OR $request->getParam('motocicleta') == 32)
        {
            $motocicleta = 3;
        }
            
        if($request->getParam('secador_de_roupas') == 21 OR $request->getParam('secador_de_roupas') == 22)
        {
            $secador_de_roupas = 2;
        }
            
        $resultado_estrato_social =
                $request->getParam('chefe_familia') + 
                $request->getParam('banheiros') + 
                $request->getParam('empregados') + 
                $request->getParam('automoveis') + 
                $request->getParam('microcomputador') + 
                $lava_louca + 
                $geladeira + 
                $freezer + 
                $lava_roupas + 
                $request->getParam('dvd') + 
                $microondas + 
                $motocicleta + 
                $secador_de_roupas + 
                $request->getParam('agua_encanada') + 
                $request->getParam('rua_pavimentada');

        $dependencia_quimica = serialize($request->getParam('DQ'));

        $saude = serialize($request->getParam('saude'));

        $indicacao_urgencia = serialize($request->getParam('indicacao_urgencia'));

        if($request->getParam('data_nascimento_conjuge') != null)
        {
            $data_nascimento_conjuge = explode("/", $request->getParam('data_nascimento_conjuge'));
            $data_nascimento_conjuge = $data_nascimento_conjuge[2]."-".$data_nascimento_conjuge[1]."-".$data_nascimento_conjuge[0];
        }

        // VERIFICA SE JÁ TEM UMA FICHA DE ENTREVISTA
        $ficha_entrevista = FichaEntrevista::where('acolhimento', $request->getParam('acolhimento'))->where('status', 1)->count();
        if($ficha_entrevista >= 1){
            $item = FichaEntrevista::where('acolhimento', $request->getParam('acolhimento'))->where('status', 1)->first();
            $item->update([
                'data' => $request->getParam('data_entrevista'), 
                'acolhido' => $request->getParam('acolhido'), 
                'acolhimento' => $request->getParam('acolhimento'), 
                'naturalidade' => $request->getParam('naturalidade'), 
                'uf_naturalidade' => $request->getParam('uf_naturalidade'), 
                'cidade_encaminhamento' => $request->getParam('cidade_encaminhamento'), 
                'uf_encaminhamento' => $request->getParam('uf_encaminhamento'), 
                'religiao' => $request->getParam('religiao'), 
                'pratica_religiao' => $request->getParam('pratica_religiao'), 
                'estado_civil' => $request->getParam('estado_civil'), 
                'nome_conjuge' => $request->getParam('nome_conjuge'), 
                'data_nascimento_conjuge' => $data_nascimento_conjuge, 
                'profissao_conjuge' => $request->getParam('profissao_conjuge'), 
                'profissao' => $request->getParam('profissao'), 
                'codificacao_profissao' => $request->getParam('codificacao_profissao'), 
                'trabalha' => $request->getParam('trabalha'), 
                'estuda' => $request->getParam('estuda'), 
                'escolaridade' => $request->getParam('escolaridade'), 
                'estrato_social' => $estrato_social, 
                'resultado_estrato_social' => $resultado_estrato_social, 
                'dependencia_quimica' => $dependencia_quimica, 
                'saude' => $saude,
            ]);
            $descricao = 'Ficha de entrevista atualizada';
        } else {
            $item = FichaEntrevista::create([
                'data' => $request->getParam('data_entrevista'), 
                'acolhido' => $request->getParam('acolhido'), 
                'acolhimento' => $request->getParam('acolhimento'), 
                'naturalidade' => $request->getParam('naturalidade'), 
                'uf_naturalidade' => $request->getParam('uf_naturalidade'), 
                'cidade_encaminhamento' => $request->getParam('cidade_encaminhamento'), 
                'uf_encaminhamento' => $request->getParam('uf_encaminhamento'), 
                'religiao' => $request->getParam('religiao'), 
                'pratica_religiao' => $request->getParam('pratica_religiao'), 
                'estado_civil' => $request->getParam('estado_civil'), 
                'nome_conjuge' => $request->getParam('nome_conjuge'), 
                'data_nascimento_conjuge' => $data_nascimento_conjuge, 
                'profissao_conjuge' => $request->getParam('profissao_conjuge'), 
                'profissao' => $request->getParam('profissao'), 
                'codificacao_profissao' => $request->getParam('codificacao_profissao'), 
                'trabalha' => $request->getParam('trabalha'), 
                'estuda' => $request->getParam('estuda'), 
                'escolaridade' => $request->getParam('escolaridade'), 
                'estrato_social' => $estrato_social, 
                'resultado_estrato_social' => $resultado_estrato_social, 
                'dependencia_quimica' => $dependencia_quimica, 
                'saude' => $saude, 
                'usuario' => $_SESSION['Auth'], 
                // 'observacoes_do_entrevistador' => $request->getParam('observacoes_do_entrevistador'), 
                // 'fatores_de_protecao' => $request->getParam('fatores_de_protecao'), 
                // 'fatores_de_risco' => $request->getParam('fatores_de_risco'), 
                // 'chance_abandono_previsao_permanencia' => $request->getParam('chance_abandono_previsao_permanencia'), 
                // 'indicacao_urgencia' => $indicacao_urgencia, 
                'status' => 1
            ]);
            $descricao = 'Ficha de entrevista preenchida';
        }
        if($item)
        {
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $request->getParam('acolhimento'),
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Ficha de Entrevista',
                'descricao' => $descricao,
                'color' => 'success',
                'icon' => 'ti-write',
            ]);
            return true;
        }
        else
        {
            return false;
        }
    }

    // FICHA DE EVOLUÇÃO
    public function ficha_evolucao ($request, $response, $args)
    {
        if($this->acesso_usuario['acolhimentos']['index']['u'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $this->view->offsetSet("flash", $this->flash->getMessages());

        $item = Acolhimentos::find($args['id']);
        $evolucoes = Evolucoes::where('acolhimento', $args['id'])->orderBy('id', 'DESC')->get()->toArray();
        $acolhido = Acolhidos::find($item['acolhido']);
        $unidade = Unidades::find($item['unidade']);
        $quarto = Quartos::find($item['quarto']);
        $convenio = Convenios::find($item['convenio']);

        // VERIFICA SE JÁ TEM UM CONTATO PRINCIPAL
        $contato_principal = Contatos::where('acolhido', $acolhido['id'])->where('status', 1)->count();
        if($contato_principal >= 1){
            $this->view->offsetSet("tem_contato_principal", true);
        }
        
        for ($i=0; $i < count($evolucoes) ; $i++) { 
            $usuario[$evolucoes[$i]['id']] = Users::find($evolucoes[$i]['usuario']);
            $this->view->offsetSet("usuario", $usuario);
        }

        return $this->view->render($response, 'acolhimentos/novo.html', [
            'Titulo_Pagina' => 'Ficha de Evolução',
            'view' => 'editar',
            'item' => $item,
            'evolucoes' => $evolucoes,
            'acolhido' => $acolhido,
            'unidade' => $unidade,
            'quarto' => $quarto,
            'usuario' => $usuario,
            'convenio' => $convenio,
            'view' => 'ficha-evolucao'
        ]);
    }

    // ADICIONAR FICHA EVOLUÇÃO
    public function add_ficha_evolucao ($request, $response, $args)
    {   
        // $data = explode("/", $request->getParam('data'));
        // $data = $data[2]."-".$data[1]."-".$data[0];

        $acolhido = Acolhidos::find($request->getParam('acolhido'));
        $usuario = Users::find($request->getParam('usuario'));

        $item = Evolucoes::create([
            'usuario' => $request->getParam('usuario'),
            'acolhido' => $request->getParam('acolhido'),
            'acolhimento' => $request->getParam('acolhimento'),
            'data' => $request->getParam('data'),
            'registro' => $request->getParam('registro'),
        ]);

        if($item)
        {
            $descricao = 'Adicionado novo registro na Ficha de Evolução do acolhido ' . $acolhido['nome'];
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $request->getParam('acolhimento'),
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Ficha de Evolução',
                'descricao' => $descricao,
                'color' => 'success',
                'icon' => 'ti-write',
            ]);
            $this->flash->addMessage('success', 'Registro de Ficha de Evolução adicionado com sucesso!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao adicionar ficha de evolução!');
        }

        return $response->withRedirect($this->router->pathFor('ficha_evolucao', ['id' => $request->getParam('acolhimento')]));
    }

    // EDITAR FICHA EVOLUÇÃO
    public function editar_ficha_evolucao ($request, $response, $args)
    {   
        // $data = explode("/", $request->getParam('data'));
        // $data = $data[2]."-".$data[1]."-".$data[0];

        $item = Evolucoes::find($args['id']);
        $acolhido = Acolhidos::find($request->getParam('acolhido'));
        $usuario = Users::find($request->getParam('usuario'));

        $item->update([
            // 'usuario' => $request->getParam('usuario'),
            // 'acolhido' => $request->getParam('acolhido'),
            // 'acolhimento' => $request->getParam('acolhimento'),
            'data' => $request->getParam('data'),
            'registro' => $request->getParam('registro'),
        ]);

        if($item)
        {
            $descricao = 'Registro de Ficha de Evolução editado';
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $request->getParam('acolhimento'),
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Ficha de Evolução',
                'descricao' => $descricao,
                'color' => 'primary',
                'icon' => 'ti-write',
            ]);
            $this->flash->addMessage('success', 'Registro de Ficha de Evolução editado com sucesso!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao editar registro na ficha de evolução!');
        }

        return $response->withRedirect($this->router->pathFor('ficha_evolucao', ['id' => $request->getParam('acolhimento')]));
    }

    // REMOVER FICHA EVOLUÇÃO
    public function remover_ficha_evolucao ($request, $response, $args)
    {
        if($this->acesso_usuario['acolhimentos']['index']['d'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Evolucoes::find($args['id']);

        if($item->delete())
        {
            $descricao = 'Registro de Ficha de Evolução removido';
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $item['acolhimento'],
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Ficha de Evolução',
                'descricao' => $descricao,
                'color' => 'danger',
                'icon' => 'ti-write',
            ]);
            return true;
        }
        else
        {
            return false;
        }
    }

    // PARECER PROFISSIONAL
    public function parecer_profissional ($request, $response, $args)
    {
        if($this->acesso_usuario['acolhimentos']['index']['u'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $this->view->offsetSet("flash", $this->flash->getMessages());

        $item = Acolhimentos::find($args['id']);
        $parecer_profissional = ParecerProfissional::where('acolhimento', $args['id'])->orderBy('id', 'DESC')->get()->toArray();
        $acolhido = Acolhidos::find($item['acolhido']);
        $unidade = Unidades::find($item['unidade']);
        $quarto = Quartos::find($item['quarto']);
        $convenio = Convenios::find($item['convenio']);

        // VERIFICA SE JÁ TEM UM CONTATO PRINCIPAL
        $contato_principal = Contatos::where('acolhido', $acolhido['id'])->where('status', 1)->count();
        if($contato_principal >= 1){
            $this->view->offsetSet("tem_contato_principal", true);
        }
        
        for ($i=0; $i < count($parecer_profissional) ; $i++) { 
            $usuario[$parecer_profissional[$i]['id']] = Users::find($parecer_profissional[$i]['usuario']);
            $this->view->offsetSet("usuario", $usuario);
        }

        return $this->view->render($response, 'acolhimentos/novo.html', [
            'Titulo_Pagina' => 'Parecer Profissional',
            'view' => 'editar',
            'item' => $item,
            'parecer_profissional' => $parecer_profissional,
            'acolhido' => $acolhido,
            'unidade' => $unidade,
            'quarto' => $quarto,
            'usuario' => $usuario,
            'convenio' => $convenio,
            'view' => 'parecer-profissional'
        ]);
    }

    // ADICIONAR PARECER PROFISSIONAL
    public function add_parecer_profissional ($request, $response, $args)
    {   
        // $data = explode("/", $request->getParam('data'));
        // $data = $data[2]."-".$data[1]."-".$data[0];

        $acolhido = Acolhidos::find($request->getParam('acolhido'));
        $usuario = Users::find($request->getParam('usuario'));

        $item = ParecerProfissional::create([
            'usuario' => $request->getParam('usuario'),
            'acolhido' => $request->getParam('acolhido'),
            'acolhimento' => $request->getParam('acolhimento'),
            'data' => $request->getParam('data'),
            'registro' => $request->getParam('registro'),
        ]);

        if($item)
        {
            $descricao = 'Adicionado novo registro de Parecer Profissional para o acolhido ' . $acolhido['nome'];
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $request->getParam('acolhimento'),
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Parecer Profissional',
                'descricao' => $descricao,
                'color' => 'success',
                'icon' => 'ti-write',
            ]);
            $this->flash->addMessage('success', 'Registro de Parecer Profissional adicionado com sucesso!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao adicionar parecer profissional!');
        }

        return $response->withRedirect($this->router->pathFor('parecer_profissional', ['id' => $request->getParam('acolhimento')]));
    }

    // EDITAR PARECER PROFISSIONAL
    public function editar_parecer_profissional ($request, $response, $args)
    {   
        // $data = explode("/", $request->getParam('data'));
        // $data = $data[2]."-".$data[1]."-".$data[0];

        $item = ParecerProfissional::find($args['id']);
        $acolhido = Acolhidos::find($request->getParam('acolhido'));
        $usuario = Users::find($request->getParam('usuario'));

        $item->update([
            // 'usuario' => $request->getParam('usuario'),
            // 'acolhido' => $request->getParam('acolhido'),
            // 'acolhimento' => $request->getParam('acolhimento'),
            'data' => $request->getParam('data'),
            'registro' => $request->getParam('registro'),
        ]);

        if($item)
        {
            $descricao = 'Registro de Parecer Profissional editado';
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $request->getParam('acolhimento'),
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Parecer Profissional',
                'descricao' => $descricao,
                'color' => 'primary',
                'icon' => 'ti-write',
            ]);
            $this->flash->addMessage('success', 'Registro de Parecer Profissional editado com sucesso!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao editar registro de Parecer Profissional!');
        }

        return $response->withRedirect($this->router->pathFor('parecer_profissional', ['id' => $request->getParam('acolhimento')]));
    }

    // REMOVER PARECER PROFISSIONAL
    public function remover_parecer_profissional ($request, $response, $args)
    {
        if($this->acesso_usuario['acolhimentos']['index']['d'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = ParecerProfissional::find($args['id']);

        if($item->delete())
        {
            $descricao = 'Registro de Parecer Profissional removido';
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $item['acolhimento'],
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Ficha de Evolução',
                'descricao' => $descricao,
                'color' => 'danger',
                'icon' => 'ti-write',
            ]);
            return true;
        }
        else
        {
            return false;
        }
    }
##### END ACOLHIMENTOS #####

##### CONTATOS #####
    // Cadastra um contato para o acolhido no banco de dados
    public function postCadastrarContato ($request, $response, $args)
    {   
        $data_nascimento = explode("/", $request->getParam('data_nascimento'));
        $data_nascimento = $data_nascimento[2]."-".$data_nascimento[1]."-".$data_nascimento[0];

        $acolhido = Acolhidos::find($request->getParam('acolhido'));

        if($request->getParam('status') == 'on'){
            $status = 1;
            $principal = 'principal';
        } else {
            $status = 0;
            $principal = '';
        }

        $item = Contatos::create([
            'nome' => $request->getParam('nome'),
            'acolhido' => $request->getParam('acolhido'),
            'grau_parentesco' => $request->getParam('grau_parentesco'),
            'rg' => $request->getParam('rg'),
            'cpf' => $request->getParam('cpf'),
            'data_nascimento' => $data_nascimento,
            'naturalidade' => $request->getParam('naturalidade'),
            'telefone' => $request->getParam('telefone'),
            'celular' => $request->getParam('celular'),
            'email' => $request->getParam('email'),
            'status' => $status,
        ]);

        if($item)
        {
            $descricao = $request->getParam('nome') . ' (' . $request->getParam('grau_parentesco') . ') foi adicionado como contato ' . $principal . ' para ' . $acolhido['nome'];
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $request->getParam('acolhimento'),
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Novo contato',
                'descricao' => $descricao,
                'color' => $color,
                'icon' => 'ti-user',
            ]);

            return true;
        }
        else
        {
            return false;
        }
    }

    // Edita um contato para o acolhido no banco de dados
    public function postEditarContato (Request $request, Response $response, $args)
    {   
        $item = Contatos::find($args['id']);

        $data_nascimento = explode("/", $request->getParam('data_nascimento'));
        $data_nascimento = $data_nascimento[2]."-".$data_nascimento[1]."-".$data_nascimento[0];

        if($request->getParam('status') == 'on'){
            $status = 1;
        } else {
            $status = 0;
        }

        $status_anterior = $item->status;
        $item->update([
            'nome' => $request->getParam('nome'),
            'acolhido' => $request->getParam('acolhido'),
            'grau_parentesco' => $request->getParam('grau_parentesco'),
            'rg' => $request->getParam('rg'),
            'cpf' => $request->getParam('cpf'),
            'data_nascimento' => $data_nascimento,
            'naturalidade' => $request->getParam('naturalidade'),
            'telefone' => $request->getParam('telefone'),
            'celular' => $request->getParam('celular'),
            'email' => $request->getParam('email'),
            'status' => $status,
        ]);

        if($item)
        {   
            if($status == 1 && $status_anterior == 0){
                $descricao = $request->getParam('nome') . ' (' . $request->getParam('grau_parentesco') . ') foi editado e definido como contato principal';
            } else {
                $descricao = $request->getParam('nome') . ' (' . $request->getParam('grau_parentesco') . ') foi editado';
            }

            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $request->getParam('acolhimento'),
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Alteração de contato',
                'descricao' => $descricao,
                'color' => $color,
                'icon' => 'ti-user',
            ]);

            $this->flash->addMessage('success', 'Contato editado com sucesso!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao editar contato!');
        }
        
        return $response->withRedirect($this->router->pathFor('editar_acolhimento', ['id' => $request->getParam('acolhimento')]));
    }

    // Remove um contato
    public function getRemoverContato ($request, $response, $args)
    {
        $item = Contatos::find($args['id']);
        $acolhido = Acolhidos::find($item['acolhido']);
        $acolhimento = Acolhimentos::where('acolhido', $item['acolhido'])->where('status', 0)->limit(1)->get()->toArray();

        if($item->delete())
        {
            $descricao = $item['nome'] . ' (' . $item['grau_parentesco'] . ') foi removido';
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $acolhimento[0]['id'],
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Remoção de contato',
                'descricao' => $descricao,
                'color' => $color,
                'icon' => 'ti-user',
            ]);

            return true;
        }
        else
        {
            return false;
        }
    }
##### END CONTATOS #####

##### ARQUIVOS #####
    // Cadastra um arquivo para o acolhido no banco de dados
    public function postCadastrarArquivo ($request, $response, $args)
    {   
        $directory = '/var/www/escudodobem.gruub.com.br/httpdocs/sistema/public/uploads/acolhimentos/arquivos';
        $uploadedFiles = $request->getUploadedFiles();

        // handle single input with single file upload
        $uploadedFile = $uploadedFiles['arquivo'];

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = moveUploadedFile($directory, $uploadedFile);

            $item = ArquivosAcolhimentos::create([
                'arquivo' => $filename,
                'descricao' => $request->getParam('descricao'),
                'acolhido' => $request->getParam('acolhido'),
                'acolhimento' => $request->getParam('acolhimento'),
            ]);
        }

        if($item)
        {
            $this->flash->addMessage('success', 'O arquivo foi adicionado para a pasta deste acolhimento.');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao adicionar arquivo para este acolhimento.');
        }
        
        return $response->withRedirect($this->router->pathFor('editar_acolhimento', ['id' => $args['id']]));
    }

    // Editar a descrição do arquivo
    public function postEditarArquivo (Request $request, Response $response, $args)
    {   
        $item = ArquivosAcolhimentos::find($args['id']);

        $item->update([
            'descricao' => $request->getParam('descricao'),
        ]);

        if($item)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    // Remove um arquivo do acolhido
    public function getRemoverArquivo ($request, $response, $args)
    {
        $item = ArquivosAcolhimentos::find($args['id']);

        if(unlink('public/uploads/acolhimentos/arquivos/'.$item['arquivo'])){
            if($item->delete())
            {
                return true;
            }
            else
            {
                return false;
            }
        } else {
            return false;
        }
    }
##### END ARQUIVOS #####

##### ALTERAR UNIDADE #####
    public function alterar_unidade ($request, $response, $args)
    {
        if($this->acesso_usuario['acolhimentos']['index']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $this->view->offsetSet("flash", $this->flash->getMessages());

        $item = Acolhimentos::find($args['id']);
        $acolhido = Acolhidos::find($item['acolhido']);
        $unidade = Unidades::find($item['unidade']);
        $quarto = Quartos::find($item['quarto']);
        $convenio = Convenios::find($item['convenio']);

        $unidades = Unidades::get()->toArray();
        $quartos = Quartos::get()->toArray();

        for($i = 0; $i <= (count($unidades)); $i++){
            $id_unidade = $unidades[$i]['id'];
            $vagas_ocupadas[$id_unidade] = Acolhimentos::whereIn('status', [0, 1])->where('unidade', $id_unidade)->count();
            $this->view->offsetSet("vagas_ocupadas", $vagas_ocupadas);

            $vagas[$id_unidade] = Quartos::where('unidade', $id_unidade)->sum('vagas');
            $this->view->offsetSet("vagas", $vagas);
        }

        for($i = 0; $i <= (count($quartos)); $i++){
            $id_quarto = $quartos[$i]['id'];
            $vagas_ocupadas_quarto[$id_quarto] = Acolhimentos::whereIn('status', [0, 1])->where('quarto', $id_quarto)->count();
            $this->view->offsetSet("vagas_ocupadas_quarto", $vagas_ocupadas_quarto);
        }

        // VERIFICA SE JÁ TEM UM CONTATO PRINCIPAL
        $contato_principal = Contatos::where('acolhido', $acolhido['id'])->where('status', 1)->count();
        if($contato_principal >= 1){
            $this->view->offsetSet("tem_contato_principal", true);
        }

        return $this->view->render($response, 'acolhimentos/novo.html', [
            'Titulo_Pagina' => 'Alterar Unidade',
            'item' => $item,
            'acolhido' => $acolhido,
            'unidade' => $unidade,
            'unidades' => $unidades,
            'quarto' => $quarto,
            'quartos' => $quartos,
            'usuario' => $usuario,
            'convenio' => $convenio,
            'view' => 'alterar-unidade'
        ]);
    }

    public function postAlterarUnidade ($request, $response, $args)
    {
        $unidade_anterior = Unidades::find($request->getParam('unidade_anterior'));
        $nova_unidade = Unidades::find($request->getParam('nova_unidade'));
        $quarto_anterior = Quartos::find($request->getParam('quarto_anterior'));
        $novo_quarto = Quartos::find($request->getParam('novo_quarto'));

        $acolhimento = Acolhimentos::find($args['id']);
        $acolhimento->update([
            'unidade' => $request->getParam('nova_unidade'),
            'quarto' => $request->getParam('novo_quarto'),
        ]);

        $acolhido = Acolhidos::find($acolhimento->acolhido);


        if($acolhimento)
        {
            if($request->getParam('motivo') != ''){
                $motivo = "\n" . "\n" . 'Motivo' . "\n";
                $motivo .= $request->getParam('motivo');
            }

            $descricao = $acolhido['nome'] . ' foi transferido da unidade ' . $unidade_anterior['nome'] . ' ('.$quarto_anterior['nome'].') para a unidade ' . $nova_unidade['nome'] . ' ('.$novo_quarto['nome'].')'  . $motivo;
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $args['id'],
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Mudança de unidade',
                'descricao' => $descricao,
                'color' => 'success',
                'icon' => 'ti-exchange-vertical',
            ]);

            $this->flash->addMessage('success', 'Unidade e/ou quarto alterado com sucesso!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao alterar unidade e/ou quarto!');
        }
        
        return $response->withRedirect($this->router->pathFor('editar_acolhimento', ['id' => $acolhimento->id]));
    }
##### END ALTERAR UNIDADE #####

##### ALTA #####
    public function desligamento ($request, $response, $args)
    {
        if($this->acesso_usuario['acolhimentos']['index']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $this->view->offsetSet("flash", $this->flash->getMessages());

        $item = Acolhimentos::find($args['id']);
        $acolhido = Acolhidos::find($item['acolhido']);
        $unidade = Unidades::find($item['unidade']);
        $quarto = Quartos::find($item['quarto']);
        $convenio = Convenios::find($item['convenio']);

        // VERIFICA SE JÁ TEM UM CONTATO PRINCIPAL
        $contato_principal = Contatos::where('acolhido', $acolhido['id'])->where('status', 1)->count();
        if($contato_principal >= 1){
            $this->view->offsetSet("tem_contato_principal", true);
        }

        return $this->view->render($response, 'acolhimentos/novo.html', [
            'Titulo_Pagina' => 'Desligamento',
            'item' => $item,
            'acolhido' => $acolhido,
            'unidade' => $unidade,
            'quarto' => $quarto,
            'usuario' => $usuario,
            'convenio' => $convenio,
            'view' => 'desligamento'
        ]);
    }

    public function postCadastrarAlta ($request, $response, $args)
    {
        // $data_saida = explode('/', $request->getParam('data_saida'));
        // $data_saida = $data_saida[2].'-'.$data_saida[1].'-'.$data_saida[0];

        $acolhimento = Acolhimentos::find($args['id']);
        $acolhimento->update([
            'data_saida' => $request->getParam('data_saida'),
            'status' => $request->getParam('tipo_alta'),
            'pertences' => $request->getParam('lista_pertences'),
            'motivo' => $request->getParam('motivo'),
        ]);

        $acolhido = Acolhidos::find($acolhimento['acolhido']);
        $acolhido->update([
            'status' => 0
        ]);

        if($request->getParam('tipo_alta') == 11){
            $tipo_alta = 'Terapêutica';
        }

        if($request->getParam('tipo_alta') == 12){
            $tipo_alta = 'Solicitada';
        }

        if($request->getParam('tipo_alta') == 13){
            $tipo_alta = 'Administrativa';
        }

        if($request->getParam('tipo_alta') == 14){
            $tipo_alta = 'Evasão';
        }

        if($acolhimento)
        {
            $descricao = $acolhido['nome'] . ' teve seu acolhimento encerrado.';
            $descricao .= "\n" . "Tipo de alta: " . $tipo_alta;
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $args['id'],
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Alta de acolhimento',
                'descricao' => $descricao,
                'color' => $color,
                'icon' => 'ti-power-off',
            ]);

            if($acolhimento->tipo_acolhimento == 1 or $acolhimento->tipo_acolhimento == 3){
                $mensalidades = Mensalidades::where('acolhimento', $acolhimento->id)->where('status', 0)->get()->toArray();
                for ($i=0; $i < count($mensalidades) ; $i++) { 
                    $mensalidade = Mensalidades::find($mensalidades[$i]['id']);
                    $mensalidade->update([
                        'status' => 99
                    ]);
                }
            }

            $this->flash->addMessage('success', 'Este acolhimento recebeu alta!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao salvar alta para este acolhimento!');
        }

        return $response->withRedirect($this->router->pathFor('acolhimentos'));
    }

    public function postLimparAlta ($request, $response, $args)
    {
        $acolhimento = Acolhimentos::find($args['id']);
        $acolhimento->update([
            'status' => 0,
            'data_saida' => '',
            'motivo' => '',
        ]);

        $acolhido = Acolhidos::find($acolhimento['acolhido']);
        $acolhido->update([
            'status' => 1
        ]);

        if($acolhimento)
        {
            $descricao = $acolhido['nome'] . ' teve sua alta cancelada e voltou para acolhimento.';
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $args['id'],
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Cancelamento de alta',
                'descricao' => $descricao,
                'color' => $color,
                'icon' => 'ti-power-off',
            ]);

            if($acolhimento->tipo_acolhimento == 1 or $acolhimento->tipo_acolhimento == 3){
                $mensalidades = Mensalidades::where('acolhimento', $acolhimento->id)->where('status', 99)->get()->toArray();
                for ($i=0; $i < count($mensalidades) ; $i++) { 
                    $mensalidade = Mensalidades::find($mensalidades[$i]['id']);
                    $mensalidade->update([
                        'status' => 0
                    ]);
                }
            }

            return true;
        }
        else
        {
            return false;
        }
    }
##### END ALTA #####

##### RESSOCIALIZAÇÃO #####
    public function postRessocializacao ($request, $response, $args)
    {
        $acolhimento = Acolhimentos::find($args['id']);
        $acolhimento->update([
            'status' => 1,
        ]);

        $acolhido = Acolhidos::find($acolhimento['acolhido']);

        $data_saida = explode('/', $request->getParam('data_saida'));
        $data_saida = $data_saida[2].'-'.$data_saida[1].'-'.$data_saida[0];

        $start = $data_saida . ' 00:00:00';

        $data_retorno = explode('/', $request->getParam('data_retorno'));
        $data_retorno = $data_retorno[2].'-'.$data_retorno[1].'-'.$data_retorno[0];

        $end = $data_retorno . ' 23:59:59';

        if($acolhimento)
        {
            $ressocializacao = Ressocializacao::create([
                'title' => '[RESSOCIALIZAÇÃO] - ' . $acolhido['nome'],
                'acolhimento' => $args['id'],
                'start' => $start,
                'end' => $end,
                'data_saida' => $data_saida,
                'data_retorno' => $data_retorno,
            ]);

            $descricao = $acolhido['nome'] . ' saiu para ressocialização em ' . $request->getParam('data_saida') . ' com retorno para ' . $request->getParam('data_retorno');
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $args['id'],
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Ressocialização #' . $ressocializacao->id,
                'descricao' => $descricao,
                'color' => 'info',
                'icon' => 'ti-direction-alt',
            ]);

            return true;
        }
        else
        {
            return false;
        }
    }

    public function getRemoverRessocializacao ($request, $response, $args)
    {
        $item = Ressocializacao::find($args['id']);
        $acolhimento = Acolhimentos::find($item['acolhimento']);
        $acolhido = Acolhidos::find($acolhimento['acolhido']);

        if($item->delete())
        {
            $acolhimento->update([
                'status' => 0
            ]);

            if($acolhimento){
                $descricao = $acolhido['nome'] . ' teve uma ressocialização removida com data de saída em ' . date('d/m/Y', strtotime($item['data_saida'])) . ' com retorno para ' . date('d/m/Y', strtotime($item['data_retorno']));
                $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
                $color = array_rand($colors);

                $timeline = Timeline::create([
                    'acolhimento' => $acolhimento['id'],
                    'usuario' => $_SESSION['Auth'],
                    'titulo' => 'Ressocialização removida',
                    'descricao' => $descricao,
                    'color' => $color,
                    'icon' => 'ti-direction-alt',
                ]);
            }

            return true;
        }
        else
        {
            return false;
        }
    }
##### END RESSOCIALIZAÇÃO #####

##### TIMELINE #####
    public function timeline ($request, $response, $args)
    {
        if($this->acesso_usuario['acolhimentos']['index']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $this->view->offsetSet("flash", $this->flash->getMessages());

        $timeline = Timeline::where('acolhimento', $args['id'])->orderBy('id', 'DESC')->get()->toArray();

        $item = Acolhimentos::find($args['id']);
        $acolhido = Acolhidos::find($item['acolhido']);
        $unidade = Unidades::find($item['unidade']);
        $quarto = Quartos::find($item['quarto']);
        $convenio = Convenios::find($item['convenio']);

        // VERIFICA SE JÁ TEM UM CONTATO PRINCIPAL
        $contato_principal = Contatos::where('acolhido', $acolhido['id'])->where('status', 1)->count();
        if($contato_principal >= 1){
            $this->view->offsetSet("tem_contato_principal", true);
        }
        
        for ($i=0; $i < count($timeline) ; $i++) { 
            $usuario[$timeline[$i]['id']] = Users::find($timeline[$i]['usuario']);
            $this->view->offsetSet("usuario", $usuario);
        }

        return $this->view->render($response, 'acolhimentos/novo.html', [
            'Titulo_Pagina' => 'Parecer Profissional',
            'view' => 'editar',
            'item' => $item,
            'timeline' => $timeline,
            'acolhido' => $acolhido,
            'unidade' => $unidade,
            'quarto' => $quarto,
            'usuario' => $usuario,
            'convenio' => $convenio,
            'view' => 'timeline'
        ]);
    }

    // Remove um registro da timeline
    public function getRemoverTimeline ($request, $response, $args)
    {
        $item = Timeline::find($args['id']);

        if($item->delete())
        {
            return true;
        }
        else
        {
            return false;
        }
    }
##### END TIMELINE #####

##### P.A.S #####
    public function abrirPas ($request, $response, $args)
    {
        $acolhimento = Acolhimentos::find($args['id']);

        $acolhido = Acolhidos::find($acolhimento['acolhido']);

        $data_abertura = explode("/", $request->getParam('data_abertura'));
        $data_abertura = $data_abertura[2] . "-" . $data_abertura[1] . "-" . $data_abertura[0];

        $pas = Pas::create([
            'acolhido' => $acolhido['id'],
            'acolhimento' => $args['id'],
            'data_abertura' => $data_abertura,
            'usuario' => $_SESSION['Auth'],
            'status' => 1,
        ]);

        if($pas)
        {
            $descricao = 'Data de abertura do P.A.S: ' . $request->getParam('data_abertura');
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $args['id'],
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Plano de Atendimento Singular',
                'descricao' => $descricao,
                'color' => $color,
                'icon' => 'ti-pencil-alt',
            ]);

            $this->flash->addMessage('success', 'Plano de Atendimento Singular iniciado com sucesso!');            
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao iniciar Plano de Atendimento Singular!');
        }

        return $response->withRedirect($this->router->pathFor('editar_acolhimento', ['id' => $args['id']]));
    }

    public function postEditarPas ($request, $response, $args)
    {
        $pas = Pas::find($args['id']);

        $acolhimento = Acolhimentos::find($pas['acolhimento']);

        $acolhido = Acolhidos::find($pas['acolhido']);

        if($request->getParam('modulo') == 'anexo_1'){
            $pas->update([
                'anexo_1' => serialize($request->getParam('anexo_1')),
            ]);

            $descricao = 'Anexo I alterado';
        }

        if($request->getParam('modulo') == 'anexo_2'){
            $pas->update([
                'anexo_2' => serialize($request->getParam('anexo_2')),
            ]);

            $descricao = 'Anexo II alterado';
        }

        if($request->getParam('modulo') == 'anexo_3'){
            $pas->update([
                'anexo_3' => $request->getParam('anexo_3'),
            ]);

            $descricao = 'Anexo III alterado';
        }

        if($pas)
        {
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $pas['acolhimento'],
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Plano de Atendimento Singular',
                'descricao' => $descricao,
                'color' => $color,
                'icon' => 'ti-pencil-alt',
            ]);

            $this->flash->addMessage('success', 'Plano de Atendimento Singular editado com sucesso!');            
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao editar Plano de Atendimento Singular!');
        }

        return $response->withRedirect($this->router->pathFor('editar_acolhimento', ['id' => $pas['acolhimento']]));
    }

    public function postMetasPas ($request, $response, $args)
    {
        $pas = Pas::find($args['id']);

        $acolhimento = Acolhimentos::find($pas['acolhimento']);

        $acolhido = Acolhidos::find($pas['acolhido']);

        // VERIFICA SE ESTE ACOLHIMENTO JÁ POSSUI
        // META PREENCHIDA PARA O MÊS E ANO EM QUESTÃO
        $verifica_metas_mes = Metas::where('acolhimento', $pas['acolhimento'])->where('pas', $pas['id'])->where('ano', $request->getParam('meta_ano'))->where('mes', $request->getParam('meta_mes'))->count();

        if($verifica_metas_mes >= 1){
            $this->flash->addMessage('error', 'Este Plano de Atendimento Singular já tem uma meta anexada para o mês e ano selecionados!');
            return $response->withRedirect($this->router->pathFor('editar_acolhimento', ['id' => $pas['acolhimento']]));
        }

        $directory = '/var/www/escudodobem.gruub.com.br/httpdocs/sistema/public/uploads/acolhimentos/pas/' . $args['id'] . '/metas';

        $uploadedFiles = $request->getUploadedFiles();

        // handle single input with single file upload
        $uploadedFile = $uploadedFiles['arquivo'];

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = moveUploadedFile($directory, $uploadedFile);

            $item = Metas::create([
                'acolhimento' => $pas['acolhimento'],
                'pas' => $args['id'],
                'ano' => $request->getParam('meta_ano'),
                'mes' => $request->getParam('meta_mes'),
                'arquivo' => $filename,
                'descricao' => $request->getParam('descricao'),
                'usuario' => $_SESSION['Auth'],
            ]);
        }

        if($item)
        {
            $pas->update([
                'last_metas' => date('Y-m-d')
            ]);

            if($pas){
                $descricao = "Meta do mês " . $request->getParam('meta_mes')."/".$request->getParam('meta_ano') . " incluída com sucesso no P.A.S";
                $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
                $color = array_rand($colors);

                $timeline = Timeline::create([
                    'acolhimento' => $pas['acolhimento'],
                    'usuario' => $_SESSION['Auth'],
                    'titulo' => 'Plano de Atendimento Singular',
                    'descricao' => $descricao,
                    'color' => $color,
                    'icon' => 'ti-pencil-alt',
                ]);

                $this->flash->addMessage('success', 'Meta do mês incluída com sucesso no Plano de Atendimento Singular!');
            } else {
                $this->flash->addMessage('error', 'Erro ao atualizar os dados da meta do Plano de Atendimento Singular!');
            } 
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao incluir a meta do mês no Plano de Atendimento Singular!');
        }

        return $response->withRedirect($this->router->pathFor('editar_acolhimento', ['id' => $pas['acolhimento']]));
    }

    // Remove uma meta do P.A.S
    public function getRemoverMetaPas ($request, $response, $args)
    {
        $item = Metas::find($args['id']);
        $pas = Pas::find($item['pas']);

        if(unlink('public/uploads/acolhimentos/pas/'.$item['pas'].'/metas/'.$item['arquivo'])){
            if($item->delete())
            {
                $pas->update([
                    'last_metas' => '0000-00-00'
                ]);

                if($pas){
                    if($item['mes'] < 10){
                        $item['mes'] = '0'.$item['mes'];
                    }

                    $descricao = "Meta do mês " . $item['mes']."/".$item['ano'] . " removida do P.A.S";
                    $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
                    $color = array_rand($colors);

                    $timeline = Timeline::create([
                        'acolhimento' => $pas['acolhimento'],
                        'usuario' => $_SESSION['Auth'],
                        'titulo' => 'Plano de Atendimento Singular',
                        'descricao' => $descricao,
                        'color' => $color,
                        'icon' => 'ti-pencil-alt',
                    ]);

                    return true;
                } else {
                    return false;
                } 
            }
            else
            {
                return false;
            }
        } else {
            return false;
        }
    }

    public function postAtividadesPas ($request, $response, $args)
    {
        $pas = Pas::find($args['id']);

        $acolhimento = Acolhimentos::find($pas['acolhimento']);

        $acolhido = Acolhidos::find($pas['acolhido']);

        // VERIFICA SE ESTE ACOLHIMENTO JÁ POSSUI
        // ATIVIDADES PREENCHIDAS PARA O MÊS E ANO EM QUESTÃO
        $verifica_atividades_mes = Atividades::where('acolhimento', $pas['acolhimento'])->where('pas', $pas['id'])->where('ano', $request->getParam('atividades_desenvolvidas_ano'))->where('mes', $request->getParam('atividades_desenvolvidas_mes'))->count();

        if($verifica_atividades_mes >= 1){
            $this->flash->addMessage('error', 'Este Plano de Atendimento Singular já tem atividades preenchidas para o mês e ano selecionados!');
            return $response->withRedirect($this->router->pathFor('editar_acolhimento', ['id' => $pas['acolhimento']]));
        }

        foreach ($request->getParam('atividade') as $key => $value) {
            $atividades_pas = Atividades::create([
                'acolhimento' => $pas['acolhimento'],
                'pas' => $pas['id'],
                'mes' => $request->getParam('atividades_desenvolvidas_mes'),
                'ano' => $request->getParam('atividades_desenvolvidas_ano'),
                'atividade' => $key,
                'usuario' => $_SESSION['Auth'],
                'status' => $value,
            ]);
        }

        if($atividades_pas)
        {
            $pas->update([
                'last_atividades' => date('Y-m-d')
            ]);

            if($pas){
                $descricao = "Atividades do mês " . $request->getParam('atividades_desenvolvidas_mes')."/".$request->getParam('atividades_desenvolvidas_ano') . " incluídas com sucesso no P.A.S";
                $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
                $color = array_rand($colors);

                $timeline = Timeline::create([
                    'acolhimento' => $pas['acolhimento'],
                    'usuario' => $_SESSION['Auth'],
                    'titulo' => 'Plano de Atendimento Singular',
                    'descricao' => $descricao,
                    'color' => $color,
                    'icon' => 'ti-pencil-alt',
                ]);

                $this->flash->addMessage('success', 'Atividades incluídas com sucesso no Plano de Atendimento Singular!'); 
            } else {
                $this->flash->addMessage('error', 'Erro ao atualizar os dados das atividades do Plano de Atendimento Singular!');
            }     
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao incluir as atividades no Plano de Atendimento Singular!');
        }

        return $response->withRedirect($this->router->pathFor('editar_acolhimento', ['id' => $pas['acolhimento']]));
    }

    public function postEvolucaoPas ($request, $response, $args)
    {
        $pas = Pas::find($args['id']);

        $acolhimento = Acolhimentos::find($pas['acolhimento']);

        $acolhido = Acolhidos::find($pas['acolhido']);

        // VERIFICA SE ESTE ACOLHIMENTO JÁ POSSUI
        // EVOLUÇÃO PREENCHIDA PARA O MÊS E ANO EM QUESTÃO
        $verifica_evolucao_mes = Evolucao::where('acolhimento', $pas['acolhimento'])->where('pas', $pas['id'])->where('ano', $request->getParam('evolucao_ano'))->where('mes', $request->getParam('evolucao_mes'))->count();

        if($verifica_evolucao_mes >= 1){
            $this->flash->addMessage('error', 'Este Plano de Atendimento Singular já tem uma evolução preenchida para o mês e ano selecionados!');
            return $response->withRedirect($this->router->pathFor('editar_acolhimento', ['id' => $pas['acolhimento']]));
        }

        $item = Evolucao::create([
            'acolhimento' => $pas['acolhimento'],
            'pas' => $pas['id'],
            'mes' => $request->getParam('evolucao_mes'),
            'ano' => $request->getParam('evolucao_ano'),
            'descricao' => $request->getParam('descricao_evolucao'),
            'usuario' => $_SESSION['Auth'],
        ]);

        if($item)
        {
            $pas->update([
                'last_evolucao' => date('Y-m-d')
            ]);

            if($pas){
                $descricao = "Evolução do mês " . $request->getParam('evolucao_mes')."/".$request->getParam('evolucao_ano') . " incluídas com sucesso no P.A.S";
                $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
                $color = array_rand($colors);

                $timeline = Timeline::create([
                    'acolhimento' => $pas['acolhimento'],
                    'usuario' => $_SESSION['Auth'],
                    'titulo' => 'Plano de Atendimento Singular',
                    'descricao' => $descricao,
                    'color' => $color,
                    'icon' => 'ti-pencil-alt',
                ]);

                $this->flash->addMessage('success', 'Evolução do mês incluída com sucesso no Plano de Atendimento Singular!');
            } else {
                $this->flash->addMessage('error', 'Erro ao atualizar os dados da evolução do Plano de Atendimento Singular!');
            } 
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao incluir a evolução do mês no Plano de Atendimento Singular!');
        }

        return $response->withRedirect($this->router->pathFor('editar_acolhimento', ['id' => $pas['acolhimento']]));
    }
##### END PAS #####

##### ESTUDO DE CASO #####
    public function abrirEstudoCaso ($request, $response, $args)
    {
        $acolhimento = Acolhimentos::find($args['id']);

        $acolhido = Acolhidos::find($acolhimento['acolhido']);

        $data_abertura = explode("/", $request->getParam('data_abertura'));
        $data_abertura = $data_abertura[2] . "-" . $data_abertura[1] . "-" . $data_abertura[0];

        $estudo_caso = EstudoCaso::create([
            'acolhido' => $acolhido['id'],
            'acolhimento' => $args['id'],
            'data_abertura' => $data_abertura,
            'usuario' => $_SESSION['Auth'],
            'status' => 1,
        ]);

        if($estudo_caso)
        {
            $descricao = 'Data de abertura do Estudo de Caso: ' . $request->getParam('data_abertura');
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $args['id'],
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Estudo de Caso',
                'descricao' => $descricao,
                'color' => $color,
                'icon' => 'ti-list',
            ]);

            $this->flash->addMessage('success', 'Estudo de Caso iniciado com sucesso!');            
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao iniciar Estudo de Caso!');
        }

        return $response->withRedirect($this->router->pathFor('editar_acolhimento', ['id' => $args['id']]));
    }

    public function editarEstudoCaso ($request, $response, $args)
    {
        $estudo_caso = EstudoCaso::find($args['id']);

        $acolhimento = Acolhimentos::find($estudo_caso['acolhimento']);

        $acolhido = Acolhidos::find($estudo_caso['acolhido']);

        $estudo_caso->update([
            'acolhido' => $estudo_caso['acolhido'],
            'acolhimento' => $estudo_caso['acolhimento'],
            'encaminhamento_responsavel' => $request->getParam('encaminhamento_responsavel'),
            'pertences' => $request->getParam('pertences'),
            'padrao_uso_droga' => $request->getParam('padrao_uso_droga'),
            'local_residencia' => $request->getParam('local_residencia'),
            'condicoes_financeiras' => $request->getParam('condicoes_financeiras'),
            'vinculo_familiar' => $request->getParam('vinculo_familiar'),
            'saude_fisica' => $request->getParam('saude_fisica'),
            'comprometimentos' => $request->getParam('comprometimentos'),
            'acompanhamentos_encaminhamentos' => $request->getParam('acompanhamentos_encaminhamentos'),
            'historico_profissional' => $request->getParam('historico_profissional'),
            'historia_de_vida' => $request->getParam('historia_de_vida'),
            'critica_consciencia_dq' => $request->getParam('critica_consciencia_dq'),
            'tratamentos_anteriores' => $request->getParam('tratamentos_anteriores'),
            'avaliacao_psicologa' => $request->getParam('avaliacao_psicologa'),
            'avaliacao_conselheiros' => $request->getParam('avaliacao_conselheiros'),
            'avaliacao_servico_social' => $request->getParam('avaliacao_servico_social'),
            // 'relato_familiar' => $request->getParam('relato_familiar'),
            'rede_de_apoio' => $request->getParam('rede_de_apoio'),
            'autocuidado' => $request->getParam('autocuidado'),
            'autoconhecimento' => $request->getParam('autoconhecimento'),
            'autonomia' => $request->getParam('autonomia'),
            'capacitacoes_propostas' => $request->getParam('capacitacoes_propostas'),
            'atcs_propostas' => $request->getParam('atcs_propostas'),
            'usuario' => $_SESSION['Auth'],
        ]);

        if($estudo_caso)
        {
            $descricao = "Formulário preenchido ou alterado";
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $estudo_caso['acolhimento'],
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Estudo de Caso',
                'descricao' => $descricao,
                'color' => $color,
                'icon' => 'ti-list',
            ]);

            $this->flash->addMessage('success', 'Estudo de Caso editado com sucesso!');            
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao editar Estudo de Caso!');
        }

        return $response->withRedirect($this->router->pathFor('editar_acolhimento', ['id' => $estudo_caso['acolhimento']]));
    }

    public function postRelatoFamiliarEstudoCaso ($request, $response, $args)
    {
        $estudo_caso = EstudoCaso::find($args['id']);

        $relato_familiar = $estudo_caso['relato_familiar'];
        if($relato_familiar == ''){
            $descricao = "Relato familiar preenchido";
        } else {
            $descricao = "Relato familiar alterado";
        }

        $acolhimento = Acolhimentos::find($estudo_caso['acolhimento']);

        $acolhido = Acolhidos::find($estudo_caso['acolhido']);

        $estudo_caso->update([
            'relato_familiar' => $request->getParam('relato_familiar'),
        ]);

        if($estudo_caso)
        {
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $estudo_caso['acolhimento'],
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Estudo de Caso',
                'descricao' => $descricao,
                'color' => $color,
                'icon' => 'ti-list',
            ]);

            $this->flash->addMessage('success', 'Estudo de Caso editado com sucesso!');            
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao editar Estudo de Caso!');
        }

        return $response->withRedirect($this->router->pathFor('editar_acolhimento', ['id' => $estudo_caso['acolhimento']]));
    }

    public function postMetasEstudoCaso ($request, $response, $args)
    {
        $estudo_caso = EstudoCaso::find($args['id']);

        $acolhimento = Acolhimentos::find($estudo_caso['acolhimento']);

        $acolhido = Acolhidos::find($estudo_caso['acolhido']);

        // VERIFICA SE ESTE ACOLHIMENTO JÁ POSSUI
        // METAS PREENCHIDAS PARA O MÊS E ANO EM QUESTÃO
        // LIMITE 4 (1 POR SEMANA)
        $verifica_metas_mes = EstudoCasoEvolucao::where('acolhimento', $estudo_caso['acolhimento'])->where('estudo_caso', $estudo_caso['id'])->where('ano', $request->getParam('meta_ano'))->where('mes', $request->getParam('meta_mes'))->count();

        if($verifica_metas_mes >= 4){
            $this->flash->addMessage('error', 'Este Estudo de Caso já tem metas preenchidas para o mês e ano selecionados!');
            return $response->withRedirect($this->router->pathFor('editar_acolhimento', ['id' => $estudo_caso['acolhimento']]));
        }

        $item = EstudoCasoMetas::create([
            'acolhimento' => $estudo_caso['acolhimento'],
            'estudo_caso' => $estudo_caso['id'],
            'mes' => $request->getParam('meta_mes'),
            'ano' => $request->getParam('meta_ano'),
            'descricao' => $request->getParam('descricao_meta'),
            'usuario' => $_SESSION['Auth'],
        ]);

        if($item)
        {
            $estudo_caso->update([
                'last_metas' => date('Y-m-d')
            ]);

            if($estudo_caso){
                $descricao = "Metas do mês " . $request->getParam('meta_mes')."/".$request->getParam('meta_ano') . " incluídas com sucesso no Estudo de Caso";
                $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
                $color = array_rand($colors);

                $timeline = Timeline::create([
                    'acolhimento' => $estudo_caso['acolhimento'],
                    'usuario' => $_SESSION['Auth'],
                    'titulo' => 'Estudo de Caso',
                    'descricao' => $descricao,
                    'color' => $color,
                    'icon' => 'ti-list',
                ]);

                $this->flash->addMessage('success', 'Metas do mês incluídas com sucesso no Estudo de Caso!');
            } else {
                $this->flash->addMessage('error', 'Erro ao atualizar os dados das metas do Estudo de Caso!');
            } 
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao incluir as metas do mês no Estudo de Caso!');
        }

        return $response->withRedirect($this->router->pathFor('editar_acolhimento', ['id' => $estudo_caso['acolhimento']]));
    }

    public function getRemoverMetaEstudoCaso ($request, $response, $args)
    {
        $item = EstudoCasoMetas::find($args['id']);
        $estudo_caso = EstudoCaso::find($item['estudo_caso']);

        if($item->delete())
        {
            $estudo_caso->update([
                'last_metas' => '0000-00-00'
            ]);

            if($estudo_caso){
                if($item['mes'] < 10){
                    $item['mes'] = '0'.$item['mes'];
                }

                $descricao = "Meta do mês " . $item['mes']."/".$item['ano'] . " removida do Estudo de Caso";
                $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
                $color = array_rand($colors);

                $timeline = Timeline::create([
                    'acolhimento' => $estudo_caso['acolhimento'],
                    'usuario' => $_SESSION['Auth'],
                    'titulo' => 'Estudo de Caso',
                    'descricao' => $descricao,
                    'color' => $color,
                    'icon' => 'ti-list',
                ]);

                return true;
            } else {
                return false;
            } 
        }
        else
        {
            return false;
        }
    }

    public function postEvolucaoEstudoCaso ($request, $response, $args)
    {
        $estudo_caso = EstudoCaso::find($args['id']);

        $acolhimento = Acolhimentos::find($estudo_caso['acolhimento']);

        $acolhido = Acolhidos::find($estudo_caso['acolhido']);

        // VERIFICA SE ESTE ACOLHIMENTO JÁ POSSUI
        // EVOLUÇÃO PREENCHIDA PARA O MÊS E ANO EM QUESTÃO
        $verifica_evolucao_mes = EstudoCasoEvolucao::where('acolhimento', $estudo_caso['acolhimento'])->where('estudo_caso', $estudo_caso['id'])->where('ano', $request->getParam('evolucao_ano'))->where('mes', $request->getParam('evolucao_mes'))->count();

        if($verifica_evolucao_mes >= 1){
            $this->flash->addMessage('error', 'Este Estudo de Caso já tem uma evolução preenchida para o mês e ano selecionados!');
            return $response->withRedirect($this->router->pathFor('editar_acolhimento', ['id' => $estudo_caso['acolhimento']]));
        }

        $item = EstudoCasoEvolucao::create([
            'acolhimento' => $estudo_caso['acolhimento'],
            'estudo_caso' => $estudo_caso['id'],
            'mes' => $request->getParam('evolucao_mes'),
            'ano' => $request->getParam('evolucao_ano'),
            'descricao' => $request->getParam('descricao_evolucao'),
            'usuario' => $_SESSION['Auth'],
        ]);

        if($item)
        {
            $estudo_caso->update([
                'last_evolucao' => date('Y-m-d')
            ]);

            if($estudo_caso){
                $descricao = "Evolução do mês " . $request->getParam('evolucao_mes')."/".$request->getParam('evolucao_ano') . " incluída com sucesso no Estudo de Caso";
                $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
                $color = array_rand($colors);

                $timeline = Timeline::create([
                    'acolhimento' => $estudo_caso['acolhimento'],
                    'usuario' => $_SESSION['Auth'],
                    'titulo' => 'Estudo de Caso',
                    'descricao' => $descricao,
                    'color' => $color,
                    'icon' => 'ti-list',
                ]);

                $this->flash->addMessage('success', 'Evolução do mês incluída com sucesso no Estudo de Caso!');
            } else {
                $this->flash->addMessage('error', 'Erro ao atualizar os dados da evolução do Estudo de Caso!');
            } 
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao incluir a evolução do mês no Estudo de Caso!');
        }

        return $response->withRedirect($this->router->pathFor('editar_acolhimento', ['id' => $estudo_caso['acolhimento']]));
    }

    public function getRemoverEvolucaoEstudoCaso ($request, $response, $args)
    {
        $item = EstudoCasoEvolucao::find($args['id']);
        $estudo_caso = EstudoCaso::find($item['estudo_caso']);

        if($item->delete())
        {
            $estudo_caso->update([
                'last_evolucao' => '0000-00-00'
            ]);

            if($estudo_caso){
                if($item['mes'] < 10){
                    $item['mes'] = '0'.$item['mes'];
                }

                $descricao = "Evolução do mês " . $item['mes']."/".$item['ano'] . " removida do Estudo de Caso";
                $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
                $color = array_rand($colors);

                $timeline = Timeline::create([
                    'acolhimento' => $estudo_caso['acolhimento'],
                    'usuario' => $_SESSION['Auth'],
                    'titulo' => 'Estudo de Caso',
                    'descricao' => $descricao,
                    'color' => $color,
                    'icon' => 'ti-list',
                ]);

                return true;
            } else {
                return false;
            } 
        }
        else
        {
            return false;
        }
    }
##### END ESTUDO DE CASO #####

##### ALTERAR FASE #####
public function postAlterarFase ($request, $response, $args)
    {
        $fase_anterior = $request->getParam('fase_anterior');
        $nova_fase = $request->getParam('fase');

        $acolhimento = Acolhimentos::find($args['id']);

        if($fase_anterior != $nova_fase){
            $acolhimento->update([
                'fase' => $nova_fase,
            ]);
        } else {
            $this->flash->addMessage('error', 'A nova fase precisa ser diferente da fase atual!');
        }

        $acolhido = Acolhidos::find($acolhimento->acolhido);

        if($acolhimento && $fase_anterior != $nova_fase)
        {
            $descricao = $acolhido['nome'] . ' teve sua fase alterada ('.$fase_anterior.' -> '.$nova_fase.')';
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $args['id'],
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Troca de Fase',
                'descricao' => $descricao,
                'color' => 'success',
                'icon' => 'ti-exchange-vertical',
            ]);

            $this->flash->addMessage('success', 'Fase alterada com sucesso!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao alterar fase!');
        }
        
        return $response->withRedirect($this->router->pathFor('editar_acolhimento', ['id' => $acolhimento->id]));
    }
##### END ALTERAR FASE #####
}