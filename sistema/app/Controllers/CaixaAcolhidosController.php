<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Respect\Validation\Validator as validate;
use Slim\Views\Twig as View;

use App\Models\Acolhidos;
use App\Models\Acolhimentos;
use App\Models\CaixaAcolhidos;
use App\Models\Unidades;
use App\Models\Users;

class CaixaAcolhidosController extends Controller
{

##########
    ###### Caixa Acolhidos
##########
      // LISTAGEM DOS ACOLHIDOS COM SEU SALDO EM CAIXA
      public function listar ($request, $response, $args)
      {     
            if($this->acesso_usuario['acolhidos']['caixa']['c'] != 'on'){
                  return $this->container->view->render($response->withStatus(403), 'error/403.html');
            }

            $item = Acolhidos::get()->toArray();

            for($i = 0; $i < count($item); $i++){
                  $id_acolhido = $item[$i]['id'];
                  $verifica_acolhimento[$id_acolhido] = Acolhimentos::whereIn('status', [0, 1])->where('acolhido', $id_acolhido)->count();
                
                  $this->view->offsetSet("verifica_acolhimento", $verifica_acolhimento);

                  // Verifica se o acolhido tem algum lançamento no CaixaAcolhidos
                  $verifica_caixa[$id_acolhido] = CaixaAcolhidos::where('acolhido', $id_acolhido)->count();
                  $this->view->offsetSet("verifica_caixa", $verifica_caixa);

                  // Pega o valor total de crédito CaixaAcolhidos
                  $credito_caixa = CaixaAcolhidos::where('acolhido', $id_acolhido)->where('tipo', 1)->sum('valor');

                  // Pega o valor total de débito CaixaAcolhidos
                  $debito_caixa = CaixaAcolhidos::where('acolhido', $id_acolhido)->where('tipo', 0)->sum('valor');

                  // Calcula o saldo final CaixaAcolhidos
                  $saldo_caixa[$id_acolhido] = $credito_caixa - $debito_caixa;
                  $this->view->offsetSet("saldo_caixa", $saldo_caixa);
            }

            $mensagem = $this->flash->getMessages();

            return $this->view->render($response, 'acolhidos/listar_caixa.html', [
                  'Titulo_Pagina' => 'Caixa dos Acolhidos',
                  'titulo'    => 'Resumo do caixa',
                  'subtitulo' => 'Listagem de todos os acolhidos com seu saldo em caixa',
                  'itens' => $item,
                  'flash' => $mensagem,
                  'status' => $args['status'],
            ]);
      }

      // RECEBE A PÁGINA DE INSERÇÃO DO ITEM E EXIBE OS LANÇAMENTOS
      public function getCaixaAcolhido ($request, $response, $args)
      {     
            if($this->acesso_usuario['acolhidos']['caixa']['c'] != 'on'){
                  return $this->container->view->render($response->withStatus(403), 'error/403.html');
            }

            // Puxa os lançamentos do CaixaAcolhidos
            $caixa = CaixaAcolhidos::get()->toArray();
            
            foreach ($caixa as $c) {
                  $id_caixa = $c['id'];
                  // Pega o nome do usuário que fez o lançamento
                  $usuario[$id_caixa] = Users::find($c['usuario']);
                  
                  $this->view->offsetSet("usuario", $usuario);
            }


            $item = Acolhidos::find($args['id']);
            $id_acolhido = $args['id'];

            // Verifica se o acolhido tem algum lançamento no CaixaAcolhidos
            $verifica_caixa[$id_acolhido] = CaixaAcolhidos::where('acolhido', $id_acolhido)->count();
            $this->view->offsetSet("verifica_caixa", $verifica_caixa);

            // Pega o valor total de crédito CaixaAcolhidos
            $credito_caixa = CaixaAcolhidos::where('acolhido', $id_acolhido)->where('tipo', 1)->sum('valor');

            // Pega o valor total de débito CaixaAcolhidos
            $debito_caixa = CaixaAcolhidos::where('acolhido', $id_acolhido)->where('tipo', 0)->sum('valor');

            // Calcula o saldo final CaixaAcolhidos
            $saldo_caixa[$id_acolhido] = $credito_caixa - $debito_caixa;
            $this->view->offsetSet("saldo_caixa", $saldo_caixa);

            $mensagem = $this->flash->getMessages();

            return $this->view->render($response, 'acolhidos/caixa.html', [
                  'Titulo_Pagina' => 'Caixa do Acolhido',
                  'titulo'    => 'Novo lançamento',
                  'subtitulo' => 'Preencha o formulário abaixo para inserir um novo lançamento para o caixa deste acolhido',
                  'item' => $item,
                  'flash' => $mensagem,
                  'caixa' => $caixa
            ]);
      }

      // SALVA O REGISTRO NO BANCO DE DADOS
      public function postCadastrar ($request, $response, $args)
      {     
            if($this->acesso_usuario['acolhidos']['caixa']['c'] != 'on'){
                  return $this->container->view->render($response->withStatus(403), 'error/403.html');
            }

            $data = $request->getParam('data');
            $data = explode('/', $data);
            $data = $data[2].'-'.$data[1].'-'.$data[0];

            $item = CaixaAcolhidos::create([
                  'acolhido' => $args['id'],
                  'data' => $data,
                  'descricao' => $request->getParam('descricao'),
                  'tipo' => $request->getParam('tipo'),
                  'valor' => $request->getParam('valor'),
                  'usuario' => $request->getParam('usuario'),
                  // 'observacoes' => $request->getParam('observacoes'),
            ]);

            if($item)
            {
                  $this->flash->addMessage('success', 'Lançamento cadastrado com sucesso para o acolhido!');
            }
            else
            {
                  $this->flash->addMessage('error', 'Erro ao efetuar lançamento para o acolhido!');
            }

            return $response->withRedirect($this->router->pathFor('caixa_acolhido', ['id' => $args['id']]));
      }

      // REMOVER
      public function getRemover ($request, $response, $args)
      {
            $item = CaixaAcolhidos::find($args['id']);

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