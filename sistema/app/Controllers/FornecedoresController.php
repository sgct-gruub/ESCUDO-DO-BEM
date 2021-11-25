<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Respect\Validation\Validator as validate;
use Slim\Views\Twig as View;

use App\Models\Fornecedores;

class FornecedoresController extends Controller
{

##########
    ###### Fornecedores
##########
      // EXIBE A LISTAGEM
      public function listar ($request, $response, $args)
      {
            if($this->acesso_usuario['fornecedores']['index']['r'] != 'on'){
                  return $this->container->view->render($response->withStatus(403), 'error/403.html');
            }

            $item = Fornecedores::get()->toArray();

            $mensagem = $this->flash->getMessages();

            return $this->view->render($response, 'fornecedores/listar.html', [
                  'Titulo_Pagina' => 'Fornecedores',
                  'titulo'    => 'Listagem dos fornecedores',
                  'subtitulo' => 'Todos os fornecedores da sua instituição',
                  'view'      => 'listar',
                  'flash'     => $mensagem,
                  'itens'     => $item,
            ]);
      }

      // Exibe formulário de cadastro
      public function getCadastrar ($request, $response)
      {
            if($this->acesso_usuario['fornecedores']['index']['c'] != 'on'){
                  return $this->container->view->render($response->withStatus(403), 'error/403.html');
            }

            return $this->view->render($response, 'fornecedores/form.html', [
                  'Titulo_Pagina' => 'Novo Registro',
                  'titulo'    => 'Cadastrar novo fornecedor',
                  'subtitulo' => 'Preencha o formulário abaixo para inserir um novo fornecedor',
                  'view'      => 'cadastrar',
            ]);
      }

      // SALVA O REGISTRO NO BANCO DE DADOS
      public function postCadastrar ($request, $response, $args)
      {
            $dados_pagamento = serialize($request->getParam('dados_transferencia'));

            $item = Fornecedores::create([
                  'nome_fantasia' => $request->getParam('nome_fantasia'),
                  'razao_social' => $request->getParam('razao_social'),
                  'cnpj' => $request->getParam('cnpj'),
                  'tipo' => $request->getParam('tipo'),
                  'inscricao_estadual' => $request->getParam('inscricao_estadual'),
                  'inscricao_municipal' => $request->getParam('inscricao_municipal'),
                  'email' => $request->getParam('email'),
                  'cep' => $request->getParam('cep'),
                  'endereco' => $request->getParam('endereco'),
                  'num' => $request->getParam('num'),
                  'complemento' => $request->getParam('complemento'),
                  'bairro' => $request->getParam('bairro'),
                  'cidade' => $request->getParam('cidade'),
                  'uf' => $request->getParam('uf'),
                  'responsavel' => $request->getParam('responsavel'),
                  'telefone' => $request->getParam('telefone'),
                  'celular' => $request->getParam('celular'),
                  'dados_pagamento' => $dados_pagamento,
                  'observacoes' => $request->getParam('observacoes'),
            ]);

            if($item)
            {
                  $this->flash->addMessage('success', 'Fornecedor cadastrado com sucesso!');
            }
            else
            {
                  $this->flash->addMessage('error', 'Erro ao cadastrar fornecedor!');
            }

            return $response->withRedirect($this->router->pathFor('fornecedores'));
      }

      // Exibe formulário de edição
      public function getEditar ($request, $response, $args)
      {
            if($this->acesso_usuario['fornecedores']['index']['u'] != 'on'){
                  return $this->container->view->render($response->withStatus(403), 'error/403.html');
            }

            $item = Fornecedores::find($args['id']);

            if($item['dados_pagamento'] != ''){
                  $dados_pagamento = unserialize($item['dados_pagamento']);
            }
            
            return $this->view->render($response, 'fornecedores/form.html', [
                  'Titulo_Pagina' => 'Editar Registro',
                  'titulo'    => 'Editar fornecedor',
                  'subtitulo' => 'Preencha o formulário abaixo para editar esse fornecedor',
                  'view'      => 'editar',
                  'item' => $item,
                  'dados_pagamento' => $dados_pagamento,
            ]);
      }

      // SALVA O REGISTRO NO BANCO DE DADOS
      public function postEditar ($request, $response, $args)
      {
            $item = Fornecedores::find($args['id']);

            $dados_pagamento = serialize($request->getParam('dados_transferencia'));

            $item->update([
                  'nome_fantasia' => $request->getParam('nome_fantasia'),
                  'razao_social' => $request->getParam('razao_social'),
                  'cnpj' => $request->getParam('cnpj'),
                  'tipo' => $request->getParam('tipo'),
                  'inscricao_estadual' => $request->getParam('inscricao_estadual'),
                  'inscricao_municipal' => $request->getParam('inscricao_municipal'),
                  'email' => $request->getParam('email'),
                  'cep' => $request->getParam('cep'),
                  'endereco' => $request->getParam('endereco'),
                  'num' => $request->getParam('num'),
                  'complemento' => $request->getParam('complemento'),
                  'bairro' => $request->getParam('bairro'),
                  'cidade' => $request->getParam('cidade'),
                  'uf' => $request->getParam('uf'),
                  'responsavel' => $request->getParam('responsavel'),
                  'telefone' => $request->getParam('telefone'),
                  'celular' => $request->getParam('celular'),
                  'dados_pagamento' => $dados_pagamento,
                  'observacoes' => $request->getParam('observacoes'),
            ]);

            if($item)
            {
                  $this->flash->addMessage('success', 'Fornecedor editado com sucesso!');
            }
            else
            {
                  $this->flash->addMessage('error', 'Erro ao editar fornecedor!');
            }

            return $response->withRedirect($this->router->pathFor('fornecedores'));
      }

      // REMOVER
      public function getRemover ($request, $response, $args)
      {
            if($this->acesso_usuario['fornecedores']['index']['d'] != 'on'){
                  return $this->container->view->render($response->withStatus(403), 'error/403.html');
            }

            $item = Fornecedores::find($args['id']);

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