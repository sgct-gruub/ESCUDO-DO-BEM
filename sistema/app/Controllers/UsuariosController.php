<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Slim\Views\Twig as View;
use App\Models\Usuarios;

class UsuariosController extends Controller
{

	// Exibe listagem dos usuários
    public function listar ($request, $response, $args)
    {
    	$usuarios = Usuarios::orderBy('id','ASC')->get()->toArray();

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'usuarios.html', [
            'titulo'    => 'Usuários',
            'view'      => 'listar',
            'flash'     => $mensagem,
            'usuarios'  => $usuarios
        ]);
    }

    // Exibe formulário de cadastro dos usuários
    public function getCadastrar ($request, $response)
    {
    	return $this->view->render($response, 'usuarios.html', [
        	'titulo'  => 'Cadastrar novo usuário',
        	'view'    => 'cadastrar'
    	]);
    }

    // Exibe formulário de edição dos usuários
    public function getEditar ($request, $response, $args)
    {
    	$usuario = Usuarios::find($args['id']);
    	
    	return $this->view->render($response, 'usuarios.html', [
        	'titulo'   => 'Editar usuário',
        	'view'     => 'editar',
        	'usuario'  => $usuario
    	]);
    }

    // Remove um usuário
    public function getRemover ($request, $response, $args)
    {
    	$usuario = Usuarios::find($args['id']);

        if($usuario->delete())
        {
            $this->flash->addMessage('success', 'Usuário removido com sucesso!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao remover usuário!');
        }
        
    	return $response->withRedirect($this->router->pathFor('usuarios'));
    }

    // Cadastra um usuário no banco de dados
    public function postCadastrar ($request, $response, $args)
    {
	    $usuario = Usuarios::create([
	       'name'         => $request->getParam('name'),
	       'email'        => $request->getParam('email'),
           'password'     => password_hash($request->getParam('password'),PASSWORD_DEFAULT)
	    ]);

        if($usuario)
        {
            $this->flash->addMessage('success', 'Usuário cadastrado com sucesso!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao cadastrar usuário!');
        }
    	return $response->withRedirect($this->router->pathFor('usuarios'));
    }

    // Edita uma usuario no banco de dados
    public function postEditar ($request, $response, $args)
    {

        $usuario = Usuarios::find($args['id']);

        if($request->getParam('password') != null)
        {
            $usuario->update([
               'name'     => $request->getParam('name'),
               'email'    => $request->getParam('email'),
               'password'     => password_hash($request->getParam('password'),PASSWORD_DEFAULT)
            ]);
        }
        else
        {
            $usuario->update([
               'name'     => $request->getParam('name'),
               'email'    => $request->getParam('email')
            ]);
        }

        if($usuario)
        {
            $this->flash->addMessage('success', 'Usuário editado com sucesso!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao editar usuário!');
        }

    	return $response->withRedirect($this->router->pathFor('usuarios'));
    }
}