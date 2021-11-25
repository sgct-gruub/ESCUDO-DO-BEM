<?php

namespace App\Controllers;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Controllers\Controller;
use Slim\Views\Twig as View;
use App\Models\Users;
use App\Models\Roles;

class UsersController extends Controller {

// Exibe listagem dos usuários
  public function getRead(Request $request, Response $response, $args) {
  	
    if($this->acesso_usuario['usuarios']['index']['r'] != 'on'){
      return $this->container->view->render($response->withStatus(403), 'error/403.html');
    }

    $item = Users::orderBy('id','ASC')->get()->toArray();
    $roles = Roles::get()->toArray();

    for($i = 0; $i < count($roles); $i++){
      $id_role = $roles[$i]['id'];
      $role[$id_role] = Roles::find($id_role);

      $this->view->offsetSet("role", $role);
    }
    
    $mensagem = $this->flash->getMessages();

    return $this->view->render($response, 'usuarios/listar.html', [
      'Titulo_Pagina' => 'Usuários',
      'titulo'    => 'Listagem dos usuários',
      'subtitulo' => 'Todos os usuários que tem acesso ao sistema',
      'view'      => 'listar',
      'flash'     => $mensagem,
      'itens'     => $item,
      'roles'     => $roles,
    ]);
  }

  // Exibe formulário de cadastro dos usuários
  public function getCreate(Request $request, Response $response, $args) {
    
    $options = array(
      'title'   => 'Cadastrar novo usuário',
      'action'  => 'post_create_usuario',
      'button'  => 'Salvar'
    );

    $this->view->offsetSet("options", $options);
  	return $this->view->render($response, 'usuarios/editing.html');
  }

  // Exibe formulário de edição dos usuários
  public function getUpdate(Request $request, Response $response, $args) {

  	$usuario = Users::find($args['id']);

    $options = array(
      'title'   => 'Editando usuário',
      'action'  => 'post_update_usuario',
      'button'  => 'Atualizar'
    );

    $this->view->offsetSet("options", $options);
    $this->view->offsetSet("usuario", $usuario);
  	return $this->view->render($response, 'usuarios/editing.html');
  }

  // Remove um usuário
  public function getDelete(Request $request, Response $response, $args) {
  	
    if($this->acesso_usuario['usuarios']['index']['d'] != 'on'){
      return $this->container->view->render($response->withStatus(403), 'error/403.html');
    }

    $usuario = Users::find($args['id']);
    $usuario->delete();

    $this->flash->addMessage('success', 'Usuário removido com sucesso!');
    return $response->withRedirect($this->router->pathFor('usuarios'));
  }

  // Cadastra um usuário no banco de dados
  public function postCreate(Request $request, Response $response, $args) {

    $params = (object) $request->getParams();

    if($params->senha != $params->senha2) {
      $this->flash->addMessage('error', 'As senhas não são iguais!');
      return $response->withRedirect($this->router->pathFor('usuarios'));
    }
    
    $itens = array(
      'name'      => $params->name,
      'email'     => $params->email,
      'password'  => password_hash($params->senha, PASSWORD_DEFAULT),
      'role'     => $params->role
    );

    $usuario = Users::create($itens);

    $this->flash->addMessage('success', 'Usuário cadastrado com sucesso!');
    return $response->withRedirect($this->router->pathFor('usuarios'));
  }

  // Edita uma usuario no banco de dados
  public function postUpdate(Request $request, Response $response, $args) {

    $usuario = Users::find($args['id']);

    $params = (object) $request->getParams();

    if($params->senha != $params->senha2) {
      $this->flash->addMessage('error', 'As senhas não são iguais!');
      return $response->withRedirect($this->router->pathFor('usuarios'));
    }

    if($params->senha != null and $params->senha2 != null) {

      $itens = array(
        'name'      => $params->name,
        'email'     => $params->email,
        'password'  => password_hash($params->senha, PASSWORD_DEFAULT),
        'role'      => $params->role
      );

    } else {

      $itens = array(
        'name'      => $params->name,
        'email'     => $params->email,
        'role'      => $params->role
      );

    }

    $usuario->update($itens);

    $this->flash->addMessage('success', 'Usuário editado com sucesso!');
    return $response->withRedirect($this->router->pathFor('usuarios'));
  }

}