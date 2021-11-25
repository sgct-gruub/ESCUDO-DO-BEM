<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Respect\Validation\Validator as validate;
use Slim\Views\Twig as View;

use App\Models\Acolhidos;
use App\Models\Acolhimentos;
use App\Models\Ressocializacao;
use App\Models\Timeline;

class RessocializacoesController extends Controller
{

	// Exibe o calendário de ressocializações
    public function listar ($request, $response, $args)
    {   
        if($this->acesso_usuario['ressocializacoes']['index']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Ressocializacao::orderBy('id','ASC')->get()->toArray();
        
        $acolhimentos = Acolhimentos::where('status', 0)->get()->toArray();

        for($i = 0; $i < count($acolhimentos); $i++){
            $id_acolhido = $acolhimentos[$i]['acolhido'];
            $acolhido[$id_acolhido] = Acolhidos::find($id_acolhido);

            $this->view->offsetSet("acolhido", $acolhido); 
        }
        
        foreach($item as $row)
        {
            $data_hoje = date('Y-m-d');
            $time_data_hoje = strtotime($data_hoje);

            $data_retorno = $row['data_retorno'];
            $time_data_retorno = strtotime($data_retorno);

            if( ($row['status'] != 2) and ($time_data_hoje >= $time_data_retorno) ){
                $color = '#ffbb44';
            }

            if( ($row['status'] != 2) and ($time_data_hoje < $time_data_retorno) ){
                $color = '#41B3F9';
            }

            if($row['status'] == 1){
                $color = '#f33155';
            }

            if($row['status'] == 2){
                $color = '#7ace4c';
            }

            $inicio = date('d/m/Y', strtotime($row["data_saida"]));
            $termino = date('d/m/Y', strtotime($row["data_retorno"]));

            $acolhimento = Acolhimentos::find($row["acolhimento"]);
            $acolhido = Acolhidos::find($acolhimento['acolhido']);

            $data[] = array(
                'id' => $row["id"],
                'title' => $row["title"],
                'start' => $row["start"],
                'end' => $row["end"],
                'acolhimento' => $row["acolhimento"],
                'inicio' => $inicio,
                'termino' => $termino,
                'observacoes' => $row["observacoes"],
                'status' => $row["status"],
                'category' => $row["category"],
                'nome_acolhido' => $acolhido['nome'],
                'color' => $color
            );
        }

        $this->view->offsetSet("item", $data); 

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'ressocializacoes/listar.html', [
            'Titulo_Pagina' => 'Ressocializações',
            'titulo'    => 'Listagem das ressocializações',
            'view'      => 'listar',
            'flash'     => $mensagem,
            'itens'     => $item,
            'acolhimentos' => $acolhimentos,
        ]);
    }

    public function getCadastrar ($request, $response)
    {
        if($this->acesso_usuario['ressocializacoes']['index']['c'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $acolhimento = Acolhimentos::find($request->getParam('acolhimentos'));
        $acolhimento->update([
            'status' => 1,
        ]);

        $acolhido = Acolhidos::find($acolhimento['acolhido']);

        $data_saida = explode('/', $request->getParam('data_saida'));
        $data_saida = $data_saida[2].'-'.$data_saida[1].'-'.$data_saida[0].' 00:00:00';

        $data_retorno = explode('/', $request->getParam('data_retorno'));
        $data_retorno = $data_retorno[2].'-'.$data_retorno[1].'-'.$data_retorno[0].' 23:59:59';

        if($acolhimento)
        {
            $ressocializacao = Ressocializacao::create([
                'acolhimento' => $request->getParam('acolhimentos'),
                'title' => '[RESSOCIALIZAÇÃO] - ' . $acolhido['nome'],
                'start' => $data_saida,
                'end' => $data_retorno,
                'data_saida' => $data_saida,
                'data_retorno' => $data_retorno,
                'category' => 'info'
            ]);

            $descricao = $acolhido['nome'] . ' saiu para ressocialização em ' . $request->getParam('data_saida') . ' com retorno para ' . $request->getParam('data_retorno');

            if($request->getParam('observacoes') != ''){
                $descricao .= "\n" . "\n" . 'Observações Gerais / Registro Multidisciplinar' . "\n";
                $descricao .= $request->getParam('observacoes');
            }

            $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
            $color = array_rand($colors);

            $timeline = Timeline::create([
                'acolhimento' => $request->getParam('acolhimentos'),
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

    public function getEditar ($request, $response, $args)
    {
        if($this->acesso_usuario['ressocializacoes']['index']['u'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Ressocializacao::find($args['id']);

        $start = $request->getParam('data_saida');
        $start = explode('/', $start);
        $start = $start[2]."-".$start[1]."-".$start[0];
        $start = $start . ' 00:00:00';

        $end = $request->getParam('data_retorno');
        $end = explode('/', $end);
        $end = $end[2]."-".$end[1]."-".$end[0];
        $end = $end . ' 23:59:59';

        $acolhimento = Acolhimentos::find($request->getParam('acolhimentos'));
        $acolhido = Acolhidos::find($acolhimento['acolhido']);

        $title = '[RESSOCIALIZAÇÃO] - ' . $acolhido['nome'];

        if($request->getParam('status') == '0'){
            $category = 'info';
        }

        if($request->getParam('status') == '1'){
            $category = 'danger';
        }

        if($request->getParam('status') == '2'){
            $category = 'success';
        }

        $item->update([
            'acolhimento' => $request->getParam('acolhimentos'),
            'start' => $start,
            'end' => $end,
            'data_saida' => $start,
            'data_retorno' => $end,
            'status' => $request->getParam('status'),
            'observacoes' => $request->getParam('observacoes'),
            'category' => $category
        ]);

        if($item)
        {   
            if($request->getParam('status') == '0'){
                $descricao = $acolhido['nome'] . ' saiu para ressocialização em ' . $request->getParam('data_saida') . ' com retorno para ' . $request->getParam('data_retorno');
                $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
                $color = array_rand($colors);

                $titulo = 'Ressocialização #' . $args['id'];
                $timeline = Timeline::where('titulo', $titulo);
                $timeline->update([
                    'acolhimento' => $request->getParam('acolhimentos'),
                    'usuario' => $_SESSION['Auth'],
                    'titulo' => 'Ressocialização #' . $item->id,
                    'descricao' => $descricao,
                    'color' => 'info',
                    'icon' => 'ti-direction-alt',
                ]);
            }

            if($request->getParam('status') == '1'){
                $descricao = $acolhido['nome'] . ' não retornou da ressocialização, na qual saiu em ' . $request->getParam('data_saida') . ' com retorno para ' . $request->getParam('data_retorno');

                $timeline = Timeline::create([
                    'acolhimento' => $request->getParam('acolhimentos'),
                    'usuario' => $_SESSION['Auth'],
                    'titulo' => 'Ressocialização #' . $args['id'],
                    'descricao' => $descricao,
                    'color' => 'danger',
                    'icon' => 'ti-direction-alt',
                ]);
            }

            if($request->getParam('status') == '2'){
                $acolhimento->update([
                    'status' => 0
                ]);

                $descricao = $acolhido['nome'] . ' retornou da ressocialização, na qual saiu em ' . $request->getParam('data_saida') . ' com retorno para ' . $request->getParam('data_retorno');

                $timeline = Timeline::create([
                    'acolhimento' => $request->getParam('acolhimentos'),
                    'usuario' => $_SESSION['Auth'],
                    'titulo' => 'Ressocialização #' . $args['id'],
                    'descricao' => $descricao,
                    'color' => 'success',
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

    // Remove o registro
    public function getRemover ($request, $response, $args)
    {
        if($this->acesso_usuario['ressocializacoes']['index']['d'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Ressocializacao::find($args['id']);
        $titulo = 'Ressocialização #' . $item['id'];
        $timeline = Timeline::where('titulo', $titulo);

        $acolhimento = Acolhimentos::find($item['acolhimento']);

        if($timeline->delete())
        {
            if($item->delete())
            {
                $acolhimento->update([
                    'status' => 0
                ]);
                if($acolhimento){
                    return true;
                } else {
                    return false;
                }
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