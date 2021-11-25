<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Respect\Validation\Validator as validate;
use Slim\Views\Twig as View;

use App\Models\Convenios;
use App\Models\Acolhimentos;

class ConveniosController extends Controller
{

	// EXIBE A LISTAGEM DOS CONVÊNIOS
    public function listar ($request, $response, $args)
    {   
        if($this->acesso_usuario['convenios']['index']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Convenios::orderBy('id','ASC')->get()->toArray();

        for($i = 0; $i < count($item); $i++){
            $vagas_ocupadas[$item[$i]['id']] = Acolhimentos::where('convenio', $item[$i]['id'])->whereIn('status', [0, 1])->count();

            $this->view->offsetSet("vagas_ocupadas", $vagas_ocupadas);
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'convenios/listar.html', [
            'Titulo_Pagina' => 'Convênios',
            'titulo'    => 'Listagem dos convênios',
            'subtitulo' => 'Todos os convênios da sua instituição.',
            'view'      => 'listar',
            'flash'     => $mensagem,
            'itens'     => $item
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
        if($this->acesso_usuario['convenios']['index']['d'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Convenios::find($args['id']);

        if($item->delete())
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    // SALVA O REGISTRO NO BANCO DE DADOS
    public function postCadastrar ($request, $response, $args)
    {
        if($this->acesso_usuario['convenios']['index']['c'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

	    $item = Convenios::create([
	    	'nome' 		=> $request->getParam('nome'),
            'vagas'     => $request->getParam('vagas'),
	       	'valor' 	=> $request->getParam('valor'),
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

    // EDITA O REGISTRO
    public function postEditar ($request, $response, $args)
    {
        if($this->acesso_usuario['convenios']['index']['u'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

    	$item = Convenios::find($args['id']);
    	$item->update([
            'nome'      => $request->getParam('nome'),
            'vagas'     => $request->getParam('vagas'),
            'valor'     => $request->getParam('valor'),
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
}