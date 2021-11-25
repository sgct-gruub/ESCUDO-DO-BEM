<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Respect\Validation\Validator as validate;
use Slim\Views\Twig as View;

use App\Models\Acolhidos;
use App\Models\Acolhimentos;
use App\Models\Contatos;
use App\Models\Mensalidades;
use App\Models\Matriculas;
use App\Models\Movimentos;
use App\Models\Recorrentes;
use App\Models\Categorias;
use App\Models\Financeiro\SubCategorias;
use App\Models\ContasBancarias;
use App\Models\Fornecedores;

class FinanceiroController extends Controller
{

##########
    ###### CONTAS A PAGAR
##########
      // EXIBE A LISTAGEM DAS CONTAS A PAGAR
      public function listarContasPagar ($request, $response, $args)
      {
            if($this->acesso_usuario['financeiro']['contas_a_pagar']['r'] != 'on'){
                  return $this->container->view->render($response->withStatus(403), 'error/403.html');
            }

            // listagem das categorias
            $categorias = Categorias::where('tipo', 0)->orderBy('nome', 'ASC')->get()->toArray();
            
            // listagem das contas bancarias
            $contas_bancarias = ContasBancarias::orderBy('nome', 'ASC')->get()->toArray();

            /* se receber ANO nem MÊS */
            if($args['mes'] != '' && $args['mes'] != 'todos' && $args['ano'] != ''){
                  $data1 = $args['ano'].'-'.$args['mes'].'-'.'01';
                  $data2 = date('Y-m-t', strtotime($data1));

                  $item = Movimentos::where('tipo', 0)->whereBetween('data_prevista', [$data1, $data2])->get()->toArray();
                  $total_pagar = Movimentos::where('tipo', 0)->where('status', 0)->whereBetween('data_prevista', [$data1, $data2])->sum('valor_previsto');
                  $total_pago = Movimentos::where('tipo', 0)->where('status', 1)->whereBetween('data_prevista', [$data1, $data2])->sum('valor_efetuado');
                  $total_pendente = Movimentos::where('tipo', 0)->where('status', 1)->whereBetween('data_prevista', [$data1, $data2])->sum('valor_previsto');
                  $total_pendente = $total_pendente - $total_pago;
            } else if($args['mes'] == 'todos') {
                  $data1 = $args['ano'].'-'.'01-01';
                  $data2 = $args['ano'].'-'.'12-31';

                  $item = Movimentos::where('tipo', 0)->whereBetween('data_prevista', [$data1, $data2])->get()->toArray();
                  $total_pagar = Movimentos::where('tipo', 0)->where('status', 0)->whereBetween('data_prevista', [$data1, $data2])->sum('valor_previsto');
                  $total_pago = Movimentos::where('tipo', 0)->where('status', 1)->whereBetween('data_prevista', [$data1, $data2])->sum('valor_efetuado');
                  $total_pendente = Movimentos::where('tipo', 0)->where('status', 1)->whereBetween('data_prevista', [$data1, $data2])->sum('valor_previsto');
                  $total_pendente = $total_pendente - $total_pago;
            } else {
                  $item = Movimentos::where('tipo', 0)->get()->toArray();
                  $total_pagar = Movimentos::where('tipo', 0)->where('status', 0)->sum('valor_previsto');
                  $total_pago = Movimentos::where('tipo', 0)->where('status', 1)->sum('valor_efetuado');
                  $total_pendente = Movimentos::where('tipo', 0)->where('status', 1)->sum('valor_previsto');
                  $total_pendente = $total_pendente - $total_pago;
            }

            $data_hoje = date('Y-m-d');
            $count_contas_a_pagar = 0;
            $count_contas_pagas = 0;
            $count_contas_vencidas = 0;

            for($i = 0; $i < count($item); $i++){
                  $categoria[$item[$i]['id']] = Categorias::find($item[$i]['categoria']);
                  $conta_bancaria[$item[$i]['id']] = ContasBancarias::find($item[$i]['conta_bancaria']);
                  $this->view->offsetSet("categoria", $categoria);
                  $this->view->offsetSet("conta_bancaria", $conta_bancaria);

                  if($data_hoje > $item[$i]['data_vencimento'] && $item[$i]['status'] == 0){
                        $count_contas_vencidas++;
                  }

                  // if($data_hoje <= $item[$i]['data_vencimento'] && $item[$i]['status'] == 0){
                  //       $count_contas_a_pagar++;
                  // }

                  if($item[$i]['status'] == 1){
                        $count_contas_pagas++;
                  }

            }

            $this->view->offsetSet("count_contas_a_pagar", $count_contas_a_pagar);
            $this->view->offsetSet("count_contas_pagas", $count_contas_pagas);
            $this->view->offsetSet("count_contas_vencidas", $count_contas_vencidas);

            $mensagem = $this->flash->getMessages();

            return $this->view->render($response, 'financeiro/contas_a_pagar/listar.html', [
                  'Titulo_Pagina_Mae' => 'Financeiro -',
                  'Titulo_Pagina' => 'Contas a Pagar',
                  'titulo'    => 'Listagem das contas a pagar',
                  'subtitulo' => 'Todas as contas a pagar do mês de',
                  'view'      => 'listar',
                  'categorias' => $categorias,
                  'contas_bancarias' => $contas_bancarias,
                  'flash'     => $mensagem,
                  'itens'     => $item,
                  'total_pagar' => $total_pagar,
                  'total_pago' => $total_pago,
                  'total_pendente' => $total_pendente,
                  'mes' => $args['mes'],
                  'ano' => $args['ano'],
                  'data1' => $data1,
                  'data2' => $data2,
            ]);
      }

      // Exibe formulário de cadastro
      public function getCadastrarContasPagar ($request, $response)
      {
            if($this->acesso_usuario['financeiro']['contas_a_pagar']['c'] != 'on'){
                  return $this->container->view->render($response->withStatus(403), 'error/403.html');
            }

            $categorias = Categorias::where('tipo', 0)->get()->toArray();
            $contas_bancarias = ContasBancarias::get()->toArray();
            $fornecedores = Fornecedores::get()->toArray();

            return $this->view->render($response, 'financeiro/contas_a_pagar/form.html', [
                  'Titulo_Pagina' => 'Novo Registro',
                  'titulo'    => 'Cadastrar nova conta a pagar',
                  'subtitulo' => 'Preencha o formulário abaixo para inserir uma nova conta a pagar no financeiro',
                  'view'      => 'cadastrar',
                  'categorias' => $categorias,
                  'contas_bancarias' => $contas_bancarias,
                  'fornecedores' => $fornecedores,
            ]);
      }

      // SALVA O REGISTRO NO BANCO DE DADOS
      public function postCadastrarContasPagar ($request, $response, $args)
      {
            $data_prevista = explode('/', $request->getParam('data_prevista'));
            $data_prevista = $data_prevista[2].'-'.$data_prevista[1].'-'.$data_prevista[0];

            if($request->getParam('status') == 'on'){
                  $status = 1;
                  $data_efetuada = $data_prevista;
                  $valor_efetuado = $request->getParam('valor_efetuado');

                  $conta_bancaria = ContasBancarias::find($request->getParam('conta_bancaria'));
                  $saldo_atual = $conta_bancaria['saldo_atual'] - $valor_efetuado;
                  $conta_bancaria->update([
                        'saldo_atual' => $saldo_atual,
                  ]);
            } else {
                  $status = 0;
                  $data_efetuada = '0000-00-00';
                  $valor_efetuado = '0.00';
            }

            if($request->getParam('forma_pagamento') == 'transferencia'){
                  $dados_pagamento = serialize($request->getParam('dados_transferencia'));
            }

            if($request->getParam('forma_pagamento') == 'cheque'){
                  $dados_pagamento = serialize($request->getParam('dados_cheque'));
            }

            if($request->getParam('forma_pagamento') == ''){
                  $dados_pagamento = '';
            }

            $item = Movimentos::create([
                  'num_nf' => $request->getParam('num_nf'),
                  'descricao' => $request->getParam('descricao'),
                  'categoria' => $request->getParam('categoria'),
                  'data_prevista' => $data_prevista,
                  'data_efetuada' => $data_efetuada,
                  'valor_previsto' => $request->getParam('valor_previsto'),
                  'valor_efetuado' => $valor_efetuado,
                  'conta_bancaria' => $request->getParam('conta_bancaria'),
                  'forma_pagamento' => $request->getParam('forma_pagamento'),
                  'dados_pagamento' => $dados_pagamento,
                  'acolhimento' => '',
                  'fornecedor' => $request->getParam('fornecedor'),
                  'tipo' => 0,
                  'status' => $status,
                  'observacoes' => $request->getParam('observacoes'),
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

      // Exibe formulário de edição
      public function getEditarContasPagar ($request, $response, $args)
      {
            if($this->acesso_usuario['financeiro']['contas_a_pagar']['u'] != 'on'){
                  return $this->container->view->render($response->withStatus(403), 'error/403.html');
            }

            $item = Movimentos::find($args['id']);
            $categorias = Categorias::where('tipo', 0)->get()->toArray();
            $contas_bancarias = ContasBancarias::get()->toArray();
            $fornecedores = Fornecedores::get()->toArray();

            if($item['dados_pagamento'] != ''){
                  $dados_pagamento = unserialize($item['dados_pagamento']);
            }
            
            return $this->view->render($response, 'financeiro/contas_a_pagar/form.html', [
                  'Titulo_Pagina' => 'Editar Registro',
                  'titulo'    => 'Editar conta a pagar',
                  'subtitulo' => 'Preencha o formulário abaixo para editar essa conta a pagar no financeiro',
                  'view'      => 'cadastrar',
                  'item' => $item,
                  'dados_pagamento' => $dados_pagamento,
                  'categorias' => $categorias,
                  'contas_bancarias' => $contas_bancarias,
                  'fornecedores' => $fornecedores,
            ]);
      }

      // SALVA O REGISTRO NO BANCO DE DADOS
      public function postEditarContasPagar ($request, $response, $args)
      {
            $item = Movimentos::find($args['id']);

            $data_prevista = explode('/', $request->getParam('data_prevista'));
            $data_prevista = $data_prevista[2].'-'.$data_prevista[1].'-'.$data_prevista[0];

            if($request->getParam('status') == 'on'){
                  $status = 1;
                  $data_efetuada = $data_prevista;
                  $valor_efetuado = $request->getParam('valor_efetuado');

                  $conta_bancaria = ContasBancarias::find($request->getParam('conta_bancaria'));
                  if($item['valor_efetuado'] > $valor_efetuado){
                        $novo_saldo = $item['valor_efetuado'] - $valor_efetuado;
                        $saldo_atual = $conta_bancaria['saldo_atual'] + $novo_saldo;
                  }

                  if($item['valor_efetuado'] < $valor_efetuado){
                        $novo_saldo = $valor_efetuado - $item['valor_efetuado'];
                        $saldo_atual = $conta_bancaria['saldo_atual'] - $novo_saldo;
                  }

                  $conta_bancaria->update([
                        'saldo_atual' => $saldo_atual,
                  ]);
            } else {
                  $status = 0;
                  $data_efetuada = '0000-00-00';
                  $valor_efetuado = '0.00';

                  $conta_bancaria = ContasBancarias::find($request->getParam('conta_bancaria'));
                  $saldo_atual = $conta_bancaria['saldo_atual'] + $item['valor_efetuado'];

                  $conta_bancaria->update([
                        'saldo_atual' => $saldo_atual,
                  ]);
            }

            if($request->getParam('forma_pagamento') == 'transferencia'){
                  $dados_pagamento = serialize($request->getParam('dados_transferencia'));
            }

            if($request->getParam('forma_pagamento') == 'cheque'){
                  $dados_pagamento = serialize($request->getParam('dados_cheque'));
            }

            if($request->getParam('forma_pagamento') == ''){
                  $dados_pagamento = '';
            }

            $item->update([
                  'num_nf' => $request->getParam('num_nf'),
                  'descricao' => $request->getParam('descricao'),
                  'categoria' => $request->getParam('categoria'),
                  'data_prevista' => $data_prevista,
                  'data_efetuada' => $data_efetuada,
                  'valor_previsto' => $request->getParam('valor_previsto'),
                  'valor_efetuado' => $valor_efetuado,
                  'conta_bancaria' => $request->getParam('conta_bancaria'),
                  'forma_pagamento' => $request->getParam('forma_pagamento'),
                  'dados_pagamento' => $dados_pagamento,
                  'acolhimento' => '',
                  'fornecedor' => $request->getParam('fornecedor'),
                  'tipo' => 0,
                  'status' => $status,
                  'observacoes' => $request->getParam('observacoes'),
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

      // REMOVER
      public function getRemoverContasPagar ($request, $response, $args)
      {
            if($this->acesso_usuario['financeiro']['contas_a_pagar']['d'] != 'on'){
                  return $this->container->view->render($response->withStatus(403), 'error/403.html');
            }

            $item = Movimentos::find($args['id']);

            if($item->delete())
            {
                  if($item['status'] == 1){
                        $conta_bancaria = ContasBancarias::find($item['conta_bancaria']);
                        $saldo_atual = $conta_bancaria['saldo_atual'] + $item['valor_efetuado'];

                        $conta_bancaria->update([
                              'saldo_atual' => $saldo_atual,
                        ]);
                  }
                  return true;
            }
            else
            {
                  return false;
            }
      }

      // REMOVER SELECIONADAS
      public function getRemoverContasPagarSelecionadas ($request, $response, $args)
      {
            if($this->acesso_usuario['financeiro']['contas_a_pagar']['d'] != 'on'){
                  return $this->container->view->render($response->withStatus(403), 'error/403.html');
            }

            $ids = explode(',', $request->getParam('id'));
            $select = Movimentos::whereIn('id', $ids)->get()->toArray();

            for($i = 0; $i < count($select); $i++){

                  if($select[$i]['status'] == 1){
                        $conta_bancaria = ContasBancarias::find($select[$i]['conta_bancaria']);
                        $saldo_atual = $conta_bancaria['saldo_atual'] + $select[$i]['valor_efetuado'];

                        $conta_bancaria->update([
                              'saldo_atual' => $saldo_atual,
                        ]);
                  }

            }

            $item = Movimentos::whereIn('id', $ids)->delete();

            if($item){
                  return true;
            } else {
                  return false;
            }
      }

      // CONFIRMA O PAGAMENTO DA CONTA A PAGAR
      public function postPagamentoContasPagar ($request, $response, $args)
      {
            $movimento = Movimentos::find($args['id']);
            
            $data_movimento = explode('-', $movimento['data_prevista']);
            $mes = $data_movimento[1];
            $ano = $data_movimento[0];

            // $data_efetuada = explode('/', $request->getParam('data_efetuada'));
            // $data_efetuada = $data_efetuada[2].'-'.$data_efetuada[1].'-'.$data_efetuada[0];
            $data_efetuada = $request->getParam('data_efetuada');
            $movimento->update([
                  'num_nf' => '', 
                  'descricao' => $request->getParam('descricao'), 
                  'categoria' => $request->getParam('categoria'),
                  'data_prevista' => $request->getParam('data_prevista'), 
                  'data_efetuada' => $data_efetuada, 
                  'valor_previsto' => $request->getParam('valor_previsto'), 
                  'valor_efetuado' => $request->getParam('valor_efetuado'), 
                  'conta_bancaria' => $request->getParam('conta_bancaria'), 
                  'fornecedor' => $request->getParam('fornecedor'),
                  'observacoes' => $request->getParam('observacoes'), 
                  'tipo' => 0, 
                  'forma_pagamento' => $request->getParam('forma_pagamento'),
                  'status' => 1
            ]);

            if($movimento)
            {
                  // $conta_bancaria = ContasBancarias::find($request->getParam('conta_bancaria'));
                  // $saldo_atual = $conta_bancaria['saldo_atual'] - $request->getParam('valor_efetuado');
                  // $conta_bancaria->update([
                  //       'saldo_atual' => $saldo_atual,
                  // ]);

                  $this->flash->addMessage('success', 'Pagamento confirmado com sucesso!');
            }
            else
            {
                  $this->flash->addMessage('error', 'Erro ao confirmar pagamento!');
            }

            return $response->withRedirect($this->router->pathFor('contas_a_pagar_mes_ano', ['mes' => $mes, 'ano' => $ano]));
      }
##########
    ###### END
##########

##########
    ###### CONTAS A RECEBER
##########
      // EXIBE A LISTAGEM DAS CONTAS A RECEBER
      public function listarContasReceber ($request, $response, $args)
      {
            if($this->acesso_usuario['financeiro']['contas_a_receber']['r'] != 'on'){
                  return $this->container->view->render($response->withStatus(403), 'error/403.html');
            }

            // listagem das categorias
            $categorias = Categorias::where('tipo', 1)->orderBy('nome', 'ASC')->get()->toArray();
            
            // listagem das contas bancarias
            $contas_bancarias = ContasBancarias::orderBy('nome', 'ASC')->get()->toArray();

            /* se receber ANO nem MÊS */
            if($args['mes'] != '' && $args['mes'] != 'todos' && $args['ano'] != ''){
                  $data1 = $args['ano'].'-'.$args['mes'].'-'.'01';
                  $data2 = date('Y-m-t', strtotime($data1));

                  $item = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [$data1, $data2])->get()->toArray();
                  $total_receber = Movimentos::where('tipo', 1)->where('status', 0)->whereBetween('data_prevista', [$data1, $data2])->sum('valor_previsto');
                  $total_recebido = Movimentos::where('tipo', 1)->where('status', 1)->whereBetween('data_prevista', [$data1, $data2])->sum('valor_efetuado');
                  $total_pendente = Movimentos::where('tipo', 1)->where('status', 1)->whereBetween('data_prevista', [$data1, $data2])->sum('valor_previsto');
                  $total_pendente = $total_pendente - $total_recebido;
            } else if($args['mes'] == 'todos') {
                  $data1 = $args['ano'].'-'.'01-01';
                  $data2 = $args['ano'].'-'.'12-31';

                  $item = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [$data1, $data2])->get()->toArray();
                  $total_receber = Movimentos::where('tipo', 1)->where('status', 0)->whereBetween('data_prevista', [$data1, $data2])->sum('valor_previsto');
                  $total_recebido = Movimentos::where('tipo', 1)->where('status', 1)->whereBetween('data_prevista', [$data1, $data2])->sum('valor_efetuado');
                  $total_pendente = Movimentos::where('tipo', 1)->where('status', 1)->whereBetween('data_prevista', [$data1, $data2])->sum('valor_previsto');
                  $total_pendente = $total_pendente - $total_recebido;
            } else {
                  $item = Movimentos::where('tipo', 1)->get()->toArray();
                  $total_receber = Movimentos::where('tipo', 1)->where('status', 0)->sum('valor_previsto');
                  $total_recebido = Movimentos::where('tipo', 1)->where('status', 1)->sum('valor_efetuado');
                  $total_pendente = Movimentos::where('tipo', 1)->where('status', 1)->sum('valor_previsto');
                  $total_pendente = $total_pendente - $total_recebido;
            }

            for($i = 0; $i < count($item); $i++){
                  $categoria[$item[$i]['id']] = Categorias::find($item[$i]['categoria']);
                  $conta_bancaria[$item[$i]['id']] = ContasBancarias::find($item[$i]['conta_bancaria']);
                  $this->view->offsetSet("categoria", $categoria);
                  $this->view->offsetSet("conta_bancaria", $conta_bancaria);
            }

            $mensagem = $this->flash->getMessages();

            return $this->view->render($response, 'financeiro/contas_a_receber/listar.html', [
                  'Titulo_Pagina_Mae' => 'Financeiro -',
                  'Titulo_Pagina' => 'Contas a Receber',
                  'titulo'    => 'Listagem das contas a receber',
                  'subtitulo' => 'Todas as contas a receber do mês de',
                  'view'      => 'listar',
                  'flash'     => $mensagem,
                  'itens'     => $item,
                  'categorias' => $categorias,
                  'contas_bancarias' => $contas_bancarias,
                  'total_receber' => $total_receber,
                  'total_recebido' => $total_recebido,
                  'total_pendente' => $total_pendente,
                  'mes' => $args['mes'],
                  'ano' => $args['ano'],
                  'data1' => $data1,
                  'data2' => $data2,
            ]);
      }

      // Exibe formulário de cadastro
      public function getCadastrarContasReceber ($request, $response)
      {
            if($this->acesso_usuario['financeiro']['contas_a_receber']['c'] != 'on'){
                  return $this->container->view->render($response->withStatus(403), 'error/403.html');
            }

            $categorias = Categorias::where('tipo', 1)->get()->toArray();
            $contas_bancarias = ContasBancarias::get()->toArray();
            $fornecedores = Fornecedores::get()->toArray();

            return $this->view->render($response, 'financeiro/contas_a_receber/form.html', [
                  'Titulo_Pagina' => 'Novo Registro',
                  'titulo'    => 'Cadastrar nova conta a receber',
                  'subtitulo' => 'Preencha o formulário abaixo para inserir uma nova conta a receber no financeiro',
                  'view'      => 'cadastrar',
                  'categorias' => $categorias,
                  'contas_bancarias' => $contas_bancarias,
                  'fornecedores' => $fornecedores,
            ]);
      }

      // SALVA O REGISTRO NO BANCO DE DADOS
      public function postCadastrarContasReceber ($request, $response, $args)
      {
            $data_prevista = explode('/', $request->getParam('data_prevista'));
            $data_prevista = $data_prevista[2].'-'.$data_prevista[1].'-'.$data_prevista[0];

            if($request->getParam('status') == 'on'){
                  $status = 1;
                  $data_efetuada = $data_prevista;
                  $valor_efetuado = $request->getParam('valor_efetuado');

                  $conta_bancaria = ContasBancarias::find($request->getParam('conta_bancaria'));
                  $saldo_atual = $conta_bancaria['saldo_atual'] + $valor_efetuado;
                  $conta_bancaria->update([
                        'saldo_atual' => $saldo_atual,
                  ]);
            } else {
                  $status = 0;
                  $data_efetuada = '0000-00-00';
                  $valor_efetuado = '0.00';
            }

            if($request->getParam('forma_pagamento') == 'transferencia'){
                  $dados_pagamento = serialize($request->getParam('dados_transferencia'));
            }

            if($request->getParam('forma_pagamento') == 'cheque'){
                  $dados_pagamento = serialize($request->getParam('dados_cheque'));
            }

            if($request->getParam('forma_pagamento') == ''){
                  $dados_pagamento = '';
            }

            // SE O MOVIMENTO FOR RECORRENTE
            if($request->getParam('recorrente') == 'on'){
                  $recorrente = 1;
                  $ciclos = $request->getParam('ciclos');
                  $repetir_a_cada = $request->getParam('repetir_a_cada');
                  $tipo_repeticao = $request->getParam('tipo_repeticao');
            } else {
                  $recorrente = 0;
                  $ciclos = 0;
                  $repetir_a_cada = 0;
                  $tipo_repeticao = '';
            }

            $item = Movimentos::create([
                  'num_nf' => $request->getParam('num_nf'),
                  'descricao' => $request->getParam('descricao'),
                  'categoria' => $request->getParam('categoria'),
                  'data_prevista' => $data_prevista,
                  'data_efetuada' => $data_efetuada,
                  'valor_previsto' => $request->getParam('valor_previsto'),
                  'valor_efetuado' => $valor_efetuado,
                  'conta_bancaria' => $request->getParam('conta_bancaria'),
                  'forma_pagamento' => $request->getParam('forma_pagamento'),
                  'dados_pagamento' => $dados_pagamento,
                  'acolhimento' => '',
                  'fornecedor' => $request->getParam('fornecedor'),
                  'tipo' => 1,
                  'recorrente' => $recorrente,
                  'ciclos' => $ciclos,
                  'repetir_a_cada' => $repetir_a_cada,
                  'tipo_repeticao' => $tipo_repeticao,
                  'status' => $status,
                  'observacoes' => $request->getParam('observacoes'),
            ]);

            if($item)
            {
                  // SE O MOVIMENTO FOR RECORRENTE
                  if($recorrente == 1){
                        // CADASTRA AS REPETIÇÕES PARA O MOVIMENTO
                        for ($i=0; $i < $ciclos; $i++) {
                              
                              if($status == 1){
                                    $status_recorrencia = 1;
                              } else {
                                    $status_recorrencia = 0;
                              }

                              $data_vencimento = date($data_prevista, strtotime('+'.$repetir_a_cada.' '.$tipo_repeticao));

                              $parcela = $i + 1;

                              $recorrencia = Recorrentes::create([
                                    'movimento' => $item->id, 
                                    'parcela' => $parcela, 
                                    'valor' => $request->getParam('valor_previsto'), 
                                    'data_vencimento' => $data_vencimento, 
                                    'data_pagamento' => '', 
                                    'status' => $status_recorrencia
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

      // Exibe formulário de edição
      public function getEditarContasReceber ($request, $response, $args)
      {
            if($this->acesso_usuario['financeiro']['contas_a_receber']['u'] != 'on'){
                  return $this->container->view->render($response->withStatus(403), 'error/403.html');
            }

            $item = Movimentos::find($args['id']);
            $categorias = Categorias::where('tipo', 1)->get()->toArray();
            $contas_bancarias = ContasBancarias::get()->toArray();
            $fornecedores = Fornecedores::get()->toArray();

            if($item['dados_pagamento'] != ''){
                  $dados_pagamento = unserialize($item['dados_pagamento']);
            }
            
            return $this->view->render($response, 'financeiro/contas_a_receber/form.html', [
                  'Titulo_Pagina' => 'Editar Registro',
                  'titulo'    => 'Editar conta a receber',
                  'subtitulo' => 'Preencha o formulário abaixo para editar essa conta a receber no financeiro',
                  'view'      => 'cadastrar',
                  'item' => $item,
                  'dados_pagamento' => $dados_pagamento,
                  'categorias' => $categorias,
                  'contas_bancarias' => $contas_bancarias,
                  'fornecedores' => $fornecedores,
            ]);
      }

      // SALVA O REGISTRO NO BANCO DE DADOS
      public function postEditarContasReceber ($request, $response, $args)
      {
            $item = Movimentos::find($args['id']);

            $data_prevista = explode('/', $request->getParam('data_prevista'));
            $data_prevista = $data_prevista[2].'-'.$data_prevista[1].'-'.$data_prevista[0];

            if($request->getParam('status') == 'on'){
                  $status = 1;
                  $data_efetuada = $data_prevista;
                  $valor_efetuado = $request->getParam('valor_efetuado');

                  $conta_bancaria = ContasBancarias::find($request->getParam('conta_bancaria'));
                  if($item['valor_efetuado'] > $valor_efetuado){
                        $novo_saldo = $item['valor_efetuado'] - $valor_efetuado;
                        $saldo_atual = $conta_bancaria['saldo_atual'] + $novo_saldo;
                  }

                  if($item['valor_efetuado'] < $valor_efetuado){
                        $novo_saldo = $valor_efetuado - $item['valor_efetuado'];
                        $saldo_atual = $conta_bancaria['saldo_atual'] + $novo_saldo;
                  }

                  $conta_bancaria->update([
                        'saldo_atual' => $saldo_atual,
                  ]);
            } else {
                  $status = 0;
                  $data_efetuada = '0000-00-00';
                  $valor_efetuado = '0.00';

                  $conta_bancaria = ContasBancarias::find($request->getParam('conta_bancaria'));
                  $saldo_atual = $conta_bancaria['saldo_atual'] - $item['valor_efetuado'];

                  $conta_bancaria->update([
                        'saldo_atual' => $saldo_atual,
                  ]);
            }

            if($request->getParam('forma_pagamento') == 'transferencia'){
                  $dados_pagamento = serialize($request->getParam('dados_transferencia'));
            }

            if($request->getParam('forma_pagamento') == 'cheque'){
                  $dados_pagamento = serialize($request->getParam('dados_cheque'));
            }

            if($request->getParam('forma_pagamento') == ''){
                  $dados_pagamento = '';
            }

            $item->update([
                  'num_nf' => $request->getParam('num_nf'),
                  'descricao' => $request->getParam('descricao'),
                  'categoria' => $request->getParam('categoria'),
                  'data_prevista' => $data_prevista,
                  'data_efetuada' => $data_efetuada,
                  'valor_previsto' => $request->getParam('valor_previsto'),
                  'valor_efetuado' => $valor_efetuado,
                  'conta_bancaria' => $request->getParam('conta_bancaria'),
                  'forma_pagamento' => $request->getParam('forma_pagamento'),
                  'dados_pagamento' => $dados_pagamento,
                  'acolhimento' => '',
                  'fornecedor' => $request->getParam('fornecedor'),
                  'tipo' => 1,
                  'status' => $status,
                  'observacoes' => $request->getParam('observacoes'),
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

      // REMOVER
      public function getRemoverContasReceber ($request, $response, $args)
      {
            if($this->acesso_usuario['financeiro']['contas_a_receber']['d'] != 'on'){
                  return $this->container->view->render($response->withStatus(403), 'error/403.html');
            }

            $item = Movimentos::find($args['id']);

            if($item->delete())
            {
                  if($item['status'] == 1){
                        $conta_bancaria = ContasBancarias::find($item['conta_bancaria']);
                        $saldo_atual = $conta_bancaria['saldo_atual'] - $item['valor_efetuado'];

                        $conta_bancaria->update([
                              'saldo_atual' => $saldo_atual,
                        ]);
                  }
                  return true;
            }
            else
            {
                  return false;
            }
      }

      // REMOVER SELECIONADAS
      public function getRemoverContasReceberSelecionadas ($request, $response, $args)
      {
            if($this->acesso_usuario['financeiro']['contas_a_receber']['d'] != 'on'){
                  return $this->container->view->render($response->withStatus(403), 'error/403.html');
            }

            $ids = explode(',', $request->getParam('id'));
            $select = Movimentos::whereIn('id', $ids)->get()->toArray();

            for($i = 0; $i < count($select); $i++){

                  if($select[$i]['status'] == 1){
                        $conta_bancaria = ContasBancarias::find($select[$i]['conta_bancaria']);
                        $saldo_atual = $conta_bancaria['saldo_atual'] - $select[$i]['valor_efetuado'];

                        $conta_bancaria->update([
                              'saldo_atual' => $saldo_atual,
                        ]);
                  }

            }
            
            $item = Movimentos::whereIn('id', $ids)->delete();

            if($item){
                  return true;
            } else {
                  return false;
            }
      }

      // CONFIRMA O RECEBIMENTO DA CONTA A RECEBER
      public function postRecebimentoContasReceber ($request, $response, $args)
      {
            $movimento = Movimentos::find($args['id']);

            // $data_efetuada = explode('/', $request->getParam('data_efetuada'));
            // $data_efetuada = $data_efetuada[2].'-'.$data_efetuada[1].'-'.$data_efetuada[0];
            $data_efetuada = $request->getParam('data_efetuada');
            
            $movimento->update([
                  'num_nf' => '', 
                  'descricao' => $request->getParam('descricao'), 
                  'categoria' => $request->getParam('categoria'),
                  'data_prevista' => $request->getParam('data_prevista'), 
                  'data_efetuada' => $data_efetuada, 
                  'valor_previsto' => $request->getParam('valor_previsto'), 
                  'valor_efetuado' => $request->getParam('valor_efetuado'), 
                  'conta_bancaria' => $request->getParam('conta_bancaria'), 
                  'fornecedor' => $request->getParam('fornecedor'),
                  'observacoes' => $request->getParam('observacoes'), 
                  'tipo' => 1, 
                  'forma_pagamento' => $request->getParam('forma_pagamento'),
                  'status' => 1
            ]);

            if($movimento)
            {
                  // $conta_bancaria = ContasBancarias::find($request->getParam('conta_bancaria'));
                  // $saldo_atual = $conta_bancaria['saldo_atual'] - $request->getParam('valor_efetuado');
                  // $conta_bancaria->update([
                  //       'saldo_atual' => $saldo_atual,
                  // ]);

                  $this->flash->addMessage('success', 'Recebimento confirmado com sucesso!');
            }
            else
            {
                  $this->flash->addMessage('error', 'Erro ao receber pagamento!');
            }

            return $response->withRedirect($this->router->pathFor('contas_a_pagar_mes_ano', ['mes' => $args['mes'], 'ano' => $args['ano']]));
      }
##########
    ###### END
##########

##########
    ###### FLUXO DE CAIXA
##########
    // EXIBE A LISTAGEM
    public function fluxoDeCaixa ($request, $response, $args)
    {
      if($this->acesso_usuario['financeiro']['fluxo_de_caixa']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
      }

      if($request->getParam('ano') != ''){
            $data_1 = $request->getParam('ano') . '-' . date('m-01');
            $data_2 = $request->getParam('ano') . '-' . date('m-t');

            // Pega as mensalidades de Janeiro
            $data_1_jan = $request->getParam('ano') . '-' . date('01-01');
            $data_2_jan = $request->getParam('ano') . '-' . date('01-t');
            $e_sum_previsto_jan = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [$data_1_jan, $data_2_jan])->sum('valor_previsto');
            $e_sum_recebidas_jan = Movimentos::where('tipo', 1)->where('status', 1)->whereBetween('data_prevista', [$data_1_jan, $data_2_jan])->sum('valor_efetuado');
            $e_sum_a_receber_jan = Movimentos::where('tipo', 1)->where('status', 0)->whereBetween('data_prevista', [$data_1_jan, $data_2_jan])->sum('valor_previsto');

            // Pega as mensalidades de Fevereiro
            $data_1_fev = $request->getParam('ano') . '-' . date('02-01');
            $data_2_fev = $request->getParam('ano') . '-' . date('02-t');
            $sum_previsto_fev = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [$data_1_fev, $data_2_fev])->sum('valor_previsto');
            $sum_recebidas_fev = Movimentos::where('tipo', 1)->where('status', 1)->whereBetween('data_prevista', [$data_1_fev, $data_2_fev])->sum('valor_efetuado');
            $sum_a_receber_fev = Movimentos::where('tipo', 1)->where('status', 0)->whereBetween('data_prevista', [$data_1_fev, $data_2_fev])->sum('valor_previsto');

            // Pega as mensalidades de Março
            $data_1_mar = $request->getParam('ano') . '-' . date('03-01');
            $data_2_mar = $request->getParam('ano') . '-' . date('03-t');
            $sum_previsto_mar = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [$data_1_mar, $data_2_mar])->sum('valor_previsto');
            $sum_recebidas_mar = Movimentos::where('tipo', 1)->where('status', 1)->whereBetween('data_prevista', [$data_1_mar, $data_2_mar])->sum('valor_efetuado');
            $sum_a_receber_mar = Movimentos::where('tipo', 1)->where('status', 0)->whereBetween('data_prevista', [$data_1_mar, $data_2_mar])->sum('valor_previsto');

            // Pega as mensalidades de Abril
            $data_1_abr = $request->getParam('ano') . '-' . date('04-01');
            $data_2_abr = $request->getParam('ano') . '-' . date('04-t');
            $sum_previsto_abr = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [$data_1_abr, $data_2_abr])->sum('valor_previsto');
            $sum_recebidas_abr = Movimentos::where('tipo', 1)->where('status', 1)->whereBetween('data_prevista', [$data_1_abr, $data_2_abr])->sum('valor_efetuado');
            $sum_a_receber_abr = Movimentos::where('tipo', 1)->where('status', 0)->whereBetween('data_prevista', [$data_1_abr, $data_2_abr])->sum('valor_previsto');

            // Pega as mensalidades de Maio
            $data_1_mai = $request->getParam('ano') . '-' . date('05-01');
            $data_2_mai = $request->getParam('ano') . '-' . date('05-t');
            $sum_previsto_mai = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [$data_1_mai, $data_2_mai])->sum('valor_previsto');
            $sum_recebidas_mai = Movimentos::where('tipo', 1)->where('status', 1)->whereBetween('data_prevista', [$data_1_mai, $data_2_mai])->sum('valor_efetuado');
            $sum_a_receber_mai = Movimentos::where('tipo', 1)->where('status', 0)->whereBetween('data_prevista', [$data_1_mai, $data_2_mai])->sum('valor_previsto');

            // Pega as mensalidades de Junho
            $data_1_jun = $request->getParam('ano') . '-' . date('06-01');
            $data_2_jun = $request->getParam('ano') . '-' . date('06-t');
            $sum_previsto_jun = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [$data_1_jun, $data_2_jun])->sum('valor_previsto');
            $sum_recebidas_jun = Movimentos::where('tipo', 1)->where('status', 1)->whereBetween('data_prevista', [$data_1_jun, $data_2_jun])->sum('valor_efetuado');
            $sum_a_receber_jun = Movimentos::where('tipo', 1)->where('status', 0)->whereBetween('data_prevista', [$data_1_jun, $data_2_jun])->sum('valor_previsto');

            // Pega as mensalidades de Julho
            $data_1_jul = $request->getParam('ano') . '-' . date('07-01');
            $data_2_jul = $request->getParam('ano') . '-' . date('07-t');
            $sum_previsto_jul = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [$data_1_jul, $data_2_jul])->sum('valor_previsto');
            $sum_recebidas_jul = Movimentos::where('tipo', 1)->where('status', 1)->whereBetween('data_prevista', [$data_1_jul, $data_2_jul])->sum('valor_efetuado');
            $sum_a_receber_jul = Movimentos::where('tipo', 1)->where('status', 0)->whereBetween('data_prevista', [$data_1_jul, $data_2_jul])->sum('valor_previsto');

            // Pega as mensalidades de Agosto
            $data_1_ago = $request->getParam('ano') . '-' . date('08-01');
            $data_2_ago = $request->getParam('ano') . '-' . date('08-t');
            $sum_previsto_ago = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [$data_1_ago, $data_2_ago])->sum('valor_previsto');
            $sum_recebidas_ago = Movimentos::where('tipo', 1)->where('status', 1)->whereBetween('data_prevista', [$data_1_ago, $data_2_ago])->sum('valor_efetuado');
            $sum_a_receber_ago = Movimentos::where('tipo', 1)->where('status', 0)->whereBetween('data_prevista', [$data_1_ago, $data_2_ago])->sum('valor_previsto');

            // Pega as mensalidades de Setembro
            $data_1_set = $request->getParam('ano') . '-' . date('09-01');
            $data_2_set = $request->getParam('ano') . '-' . date('09-t');
            $sum_previsto_set = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [$data_1_set, $data_2_set])->sum('valor_previsto');
            $sum_recebidas_set = Movimentos::where('tipo', 1)->where('status', 1)->whereBetween('data_prevista', [$data_1_set, $data_2_set])->sum('valor_efetuado');
            $sum_a_receber_set = Movimentos::where('tipo', 1)->where('status', 0)->whereBetween('data_prevista', [$data_1_set, $data_2_set])->sum('valor_previsto');

            // Pega as mensalidades de Outubro
            $data_1_out = $request->getParam('ano') . '-' . date('10-01');
            $data_2_out = $request->getParam('ano') . '-' . date('10-t');
            $sum_previsto_out = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [$data_1_out, $data_2_out])->sum('valor_previsto');
            $sum_recebidas_out = Movimentos::where('tipo', 1)->where('status', 1)->whereBetween('data_prevista', [$data_1_out, $data_2_out])->sum('valor_efetuado');
            $sum_a_receber_out = Movimentos::where('tipo', 1)->where('status', 0)->whereBetween('data_prevista', [$data_1_out, $data_2_out])->sum('valor_previsto');

            // Pega as mensalidades de Novembro
            $data_1_nov = $request->getParam('ano') . '-' . date('11-01');
            $data_2_nov = $request->getParam('ano') . '-' . date('11-t');
            $sum_previsto_nov = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [$data_1_nov, $data_2_nov])->sum('valor_previsto');
            $sum_recebidas_nov = Movimentos::where('tipo', 1)->where('status', 1)->whereBetween('data_prevista', [$data_1_nov, $data_2_nov])->sum('valor_efetuado');
            $sum_a_receber_nov = Movimentos::where('tipo', 1)->where('status', 0)->whereBetween('data_prevista', [$data_1_nov, $data_2_nov])->sum('valor_previsto');

            // Pega as mensalidades de Dezembro
            $data_1_dez = $request->getParam('ano') . '-' . date('12-01');
            $data_2_dez = $request->getParam('ano') . '-' . date('12-t');
            $sum_previsto_dez = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [$data_1_dez, $data_2_dez])->sum('valor_previsto');
            $sum_recebidas_dez = Movimentos::where('tipo', 1)->where('status', 1)->whereBetween('data_prevista', [$data_1_dez, $data_2_dez])->sum('valor_efetuado');
            $sum_a_receber_dez = Movimentos::where('tipo', 1)->where('status', 0)->whereBetween('data_prevista', [$data_1_dez, $data_2_dez])->sum('valor_previsto');

            // Pega as mensalidades do Ano
            $data_11 = $request->getParam('ano') . '-' . date('01-01');
            $data_22 = $request->getParam('ano') . '-' . date('12-t');
            $sum_previsto_ano = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [$data_11, $data_22])->sum('valor_previsto');
            $sum_recebidas_ano = Movimentos::where('tipo', 1)->where('status', 1)->whereBetween('data_prevista', [$data_11, $data_22])->sum('valor_efetuado');
            $sum_a_receber_ano = Movimentos::where('tipo', 1)->where('status', 0)->whereBetween('data_prevista', [$data_11, $data_22])->sum('valor_previsto');
      } else {
            // Pega os movimentos de Janeiro
            $sum_entradas_jan = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [date('Y-01-01'), date('Y-01-t')])->sum('valor_efetuado');
            $sum_saidas_jan = Movimentos::where('tipo', 0)->whereBetween('data_prevista', [date('Y-01-01'), date('Y-01-t')])->sum('valor_previsto');

            // Pega os movimentos de Fevereiro
            $sum_entradas_fev = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [date('Y-02-01'), date('Y-02-t')])->sum('valor_efetuado');
            $sum_saidas_fev = Movimentos::where('tipo', 0)->whereBetween('data_prevista', [date('Y-02-01'), date('Y-02-t')])->sum('valor_previsto');

            // Pega os movimentos de Março
            $sum_entradas_mar = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [date('Y-03-01'), date('Y-03-t')])->sum('valor_efetuado');
            $sum_saidas_mar = Movimentos::where('tipo', 0)->whereBetween('data_prevista', [date('Y-03-01'), date('Y-03-t')])->sum('valor_previsto');

            // Pega os movimentos de Abril
            $sum_entradas_abr = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [date('Y-04-01'), date('Y-04-t')])->sum('valor_efetuado');
            $sum_saidas_abr = Movimentos::where('tipo', 0)->whereBetween('data_prevista', [date('Y-04-01'), date('Y-04-t')])->sum('valor_previsto');

            // Pega os movimentos de Maio
            $sum_entradas_mai = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [date('Y-05-01'), date('Y-05-t')])->sum('valor_efetuado');
            $sum_saidas_mai = Movimentos::where('tipo', 0)->whereBetween('data_prevista', [date('Y-05-01'), date('Y-05-t')])->sum('valor_previsto');

            // Pega os movimentos de Junho
            $sum_entradas_jun = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [date('Y-06-01'), date('Y-06-t')])->sum('valor_efetuado');
            $sum_saidas_jun = Movimentos::where('tipo', 0)->whereBetween('data_prevista', [date('Y-06-01'), date('Y-06-t')])->sum('valor_previsto');

            // Pega os movimentos de Julho
            $sum_entradas_jul = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [date('Y-07-01'), date('Y-07-t')])->sum('valor_efetuado');
            $sum_saidas_jul = Movimentos::where('tipo', 0)->whereBetween('data_prevista', [date('Y-07-01'), date('Y-07-t')])->sum('valor_previsto');

            // Pega os movimentos de Agosto
            $sum_entradas_ago = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [date('Y-08-01'), date('Y-08-t')])->sum('valor_efetuado');
            $sum_saidas_ago = Movimentos::where('tipo', 0)->whereBetween('data_prevista', [date('Y-08-01'), date('Y-08-t')])->sum('valor_previsto');

            // Pega os movimentos de Setembro
            $sum_entradas_set = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [date('Y-09-01'), date('Y-09-t')])->sum('valor_efetuado');
            $sum_saidas_set = Movimentos::where('tipo', 0)->whereBetween('data_prevista', [date('Y-09-01'), date('Y-09-t')])->sum('valor_previsto');

            // Pega os movimentos de Outubro
            $sum_entradas_out = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [date('Y-10-01'), date('Y-10-t')])->sum('valor_efetuado');
            $sum_saidas_out = Movimentos::where('tipo', 0)->whereBetween('data_prevista', [date('Y-10-01'), date('Y-10-t')])->sum('valor_previsto');

            // Pega os movimentos de Novembro
            $sum_entradas_nov = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [date('Y-11-01'), date('Y-11-t')])->sum('valor_efetuado');
            $sum_saidas_nov = Movimentos::where('tipo', 0)->whereBetween('data_prevista', [date('Y-11-01'), date('Y-11-t')])->sum('valor_previsto');

            // Pega os movimentos de Dezembro
            $sum_entradas_dez = Movimentos::where('tipo', 1)->whereBetween('data_prevista', [date('Y-12-01'), date('Y-12-t')])->sum('valor_efetuado');
            $sum_saidas_dez = Movimentos::where('tipo', 0)->whereBetween('data_prevista', [date('Y-12-01'), date('Y-12-t')])->sum('valor_previsto');

            // Pega os movimentos do Ano
            $sum_entradas_ano_1 = Movimentos::where('status', 1)->where('tipo', 1)->whereBetween('data_prevista', [date('Y-01-01'), date('Y-12-t')])->sum('valor_efetuado');
            $sum_saidas_ano_1 = Movimentos::where('status', 1)->where('tipo', 0)->whereBetween('data_prevista', [date('Y-01-01'), date('Y-12-t')])->sum('valor_previsto');
            
            $sum_entradas_ano_0 = Movimentos::where('status', 0)->where('tipo', 1)->whereBetween('data_prevista', [date('Y-01-01'), date('Y-12-t')])->sum('valor_efetuado');
            $sum_saidas_ano_0 = Movimentos::where('status', 0)->where('tipo', 0)->whereBetween('data_prevista', [date('Y-01-01'), date('Y-12-t')])->sum('valor_previsto');
      }

      /* se receber ANO nem MÊS */
      if($args['mes'] != '' && $args['mes'] != 'todos' && $args['ano'] != ''){
            $data1 = $args['ano'].'-'.$args['mes'].'-'.'01';
            $data2 = date('Y-m-t', strtotime($data1));

            $data_final_mes_anterior = date('Y-m-t', strtotime('-1 month', strtotime($data1)));
            $data_final_mes_anterior_2 = date('Y-m-t', strtotime('-2 month', strtotime($data1)));

            $total_entradas_1 = Movimentos::where('tipo', 1)->where('status', 1)->where('data_prevista', '<=', $data_final_mes_anterior)->sum('valor_efetuado');
            $total_entradas_2 = Movimentos::where('tipo', 1)->where('status', 1)->where('data_prevista', '<=', $data2)->sum('valor_efetuado');

            $total_saidas_1 = Movimentos::where('tipo', 0)->where('status', 1)->where('data_prevista', '<=', $data_final_mes_anterior)->sum('valor_efetuado');
            $total_saidas_2 = Movimentos::where('tipo', 0)->where('status', 1)->where('data_prevista', '<=', $data2)->sum('valor_efetuado');

            $saldo_inicial = $total_entradas_1 - $total_saidas_1;
            $saldo_inicial = str_replace(',', '.', $saldo_inicial);

            $saldo_final = $total_entradas_2 - $total_saidas_2;
            $saldo_final = str_replace(',', '.', $saldo_final);

            // $saldo_inicial = Movimentos::where('tipo', 1)->where('status', 1)->where('data_prevista', '<=', $data_final_mes_anterior)->sum('valor_efetuado');
            // $saldo_final = Movimentos::where('tipo', 1)->where('status', 1)->where('data_prevista', '<=', $data2)->sum('valor_efetuado');

            $total_entradas_mes_anterior_1 = Movimentos::where('tipo', 1)->where('status', 1)->where('data_prevista', '<=', $data_final_mes_anterior_2)->sum('valor_efetuado');
            $total_entradas_mes_anterior_2 = Movimentos::where('tipo', 1)->where('status', 1)->where('data_prevista', '<=', $data_final_mes_anterior)->sum('valor_efetuado');

            $total_saidas_mes_anterior_1 = Movimentos::where('tipo', 0)->where('status', 1)->where('data_prevista', '<=', $data_final_mes_anterior_2)->sum('valor_efetuado');
            $total_saidas_mes_anterior_2 = Movimentos::where('tipo', 0)->where('status', 1)->where('data_prevista', '<=', $data_final_mes_anterior)->sum('valor_efetuado');

            // $saldo_inicial_mes_anterior = Movimentos::where('tipo', 1)->where('status', 1)->where('data_prevista', '<=', $data_final_mes_anterior_2)->sum('valor_efetuado');
            // $saldo_final_mes_anterior = Movimentos::where('tipo', 1)->where('status', 1)->where('data_prevista', '<', $data1)->sum('valor_efetuado');

            $saldo_inicial_mes_anterior = $total_entradas_mes_anterior_1 - $total_saidas_mes_anterior_1;
            $saldo_inicial_mes_anterior = str_replace(',', '.', $saldo_inicial_mes_anterior);

            $saldo_final_mes_anterior = $total_entradas_mes_anterior_2 - $total_saidas_mes_anterior_2;
            $saldo_final_mes_anterior = str_replace(',', '.', $saldo_final_mes_anterior);

            $total_pagar = Movimentos::where('tipo', 0)->where('status', 0)->whereBetween('data_prevista', [$data1, $data2])->sum('valor_previsto');
            $total_pago = Movimentos::where('tipo', 0)->where('status', 1)->whereBetween('data_prevista', [$data1, $data2])->sum('valor_efetuado');

            $total_receber_mensalidades = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [$data1, $data2])->sum('valor');
            $total_recebido_mensalidades = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [$data1, $data2])->sum('valor');

            $total_receber = Movimentos::where('tipo', 1)->where('status', 0)->whereBetween('data_prevista', [$data1, $data2])->sum('valor_previsto');
            $total_recebido = Movimentos::where('tipo', 1)->where('status', 1)->whereBetween('data_prevista', [$data1, $data2])->sum('valor_efetuado');

            $total_receber = $total_receber + $total_receber_mensalidades;
            $total_receber = str_replace(',', '.', $total_receber);

            // despesas por categoria
            $movimentos_despesa = Movimentos::where('tipo', 0)->where('status', 1)->whereBetween('data_prevista', [$data1, $data2])->get()->toArray();

            $arr = [];
            for($i = 0; $i < count($movimentos_despesa); $i++){
                  $categorias_despesa = Categorias::find($movimentos_despesa[$i]['categoria']);
                  $total_despesas = Movimentos::where('tipo', 0)->where('categoria', $categorias_despesa['id'])->where('status', 1)->whereBetween('data_prevista', [$data1, $data2])->sum('valor_efetuado');

                  $data = [
                        'label' => $categorias_despesa['nome'],
                        'data' => $total_despesas,
                        'color' => $color = "#".substr(md5(rand()), 0, 6)
                  ];
                  
                  $arr[] = $data;

                  $arr_json = json_encode($arr);
            }
            // end despesas por categoria
      }

      $contas_bancarias = ContasBancarias::orderBy('id','ASC')->get()->toArray();

      // saldo das contas bancárias
      for($i = 0; $i < count($contas_bancarias); $i++){
            $conta_bancaria = ContasBancarias::find($contas_bancarias[$i]['id']);
            $saldo_atual = number_format($conta_bancaria['saldo_atual'], 2, ".", "");

            $data = [
                  'y' => $conta_bancaria['nome'],
                  'a' => $saldo_atual
            ];
                  
            $arr2[] = $data;

            $saldo_contas_bancarias = json_encode($arr2);
      }
      // end saldo das contas bancárias

      $mensagem = $this->flash->getMessages();

      return $this->view->render($response, 'financeiro/fluxo_de_caixa/listar.html', [
            'Titulo_Pagina_Mae' => 'Financeiro -',
            'Titulo_Pagina' => 'Fluxo de Caixa',
            'titulo'    => 'Listagem das mensalidades - ',
            'subtitulo' => 'Todas as mensalidades do mês de ',
            'view'      => 'listar',
            'flash'     => $mensagem,
            'contas_bancarias' => $contas_bancarias,
            'mes' => $args['mes'],
            'ano' => $args['ano'],
            'total_pagar' => $total_pagar,
            'total_pago' => $total_pago,
            'total_receber' => $total_receber,
            'total_recebido' => $total_recebido,
            'saldo_inicial' => $saldo_inicial,
            'saldo_final' => $saldo_final,
            'saldo_inicial_mes_anterior' => $saldo_inicial_mes_anterior,
            'saldo_final_mes_anterior' => $saldo_final_mes_anterior,
            'data1' => $data1,
            'data2' => $data2,
            'arr_json' => $arr_json,
            'saldo_contas_bancarias' => $saldo_contas_bancarias,

            'sum_entradas_jan' => number_format($sum_entradas_jan, 2, ".", ""),
            'sum_saidas_jan' => number_format($sum_saidas_jan, 2, ".", ""),
            'sum_entradas_fev' => number_format($sum_entradas_fev, 2, ".", ""),
            'sum_saidas_fev' => number_format($sum_saidas_fev, 2, ".", ""),
            'sum_entradas_mar' => number_format($sum_entradas_mar, 2, ".", ""),
            'sum_saidas_mar' => number_format($sum_saidas_mar, 2, ".", ""),
            'sum_entradas_abr' => number_format($sum_entradas_abr, 2, ".", ""),
            'sum_saidas_abr' => number_format($sum_saidas_abr, 2, ".", ""),
            'sum_entradas_mai' => number_format($sum_entradas_mai, 2, ".", ""),
            'sum_saidas_mai' => number_format($sum_saidas_mai, 2, ".", ""),
            'sum_entradas_jun' => number_format($sum_entradas_jun, 2, ".", ""),
            'sum_saidas_jun' => number_format($sum_saidas_jun, 2, ".", ""),
            'sum_entradas_jul' => number_format($sum_entradas_jul, 2, ".", ""),
            'sum_saidas_jul' => number_format($sum_saidas_jul, 2, ".", ""),
            'sum_entradas_ago' => number_format($sum_entradas_ago, 2, ".", ""),
            'sum_saidas_ago' => number_format($sum_saidas_ago, 2, ".", ""),
            'sum_entradas_set' => number_format($sum_entradas_set, 2, ".", ""),
            'sum_saidas_set' => number_format($sum_saidas_set, 2, ".", ""),
            'sum_entradas_out' => number_format($sum_entradas_out, 2, ".", ""),
            'sum_saidas_out' => number_format($sum_saidas_out, 2, ".", ""),
            'sum_entradas_nov' => number_format($sum_entradas_nov, 2, ".", ""),
            'sum_saidas_nov' => number_format($sum_saidas_nov, 2, ".", ""),
            'sum_entradas_dez' => number_format($sum_entradas_dez, 2, ".", ""),
            'sum_saidas_dez' => number_format($sum_saidas_dez, 2, ".", ""),

            'sum_entradas_ano_1' => number_format($sum_entradas_ano_1, 2, ".", ""),
            'sum_saidas_ano_1' => number_format($sum_saidas_ano_1, 2, ".", ""),
            'sum_entradas_ano_0' => number_format($sum_entradas_ano_0, 2, ".", ""),
            'sum_saidas_ano_0' => number_format($sum_saidas_ano_0, 2, ".", ""),
      ]);
    }

    public function getCategoriasDespesasJSON($request, $response, $args){

      /* se receber ANO nem MÊS */
      if($args['mes'] != '' && $args['mes'] != 'todos' && $args['ano'] != ''){
            $data1 = $args['ano'].'-'.$args['mes'].'-'.'01';
            $data2 = date('Y-m-t', strtotime($data1));
            
            $movimentos_despesa = Movimentos::where('tipo', 0)->where('status', 1)->whereBetween('data_prevista', [$data1, $data2])->get()->toArray();

            $arr = [];
            for($i = 0; $i < count($movimentos_despesa); $i++){
                  $categorias_despesa = Categorias::find($movimentos_despesa[$i]['categoria']);
                  $total_despesas = Movimentos::where('tipo', 0)->where('categoria', $categorias_despesa['id'])->where('status', 1)->whereBetween('data_prevista', [$data1, $data2])->sum('valor_efetuado');

                  $arr['label'] = $categorias_despesa['nome'];
                  $arr['data'] = $total_despesas;
                  $arr['color'] = $color = "#".substr(md5(rand()), 0, 6);

                  // echo json_encode($arr, JSON_HEX_QUOT | JSON_HEX_TAG);
                  echo json_encode($arr);
            }
      }

    }
##########
    ###### END
##########

##########
    ###### MENSALIDADES
##########
    // EXIBE A LISTAGEM DAS MENSALIDADES
    public function listarMensalidades ($request, $response, $args)
    {   
      if($this->acesso_usuario['financeiro']['mensalidades']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
      }

      if($request->getParam('ano') != ''){
            if($request->getParam('mes') != ''){
                  $data_1 = $request->getParam('ano') . '-' . $request->getParam('mes') . '-' . '01';
                  $data_2 = $request->getParam('ano') . '-' . $request->getParam('mes') . '-' . date('t');
            } else {
                  $data_1 = $request->getParam('ano') . '-' . date('m-01');
                  $data_2 = $request->getParam('ano') . '-' . date('m-t');
            }

            // Pega as mensalidades de Janeiro
            $data_1_jan = $request->getParam('ano') . '-' . date('01-01');
            $data_2_jan = $request->getParam('ano') . '-' . date('01-t');
            $sum_previsto_jan = Mensalidades::whereBetween('data_vencimento', [$data_1_jan, $data_2_jan])->sum('valor');
            $sum_recebidas_jan = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [$data_1_jan, $data_2_jan])->sum('valor');
            $sum_a_receber_jan = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [$data_1_jan, $data_2_jan])->sum('valor');

            // Pega as mensalidades de Fevereiro
            $data_1_fev = $request->getParam('ano') . '-' . date('02-01');
            $data_2_fev = $request->getParam('ano') . '-' . date('02-t');
            $sum_previsto_fev = Mensalidades::whereBetween('data_vencimento', [$data_1_fev, $data_2_fev])->sum('valor');
            $sum_recebidas_fev = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [$data_1_fev, $data_2_fev])->sum('valor');
            $sum_a_receber_fev = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [$data_1_fev, $data_2_fev])->sum('valor');

            // Pega as mensalidades de Março
            $data_1_mar = $request->getParam('ano') . '-' . date('03-01');
            $data_2_mar = $request->getParam('ano') . '-' . date('03-t');
            $sum_previsto_mar = Mensalidades::whereBetween('data_vencimento', [$data_1_mar, $data_2_mar])->sum('valor');
            $sum_recebidas_mar = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [$data_1_mar, $data_2_mar])->sum('valor');
            $sum_a_receber_mar = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [$data_1_mar, $data_2_mar])->sum('valor');

            // Pega as mensalidades de Abril
            $data_1_abr = $request->getParam('ano') . '-' . date('04-01');
            $data_2_abr = $request->getParam('ano') . '-' . date('04-t');
            $sum_previsto_abr = Mensalidades::whereBetween('data_vencimento', [$data_1_abr, $data_2_abr])->sum('valor');
            $sum_recebidas_abr = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [$data_1_abr, $data_2_abr])->sum('valor');
            $sum_a_receber_abr = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [$data_1_abr, $data_2_abr])->sum('valor');

            // Pega as mensalidades de Maio
            $data_1_mai = $request->getParam('ano') . '-' . date('05-01');
            $data_2_mai = $request->getParam('ano') . '-' . date('05-t');
            $sum_previsto_mai = Mensalidades::whereBetween('data_vencimento', [$data_1_mai, $data_2_mai])->sum('valor');
            $sum_recebidas_mai = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [$data_1_mai, $data_2_mai])->sum('valor');
            $sum_a_receber_mai = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [$data_1_mai, $data_2_mai])->sum('valor');

            // Pega as mensalidades de Junho
            $data_1_jun = $request->getParam('ano') . '-' . date('06-01');
            $data_2_jun = $request->getParam('ano') . '-' . date('06-t');
            $sum_previsto_jun = Mensalidades::whereBetween('data_vencimento', [$data_1_jun, $data_2_jun])->sum('valor');
            $sum_recebidas_jun = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [$data_1_jun, $data_2_jun])->sum('valor');
            $sum_a_receber_jun = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [$data_1_jun, $data_2_jun])->sum('valor');

            // Pega as mensalidades de Julho
            $data_1_jul = $request->getParam('ano') . '-' . date('07-01');
            $data_2_jul = $request->getParam('ano') . '-' . date('07-t');
            $sum_previsto_jul = Mensalidades::whereBetween('data_vencimento', [$data_1_jul, $data_2_jul])->sum('valor');
            $sum_recebidas_jul = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [$data_1_jul, $data_2_jul])->sum('valor');
            $sum_a_receber_jul = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [$data_1_jul, $data_2_jul])->sum('valor');

            // Pega as mensalidades de Agosto
            $data_1_ago = $request->getParam('ano') . '-' . date('08-01');
            $data_2_ago = $request->getParam('ano') . '-' . date('08-t');
            $sum_previsto_ago = Mensalidades::whereBetween('data_vencimento', [$data_1_ago, $data_2_ago])->sum('valor');
            $sum_recebidas_ago = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [$data_1_ago, $data_2_ago])->sum('valor');
            $sum_a_receber_ago = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [$data_1_ago, $data_2_ago])->sum('valor');

            // Pega as mensalidades de Setembro
            $data_1_set = $request->getParam('ano') . '-' . date('09-01');
            $data_2_set = $request->getParam('ano') . '-' . date('09-t');
            $sum_previsto_set = Mensalidades::whereBetween('data_vencimento', [$data_1_set, $data_2_set])->sum('valor');
            $sum_recebidas_set = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [$data_1_set, $data_2_set])->sum('valor');
            $sum_a_receber_set = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [$data_1_set, $data_2_set])->sum('valor');

            // Pega as mensalidades de Outubro
            $data_1_out = $request->getParam('ano') . '-' . date('10-01');
            $data_2_out = $request->getParam('ano') . '-' . date('10-t');
            $sum_previsto_out = Mensalidades::whereBetween('data_vencimento', [$data_1_out, $data_2_out])->sum('valor');
            $sum_recebidas_out = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [$data_1_out, $data_2_out])->sum('valor');
            $sum_a_receber_out = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [$data_1_out, $data_2_out])->sum('valor');

            // Pega as mensalidades de Novembro
            $data_1_nov = $request->getParam('ano') . '-' . date('11-01');
            $data_2_nov = $request->getParam('ano') . '-' . date('11-t');
            $sum_previsto_nov = Mensalidades::whereBetween('data_vencimento', [$data_1_nov, $data_2_nov])->sum('valor');
            $sum_recebidas_nov = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [$data_1_nov, $data_2_nov])->sum('valor');
            $sum_a_receber_nov = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [$data_1_nov, $data_2_nov])->sum('valor');

            // Pega as mensalidades de Dezembro
            $data_1_dez = $request->getParam('ano') . '-' . date('12-01');
            $data_2_dez = $request->getParam('ano') . '-' . date('12-t');
            $sum_previsto_dez = Mensalidades::whereBetween('data_vencimento', [$data_1_dez, $data_2_dez])->sum('valor');
            $sum_recebidas_dez = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [$data_1_dez, $data_2_dez])->sum('valor');
            $sum_a_receber_dez = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [$data_1_dez, $data_2_dez])->sum('valor');

            // Pega as mensalidades do Ano
            $data_11 = $request->getParam('ano') . '-' . date('01-01');
            $data_22 = $request->getParam('ano') . '-' . date('12-t');
            $sum_previsto_ano = Mensalidades::whereBetween('data_vencimento', [$data_11, $data_22])->sum('valor');
            $sum_recebidas_ano = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [$data_11, $data_22])->sum('valor');
            $sum_a_receber_ano = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [$data_11, $data_22])->sum('valor');
      } else {
            // Pega as mensalidades de Janeiro
            $sum_previsto_jan = Mensalidades::whereBetween('data_vencimento', [date('Y-01-01'), date('Y-01-t')])->sum('valor');
            $sum_recebidas_jan = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [date('Y-01-01'), date('Y-01-t')])->sum('valor');
            $sum_a_receber_jan = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [date('Y-01-01'), date('Y-01-t')])->sum('valor');

            // Pega as mensalidades de Fevereiro
            $sum_previsto_fev = Mensalidades::whereBetween('data_vencimento', [date('Y-02-01'), date('Y-02-t')])->sum('valor');
            $sum_recebidas_fev = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [date('Y-02-01'), date('Y-02-t')])->sum('valor');
            $sum_a_receber_fev = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [date('Y-02-01'), date('Y-02-t')])->sum('valor');

            // Pega as mensalidades de Março
            $sum_previsto_mar = Mensalidades::whereBetween('data_vencimento', [date('Y-03-01'), date('Y-03-t')])->sum('valor');
            $sum_recebidas_mar = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [date('Y-03-01'), date('Y-03-t')])->sum('valor');
            $sum_a_receber_mar = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [date('Y-03-01'), date('Y-03-t')])->sum('valor');

            // Pega as mensalidades de Abril
            $sum_previsto_abr = Mensalidades::whereBetween('data_vencimento', [date('Y-04-01'), date('Y-04-t')])->sum('valor');
            $sum_recebidas_abr = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [date('Y-04-01'), date('Y-04-t')])->sum('valor');
            $sum_a_receber_abr = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [date('Y-04-01'), date('Y-04-t')])->sum('valor');

            // Pega as mensalidades de Maio
            $sum_previsto_mai = Mensalidades::whereBetween('data_vencimento', [date('Y-05-01'), date('Y-05-t')])->sum('valor');
            $sum_recebidas_mai = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [date('Y-05-01'), date('Y-05-t')])->sum('valor');
            $sum_a_receber_mai = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [date('Y-05-01'), date('Y-05-t')])->sum('valor');

            // Pega as mensalidades de Junho
            $sum_previsto_jun = Mensalidades::whereBetween('data_vencimento', [date('Y-06-01'), date('Y-06-t')])->sum('valor');
            $sum_recebidas_jun = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [date('Y-06-01'), date('Y-06-t')])->sum('valor');
            $sum_a_receber_jun = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [date('Y-06-01'), date('Y-06-t')])->sum('valor');

            // Pega as mensalidades de Julho
            $sum_previsto_jul = Mensalidades::whereBetween('data_vencimento', [date('Y-07-01'), date('Y-07-t')])->sum('valor');
            $sum_recebidas_jul = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [date('Y-07-01'), date('Y-07-t')])->sum('valor');
            $sum_a_receber_jul = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [date('Y-07-01'), date('Y-07-t')])->sum('valor');

            // Pega as mensalidades de Agosto
            $sum_previsto_ago = Mensalidades::whereBetween('data_vencimento', [date('Y-08-01'), date('Y-08-t')])->sum('valor');
            $sum_recebidas_ago = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [date('Y-08-01'), date('Y-08-t')])->sum('valor');
            $sum_a_receber_ago = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [date('Y-08-01'), date('Y-08-t')])->sum('valor');

            // Pega as mensalidades de Setembro
            $sum_previsto_set = Mensalidades::whereBetween('data_vencimento', [date('Y-09-01'), date('Y-09-t')])->sum('valor');
            $sum_recebidas_set = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [date('Y-09-01'), date('Y-09-t')])->sum('valor');
            $sum_a_receber_set = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [date('Y-09-01'), date('Y-09-t')])->sum('valor');

            // Pega as mensalidades de Outubro
            $sum_previsto_out = Mensalidades::whereBetween('data_vencimento', [date('Y-10-01'), date('Y-10-t')])->sum('valor');
            $sum_recebidas_out = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [date('Y-10-01'), date('Y-10-t')])->sum('valor');
            $sum_a_receber_out = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [date('Y-10-01'), date('Y-10-t')])->sum('valor');

            // Pega as mensalidades de Novembro
            $sum_previsto_nov = Mensalidades::whereBetween('data_vencimento', [date('Y-11-01'), date('Y-11-t')])->sum('valor');
            $sum_recebidas_nov = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [date('Y-11-01'), date('Y-11-t')])->sum('valor');
            $sum_a_receber_nov = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [date('Y-11-01'), date('Y-11-t')])->sum('valor');

            // Pega as mensalidades de Dezembro
            $sum_previsto_dez = Mensalidades::whereBetween('data_vencimento', [date('Y-12-01'), date('Y-12-t')])->sum('valor');
            $sum_recebidas_dez = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [date('Y-12-01'), date('Y-12-t')])->sum('valor');
            $sum_a_receber_dez = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [date('Y-12-01'), date('Y-12-t')])->sum('valor');

            // Pega as mensalidades do Ano
            $sum_previsto_ano = Mensalidades::whereBetween('data_vencimento', [date('Y-01-01'), date('Y-12-t')])->sum('valor');
            $sum_recebidas_ano = Mensalidades::where('status', 1)->whereBetween('data_vencimento', [date('Y-01-01'), date('Y-12-t')])->sum('valor');
            $sum_a_receber_ano = Mensalidades::where('status', 0)->whereBetween('data_vencimento', [date('Y-01-01'), date('Y-12-t')])->sum('valor');
      }

      $contas_bancarias = ContasBancarias::orderBy('id','ASC')->get()->toArray();

      if(isset($args['mes']) && $args['mes'] != ''){
            if($request->getParam('ano') != '') {
                  $data_1 = $request->getParam('ano') . '-' . $args['mes'] . '-' . '01';
                  $data_2 = $request->getParam('ano') . '-' . $args['mes'] . '-' . date('t');
            } else {
                  $data_1 = date('Y') . '-' . $args['mes'] . '-' . '01';
                  $data_2 = date('Y') . '-' . $args['mes'] . '-' . date('t');
            }

            $item = Mensalidades::whereBetween('data_vencimento', [$data_1, $data_2])->where('status', '!=', 99)->get()->toArray();

            switch ($args['mes']) {
                  case 1:
                        $nome_mes = 'Janeiro';
                        break;
                  case 2:
                        $nome_mes = 'Fevereiro';
                        break;
                  case 3:
                        $nome_mes = 'Março';
                        break;
                  case 4:
                        $nome_mes = 'Abril';
                        break;
                  case 5:
                        $nome_mes = 'Maio';
                        break;
                  case 6:
                        $nome_mes = 'Junho';
                        break;
                  case 7:
                        $nome_mes = 'Julho';
                        break;
                  case 8:
                        $nome_mes = 'Agosto';
                        break;
                  case 9:
                        $nome_mes = 'Setembro';
                        break;
                  case 10:
                        $nome_mes = 'Outubro';
                        break;
                  case 11:
                        $nome_mes = 'Novembro';
                        break;
                  case 12:
                        $nome_mes = 'Dezembro';
                        break;
            }
      } else {
            if($request->getParam('ano') != '') {
                  $data_1 = $request->getParam('ano') . '-' . date('m-01');
                  $data_2 = $request->getParam('ano') . '-' . date('m-t');
            } else {
                  $data_1 = date('Y-m-01');
                  $data_2 = date('Y-m-t');
            }

            $item = Mensalidades::whereBetween('data_vencimento', [$data_1, $data_2])->where('status', '!=', 99)->get()->toArray();

            switch (date('m')) {
                  case 1:
                        $nome_mes = 'Janeiro';
                        break;
                  case 2:
                        $nome_mes = 'Fevereiro';
                        break;
                  case 3:
                        $nome_mes = 'Março';
                        break;
                  case 4:
                        $nome_mes = 'Abril';
                        break;
                  case 5:
                        $nome_mes = 'Maio';
                        break;
                  case 6:
                        $nome_mes = 'Junho';
                        break;
                  case 7:
                        $nome_mes = 'Julho';
                        break;
                  case 8:
                        $nome_mes = 'Agosto';
                        break;
                  case 9:
                        $nome_mes = 'Setembro';
                        break;
                  case 10:
                        $nome_mes = 'Outubro';
                        break;
                  case 11:
                        $nome_mes = 'Novembro';
                        break;
                  case 12:
                        $nome_mes = 'Dezembro';
                        break;
            }
      }

      $data_hoje = date('Y-m-d');
      $count_mensalidades_vencidas = 0;
      $count_mensalidades_a_receber = 0;
      $count_mensalidades_recebidas = 0;
      for($i = 0; $i < count($item); $i++){
            $id_mensalidade = $item[$i]['id'];
            $acolhimento[$id_mensalidade] = Acolhimentos::find($item[$i]['acolhimento']);
            $acolhido[$id_mensalidade] = Acolhidos::find($acolhimento[$id_mensalidade]['acolhido']);
            $contato_principal[$id_mensalidade] = Contatos::where('acolhido', $acolhido[$id_mensalidade]['id'])->where('status', 1)->first();
            $total_mensalidades[$id_mensalidade] = Mensalidades::where('acolhimento', $item[$i]['acolhimento'])->count();
            // editar mensalidade já paga
            if($item[$i]['status'] == 1){
                  if($acolhimento[$id_mensalidade]['tipo_acolhimento'] == 3){
                        $descricao = "Mensalidade ".$item[$i]['parcela']." de ".$total_mensalidades[$id_mensalidade]." - Acolhimento #".$item[$i]['acolhimento']." - ".$acolhido[$id_mensalidade]['nome']."";
                  } else {
                        $descricao = "Mensalidade ".$item[$i]['parcela']." de 6 - Acolhimento #".$item[$i]['acolhimento']." - ".$acolhido[$id_mensalidade]['nome']."";
                  }
                  $editar_mensalidade[$id_mensalidade] = Movimentos::where('descricao', $descricao)->first();
            }
            if($data_hoje > $item[$i]['data_vencimento'] && $item[$i]['status'] == 0){
                  $count_mensalidades_vencidas++;
            }

            if($data_hoje < $item[$i]['data_vencimento'] && $item[$i]['status'] == 0){
                  $count_mensalidades_a_receber++;
            }

            if($item[$i]['status'] == 1){
                  $count_mensalidades_recebidas++;
            }

            $this->view->offsetSet("acolhimento", $acolhimento);
            $this->view->offsetSet("acolhido", $acolhido);
            $this->view->offsetSet("contato_principal", $contato_principal);
            $this->view->offsetSet("total_mensalidades", $total_mensalidades);
            $this->view->offsetSet("editar_mensalidade", $editar_mensalidade);
      }
      $this->view->offsetSet("count_mensalidades_vencidas", $count_mensalidades_vencidas);
      $this->view->offsetSet("count_mensalidades_a_receber", $count_mensalidades_a_receber);
      $this->view->offsetSet("count_mensalidades_recebidas", $count_mensalidades_recebidas);

      $mensagem = $this->flash->getMessages();

      return $this->view->render($response, 'financeiro/mensalidades/listar.html', [
            'Titulo_Pagina_Mae' => 'Financeiro -',
            'Titulo_Pagina' => 'Mensalidades',
            'titulo'    => 'Listagem das mensalidades - ',
            'subtitulo' => 'Todas as mensalidades do mês de ',
            'view'      => 'listar',
            'flash'     => $mensagem,
            'itens'     => $item,
            'contas_bancarias' => $contas_bancarias,
            'nome_mes' => $nome_mes,
            'mes' => $args['mes'],
            'sum_previsto_jan' => number_format($sum_previsto_jan, 2, ".", ""),
            'sum_recebidas_jan' => number_format($sum_recebidas_jan, 2, ".", ""),
            'sum_a_receber_jan' => number_format($sum_a_receber_jan, 2, ".", ""),
            'sum_previsto_fev' => number_format($sum_previsto_fev, 2, ".", ""),
            'sum_recebidas_fev' => number_format($sum_recebidas_fev, 2, ".", ""),
            'sum_a_receber_fev' => number_format($sum_a_receber_fev, 2, ".", ""),
            'sum_previsto_mar' => number_format($sum_previsto_mar, 2, ".", ""),
            'sum_recebidas_mar' => number_format($sum_recebidas_mar, 2, ".", ""),
            'sum_a_receber_mar' => number_format($sum_a_receber_mar, 2, ".", ""),
            'sum_previsto_abr' => number_format($sum_previsto_abr, 2, ".", ""),
            'sum_recebidas_abr' => number_format($sum_recebidas_abr, 2, ".", ""),
            'sum_a_receber_abr' => number_format($sum_a_receber_abr, 2, ".", ""),
            'sum_previsto_mai' => number_format($sum_previsto_mai, 2, ".", ""),
            'sum_recebidas_mai' => number_format($sum_recebidas_mai, 2, ".", ""),
            'sum_a_receber_mai' => number_format($sum_a_receber_mai, 2, ".", ""),
            'sum_previsto_jun' => number_format($sum_previsto_jun, 2, ".", ""),
            'sum_recebidas_jun' => number_format($sum_recebidas_jun, 2, ".", ""),
            'sum_a_receber_jun' => number_format($sum_a_receber_jun, 2, ".", ""),
            'sum_previsto_jul' => number_format($sum_previsto_jul, 2, ".", ""),
            'sum_recebidas_jul' => number_format($sum_recebidas_jul, 2, ".", ""),
            'sum_a_receber_jul' => number_format($sum_a_receber_jul, 2, ".", ""),
            'sum_previsto_ago' => number_format($sum_previsto_ago, 2, ".", ""),
            'sum_recebidas_ago' => number_format($sum_recebidas_ago, 2, ".", ""),
            'sum_a_receber_ago' => number_format($sum_a_receber_ago, 2, ".", ""),
            'sum_previsto_set' => number_format($sum_previsto_set, 2, ".", ""),
            'sum_recebidas_set' => number_format($sum_recebidas_set, 2, ".", ""),
            'sum_a_receber_set' => number_format($sum_a_receber_set, 2, ".", ""),
            'sum_previsto_out' => number_format($sum_previsto_out, 2, ".", ""),
            'sum_recebidas_out' => number_format($sum_recebidas_out, 2, ".", ""),
            'sum_a_receber_out' => number_format($sum_a_receber_out, 2, ".", ""),
            'sum_previsto_nov' => number_format($sum_previsto_nov, 2, ".", ""),
            'sum_recebidas_nov' => number_format($sum_recebidas_nov, 2, ".", ""),
            'sum_a_receber_nov' => number_format($sum_a_receber_nov, 2, ".", ""),
            'sum_previsto_dez' => number_format($sum_previsto_dez, 2, ".", ""),
            'sum_recebidas_dez' => number_format($sum_recebidas_dez, 2, ".", ""),
            'sum_a_receber_dez' => number_format($sum_a_receber_dez, 2, ".", ""),
            'sum_previsto_ano' => number_format($sum_previsto_ano, 2, ".", ""),
            'sum_recebidas_ano' => number_format($sum_recebidas_ano, 2, ".", ""),
            'sum_a_receber_ano' => number_format($sum_a_receber_ano, 2, ".", ""),
      ]);
    }

    // EDITA A MENSALIDADE
    public function postEditarMensalidade ($request, $response, $args)
    {
      $item = Mensalidades::find($args['id']);

      // se a mensalidade já estiver paga
      if($item->status == 1){
            $data_pagamento = explode('/', $request->getParam('data_pagamento'));
            $data_pagamento = $data_pagamento[2].'-'.$data_pagamento[1].'-'.$data_pagamento[0];

            $movimento = Movimentos::where('descricao', $request->getParam('descricao'))->first();
            // se a mensalidade não estiver registrada no fluxo de caixa (contas a receber)
            if(!$movimento){
                  $create_movimento = Movimentos::create([
                        'descricao' => $request->getParam('descricao'),
                        'valor_previsto' => $request->getParam('valor'),
                        'valor_efetuado' => $request->getParam('valor'),
                        'data_prevista' => $data_pagamento,
                        'data_efetuada' => $data_pagamento,
                        'categoria' => $request->getParam('categoria'), // mensalidades acolhidos
                        'conta_bancaria' => $request->getParam('conta_bancaria'),
                        'forma_pagamento' => $request->getParam('forma_pagamento'),
                        'observacoes' => $request->getParam('observacoes'),
                        'acolhimento' => $item->acolhimento,
                        'tipo' => 1, // entrada
                        'status' => 1, // pago
                  ]);
                  if(!$create_movimento->id){
                        $this->flash->addMessage('error', 'Erro ao gerar mensalidade no fluxo de caixa!');
                  }
            } else {
                  $movimento->update([
                        'valor_previsto' => $request->getParam('valor'),
                        'valor_efetuado' => $request->getParam('valor'),
                        'data_prevista' => $data_pagamento,
                        'data_efetuada' => $data_pagamento,
                  ]);

                  if(!$movimento){
                        $this->flash->addMessage('error', 'Erro ao editar mensalidade no fluxo de caixa!');
                  }
            }

            $item->update([
                  'data_pagamento' => $data_pagamento,
                  'valor' => $request->getParam('valor'),
            ]);
      }

      if($item->status == 0){
            $data_vencimento = explode('/', $request->getParam('data_vencimento'));
            $data_vencimento = $data_vencimento[2].'-'.$data_vencimento[1].'-'.$data_vencimento[0];
            $item->update([
                  'data_vencimento' => $data_vencimento,
                  'valor' => $request->getParam('valor'),
            ]);
      }

      if($item)
      {
            $this->flash->addMessage('success', 'Mensalidade editada com sucesso!');
      }
      else
      {
            $this->flash->addMessage('error', 'Erro ao editar mensalidade!');
      }
        
      return $response->withRedirect($this->router->pathFor('mensalidades'));
    }

    // CONFIRMA O PAGAMENTO DA MENSALIDADE
    public function postPagamentoMensalidade ($request, $response, $args)
    {
        $mensalidade = Mensalidades::find($args['id']);

        $data_pagamento = explode('/', $request->getParam('data_pagamento'));
        $data_pagamento = $data_pagamento[2].'-'.$data_pagamento[1].'-'.$data_pagamento[0];
        $mensalidade->update([
            'valor' => $request->getParam('valor'),
            'data_pagamento' => $data_pagamento,
            'status' => 1,
        ]);

        if($mensalidade)
        {
            $conta_bancaria = ContasBancarias::find($request->getParam('conta_bancaria'));
            $saldo_atual = $conta_bancaria['saldo_atual'] + $request->getParam('valor');
            $conta_bancaria->update([
                  'saldo_atual' => $saldo_atual,
            ]);

            $movimento = Movimentos::create([
                'num_nf' => '', 
                'descricao' => $request->getParam('descricao'), 
                'categoria' => 999,
                'data_prevista' => $request->getParam('data_vencimento'), 
                'data_efetuada' => $data_pagamento, 
                'valor_previsto' => $request->getParam('valor_previsto'), 
                'valor_efetuado' => $request->getParam('valor'), 
                'acolhimento' => $request->getParam('acolhimento'), 
                'conta_bancaria' => $request->getParam('conta_bancaria'), 
                'fornecedor' => '', 
                'observacoes' => $request->getParam('observacoes'), 
                'tipo' => 1, 
                'forma_pagamento' => $request->getParam('forma_pagamento'),
                'status' => 1
            ]);

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

##########
    ###### MATRICULAS
##########
    // EXIBE A LISTAGEM DAS MATRICULAS
    public function listarMatriculas ($request, $response, $args)
    {   
      if($this->acesso_usuario['financeiro']['matriculas']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
      }

      if($request->getParam('ano') != ''){
            $data_1 = $request->getParam('ano') . '-' . date('m-01');
            $data_2 = $request->getParam('ano') . '-' . date('m-t');

            // Pega as mensalidades de Janeiro
            $data_1_jan = $request->getParam('ano') . '-' . date('01-01');
            $data_2_jan = $request->getParam('ano') . '-' . date('01-t');
            $sum_previsto_jan = Matriculas::whereBetween('data_vencimento', [$data_1_jan, $data_2_jan])->sum('valor');
            $sum_recebidas_jan = Matriculas::where('status', 1)->whereBetween('data_vencimento', [$data_1_jan, $data_2_jan])->sum('valor');
            $sum_a_receber_jan = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_jan, $data_2_jan])->sum('valor');
            $sum_atrasadas_jan = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_jan, $data_2_jan])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades de Fevereiro
            $data_1_fev = $request->getParam('ano') . '-' . date('02-01');
            $data_2_fev = $request->getParam('ano') . '-' . date('02-t');
            $sum_previsto_fev = Matriculas::whereBetween('data_vencimento', [$data_1_fev, $data_2_fev])->sum('valor');
            $sum_recebidas_fev = Matriculas::where('status', 1)->whereBetween('data_vencimento', [$data_1_fev, $data_2_fev])->sum('valor');
            $sum_a_receber_fev = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_fev, $data_2_fev])->sum('valor');
            $sum_atrasadas_fev = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_fev, $data_2_fev])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades de Março
            $data_1_mar = $request->getParam('ano') . '-' . date('03-01');
            $data_2_mar = $request->getParam('ano') . '-' . date('03-t');
            $sum_previsto_mar = Matriculas::whereBetween('data_vencimento', [$data_1_mar, $data_2_mar])->sum('valor');
            $sum_recebidas_mar = Matriculas::where('status', 1)->whereBetween('data_vencimento', [$data_1_mar, $data_2_mar])->sum('valor');
            $sum_a_receber_mar = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_mar, $data_2_mar])->sum('valor');
            $sum_atrasadas_mar = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_mar, $data_2_mar])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades de Abril
            $data_1_abr = $request->getParam('ano') . '-' . date('04-01');
            $data_2_abr = $request->getParam('ano') . '-' . date('04-t');
            $sum_previsto_abr = Matriculas::whereBetween('data_vencimento', [$data_1_abr, $data_2_abr])->sum('valor');
            $sum_recebidas_abr = Matriculas::where('status', 1)->whereBetween('data_vencimento', [$data_1_abr, $data_2_abr])->sum('valor');
            $sum_a_receber_abr = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_abr, $data_2_abr])->sum('valor');
            $sum_atrasadas_abr = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_abr, $data_2_abr])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades de Maio
            $data_1_mai = $request->getParam('ano') . '-' . date('05-01');
            $data_2_mai = $request->getParam('ano') . '-' . date('05-t');
            $sum_previsto_mai = Matriculas::whereBetween('data_vencimento', [$data_1_mai, $data_2_mai])->sum('valor');
            $sum_recebidas_mai = Matriculas::where('status', 1)->whereBetween('data_vencimento', [$data_1_mai, $data_2_mai])->sum('valor');
            $sum_a_receber_mai = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_mai, $data_2_mai])->sum('valor');
            $sum_atrasadas_mai = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_mai, $data_2_mai])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades de Junho
            $data_1_jun = $request->getParam('ano') . '-' . date('06-01');
            $data_2_jun = $request->getParam('ano') . '-' . date('06-t');
            $sum_previsto_jun = Matriculas::whereBetween('data_vencimento', [$data_1_jun, $data_2_jun])->sum('valor');
            $sum_recebidas_jun = Matriculas::where('status', 1)->whereBetween('data_vencimento', [$data_1_jun, $data_2_jun])->sum('valor');
            $sum_a_receber_jun = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_jun, $data_2_jun])->sum('valor');
            $sum_atrasadas_jun = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_jun, $data_2_jun])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades de Julho
            $data_1_jul = $request->getParam('ano') . '-' . date('07-01');
            $data_2_jul = $request->getParam('ano') . '-' . date('07-t');
            $sum_previsto_jul = Matriculas::whereBetween('data_vencimento', [$data_1_jul, $data_2_jul])->sum('valor');
            $sum_recebidas_jul = Matriculas::where('status', 1)->whereBetween('data_vencimento', [$data_1_jul, $data_2_jul])->sum('valor');
            $sum_a_receber_jul = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_jul, $data_2_jul])->sum('valor');
            $sum_atrasadas_jul = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_jul, $data_2_jul])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades de Agosto
            $data_1_ago = $request->getParam('ano') . '-' . date('08-01');
            $data_2_ago = $request->getParam('ano') . '-' . date('08-t');
            $sum_previsto_ago = Matriculas::whereBetween('data_vencimento', [$data_1_ago, $data_2_ago])->sum('valor');
            $sum_recebidas_ago = Matriculas::where('status', 1)->whereBetween('data_vencimento', [$data_1_ago, $data_2_ago])->sum('valor');
            $sum_a_receber_ago = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_ago, $data_2_ago])->sum('valor');
            $sum_atrasadas_ago = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_ago, $data_2_ago])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades de Setembro
            $data_1_set = $request->getParam('ano') . '-' . date('09-01');
            $data_2_set = $request->getParam('ano') . '-' . date('09-t');
            $sum_previsto_set = Matriculas::whereBetween('data_vencimento', [$data_1_set, $data_2_set])->sum('valor');
            $sum_recebidas_set = Matriculas::where('status', 1)->whereBetween('data_vencimento', [$data_1_set, $data_2_set])->sum('valor');
            $sum_a_receber_set = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_set, $data_2_set])->sum('valor');
            $sum_atrasadas_set = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_set, $data_2_set])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades de Outubro
            $data_1_out = $request->getParam('ano') . '-' . date('10-01');
            $data_2_out = $request->getParam('ano') . '-' . date('10-t');
            $sum_previsto_out = Matriculas::whereBetween('data_vencimento', [$data_1_out, $data_2_out])->sum('valor');
            $sum_recebidas_out = Matriculas::where('status', 1)->whereBetween('data_vencimento', [$data_1_out, $data_2_out])->sum('valor');
            $sum_a_receber_out = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_out, $data_2_out])->sum('valor');
            $sum_atrasadas_out = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_out, $data_2_out])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades de Novembro
            $data_1_nov = $request->getParam('ano') . '-' . date('11-01');
            $data_2_nov = $request->getParam('ano') . '-' . date('11-t');
            $sum_previsto_nov = Matriculas::whereBetween('data_vencimento', [$data_1_nov, $data_2_nov])->sum('valor');
            $sum_recebidas_nov = Matriculas::where('status', 1)->whereBetween('data_vencimento', [$data_1_nov, $data_2_nov])->sum('valor');
            $sum_a_receber_nov = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_nov, $data_2_nov])->sum('valor');
            $sum_atrasadas_nov = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_nov, $data_2_nov])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades de Dezembro
            $data_1_dez = $request->getParam('ano') . '-' . date('12-01');
            $data_2_dez = $request->getParam('ano') . '-' . date('12-t');
            $sum_previsto_dez = Matriculas::whereBetween('data_vencimento', [$data_1_dez, $data_2_dez])->sum('valor');
            $sum_recebidas_dez = Matriculas::where('status', 1)->whereBetween('data_vencimento', [$data_1_dez, $data_2_dez])->sum('valor');
            $sum_a_receber_dez = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_dez, $data_2_dez])->sum('valor');
            $sum_atrasadas_dez = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_1_dez, $data_2_dez])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades do Ano
            $data_11 = $request->getParam('ano') . '-' . date('01-01');
            $data_22 = $request->getParam('ano') . '-' . date('12-t');
            $sum_previsto_ano = Matriculas::whereBetween('data_vencimento', [$data_11, $data_22])->sum('valor');
            $sum_recebidas_ano = Matriculas::where('status', 1)->whereBetween('data_vencimento', [$data_11, $data_22])->sum('valor');
            $sum_a_receber_ano = Matriculas::where('status', 0)->whereBetween('data_vencimento', [$data_11, $data_22])->sum('valor');

      } else {
            // Pega as mensalidades de Janeiro
            $sum_previsto_jan = Matriculas::whereBetween('data_vencimento', [date('Y-01-01'), date('Y-01-t')])->sum('valor');
            $sum_recebidas_jan = Matriculas::where('status', 1)->whereBetween('data_vencimento', [date('Y-01-01'), date('Y-01-t')])->sum('valor');
            $sum_a_receber_jan = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-01-01'), date('Y-01-t')])->sum('valor');
            $sum_atrasadas_jan = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-01-01'), date('Y-01-t')])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades de Fevereiro
            $sum_previsto_fev = Matriculas::whereBetween('data_vencimento', [date('Y-02-01'), date('Y-02-t')])->sum('valor');
            $sum_recebidas_fev = Matriculas::where('status', 1)->whereBetween('data_vencimento', [date('Y-02-01'), date('Y-02-t')])->sum('valor');
            $sum_a_receber_fev = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-02-01'), date('Y-02-t')])->sum('valor');
            $sum_atrasadas_fev = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-02-01'), date('Y-02-t')])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades de Março
            $sum_previsto_mar = Matriculas::whereBetween('data_vencimento', [date('Y-03-01'), date('Y-03-t')])->sum('valor');
            $sum_recebidas_mar = Matriculas::where('status', 1)->whereBetween('data_vencimento', [date('Y-03-01'), date('Y-03-t')])->sum('valor');
            $sum_a_receber_mar = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-03-01'), date('Y-03-t')])->sum('valor');
            $sum_atrasadas_mar = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-03-01'), date('Y-03-t')])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades de Abril
            $sum_previsto_abr = Matriculas::whereBetween('data_vencimento', [date('Y-04-01'), date('Y-04-t')])->sum('valor');
            $sum_recebidas_abr = Matriculas::where('status', 1)->whereBetween('data_vencimento', [date('Y-04-01'), date('Y-04-t')])->sum('valor');
            $sum_a_receber_abr = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-04-01'), date('Y-04-t')])->sum('valor');
            $sum_atrasadas_abr = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-04-01'), date('Y-04-t')])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades de Maio
            $sum_previsto_mai = Matriculas::whereBetween('data_vencimento', [date('Y-05-01'), date('Y-05-t')])->sum('valor');
            $sum_recebidas_mai = Matriculas::where('status', 1)->whereBetween('data_vencimento', [date('Y-05-01'), date('Y-05-t')])->sum('valor');
            $sum_a_receber_mai = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-05-01'), date('Y-05-t')])->sum('valor');
            $sum_atrasadas_mai = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-05-01'), date('Y-05-t')])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades de Junho
            $sum_previsto_jun = Matriculas::whereBetween('data_vencimento', [date('Y-06-01'), date('Y-06-t')])->sum('valor');
            $sum_recebidas_jun = Matriculas::where('status', 1)->whereBetween('data_vencimento', [date('Y-06-01'), date('Y-06-t')])->sum('valor');
            $sum_a_receber_jun = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-06-01'), date('Y-06-t')])->sum('valor');
            $sum_atrasadas_jun = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-06-01'), date('Y-06-t')])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades de Julho
            $sum_previsto_jul = Matriculas::whereBetween('data_vencimento', [date('Y-07-01'), date('Y-07-t')])->sum('valor');
            $sum_recebidas_jul = Matriculas::where('status', 1)->whereBetween('data_vencimento', [date('Y-07-01'), date('Y-07-t')])->sum('valor');
            $sum_a_receber_jul = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-07-01'), date('Y-07-t')])->sum('valor');
            $sum_atrasadas_jul = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-07-01'), date('Y-07-t')])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades de Agosto
            $sum_previsto_ago = Matriculas::whereBetween('data_vencimento', [date('Y-08-01'), date('Y-08-t')])->sum('valor');
            $sum_recebidas_ago = Matriculas::where('status', 1)->whereBetween('data_vencimento', [date('Y-08-01'), date('Y-08-t')])->sum('valor');
            $sum_a_receber_ago = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-08-01'), date('Y-08-t')])->sum('valor');
            $sum_atrasadas_ago = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-08-01'), date('Y-08-t')])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades de Setembro
            $sum_previsto_set = Matriculas::whereBetween('data_vencimento', [date('Y-09-01'), date('Y-09-t')])->sum('valor');
            $sum_recebidas_set = Matriculas::where('status', 1)->whereBetween('data_vencimento', [date('Y-09-01'), date('Y-09-t')])->sum('valor');
            $sum_a_receber_set = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-09-01'), date('Y-09-t')])->sum('valor');
            $sum_atrasadas_set = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-09-01'), date('Y-09-t')])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades de Outubro
            $sum_previsto_out = Matriculas::whereBetween('data_vencimento', [date('Y-10-01'), date('Y-10-t')])->sum('valor');
            $sum_recebidas_out = Matriculas::where('status', 1)->whereBetween('data_vencimento', [date('Y-10-01'), date('Y-10-t')])->sum('valor');
            $sum_a_receber_out = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-10-01'), date('Y-10-t')])->sum('valor');
            $sum_atrasadas_out = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-10-01'), date('Y-10-t')])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades de Novembro
            $sum_previsto_nov = Matriculas::whereBetween('data_vencimento', [date('Y-11-01'), date('Y-11-t')])->sum('valor');
            $sum_recebidas_nov = Matriculas::where('status', 1)->whereBetween('data_vencimento', [date('Y-11-01'), date('Y-11-t')])->sum('valor');
            $sum_a_receber_nov = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-11-01'), date('Y-11-t')])->sum('valor');
            $sum_atrasadas_nov = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-11-01'), date('Y-11-t')])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades de Dezembro
            $sum_previsto_dez = Matriculas::whereBetween('data_vencimento', [date('Y-12-01'), date('Y-12-t')])->sum('valor');
            $sum_recebidas_dez = Matriculas::where('status', 1)->whereBetween('data_vencimento', [date('Y-12-01'), date('Y-12-t')])->sum('valor');
            $sum_a_receber_dez = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-12-01'), date('Y-12-t')])->sum('valor');
            $sum_atrasadas_dez = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-12-01'), date('Y-12-t')])->where('data_vencimento', '<', date('Y-m-d'))->sum('valor');

            // Pega as mensalidades do Ano
            $sum_previsto_ano = Matriculas::whereBetween('data_vencimento', [date('Y-01-01'), date('Y-12-t')])->sum('valor');
            $sum_recebidas_ano = Matriculas::where('status', 1)->whereBetween('data_vencimento', [date('Y-01-01'), date('Y-12-t')])->sum('valor');
            $sum_a_receber_ano = Matriculas::where('status', 0)->whereBetween('data_vencimento', [date('Y-01-01'), date('Y-12-t')])->sum('valor');
      
      }

      $item = Matriculas::get()->toArray();
      $contas_bancarias = ContasBancarias::orderBy('id','ASC')->get()->toArray();

      for($i = 0; $i < count($item); $i++){
            $id_mensalidade = $item[$i]['id'];
            $acolhimento[$id_mensalidade] = Acolhimentos::find($item[$i]['acolhimento']);
            $acolhido[$id_mensalidade] = Acolhidos::find($acolhimento[$id_mensalidade]['acolhido']);

            $this->view->offsetSet("acolhimento", $acolhimento);
            $this->view->offsetSet("acolhido", $acolhido);
      }

      $mensagem = $this->flash->getMessages();

      return $this->view->render($response, 'financeiro/matriculas/listar.html', [
            'Titulo_Pagina_Mae' => 'Financeiro -',
            'Titulo_Pagina' => 'Matrículas',
            'titulo'    => 'Listagem das matrículas - ',
            'subtitulo' => 'Todas as matrículas do mês de ',
            'view'      => 'listar',
            'flash'     => $mensagem,
            'itens'     => $item,
            'contas_bancarias' => $contas_bancarias,
            'sum_previsto_jan' => number_format($sum_previsto_jan, 2, ".", ""),
            'sum_recebidas_jan' => number_format($sum_recebidas_jan, 2, ".", ""),
            'sum_a_receber_jan' => number_format($sum_a_receber_jan, 2, ".", ""),
            'sum_atrasadas_jan' => number_format($sum_atrasadas_jan, 2, ".", ""),

            'sum_previsto_fev' => number_format($sum_previsto_fev, 2, ".", ""),
            'sum_recebidas_fev' => number_format($sum_recebidas_fev, 2, ".", ""),
            'sum_a_receber_fev' => number_format($sum_a_receber_fev, 2, ".", ""),
            'sum_atrasadas_fev' => number_format($sum_atrasadas_fev, 2, ".", ""),

            'sum_previsto_mar' => number_format($sum_previsto_mar, 2, ".", ""),
            'sum_recebidas_mar' => number_format($sum_recebidas_mar, 2, ".", ""),
            'sum_a_receber_mar' => number_format($sum_a_receber_mar, 2, ".", ""),
            'sum_atrasadas_mar' => number_format($sum_atrasadas_mar, 2, ".", ""),

            'sum_previsto_abr' => number_format($sum_previsto_abr, 2, ".", ""),
            'sum_recebidas_abr' => number_format($sum_recebidas_abr, 2, ".", ""),
            'sum_a_receber_abr' => number_format($sum_a_receber_abr, 2, ".", ""),
            'sum_atrasadas_abr' => number_format($sum_atrasadas_abr, 2, ".", ""),

            'sum_previsto_mai' => number_format($sum_previsto_mai, 2, ".", ""),
            'sum_recebidas_mai' => number_format($sum_recebidas_mai, 2, ".", ""),
            'sum_a_receber_mai' => number_format($sum_a_receber_mai, 2, ".", ""),
            'sum_atrasadas_mai' => number_format($sum_atrasadas_mai, 2, ".", ""),

            'sum_previsto_jun' => number_format($sum_previsto_jun, 2, ".", ""),
            'sum_recebidas_jun' => number_format($sum_recebidas_jun, 2, ".", ""),
            'sum_a_receber_jun' => number_format($sum_a_receber_jun, 2, ".", ""),
            'sum_atrasadas_jun' => number_format($sum_atrasadas_jun, 2, ".", ""),

            'sum_previsto_jul' => number_format($sum_previsto_jul, 2, ".", ""),
            'sum_recebidas_jul' => number_format($sum_recebidas_jul, 2, ".", ""),
            'sum_a_receber_jul' => number_format($sum_a_receber_jul, 2, ".", ""),
            'sum_atrasadas_jul' => number_format($sum_atrasadas_jul, 2, ".", ""),

            'sum_previsto_ago' => number_format($sum_previsto_ago, 2, ".", ""),
            'sum_recebidas_ago' => number_format($sum_recebidas_ago, 2, ".", ""),
            'sum_a_receber_ago' => number_format($sum_a_receber_ago, 2, ".", ""),
            'sum_atrasadas_ago' => number_format($sum_atrasadas_ago, 2, ".", ""),

            'sum_previsto_set' => number_format($sum_previsto_set, 2, ".", ""),
            'sum_recebidas_set' => number_format($sum_recebidas_set, 2, ".", ""),
            'sum_a_receber_set' => number_format($sum_a_receber_set, 2, ".", ""),
            'sum_atrasadas_set' => number_format($sum_atrasadas_set, 2, ".", ""),

            'sum_previsto_out' => number_format($sum_previsto_out, 2, ".", ""),
            'sum_recebidas_out' => number_format($sum_recebidas_out, 2, ".", ""),
            'sum_a_receber_out' => number_format($sum_a_receber_out, 2, ".", ""),
            'sum_atrasadas_out' => number_format($sum_atrasadas_out, 2, ".", ""),

            'sum_previsto_nov' => number_format($sum_previsto_nov, 2, ".", ""),
            'sum_recebidas_nov' => number_format($sum_recebidas_nov, 2, ".", ""),
            'sum_a_receber_nov' => number_format($sum_a_receber_nov, 2, ".", ""),
            'sum_atrasadas_nov' => number_format($sum_atrasadas_nov, 2, ".", ""),

            'sum_previsto_dez' => number_format($sum_previsto_dez, 2, ".", ""),
            'sum_recebidas_dez' => number_format($sum_recebidas_dez, 2, ".", ""),
            'sum_a_receber_dez' => number_format($sum_a_receber_dez, 2, ".", ""),
            'sum_atrasadas_dez' => number_format($sum_atrasadas_dez, 2, ".", ""),

            'sum_previsto_ano' => number_format($sum_previsto_ano, 2, ".", ""),
            'sum_recebidas_ano' => number_format($sum_recebidas_ano, 2, ".", ""),
            'sum_a_receber_ano' => number_format($sum_a_receber_ano, 2, ".", ""),
        ]);
    }

    // CONFIRMA O PAGAMENTO DA MATRICULA
    public function postPagamentoMatricula ($request, $response, $args)
    {
        $matricula = Matriculas::find($args['id']);

        $data_pagamento = explode('/', $request->getParam('data_pagamento'));
        $data_pagamento = $data_pagamento[2].'-'.$data_pagamento[1].'-'.$data_pagamento[0];
        $matricula->update([
            'data_pagamento' => $data_pagamento,
            'status' => 1,
        ]);

        if($matricula)
        {
            $conta_bancaria = ContasBancarias::find($request->getParam('conta_bancaria'));
            $saldo_atual = $conta_bancaria['saldo_atual'] + $request->getParam('valor');
            $conta_bancaria->update([
                  'saldo_atual' => $saldo_atual,
            ]);

            $movimento = Movimentos::create([
                'num_nf' => '', 
                'descricao' => $request->getParam('descricao'), 
                'categoria' => 998,
                'data_prevista' => $request->getParam('data_vencimento'), 
                'data_efetuada' => $data_pagamento, 
                'valor_previsto' => $request->getParam('valor_previsto'), 
                'valor_efetuado' => $request->getParam('valor'), 
                'acolhimento' => $request->getParam('acolhimento'), 
                'conta_bancaria' => $request->getParam('conta_bancaria'), 
                'fornecedor' => '', 
                'observacoes' => $request->getParam('observacoes'), 
                'tipo' => 1, 
                'forma_pagamento' => $request->getParam('forma_pagamento'),
                'status' => 1
            ]);

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

##########
    ###### CATEGORIAS
##########
    // EXIBE A LISTAGEM DAS CATEGORIAS
    public function listarCategorias ($request, $response, $args)
    {   
      if($this->acesso_usuario['financeiro']['categorias']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
      }

    	$item = Categorias::orderBy('id','ASC')->get()->toArray();

      for ($i=0; $i < count($item) ; $i++) { 
            $subcategorias[$item[$i]['id']] = SubCategorias::where('categoria', $item[$i]['id'])->count();

            $this->view->offsetSet("count_subcategorias", $subcategorias);
      }

      $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'financeiro/categorias/listar.html', [
            'Titulo_Pagina_Mae' => 'Financeiro -',
            'Titulo_Pagina' => 'Categorias',
            'titulo'    => 'Listagem das categorias',
            'subtitulo' => 'Todas as categorias cadastradas no sistema.',
            'view'      => 'listar',
            'flash'     => $mensagem,
            'itens'     => $item
        ]);
    }

    // REMOVER
    public function getRemoverCategoria ($request, $response, $args)
    {
      if($this->acesso_usuario['financeiro']['categorias']['d'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
      }

        $item = Categorias::find($args['id']);

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
    public function postCadastrarCategoria ($request, $response, $args)
    {
	    $item = Categorias::create([
	    	'nome' 		=> $request->getParam('nome'),
            'tipo'      => $request->getParam('tipo'),
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
    public function postEditarCategoria ($request, $response, $args)
    {
    	$item = Categorias::find($args['id']);
    	$item->update([
            'nome'      => $request->getParam('nome'),
            'tipo'      => $request->getParam('tipo'),
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
##########
    ###### END
##########

##########
    ###### CONTAS BANCÁRIAS
##########
    // EXIBE A LISTAGEM
    public function listarContasBancarias ($request, $response, $args)
    {   
      if($this->acesso_usuario['financeiro']['contas_bancarias']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
      }

        $item = ContasBancarias::orderBy('id','ASC')->get()->toArray();

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'financeiro/contas-bancarias/listar.html', [
            'Titulo_Pagina_Mae' => 'Financeiro -',
            'Titulo_Pagina' => 'Contas Bancárias',
            'titulo'    => 'Listagem das contas bancárias',
            'subtitulo' => 'Todas as contas bancárias da sua instituição cadastradas no sistema.',
            'view'      => 'listar',
            'flash'     => $mensagem,
            'itens'     => $item
        ]);
    }

    // REMOVER
    public function getRemoverContaBancaria ($request, $response, $args)
    {
      if($this->acesso_usuario['financeiro']['contas_bancarias']['d'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
      }

        $item = ContasBancarias::find($args['id']);

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
    public function postCadastrarContaBancaria ($request, $response, $args)
    {
        $item = ContasBancarias::create([
            'nome' => $request->getParam('nome'),
            'favorecido' => $request->getParam('favorecido'),
            'agencia' => $request->getParam('agencia'),
            'conta' => $request->getParam('conta'),
            'dv' => $request->getParam('dv'),
            'op' => $request->getParam('op'),
            'saldo_inicial' => $request->getParam('saldo_inicial'),
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
    public function postEditarContaBancaria ($request, $response, $args)
    {
        $item = ContasBancarias::find($args['id']);
        $item->update([
            'nome' => $request->getParam('nome'),
            'favorecido' => $request->getParam('favorecido'),
            'agencia' => $request->getParam('agencia'),
            'conta' => $request->getParam('conta'),
            'dv' => $request->getParam('dv'),
            'op' => $request->getParam('op'),
            'saldo_inicial' => $request->getParam('saldo_inicial'),
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
##########
    ###### END
##########


##########
    ###### CALENDÁRIO
##########
      // Exibe o calendário de eventos
      public function listarCalendario ($request, $response, $args)
      {
            if($this->acesso_usuario['financeiro']['calendario']['r'] != 'on'){
                  return $this->container->view->render($response->withStatus(403), 'error/403.html');
            }

            $item = Movimentos::orderBy('id','ASC')->get()->toArray();
            $categorias_despesa = Categorias::where('tipo', 0)->orderBy('id','ASC')->get()->toArray();
            $categorias_receita = Categorias::where('tipo', 1)->orderBy('id','ASC')->get()->toArray();
        
            foreach($item as $row)
            {
               $inicio = date('d/m/Y', strtotime($row["data_prevista"]));
               // $termino = date('d/m/Y', strtotime($row["end"]));
               // if($termino = '30/11/-0001' OR $termino = '' OR $termino = '0000-00-00'){
               //    $termino = '';
               // }
               if($row["tipo"] == 0 && $row["status"] == 1){
                  $bg_color = 'bg-danger';
               }

               if($row["tipo"] == 0 && $row["status"] == 0){
                  $bg_color = 'bg-warning';
               }

               if($row["tipo"] == 1 && $row["status"] == 1){
                  $bg_color = 'bg-success';
               }

               if($row["tipo"] == 1 && $row["status"] == 0){
                  $bg_color = 'bg-info';
               }

               $data[] = array(
                'id' => $row["id"],
                'num_nf' => $row["num_nf"],
                'categoria' => $row["categoria"],
                'start' => $row["data_prevista"],
                'data_efetuada' => $row["data_efetuada"],
                'title' => $row["descricao"],
                'valor_previsto' => $row["valor_previsto"],
                'valor_efetuado' => $row["valor_efetuado"],
                'conta_bancaria' => $row["conta_bancaria"],
                'forma_pagamento' => $row["forma_pagamento"],
                'acolhimento' => $row["acolhimento"],
                'fornecedor' => $row["fornecedor"],
                'inicio' => $inicio,
                // 'termino' => $termino,
                // 'end' => $row["end"],
                'tipo' => $row["tipo"],
                'status' => $row["status"],
                'className' => $bg_color,
               );
            }

        $this->view->offsetSet("evento", $data); 

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'financeiro/calendario/listar.html', [
            'Titulo_Pagina' => 'Agenda',
            'titulo'    => 'Listagem dos slides',
            'subtitulo' => 'Todos os slides do seu site',
            'view'      => 'listar',
            'flash'     => $mensagem,
            'itens'     => $item,
            'categorias_despesa' => $categorias_despesa,
            'categorias_receita' => $categorias_receita,
        ]);
      }

      public function getRemoverMovimentoCalendario ($request, $response, $args)
      {
            $item = Movimentos::find($args['id']);

            if($item->delete())
            {
                  if($item['status'] == 1){
                        $conta_bancaria = ContasBancarias::find($item['conta_bancaria']);
                        $saldo_atual = $conta_bancaria['saldo_atual'] + $item['valor_efetuado'];

                        $conta_bancaria->update([
                              'saldo_atual' => $saldo_atual,
                        ]);
                  }
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