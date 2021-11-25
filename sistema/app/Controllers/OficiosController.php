<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Respect\Validation\Validator as validate;
use Slim\Views\Twig as View;

use App\Models\Oficios;
use App\Models\Acolhidos;
use App\Models\Acolhimentos;
use App\Models\Timeline;

class OficiosController extends Controller
{

	// EXIBE A LISTAGEM DAS ATIVIDADES TERAPÊUTICAS
    public function listar ($request, $response, $args)
    {   
        if($this->acesso_usuario['atividades_terapeuticas']['index']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Oficios::orderBy('id','ASC')->get()->toArray();

        $acolhimentos = Acolhimentos::where('status', 0)->get()->toArray();

        for($i = 0; $i < count($acolhimentos); $i++){
            $id_acolhido = $acolhimentos[$i]['acolhido'];
            $acolhido[$id_acolhido] = Acolhidos::where('id', $acolhimentos[$i]['acolhido'])->first();
            $oficios = Oficios::whereIn('acolhimentos', [$acolhimentos[$i]['id']])->get()->toArray();

            $this->view->offsetSet("acolhido", $acolhido);
            $this->view->offsetSet("oficios", $oficios);
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'oficios/listar.html', [
            'Titulo_Pagina' => 'Atividades Terapêuticas',
            'titulo'    => 'Listagem das atividades terapêuticas',
            'subtitulo' => 'Todas as atividades terapêuticas da sua instituição.',
            'view'      => 'listar',
            'flash'     => $mensagem,
            'itens'     => $item,
            'acolhimentos' => $acolhimentos
        ]);
    }

    // // EXIBE O FORMULÁRIO DE CADASTRO
    // public function getCadastrar ($request, $response)
    // {
    // 	return $this->view->render($response, 'unidades/form.html', [
    //     	'titulo' => 'Cadastrar nova unidade',
    //     	'view' => 'cadastrar'
    // 	]);
    // }

    // // EXIBE O FORMULÁRIO DE EDIÇÃO
    // public function getEditar ($request, $response, $args)
    // {
    // 	$item = Unidades::find($args['id']);
    	
    // 	return $this->view->render($response, 'unidades/form.html', [
    //     	'titulo' => 'Editar unidade',
    //     	'view' => 'editar',
    //     	'item' => $item
    // 	]);
    // }

    // REMOVER
    public function getRemover ($request, $response, $args)
    {
        if($this->acesso_usuario['atividades_terapeuticas']['index']['d'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Oficios::find($args['id']);

        if($item->delete())
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    // LIMPAR
    public function getLimpar ($request, $response, $args)
    {
        if($this->acesso_usuario['atividades_terapeuticas']['index']['u'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Oficios::find($args['id']);

        $acolhimentos = explode(',', $item['acolhimentos']);

        $item->update([
            'acolhimentos' => ''
        ]);

        if($item)
        {
            foreach ($acolhimentos as $key => $value) {
                $acolhimento = Acolhimentos::find($value);
                $acolhido = Acolhidos::find($acolhimento['acolhido']);
                $descricao = $acolhido['nome'] . ' foi retirado da Atividade Terapêutica ' . $item['nome'] . ' na data de ' . date('d/m/Y');
                $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
                $color = array_rand($colors);

                $timeline = Timeline::create([
                    'acolhimento' => $value,
                    'usuario' => $_SESSION['Auth'],
                    'titulo' => 'Retirada de atividade terapêutica',
                    'descricao' => $descricao,
                    'color' => $color,
                    'icon' => 'mdi mdi-briefcase',
                ]);
            }
        }
        else
        {
            return false;
        }
    }

    // SALVA O REGISTRO NO BANCO DE DADOS
    public function postCadastrar ($request, $response, $args)
    {
        $acolhimentos = implode(',', $request->getParam('acolhimentos'));

	    $item = Oficios::create([
            'nome' => $request->getParam('nome'),
            'atividades' => $request->getParam('atividades'),
            'acolhimentos' => $acolhimentos,
        ]);

        if($item)
        {
            foreach ($request->getParam('acolhimentos') as $key => $value) {
                $acolhimento = Acolhimentos::find($value);
                $acolhido = Acolhidos::find($acolhimento['acolhido']);
                $descricao = $acolhido['nome'] . ' foi incluído como responsável pela Atividade Terapêutica ' . $request->getParam('nome') . ' na data de ' . date('d/m/Y');
                $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
                $color = array_rand($colors);

                $timeline = Timeline::create([
                    'acolhimento' => $value,
                    'usuario' => $_SESSION['Auth'],
                    'titulo' => 'Nova atividade terapêutica',
                    'descricao' => $descricao,
                    'color' => $color,
                    'icon' => 'mdi mdi-briefcase',
                ]);
            }

            return true;
        }
        else
        {
            return false;
        }
    }

    // EDITA O REGISTRO
    public function postEditar ($request, $response, $args)
    {
        $acolhimentos = implode(',', $request->getParam('acolhimentos'));
        $acolhimentos_old = explode(',', $request->getParam('acolhimentos_old'));
        $responsavel_old = $request->getParam('responsavel_old');

        $item = Oficios::find($args['id']);

    	$item->update([
            'nome' => $request->getParam('nome'),
            'atividades' => $request->getParam('atividades'),
            'acolhimentos' => $acolhimentos,
            'responsavel' => $request->getParam('responsavel')
        ]);

        if($item)
        {
            foreach ($request->getParam('acolhimentos') as $key => $value) {
                $acolhimento = Acolhimentos::find($value);
                $acolhido = Acolhidos::find($acolhimento['acolhido']);

                if(!in_array($value, $acolhimentos_old)){
                    $descricao = $acolhido['nome'] . ' foi incluído na Atividade Terapêutica ' . $request->getParam('nome') . ' na data de ' . date('d/m/Y');
                    $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
                    $color = array_rand($colors);

                    $timeline = Timeline::create([
                        'acolhimento' => $value,
                        'usuario' => $_SESSION['Auth'],
                        'titulo' => 'Nova atividade terapêutica',
                        'descricao' => $descricao,
                        'color' => $color,
                        'icon' => 'mdi mdi-briefcase',
                    ]);
                }
            }

            foreach ($acolhimentos_old as $key => $value) {
                $acolhimento = Acolhimentos::find($value);
                $acolhido = Acolhidos::find($acolhimento['acolhido']);

                if(!in_array($value, $request->getParam('acolhimentos'))){
                    $descricao = $acolhido['nome'] . ' foi retirado da Atividade Terapêutica ' . $request->getParam('nome') . ' na data de ' . date('d/m/Y');
                    $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
                    $color = array_rand($colors);

                    $timeline = Timeline::create([
                        'acolhimento' => $value,
                        'usuario' => $_SESSION['Auth'],
                        'titulo' => 'Retirada de atividade terapêutica',
                        'descricao' => $descricao,
                        'color' => $color,
                        'icon' => 'mdi mdi-briefcase',
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
}