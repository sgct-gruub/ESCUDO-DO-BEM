<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Respect\Validation\Validator as validate;
use Slim\Views\Twig as View;

use App\Models\Doadores;
use App\Models\Doacoes\Redes;

class DoadoresController extends Controller
{

##########
    ###### Doadores
##########
      // EXIBE A LISTAGEM
      public function listar ($request, $response, $args)
      {
            if($this->acesso_usuario['doadores']['index']['r'] != 'on'){
                  return $this->container->view->render($response->withStatus(403), 'error/403.html');
            }

            $item = Doadores::get()->toArray();

            for ($i=0; $i < count($item) ; $i++) { 
                  $id_doador = $item[$i]['id'];
                  $rede[$id_doador] = Redes::where('link', [$item[$i]['rede']])->first();
                  $this->view->offsetSet("rede", $rede);
            }

            $mensagem = $this->flash->getMessages();

            return $this->view->render($response, 'doacoes/doadores/listar.html', [
                  'Titulo_Pagina' => 'Doadores',
                  'titulo'    => 'Listagem dos doadores',
                  'subtitulo' => 'Todos os doadores da sua instituição',
                  'view'      => 'listar',
                  'flash'     => $mensagem,
                  'itens'     => $item,
            ]);
      }

      // Exibe formulário de cadastro
      public function getCadastrar ($request, $response)
      {
            if($this->acesso_usuario['doadores']['index']['c'] != 'on'){
                  return $this->container->view->render($response->withStatus(403), 'error/403.html');
            }

            return $this->view->render($response, 'doacoes/doadores/form.html', [
                  'Titulo_Pagina' => 'Novo Registro',
                  'titulo'    => 'Cadastrar novo doador',
                  'subtitulo' => 'Preencha o formulário abaixo para inserir um novo doador',
                  'view'      => 'cadastrar',
            ]);
      }

      // SALVA O REGISTRO NO BANCO DE DADOS
      public function postCadastrar ($request, $response, $args)
      {     
            $data_nascimento = $request->getParam('data_nascimento');
            $data_nascimento = explode('/', $data_nascimento);
            $data_nascimento = $data_nascimento[2].'-'.$data_nascimento[1].'-'.$data_nascimento[0];

            // if($request->getParam('status') == 'on'){
            //       $status = 1;
            // } else {
            //       $status = 0;
            // }

            $item = Doadores::create([
                  'nome' => $request->getParam('nome'),
                  'cpf' => $request->getParam('cpf'),
                  'data_nascimento' => $data_nascimento,
                  'telefone' => $request->getParam('telefone'),
                  'celular' => $request->getParam('celular'),
                  'email' => $request->getParam('email'),
                  'cep' => $request->getParam('cep'),
                  'endereco' => $request->getParam('endereco'),
                  'num' => $request->getParam('num'),
                  'complemento' => $request->getParam('complemento'),
                  'bairro' => $request->getParam('bairro'),
                  'cidade' => $request->getParam('cidade'),
                  'uf' => $request->getParam('uf'),
                  // 'status' => $status,
                  'observacoes' => $request->getParam('observacoes'),
            ]);

            if($item)
            {
                  $this->flash->addMessage('success', 'Doador cadastrado com sucesso!');
            }
            else
            {
                  $this->flash->addMessage('error', 'Erro ao cadastrar doador!');
            }

            return $response->withRedirect($this->router->pathFor('doadores'));
      }

      // Exibe formulário de edição
      public function getEditar ($request, $response, $args)
      {
            if($this->acesso_usuario['doadores']['index']['u'] != 'on'){
                  return $this->container->view->render($response->withStatus(403), 'error/403.html');
            }

            $item = Doadores::find($args['id']);
            
            return $this->view->render($response, 'doacoes/doadores/form.html', [
                  'Titulo_Pagina' => 'Editar Registro',
                  'titulo'    => 'Editar doador',
                  'subtitulo' => 'Preencha o formulário abaixo para editar esse doador',
                  'view'      => 'editar',
                  'item' => $item,
            ]);
      }

      // SALVA O REGISTRO NO BANCO DE DADOS
      public function postEditar ($request, $response, $args)
      {
            $item = Doadores::find($args['id']);

            $data_nascimento = $request->getParam('data_nascimento');
            $data_nascimento = explode('/', $data_nascimento);
            $data_nascimento = $data_nascimento[2].'-'.$data_nascimento[1].'-'.$data_nascimento[0];

            // if($request->getParam('status') == 'on'){
            //       $status = 1;
            // } else {
            //       $status = 0;
            // }

            $item->update([
                  'nome' => $request->getParam('nome'),
                  'cpf' => $request->getParam('cpf'),
                  'data_nascimento' => $data_nascimento,
                  'telefone' => $request->getParam('telefone'),
                  'celular' => $request->getParam('celular'),
                  'email' => $request->getParam('email'),
                  'cep' => $request->getParam('cep'),
                  'endereco' => $request->getParam('endereco'),
                  'num' => $request->getParam('num'),
                  'complemento' => $request->getParam('complemento'),
                  'bairro' => $request->getParam('bairro'),
                  'cidade' => $request->getParam('cidade'),
                  'uf' => $request->getParam('uf'),
                  // 'status' => $status,
                  'observacoes' => $request->getParam('observacoes'),
            ]);

            if($item)
            {
                  $this->flash->addMessage('success', 'Doador editado com sucesso!');
            }
            else
            {
                  $this->flash->addMessage('error', 'Erro ao editar doador!');
            }

            return $response->withRedirect($this->router->pathFor('doadores'));
      }

      // REMOVER
      public function getRemover ($request, $response, $args)
      {
            if($this->acesso_usuario['doadores']['index']['d'] != 'on'){
                  return $this->container->view->render($response->withStatus(403), 'error/403.html');
            }

            $item = Doadores::find($args['id']);

            if($item->delete())
            {
                  return true;
            }
            else
            {
                  return false;
            }
      }
##########
    ###### END
##########
}