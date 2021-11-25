<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Respect\Validation\Validator as validate;
use Slim\Views\Twig as View;

use App\Models\Mensagens;
use App\Models\Users;

class MensagensController extends Controller
{

    public function listar ($request, $response, $args)
    {   
        // if($this->acesso_usuario['convenios']['index']['r'] != 'on'){
        //     return $this->container->view->render($response->withStatus(403), 'error/403.html');
        // }

        $item = Mensagens::where('destinatario', $_SESSION['Auth'])->orderBy('created_at','DESC')->get()->toArray();

        for ($i=0; $i < count($item) ; $i++) { 
            $remetente[$item[$i]['id']] = Users::find($item[$i]['remetente']);
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'mensagens/listar.html', [
            'Titulo_Pagina' => 'Mensagens',
            'titulo'    => 'Listagem dos convênios',
            'subtitulo' => 'Todos os convênios da sua instituição.',
            'view'      => 'caixa-entrada',
            'remetente' => $remetente,
            'flash'     => $mensagem,
            'itens'     => $item
        ]);
    }

    public function listarEnviadas ($request, $response, $args)
    {   
        // if($this->acesso_usuario['convenios']['index']['r'] != 'on'){
        //     return $this->container->view->render($response->withStatus(403), 'error/403.html');
        // }

        $item = Mensagens::where('remetente', $_SESSION['Auth'])->orderBy('created_at','DESC')->get()->toArray();

        for ($i=0; $i < count($item) ; $i++) { 
            $destinatario[$item[$i]['id']] = Users::find($item[$i]['destinatario']);
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'mensagens/listar.html', [
            'Titulo_Pagina' => 'Mensagens',
            'titulo'    => 'Listagem dos convênios',
            'subtitulo' => 'Todos os convênios da sua instituição.',
            'view'      => 'caixa-saida',
            'destinatario' => $destinatario,
            'flash'     => $mensagem,
            'itens'     => $item
        ]);
    }

    public function getLer ($request, $response, $args)
    {
    	$item = Mensagens::find($args['id']);
    	
        if($item['data_leitura'] == '0000-00-00 00:00:00'){
            $item->update([
                'data_leitura' => date('Y-m-d H:i:s')
            ]);
        }

    	return $this->view->render($response, 'mensagens/listar.html', [
        	'Titulo_Pagina' => 'Ler mensagem',
        	'view' => 'ler-mensagem',
        	'item' => $item
    	]);
    }

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
	    $item = Mensagens::create([
	    	'remetente' => $request->getParam('remetente'),
            'destinatario' => $request->getParam('destinatario'),
	       	'mensagem' => $request->getParam('mensagem'),
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