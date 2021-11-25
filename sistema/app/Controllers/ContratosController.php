<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Respect\Validation\Validator as validate;
use Slim\Views\Twig as View;

use App\Models\Contratos;
use App\Models\Convenios;

class ContratosController extends Controller
{
    
	// EXIBE A LISTAGEM
    public function listar ($request, $response, $args)
    {   
        if($this->acesso_usuario['contratos']['index']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Contratos::orderBy('id','ASC')->get()->toArray();

        for($i = 0; $i < count($item); $i++){
            $id_contrato = $item[$i]['id'];
            $convenio[$id_contrato] = Convenios::find($item[$i]['convenio']);

            $this->view->offsetSet("convenio", $convenio);
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'contratos/listar.html', [
            'Titulo_Pagina' => 'Contratos',
            'titulo'    => 'Listagem dos contratos',
            'subtitulo' => 'Todos os contratos da sua instituição.',
            'view'      => 'listar',
            'flash'     => $mensagem,
            'itens'     => $item
        ]);
    }

    // EXIBE O FORMULÁRIO DE CADASTRO
    public function getCadastrar ($request, $response)
    {
        if($this->acesso_usuario['contratos']['index']['c'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $convenios = Convenios::orderBy('id','ASC')->get()->toArray();

        $mensagem = $this->flash->getMessages();

    	return $this->view->render($response, 'contratos/form.html', [
            'Titulo_Pagina' => 'Novo registro',
        	'titulo' => 'Cadastrar novo contrato',
            'subtitulo' => 'Preencha o formulário abaixo para insernir um novo contrato de acolhimento.',
        	'view' => 'cadastrar',
            'flash' => $mensagem,
            'convenios' => $convenios
    	]);
    }

    // EXIBE O FORMULÁRIO DE EDIÇÃO
    public function getEditar ($request, $response, $args)
    {
        if($this->acesso_usuario['contratos']['index']['u'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

    	$item = Contratos::find($args['id']);
        $convenios = Convenios::orderBy('id','ASC')->get()->toArray();

        $mensagem = $this->flash->getMessages();
    	
    	return $this->view->render($response, 'contratos/form.html', [
        	'Titulo_Pagina' => 'Editar registro',
            'titulo' => 'Editar contrato',
            'subtitulo' => 'Preencha o formulário abaixo para editar o contrato de acolhimento.',
        	'view' => 'editar',
        	'item' => $item,
            'convenios' => $convenios,
            'flash' => $mensagem
    	]);
    }

    // REMOVER
    public function getRemover ($request, $response, $args)
    {
        if($this->acesso_usuario['contratos']['index']['d'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Contratos::find($args['id']);

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
        if($request->getParam('convenio') != ''){
            $contrato = Contratos::where('tipo', $request->getParam('tipo'))->where('convenio', $request->getParam('convenio'))->count();
            $convenio = $request->getParam('convenio');

            $msg = array('error', 'Já existe um contrato cadastrado para este tipo de acolhimento e convênio!');
        } else {
            $contrato = Contratos::where('tipo', $request->getParam('tipo'))->count();
            $convenio = '';

            $msg = array();
        }

        if($contrato >= 1){
            if($request->getParam('convenio') != ''){
                $this->flash->addMessage('error', 'Já existe um contrato cadastrado para este tipo de acolhimento e convênio!');
            } else {
                $this->flash->addMessage('error', 'Já existe um contrato cadastrado para este tipo de acolhimento!');
            }

            return $response->withRedirect($this->router->pathFor('cadastrar_contrato'));
        }

	    $item = Contratos::create([
            'tipo' => $request->getParam('tipo'),
            'voluntario' => $request->getParam('voluntario'),
	    	'terceirizado' => $request->getParam('terceirizado'),
            'convenio' => $convenio,
            'titulo' => $request->getParam('titulo'),
            'conteudo' => $request->getParam('conteudo'),
	       	// 'status' => $request->getParam('status'),
	    ]);

    	if($item)
        {
            $this->flash->addMessage('success', 'Contrato adicionado com sucesso!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao adicionar contrato!');
        }

        return $response->withRedirect($this->router->pathFor('contratos'));
    }

    // EDITA O REGISTRO
    public function postEditar ($request, $response, $args)
    {
    	$item = Contratos::find($args['id']);

        if($request->getParam('convenio') != ''){
            $convenio = $request->getParam('convenio');
        } else {
            $convenio = '';
        }

    	$item->update([
            'tipo' => $request->getParam('tipo'),
            'voluntario' => $request->getParam('voluntario'),
            'terceirizado' => $request->getParam('terceirizado'),
            'convenio' => $convenio,
            'titulo' => $request->getParam('titulo'),
            'conteudo' => $request->getParam('conteudo'),
            // 'status' => $request->getParam('status'),
        ]);

        if($item)
        {
            $this->flash->addMessage('success', 'Contrato editado com sucesso!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao editar contrato!');
        }

        return $response->withRedirect($this->router->pathFor('editar_contrato', ['id' => $args['id']]));
    }
}