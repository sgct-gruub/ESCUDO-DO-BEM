<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Respect\Validation\Validator as validate;
use Slim\Views\Twig as View;

use App\Models\Dispensa\Compras;
use App\Models\Dispensa\Produtos;
use App\Models\Dispensa\ItensCompra;
use App\Models\ContasBancarias;
use App\Models\Movimentos;
use App\Models\Fornecedores;

class DispensaController extends Controller
{

	// EXIBE A LISTAGEM DOS PRODUTOS
    public function listarProdutos ($request, $response, $args)
    {   
        if($this->acesso_usuario['dispensa']['produtos']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Produtos::orderBy('id','ASC')->get()->toArray();

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'dispensa/produtos/listar.html', [
            'Titulo_Pagina_Mae' => 'Dispensa - ',
            'Titulo_Pagina' => 'Todos os produtos',
            'titulo'    => 'Listagem dos produtos',
            'subtitulo' => 'Todos os produtos da sua instituição.',
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
    public function getRemoverProduto ($request, $response, $args)
    {
        if($this->acesso_usuario['dispensa']['produtos']['d'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Produtos::find($args['id']);

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
    public function postCadastrarProduto ($request, $response, $args)
    {
        if($this->acesso_usuario['dispensa']['produtos']['c'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

	    $item = Produtos::create([
	    	'nome' => $request->getParam('nome'),
            'unidade' => $request->getParam('unidade'),
            'estoque' => $request->getParam('estoque'),
	       	'estoque_minimo' => $request->getParam('estoque_minimo'),
	    ]);

    	if($item)
        {
            $this->flash->addMessage('success', 'Produto adicionado com sucesso!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao adicionar produto!');
        }

        return $response->withRedirect($this->router->pathFor('produtos'));
    }

    // EDITA O REGISTRO
    public function postEditarProduto ($request, $response, $args)
    {
        if($this->acesso_usuario['dispensa']['produtos']['u'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

    	$item = Produtos::find($args['id']);
    	$item->update([
            'nome' => $request->getParam('nome'),
            'unidade' => $request->getParam('unidade'),
            'estoque' => $request->getParam('estoque'),
            'estoque_minimo' => $request->getParam('estoque_minimo'),
        ]);

        if($item)
        {
            $this->flash->addMessage('success', 'Produto editado com sucesso!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao editar produto!');
        }

        return $response->withRedirect($this->router->pathFor('produtos'));
    }


#####
##### ENTRADA DE PRODUTOS
#####
    public function listarEntradas ($request, $response, $args)
    {   
        if($this->acesso_usuario['dispensa']['produtos']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $compras = Compras::orderBy('id','ASC')->get()->toArray();
        $contas_bancarias = ContasBancarias::orderBy('id','ASC')->get()->toArray();

        for($i = 0; $i < count($compras); $i++){
            $id_compra = $compras[$i]['id'];
            $nome_fornecedor[$id_compra] = Fornecedores::find($compras[$i]['fornecedor']);

            $this->view->offsetSet("nome_fornecedor", $nome_fornecedor);
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'dispensa/entrada/listar.html', [
            'Titulo_Pagina_Mae' => 'Dispensa - ',
            'Titulo_Pagina' => 'Entrada de produtos',
            'titulo' => 'Listagem das entradas de produtos',
            'subtitulo' => 'Todas as compras ou doações de produtos para a sua instituição.',
            'view' => 'listar',
            'flash' => $mensagem,
            'compras' => $compras,
            'contas_bancarias' => $contas_bancarias,
            'fornecedores' => $fornecedores,
        ]);
    }

    // EXIBE O FORMULÁRIO DE CADASTRO
    public function getCadastrarCompra ($request, $response)
    {
        $produtos = Produtos::orderBy('id','ASC')->get()->toArray();
        $contas_bancarias = ContasBancarias::orderBy('id','ASC')->get()->toArray();
        $fornecedores = Fornecedores::orderBy('id','ASC')->get()->toArray();

        return $this->view->render($response, 'dispensa/entrada/form.html', [
            'Titulo_Pagina' => 'Novo registro',
            'titulo' => 'Cadastrar nova compra',
            'subtitulo' => 'Preencha o formulário abaixo para inserir novos produtos na sua dispensa',
            'view' => 'cadastrar',
            'contas_bancarias' => $contas_bancarias,
            'fornecedores' => $fornecedores,
            'produtos' => $produtos,
        ]);
    }

    // EXIBE O FORMULÁRIO DE EDIÇÃO
    public function getEditarCompra ($request, $response, $args)
    {
        $item = Compras::find($args['id']);
        $itens_compra = ItensCompra::where('compra', $args['id'])->get()->toArray();

        $produtos = Produtos::orderBy('id','ASC')->get()->toArray();
        $contas_bancarias = ContasBancarias::orderBy('id','ASC')->get()->toArray();
        $fornecedores = Fornecedores::orderBy('id','ASC')->get()->toArray();

        if($item['dados_pagamento'] != ''){
            $dados_pagamento = unserialize($item['dados_pagamento']);
        }
        
        return $this->view->render($response, 'dispensa/entrada/form.html', [
            'Titulo_Pagina' => 'Editar registro',
            'titulo' => 'Editar compra',
            'subtitulo' => 'Preencha o formulário abaixo para editar esta compra',
            'item' => $item,
            'itens_compra' => $itens_compra,
            'dados_pagamento' => $dados_pagamento,
            'contas_bancarias' => $contas_bancarias,
            'fornecedores' => $fornecedores,
            'produtos' => $produtos,
        ]);
    }

    // SALVA O REGISTRO NO BANCO DE DADOS
    public function postCadastrarCompra ($request, $response, $args)
    {
        if($this->acesso_usuario['dispensa']['produtos']['c'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $data_compra = explode('/', $request->getParam('data_compra'));
        $data_compra = $data_compra[2].'-'.$data_compra[1].'-'.$data_compra[0];

        if($request->getParam('previsao_entrega') != ''){
            $previsao_entrega = explode('/', $request->getParam('previsao_entrega'));
            $previsao_entrega = $previsao_entrega[2].'-'.$previsao_entrega[1].'-'.$previsao_entrega[0];
        } else {
            $previsao_entrega = '';
        }

        $dados_pagamento = '';

        if($request->getParam('forma_pagamento') == 'transferencia'){
            $dados_pagamento = serialize($request->getParam('dados_transferencia'));
        }

        if($request->getParam('forma_pagamento') == 'cheque'){
            $dados_pagamento = serialize($request->getParam('dados_cheque'));
        }

        if($request->getParam('forma_pagamento') == ''){
            $dados_pagamento = '';
        }

        // GERA A COMPRA
        $item = Compras::create([
            'data_compra' => $data_compra, 
            'fornecedor' => $request->getParam('fornecedor'), 
            'numero_pedido' => $request->getParam('numero_pedido'), 
            'vendedor' => $request->getParam('vendedor'), 
            'tipo_frete' => $request->getParam('tipo_frete'), 
            'valor_frete' => $request->getParam('valor_frete'), 
            'previsao_entrega' => $previsao_entrega, 
            'forma_pagamento' => $request->getParam('forma_pagamento'), 
            'dados_pagamento' => $dados_pagamento, 
            'valor_total' => $request->getParam('valor_total'), 
            'conta_bancaria' => $request->getParam('conta_bancaria'),
        ]);

        if($item)
        {
            $error_itens = 0;
            for($i = 0; $i < count($request->getParam('product')); $i++){
                $produto = $request->getParam('product')[$i];
                $qtd = $request->getParam('qty')[$i];
                $valor_unitario = $request->getParam('price')[$i];

                // CADASTRA OS ITENS DA COMPRA
                $itens_compra = ItensCompra::create([
                    'compra' => $item->id,
                    'produto' => $produto,
                    'quantidade' => $qtd,
                    'valor_unitario' => $valor_unitario,
                ]);

                if($itens_compra){
                    $error_itens = 0;
                } else {
                    $error_itens = 1;
                }
            }

            if($error_itens == 0){

                // GERA O MOVIMENTO NO CONTAS A PAGAR
                $movimento = Movimentos::create([
                    'num_nf' => $request->getParam('numero_pedido'),
                    'descricao' => '[COMPRA] Entrada de produtos',
                    'categoria' => 74,
                    'data_prevista' => $data_compra,
                    'data_efetuada' => $data_compra,
                    'valor_previsto' => $request->getParam('valor_total'),
                    'valor_efetuado' => $request->getParam('valor_total'),
                    'conta_bancaria' => $request->getParam('conta_bancaria'),
                    'forma_pagamento' => $request->getParam('forma_pagamento'),
                    'dados_pagamento' => $dados_pagamento,
                    'acolhimento' => '',
                    'fornecedor' => $request->getParam('fornecedor'),
                    'compra' => $item->id,
                    'tipo' => 0,
                    'status' => 1,
                ]);

                if($movimento){
                    // REDUZ O SALDO DA CONTA BANCÁRIA
                    $valor_efetuado = $request->getParam('valor_total');

                    $conta_bancaria = ContasBancarias::find($request->getParam('conta_bancaria'));
                    $saldo_atual = $conta_bancaria['saldo_atual'] - $valor_efetuado;
                    $conta_bancaria->update([
                        'saldo_atual' => $saldo_atual,
                    ]);

                    $this->flash->addMessage('success', 'Compra adicionada com sucesso!');
                } else {
                    $this->flash->addMessage('error', 'Erro ao incluir compra no contas a pagar!');
                }

            } else {
                $this->flash->addMessage('error', 'Erro ao incluir os itens desta compra!');
            }
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao adicionar compra!');
        }

        return $response->withRedirect($this->router->pathFor('entradas'));
    }

    // REMOVER
    public function getRemoverCompra ($request, $response, $args)
    {
        if($this->acesso_usuario['dispensa']['produtos']['d'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Compras::find($args['id']);
        $itens_compra = ItensCompra::where('compra', $args['id']);
        $movimento = Movimentos::where('compra', $args['id']);

        $valor_efetuado = $item['valor_total'];

        $conta_bancaria = ContasBancarias::find($item['conta_bancaria']);
        $saldo_atual = $conta_bancaria['saldo_atual'] + $valor_efetuado;
        $conta_bancaria->update([
            'saldo_atual' => $saldo_atual,
        ]);

        if($item->delete())
        {
            if($itens_compra->delete()){
                if($movimento->delete()){
                    $this->flash->addMessage('success', 'Compra removida com sucesso!');
                } else {
                    $this->flash->addMessage('error', 'Erro ao remover movimento do contas a pagar!');
                }
            } else {
                $this->flash->addMessage('error', 'Erro ao remover os itens da compra!');
            }
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao remover compra!');
        }

        return $response->withRedirect($this->router->pathFor('entradas'));
    }
}