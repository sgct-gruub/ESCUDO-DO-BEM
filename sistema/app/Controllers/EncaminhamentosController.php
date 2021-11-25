<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Respect\Validation\Validator as validate;
use Slim\Views\Twig as View;

use App\Models\Acolhidos;
use App\Models\Acolhimentos;
use App\Models\Encaminhamentos;
use App\Models\Cantina\Lancamentos;
use App\Models\Timeline;

class EncaminhamentosController extends Controller
{

	// Exibe o calendário de eventos
    public function listar ($request, $response, $args)
    {   
        if($this->acesso_usuario['encaminhamentos']['index']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Encaminhamentos::orderBy('id','ASC')->get()->toArray();
        
        $acolhimentos = Acolhimentos::whereIn('status', [0, 1])->get()->toArray();

        for($i = 0; $i < count($acolhimentos); $i++){
            $id_acolhido = $acolhimentos[$i]['acolhido'];
            $acolhido[$id_acolhido] = Acolhidos::find($id_acolhido);

            $this->view->offsetSet("acolhido", $acolhido); 
        }
        
        foreach($item as $row)
        {
         $inicio = date('d/m/Y', strtotime($row["start"]));
         $hora = date('H:i', strtotime($row["start"]));
         // $termino = date('d/m/Y', strtotime($row["end"]));
         // if($termino = '30/11/-0001' OR $termino = '' OR $termino = '0000-00-00'){
         //    $termino = '';
         // }

         $acolhimento = Acolhimentos::find($row["acolhimento"]);
         $acolhido = Acolhidos::find($acolhimento['acolhido']);

         $data[] = array(
          'id' => $row["id"],
          'title' => $row["title"],
          'start' => $row["start"],
          'end' => $row["end"],
          'acolhimento' => $row["acolhimento"],
          'tipo' => $row["tipo"],
          'motivo' => $row["motivo"],
          'local' => $row["local"],
          'cep' => $row["cep"],
          'endereco' => $row["endereco"],
          'num' => $row["num"],
          'bairro' => $row["bairro"],
          'cidade' => $row["cidade"],
          'uf' => $row["uf"],
          'telefone' => $row["telefone"],
          'celular' => $row["celular"],
          'custo' => $row["custo"],
          'inicio' => $inicio,
          'hora' => $hora,
          'observacoes' => $row["observacoes"],
          'usuario' => $row["usuario"],
          'status' => $row["status"],
          'className' => $row["category"],
          'nome_acolhido' => $acolhido['nome']
         );
        }

        $this->view->offsetSet("item", $data); 

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'encaminhamentos/listar.html', [
            'Titulo_Pagina' => 'Encaminhamentos',
            'titulo'    => 'Listagem dos encaminhamentos',
            'view'      => 'listar',
            'flash'     => $mensagem,
            'itens'     => $item,
            'acolhimentos' => $acolhimentos,
        ]);
    }

    // Cadastra um evento
    public function getCadastrar ($request, $response)
    {
        if($this->acesso_usuario['encaminhamentos']['index']['c'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $acolhimento = Acolhimentos::find($request->getParam('acolhimentos'));
        $acolhido = Acolhidos::find($acolhimento['acolhido']);

        $start = $request->getParam('start');
        // $start = explode('/', $start);
        // $start = $start[2]."-".$start[1]."-".$start[0];
        // $start = $start . ' ' . $request->getParam('hora');

        // $end = $request->getParam('end');
        // $end = explode('/', $end);
        // $end = $end[2]."-".$end[1]."-".$end[0];

        // $cor = array('bg-success', 'bg-danger', 'bg-purple', 'bg-info', 'bg-warning');
        $cor = array('bg-purple', 'bg-info');
        $rand_cor = array_rand($cor, 1);

        $title = '[' . $request->getParam('tipo') . '] - ' . $acolhido['nome'];

        $item = Encaminhamentos::create([
            'title' => $title,
            'start' => $start,
            'end' => $start,
            'acolhimento' => $request->getParam('acolhimentos'),
            'tipo' => $request->getParam('tipo'),
            'motivo' => $request->getParam('motivo'),
            'local' => $request->getParam('local'),
            'cep' => $request->getParam('cep'),
            'endereco' => $request->getParam('endereco'),
            'num' => $request->getParam('num'),
            'bairro' => $request->getParam('bairro'),
            'cidade' => $request->getParam('cidade'),
            'uf' => $request->getParam('uf'),
            'telefone' => $request->getParam('telefone'),
            'celular' => $request->getParam('celular'),
            'custo' => $request->getParam('custo'),
            'observacoes' => $request->getParam('observacoes'),
            'usuario' => $_SESSION['Auth'],
            'status' => 2,
            'category' => $cor[$rand_cor],
        ]);

        if($item)
        {
            if($request->getParam('local') != ''){
                $local = 'Local: ' . $request->getParam('local') . "\n";
            }

            if($request->getParam('endereco') != ''){
                $endereco = 'Endereço: ' . $request->getParam('endereco') . ', ' . $request->getParam('num') . ', ' . $request->getParam('bairro') . ' - ' . $request->getParam('cidade') . ' - ' . $request->getParam('uf') . "\n";
            }

            if($request->getParam('telefone') != ''){
                $telefone = 'Telefone: ' . $request->getParam('telefone') . "\n";
            }

            if($request->getParam('celular') != ''){
                $celular = 'Celular: ' . $request->getParam('celular') . "\n";
            }

            if($request->getParam('custo') != ''){
                $custo = 'Custo: R$' . $request->getParam('custo') . "\n";
                $lancamento = Lancamentos::create([
                    'data' => $start, 
                    'acolhido' => $acolhido['id'],
                    'valor_total' => $request->getParam('custo'), 
                    'observacoes' => 'Custo encaminhamento', 
                    'status' => 0,
                    'usuario' => $_SESSION['Auth']
                ]);
            }

            if($request->getParam('observacoes') != ''){
                $observacoes = "\n" . "\n" . 'Observações Gerais / Registro Multidisciplinar' . "\n";
                $observacoes .= $request->getParam('observacoes');
            }

            $descricao = 'Tipo de encaminhamento: ' . $request->getParam('tipo') . "\n" . 'Motivo de encaminhamento: ' . $request->getParam('motivo') . "\n" . 'Data e hora: ' . date('d/m/Y H:i', strtotime($start)) . "\n" . $local . $endereco . $telefone . $celular . $custo . $observacoes;
            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $request->getParam('acolhimentos'),
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Encaminhamento #' . $item->id,
                'descricao' => $descricao,
                'color' => $color,
                'icon' => 'mdi mdi-call-split',
            ]);

            return true;
        }
        else
        {
            return false;
        }
    }

    // Exibe formulário de edição
    public function getEditar ($request, $response, $args)
    {
        if($this->acesso_usuario['encaminhamentos']['index']['u'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Encaminhamentos::find($args['id']);

        $start = $request->getParam('start');
        $start = explode('/', $start);
        $start = $start[2]."-".$start[1]."-".$start[0];
        $start = $start . ' ' . $request->getParam('hora');

        $acolhimento = Acolhimentos::find($request->getParam('acolhimentos'));
        $acolhido = Acolhidos::find($acolhimento['acolhido']);

        $title = '[' . $request->getParam('tipo') . '] - ' . $acolhido['nome'];

        if($request->getParam('status') == 0){
            $cor = 'bg-danger';
        } elseif($request->getParam('status') == 1){
            $cor = 'bg-success';
        } else {
            $cor = 'bg-info';
        }

        $item->update([
            'title' => $title,
            'start' => $start,
            'end' => $start,
            'acolhimento' => $request->getParam('acolhimentos'),
            'tipo' => $request->getParam('tipo'),
            'motivo' => $request->getParam('motivo'),
            'local' => $request->getParam('local'),
            'cep' => $request->getParam('cep'),
            'endereco' => $request->getParam('endereco'),
            'num' => $request->getParam('num'),
            'bairro' => $request->getParam('bairro'),
            'cidade' => $request->getParam('cidade'),
            'uf' => $request->getParam('uf'),
            'telefone' => $request->getParam('telefone'),
            'celular' => $request->getParam('celular'),
            'status' => $request->getParam('status'),
            'observacoes' => $request->getParam('observacoes'),
            'category' => $cor,
        ]);

        if($item)
        {
            if($request->getParam('local') != ''){
                $local = 'Local: ' . $request->getParam('local') . "\n";
            }

            if($request->getParam('endereco') != ''){
                $endereco = 'Endereço: ' . $request->getParam('endereco') . ', ' . $request->getParam('num') . ', ' . $request->getParam('bairro') . ' - ' . $request->getParam('cidade') . ' - ' . $request->getParam('uf') . "\n";
            }

            if($request->getParam('telefone') != ''){
                $telefone = 'Telefone: ' . $request->getParam('telefone') . "\n";
            }

            if($request->getParam('celular') != ''){
                $celular = 'Celular: ' . $request->getParam('celular') . "\n";
            }

            if($request->getParam('observacoes') != ''){
                $observacoes = "\n" . "\n" . 'Observações Gerais / Registro Multidisciplinar' . "\n";
                $observacoes .= $request->getParam('observacoes');
            }

            // se não compareceu no encaminhamento
            if($request->getParam('status') == 0){
                $descricao = 'Status do encaminhamento: NÃO REALIZADO' . "\n" . 'Tipo de encaminhamento: ' . $request->getParam('tipo') . "\n" . 'Motivo de encaminhamento: ' . $request->getParam('motivo') . "\n" . 'Data e hora: ' . date('d/m/Y H:i', strtotime($start)) . "\n" . $local . $endereco . $telefone . $celular . $observacoes;
                $colors = ['danger' => 'danger', 'danger' => 'danger'];
                $color = array_rand($colors);

                $timeline = Timeline::create([
                    'acolhimento' => $request->getParam('acolhimentos'),
                    'usuario' => $_SESSION['Auth'],
                    'titulo' => 'Encaminhamento #' . $item->id,
                    'descricao' => $descricao,
                    'color' => $color,
                    'icon' => 'mdi mdi-call-split',
                ]);
            }

            // se compareceu no encaminhamento
            if($request->getParam('status') == 1){
                $descricao = 'Status do encaminhamento: REALIZADO' . "\n" . 'Tipo de encaminhamento: ' . $request->getParam('tipo') . "\n" . 'Motivo de encaminhamento: ' . $request->getParam('motivo') . "\n" . 'Data e hora: ' . date('d/m/Y H:i', strtotime($start)) . "\n" . $local . $endereco . $telefone . $celular . $observacoes;
                $colors = ['success' => 'success', 'success' => 'success'];
                $color = array_rand($colors);

                $timeline = Timeline::create([
                    'acolhimento' => $request->getParam('acolhimentos'),
                    'usuario' => $_SESSION['Auth'],
                    'titulo' => 'Encaminhamento #' . $item->id,
                    'descricao' => $descricao,
                    'color' => $color,
                    'icon' => 'mdi mdi-call-split',
                ]);
            }

            // se não alterar o status do encaminhamento
            if($request->getParam('status') == 2){
                $descricao = 'Tipo de encaminhamento: ' . $request->getParam('tipo') . "\n" . 'Motivo de encaminhamento: ' . $request->getParam('motivo') . "\n" . 'Data e hora: ' . date('d/m/Y H:i', strtotime($start)) . "\n" . $local . $endereco . $telefone . $celular . $observacoes;
                $colors = ['primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'default' => 'default'];
                $color = array_rand($colors);

                $titulo = 'Encaminhamento #' . $args['id'];
                $timeline = Timeline::where('titulo', $titulo);
                $timeline->update([
                    'acolhimento' => $request->getParam('acolhimentos'),
                    'usuario' => $_SESSION['Auth'],
                    'titulo' => 'Encaminhamento #' . $item->id,
                    'descricao' => $descricao,
                    'color' => $color,
                    'icon' => 'mdi mdi-call-split',
                ]);
            }

            return true;
        }
        else
        {
            return false;
        }
    }

    // Remove o registro
    public function getRemover ($request, $response, $args)
    {
        if($this->acesso_usuario['encaminhamentos']['index']['d'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Encaminhamentos::find($args['id']);
        $titulo = 'Encaminhamento #' . $item['id'];
        $timeline = Timeline::where('titulo', $titulo);

        if($timeline->delete())
        {
            if($item->delete())
            {
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
}