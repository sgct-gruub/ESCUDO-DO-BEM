<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Respect\Validation\Validator as validate;
use Slim\Views\Twig as View;

use App\Models\Despensa\Compras;
use App\Models\Despensa\Doacoes;
use App\Models\Despensa\Estoque;
use App\Models\Despensa\Saida;
use App\Models\Despensa\ItensCompra;
use App\Models\Despensa\ItensDoacao;
use App\Models\Cantina\Produtos;
use App\Models\Cantina\Lancamentos;
use App\Models\Cantina\ItensLancamento;
use App\Models\Cantina\BaixaLancamentos;
use App\Models\ContasBancarias;
use App\Models\Movimentos;
use App\Models\Fornecedores;
use App\Models\Doadores;
use App\Models\Acolhidos;
use App\Models\Acolhimentos;

class CantinaController extends Controller
{

#####
##### PRODUTOS
#####    
	// EXIBE A LISTAGEM DOS PRODUTOS
    public function listarProdutos ($request, $response, $args)
    {   
        if($this->acesso_usuario['cantina']['produtos']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Produtos::orderBy('nome','ASC')->get()->toArray();
        $acolhidos = Acolhidos::where('status', 1)->orderBy('nome', 'ASC')->get()->toArray();

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'cantina/produtos/listar.html', [
            'Titulo_Pagina_Mae' => 'Cantina - ',
            'Titulo_Pagina' => 'Todos os produtos',
            'titulo'    => 'Listagem dos produtos',
            'subtitulo' => 'Todos os produtos da sua cantina.',
            'view'      => 'listar',
            'flash'     => $mensagem,
            'itens'     => $item,
            'acolhidos' => $acolhidos,
        ]);
    }

    // REMOVER
    public function getRemoverProduto ($request, $response, $args)
    {
        if($this->acesso_usuario['cantina']['produtos']['d'] != 'on'){
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
        if($this->acesso_usuario['cantina']['produtos']['c'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

	    $item = Produtos::create([
	    	'nome' => $request->getParam('nome'),
            'valor_unitario' => $request->getParam('valor_unitario'),
            'status' => $request->getParam('status'),
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
        if($this->acesso_usuario['cantina']['produtos']['u'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

    	$item = Produtos::find($args['id']);
    	$item->update([
            'nome' => $request->getParam('nome'),
            'valor_unitario' => $request->getParam('valor_unitario'),
            'status' => $request->getParam('status'),
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
##### LANÇAMENTOS
#####
    public function lancamentos ($request, $response, $args)
    {   
        if($this->acesso_usuario['cantina']['lancamentos']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $itens = Lancamentos::orderBy('data','DESC')->get()->toArray();
        $acolhidos = Acolhidos::orderBy('nome','ASC')->get()->toArray();
        
        $itens_lancamento = ItensLancamento::orderBy('id','ASC')->get()->toArray();

        $produtos = Produtos::where('status', 1)->orderBy('nome','ASC')->get()->toArray();

        $contas_bancarias = ContasBancarias::get()->toArray();

        for($i = 0; $i < count($itens); $i++){
            $id_lancamento = $itens[$i]['id'];
            $acolhido[$id_lancamento] = Acolhidos::find($itens[$i]['acolhido']);
            $total_lancamento = $itens[$i]['valor_total'];
            $total_lancamentos += $total_lancamento;

            $lancamentos_baixados[$id_lancamento] = BaixaLancamentos::where('lancamento', $id_lancamento)->sum('valor_pagamento');
            $this->view->offsetSet("lancamentos_baixados", $lancamentos_baixados);

            $this->view->offsetSet("total_lancamentos", $total_lancamentos);
            $this->view->offsetSet("acolhido", $acolhido);
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'cantina/lancamentos/listar.html', [
            'Titulo_Pagina_Mae' => 'Cantina - ',
            'Titulo_Pagina' => 'Lançamentos',
            'titulo' => 'Listagem dos lançamentos',
            'subtitulo' => 'Todos os lançamentos feitos na sua cantina.',
            'view' => 'listar',
            'flash' => $mensagem,
            'itens' => $itens,
            'itens_lancamento' => $itens_lancamento,
            'produtos' => $produtos,
            'contas_bancarias' => $contas_bancarias,
            'acolhidos' => $acolhidos,
        ]);
    }

    public function postCadastrarLancamento ($request, $response, $args)
    {
        if($this->acesso_usuario['cantina']['lancamentos']['c'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $data_compra = explode('/', $request->getParam('data_compra'));
        $data_compra = $data_compra[2].'-'.$data_compra[1].'-'.$data_compra[0];

        $valor_total = $request->getParam('valor_total');

        $item = Lancamentos::create([
            'data' => $request->getParam('data'), 
            'acolhido' => $request->getParam('acolhido'),
            'valor_total' => $valor_total, 
            'observacoes' => $request->getParam('observacoes'), 
            'status' => $request->getParam('status'),
            'usuario' => $_SESSION['Auth']
        ]);
        
        // $valor_total = 0;

        if($item)
        {
            $error_itens = 0;
            for($i = 0; $i < count($request->getParam('product')); $i++){
                $produto = $request->getParam('product')[$i];
                $qtd = $request->getParam('qty')[$i];
                $valor_unitario = Produtos::find($produto);
                $valor_unitario = $valor_unitario->valor_unitario;
                $valor_total += $valor_unitario * $qtd;

                // CADASTRA OS ITENS DA COMPRA
                $itens_compra = ItensLancamento::create([
                    'lancamento' => $item->id,
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

            if($error_itens == 1){
                $this->flash->addMessage('error', 'Erro ao incluir os itens deste lançamento!');
            }
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao adicionar lançamento!');
        }

        if($request->getParam('status') == 1){
            $acolhido = Acolhidos::find($request->getParam('acolhido'));
            $movimento = Movimentos::create([
                'num_nf' => '',
                'descricao' => '[LANÇAMENTO] #'.$item->id.' - '.$acolhido['nome'],
                'categoria' => 99,
                'data_prevista' => $request->getParam('data'),
                'data_efetuada' => $request->getParam('data'),
                'valor_previsto' => $valor_total,
                'valor_efetuado' => $valor_total,
                'acolhido' => $request->getParam('acolhido'),
                'cantina_lancamento' => $item->id,
                'tipo' => 1,
                'status' => 1,
            ]);
        }

        $item->update([
            'valor_total' => $valor_total
        ]);

        if(!$item) {
            $this->flash->addMessage('error', 'Erro ao adicionar lançamento!');
        }
        else
        {
            $this->flash->addMessage('success', 'Lançamento adicionado com sucesso!');
        }

        return $response->withRedirect($this->router->pathFor('lancamentos'));
    }

    public function getEditarLancamento ($request, $response, $args)
    {   
        if($this->acesso_usuario['cantina']['lancamentos']['u'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Lancamentos::find($args['id']);
        $acolhido = Acolhidos::find($item['acolhido']);
        $itens_lancamento = ItensLancamento::where('lancamento', $args['id'])->get()->toArray();
        $produtos = Produtos::where('status', 1)->orderBy('nome','ASC')->get()->toArray();

        for($i = 0; $i < count($itens_lancamento); $i++){
            $id_item = $itens_lancamento[$i]['id'];
            $produto[$id_item] = Produtos::find($itens_lancamento[$i]['produto']);

            $this->view->offsetSet("produto", $produto);
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'cantina/lancamentos/editar.html', [
            'Titulo_Pagina_Mae' => 'Cantina - ',
            'Titulo_Pagina' => 'Editar lançamento',
            'titulo' => 'Editar lançamento',
            // 'subtitulo' => 'Todos os lançamentos feitos na sua cantina.',
            'view' => 'listar',
            'flash' => $mensagem,
            'item' => $item,
            'itens_lancamento' => $itens_lancamento,
            'acolhido' => $acolhido,
            'produtos' => $produtos,
        ]);
    }

    public function postEditarLancamento ($request, $response, $args)
    {
        if($this->acesso_usuario['cantina']['lancamentos']['u'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $data_compra = explode('/', $request->getParam('data_compra'));
        $data_compra = $data_compra[2].'-'.$data_compra[1].'-'.$data_compra[0];

        $item = Lancamentos::find($args['id']);
        $item->update([
            'data' => $request->getParam('data'), 
            'acolhido' => $request->getParam('acolhido'),
            'valor_total' => $request->getParam('valor_total'), 
            'observacoes' => $request->getParam('observacoes'), 
            'status' => 0,
            'usuario' => $_SESSION['Auth']
        ]);
        
        $valor_total = 0;

        // limpa os itens do lançamento
        $itens_lancamento = ItensLancamento::where('lancamento', $args['id'])->get()->toArray();
        for ($il=0; $il < count($itens_lancamento) ; $il++) { 
            $item_lancamento = ItensLancamento::find($itens_lancamento[$il]['id']);
            $item_lancamento->delete();
        }

        if($item)
        {
            $error_itens = 0;
            for($i = 0; $i < count($request->getParam('product')); $i++){
                $produto = $request->getParam('product')[$i];
                $qtd = $request->getParam('qty')[$i];
                $valor_unitario = Produtos::find($produto);
                $valor_unitario = $valor_unitario->valor_unitario;
                $valor_total += $valor_unitario * $qtd;

                // CADASTRA OS ITENS DA COMPRA
                $itens_compra = ItensLancamento::create([
                    'lancamento' => $item->id,
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

            if($error_itens == 1){
                $this->flash->addMessage('error', 'Erro ao incluir os itens deste lançamento!');
            }
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao adicionar lançamento!');
        }

        $item->update([
            'valor_total' => $valor_total
        ]);

        if(!$item) {
            $this->flash->addMessage('error', 'Erro!!');
        }
        else
        {
            $this->flash->addMessage('success', 'Lançamento editado com sucesso!');
        }

        return $response->withRedirect($this->router->pathFor('editar_lancamento', ['id' => $args['id']]));
    }

    // BAIXAR/LIQUIDAR/CONFIRMAR PAGAMENTO DO LANÇAMENTO
    public function postBaixarLancamento ($request, $response, $args)
    {
        // if($this->acesso_usuario['cantina']['baixar']['c'] != 'on'){
        //     return $this->container->view->render($response->withStatus(403), 'error/403.html');
        // }

        $lancamento = Lancamentos::find($args['id']);

        if($lancamento['status'] == 2){
            $lancamentos_baixados = BaixaLancamentos::where('lancamento', $args['id'])->sum('valor_pagamento');
            $valor_total = $lancamento['valor_total'] - $lancamentos_baixados;
        } else {
            $valor_total = $lancamento['valor_total'];
        }

        if($request->getParam('valor') < $valor_total){
            $status = 2;
        } else {
            $status = 1;
        }

        $lancamento->update([
            'status' => $status
        ]);

        if($lancamento)
        {
            $data_pagamento = explode('/', $request->getParam('data_pagamento'));
            $data_pagamento = $data_pagamento[2].'-'.$data_pagamento[1].'-'.$data_pagamento[0];

            $baixaLancamento = BaixaLancamentos::create([
                'lancamento' => $args['id'],
                'data_pagamento' => $data_pagamento,
                'valor_pagamento' => $request->getParam('valor'),
                'observacoes' => $request->getParam('observacoes'),
                'usuario' => $_SESSION['Auth'],
            ]);

            $acolhido = Acolhidos::find($lancamento['acolhido']);

            $movimento = Movimentos::create([
                'num_nf' => '',
                'descricao' => '[LANÇAMENTO] #'.$args['id'].' - '.$acolhido['nome'],
                'categoria' => 99,
                'data_prevista' => $lancamento['data'],
                'data_efetuada' => $data_pagamento,
                'valor_previsto' => $valor_total,
                'valor_efetuado' => $request->getParam('valor'),
                'acolhido' => $lancamento['acolhido'],
                'cantina_lancamento' => $args['id'],
                'tipo' => 1,
                'status' => 1,
            ]);

            if($movimento)
            {
                $this->flash->addMessage('success', 'Todos os dados do lançamento selecionado foram atualizados!');
            }
            else
            {
                $this->flash->addMessage('error', 'Erro ao gerar movimento no Contas a Pagar!');
            }
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao dar baixa no lançamento!');
        }

        return $response->withRedirect($this->router->pathFor('lancamentos'));
    }

    // BAIXAR/LIQUIDAR/CONFIRMAR PAGAMENTO DOS LANÇAMENTOS SELECIONADOS
    public function getBaixarLancamentosSelecionados ($request, $response, $args)
    {
        // if($this->acesso_usuario['cantina']['baixar']['c'] != 'on'){
        //     return $this->container->view->render($response->withStatus(403), 'error/403.html');
        // }

        $ids = explode(',', $request->getParam('id'));
        $select = Lancamentos::whereIn('id', $ids)->get()->toArray();

        for($i = 0; $i < count($select); $i++){

            $lancamento = Lancamentos::find($select[$i]['id']);
            $lancamento->update([
                'status' => 1
            ]);

            if($lancamento)
            {
                $acolhido = Acolhidos::find($select[$i]['acolhido']);

                $movimento = Movimentos::create([
                    'num_nf' => '',
                    'descricao' => '[LANÇAMENTO] #'.$select[$i]['id'].' - '.$acolhido['nome'],
                    'categoria' => 99,
                    'data_prevista' => $select[$i]['data'],
                    'data_efetuada' => date('Y-m-d'),
                    'valor_previsto' => $select[$i]['valor_total'],
                    'valor_efetuado' => $select[$i]['valor_total'],
                    'acolhido' => $select[$i]['acolhido'],
                    'cantina_lancamento' => $select[$i]['id'],
                    'tipo' => 1,
                    'status' => 1,
                ]);

                if($movimento)
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }

        }
    }

    public function getRemoverLancamento ($request, $response, $args)
    {
        if($this->acesso_usuario['cantina']['lancamentos']['d'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Lancamentos::find($args['id']);

        if($item->delete())
        {
            $remove_itens = ItensLancamento::where('lancamento', $args['id'])->delete();

            if($item['status'] == 1)
            {
                $movimento = Movimentos::where('cantina_lancamento', $args['id'])->delete();
                if($movimento)
                {
                    return true;
                } else {
                    return false;
                }
            }
        }
        else
        {
            return false;
        }
    }

    // REMOVER LANÇAMENTOS SELECIONADOS
    public function getRemoverLancamentosSelecionados ($request, $response, $args)
    {
        if($this->acesso_usuario['cantina']['lancamentos']['d'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $ids = explode(',', $request->getParam('id'));
        $select = Lancamentos::whereIn('id', $ids)->get()->toArray();

        for($i = 0; $i < count($select); $i++){

            $itens_lancamento = ItensLancamento::where('lancamento', $select[$i]['id'])->delete();

        }

        $item = Lancamentos::whereIn('id', $ids)->delete();

        if($item){
            return true;
        } else {
            return false;
        }
    }

    public function getRemoverItemLancamento ($request, $response, $args)
    {
        if($this->acesso_usuario['cantina']['lancamentos']['d'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = ItensLancamento::find($args['id']);

        if($item->delete())
        {
           $this->flash->addMessage('success', 'Item removido com sucesso deste lançamento!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao remover item do lançamento!');
        }

        return $response->withRedirect($this->router->pathFor('editar_lancamento', ['id' => $item['lancamento']]));
    }

    public function lancamentosDia ($request, $response, $args)
    {   
        if($this->acesso_usuario['cantina']['lancamentos']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $itens = Lancamentos::whereDay('created_at', date('d'))->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->orderBy('data','DESC')->get()->toArray();
        $acolhidos = Acolhidos::orderBy('nome','ASC')->get()->toArray();
        
        $itens_lancamento = ItensLancamento::orderBy('id','ASC')->get()->toArray();

        $produtos = Produtos::where('status', 1)->orderBy('nome','ASC')->get()->toArray();

        for($i = 0; $i < count($itens); $i++){
            $id_lancamento = $itens[$i]['id'];
            $acolhido[$id_lancamento] = Acolhidos::find($itens[$i]['acolhido']);
            $total_lancamento = $itens[$i]['valor_total'];
            $total_lancamentos += $total_lancamento;

            $this->view->offsetSet("total_lancamentos", $total_lancamentos);
            $this->view->offsetSet("acolhido", $acolhido);
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'cantina/lancamentos/listar.html', [
            'Titulo_Pagina_Mae' => 'Cantina - ',
            'Titulo_Pagina' => 'Lançamentos',
            'titulo' => 'Listagem dos lançamentos do dia ' . date('d/m/Y'),
            'subtitulo' => 'Todos os lançamentos deste acolhido na sua cantina neste dia.',
            'view' => 'listar',
            'flash' => $mensagem,
            'itens' => $itens,
            'itens_lancamento' => $itens_lancamento,
            'produtos' => $produtos,
            'acolhidos' => $acolhidos,
        ]);
    }

    public function lancamentos7Dias ($request, $response, $args)
    {   
        if($this->acesso_usuario['cantina']['lancamentos']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $de = date('Y-m-d', strtotime('-7 days'));
        $ate = date('Y-m-d', strtotime('+7 days'));

        $itens = Lancamentos::whereBetween('created_at', [$de, $ate])->orderBy('data','DESC')->get()->toArray();
        $acolhidos = Acolhidos::orderBy('nome','ASC')->get()->toArray();
        
        $itens_lancamento = ItensLancamento::orderBy('id','ASC')->get()->toArray();

        $produtos = Produtos::where('status', 1)->orderBy('nome','ASC')->get()->toArray();

        for($i = 0; $i < count($itens); $i++){
            $id_lancamento = $itens[$i]['id'];
            $acolhido[$id_lancamento] = Acolhidos::find($itens[$i]['acolhido']);
            $total_lancamento = $itens[$i]['valor_total'];
            $total_lancamentos += $total_lancamento;

            $this->view->offsetSet("total_lancamentos", $total_lancamentos);
            $this->view->offsetSet("acolhido", $acolhido);
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'cantina/lancamentos/listar.html', [
            'Titulo_Pagina_Mae' => 'Cantina - ',
            'Titulo_Pagina' => 'Lançamentos',
            'titulo' => 'Listagem dos lançamentos nos últimos 7 dias ' . date('d/m/Y', strtotime($de)) . ' a ' . date('d/m/Y', strtotime($ate)),
            'subtitulo' => 'Todos os lançamentos da sua cantina nos últimos 7 dias.',
            'view' => 'listar',
            'flash' => $mensagem,
            'itens' => $itens,
            'itens_lancamento' => $itens_lancamento,
            'produtos' => $produtos,
            'acolhidos' => $acolhidos,
        ]);
    }

    public function lancamentosMes ($request, $response, $args)
    {   
        if($this->acesso_usuario['cantina']['lancamentos']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $itens = Lancamentos::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->orderBy('data','DESC')->get()->toArray();
        $acolhidos = Acolhidos::orderBy('nome','ASC')->get()->toArray();
        
        $itens_lancamento = ItensLancamento::orderBy('id','ASC')->get()->toArray();

        $produtos = Produtos::where('status', 1)->orderBy('nome','ASC')->get()->toArray();

        for($i = 0; $i < count($itens); $i++){
            $id_lancamento = $itens[$i]['id'];
            $acolhido[$id_lancamento] = Acolhidos::find($itens[$i]['acolhido']);
            $total_lancamento = $itens[$i]['valor_total'];
            $total_lancamentos += $total_lancamento;

            $this->view->offsetSet("total_lancamentos", $total_lancamentos);
            $this->view->offsetSet("acolhido", $acolhido);
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'cantina/lancamentos/listar.html', [
            'Titulo_Pagina_Mae' => 'Cantina - ',
            'Titulo_Pagina' => 'Lançamentos',
            'titulo' => 'Listagem dos lançamentos do mês ' . date('m/Y'),
            'subtitulo' => 'Todos os lançamentos da sua cantina neste mês.',
            'view' => 'listar',
            'flash' => $mensagem,
            'itens' => $itens,
            'itens_lancamento' => $itens_lancamento,
            'produtos' => $produtos,
            'acolhidos' => $acolhidos,
        ]);
    }

    public function lancamentosPeriodo ($request, $response, $args)
    {   
        if($this->acesso_usuario['cantina']['lancamentos']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $de = explode("/", $request->getParam('de'));
        $de = $de[2] . "-" . $de[1] . "-" . $de[0] . " 00:00:00";
        $de = date($de);
        $ate = explode("/", $request->getParam('ate'));
        $ate = $ate[2] . "-" . $ate[1] . "-" . $ate[0] . " 23:59:59";
        $ate = date($ate);
        
        $itens = Lancamentos::whereBetween('created_at', [$de, $ate])->orderBy('data','DESC')->get()->toArray();
        $acolhidos = Acolhidos::orderBy('nome','ASC')->get()->toArray();
        
        $itens_lancamento = ItensLancamento::orderBy('id','ASC')->get()->toArray();

        $produtos = Produtos::where('status', 1)->orderBy('nome','ASC')->get()->toArray();

        for($i = 0; $i < count($itens); $i++){
            $id_lancamento = $itens[$i]['id'];
            $acolhido[$id_lancamento] = Acolhidos::find($itens[$i]['acolhido']);
            $total_lancamento = $itens[$i]['valor_total'];
            $total_lancamentos += $total_lancamento;

            $this->view->offsetSet("total_lancamentos", $total_lancamentos);
            $this->view->offsetSet("acolhido", $acolhido);
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'cantina/lancamentos/listar.html', [
            'Titulo_Pagina_Mae' => 'Cantina - ',
            'Titulo_Pagina' => 'Lançamentos',
            'titulo' => 'Listagem dos lançamentos no período de ' . $request->getParam('de') . ' até ' . $request->getParam('ate'),
            'subtitulo' => 'Todos os lançamentos na sua cantina neste período.',
            'view' => 'listar',
            'flash' => $mensagem,
            'itens' => $itens,
            'itens_lancamento' => $itens_lancamento,
            'produtos' => $produtos,
            'acolhidos' => $acolhidos,
        ]);
    }

#####
##### LANÇAMENTOS ACOLHIDO
#####
    public function lancamentosAcolhido ($request, $response, $args)
    {   
        if($this->acesso_usuario['cantina']['lancamentos']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $acolhido = Acolhidos::find($args['id']);
        $acolhidos = Acolhidos::orderBy('nome', 'ASC')->get()->toArray();
        $itens = Lancamentos::where('acolhido', $args['id'])->orderBy('data','DESC')->get()->toArray();
        $lancamentos_em_aberto = Lancamentos::where('acolhido', $args['id'])->where('status', '!=', 1)->sum('valor_total');
        
        $itens_lancamento = ItensLancamento::orderBy('id','ASC')->get()->toArray();

        $produtos = Produtos::where('status', 1)->orderBy('nome','ASC')->get()->toArray();

        for($i = 0; $i < count($itens); $i++){
            $id_lancamento = $itens[$i]['id'];
            $acolhido[$id_lancamento] = Acolhidos::find($itens[$i]['acolhido']);
            $total_lancamento = $itens[$i]['valor_total'];
            $total_lancamentos += $total_lancamento;
            
            $lancamentos_baixados = BaixaLancamentos::where('lancamento', $id_lancamento)->sum('valor_pagamento');
            $this->view->offsetSet("lancamentos_baixados", $lancamentos_baixados);

            $this->view->offsetSet("total_lancamentos", $total_lancamentos);
            $this->view->offsetSet("acolhido", $acolhido);
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'cantina/lancamentos/listarAcolhido.html', [
            'Titulo_Pagina_Mae' => 'Cantina - ',
            'Titulo_Pagina' => 'Lançamentos por acolhido',
            'titulo' => 'Listagem dos lançamentos',
            'subtitulo' => 'Todos os lançamentos deste acolhido na sua cantina.',
            'view' => 'listar',
            'flash' => $mensagem,
            'itens' => $itens,
            'itens_lancamento' => $itens_lancamento,
            'produtos' => $produtos,
            'acolhidos' => $acolhidos,
            'acolhido' => $acolhido,
            'lancamentos_em_aberto' => $lancamentos_em_aberto,
        ]);
    }

    public function lancamentosAcolhidoDia ($request, $response, $args)
    {   
        if($this->acesso_usuario['cantina']['lancamentos']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $acolhido = Acolhidos::find($args['id']);
        $acolhidos = Acolhidos::orderBy('nome', 'ASC')->get()->toArray();
        $itens = Lancamentos::where('acolhido', $args['id'])->whereDay('created_at', date('d'))->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->orderBy('data','DESC')->get()->toArray();
        $lancamentos_em_aberto = Lancamentos::where('acolhido', $args['id'])->whereDay('created_at', date('d'))->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->where('status', 0)->sum('valor_total');
        
        $itens_lancamento = ItensLancamento::orderBy('id','ASC')->get()->toArray();

        $produtos = Produtos::where('status', 1)->orderBy('nome','ASC')->get()->toArray();

        for($i = 0; $i < count($itens); $i++){
            $id_lancamento = $itens[$i]['id'];
            $acolhido[$id_lancamento] = Acolhidos::find($itens[$i]['acolhido']);
            $total_lancamento = $itens[$i]['valor_total'];
            $total_lancamentos += $total_lancamento;

            $this->view->offsetSet("total_lancamentos", $total_lancamentos);
            $this->view->offsetSet("acolhido", $acolhido);
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'cantina/lancamentos/listarAcolhido.html', [
            'Titulo_Pagina_Mae' => 'Cantina - ',
            'Titulo_Pagina' => 'Lançamentos por acolhido',
            'titulo' => 'Listagem dos lançamentos do dia ' . date('d/m/Y'),
            'subtitulo' => 'Todos os lançamentos deste acolhido na sua cantina neste dia.',
            'view' => 'listar',
            'flash' => $mensagem,
            'itens' => $itens,
            'itens_lancamento' => $itens_lancamento,
            'produtos' => $produtos,
            'acolhidos' => $acolhidos,
            'acolhido' => $acolhido,
            'lancamentos_em_aberto' => $lancamentos_em_aberto,
        ]);
    }

    public function lancamentosAcolhido7Dias ($request, $response, $args)
    {   
        if($this->acesso_usuario['cantina']['lancamentos']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $de = date('Y-m-d', strtotime('-7 days'));
        $ate = date('Y-m-d', strtotime('+7 days'));

        $itens = Lancamentos::where('acolhido', $args['id'])->whereBetween('created_at', [$de, $ate])->orderBy('data','DESC')->get()->toArray();
        $acolhidos = Acolhidos::orderBy('nome','ASC')->get()->toArray();
        
        $itens_lancamento = ItensLancamento::orderBy('id','ASC')->get()->toArray();

        $produtos = Produtos::where('status', 1)->orderBy('nome','ASC')->get()->toArray();

        for($i = 0; $i < count($itens); $i++){
            $id_lancamento = $itens[$i]['id'];
            $acolhido[$id_lancamento] = Acolhidos::find($itens[$i]['acolhido']);
            $total_lancamento = $itens[$i]['valor_total'];
            $total_lancamentos += $total_lancamento;

            $this->view->offsetSet("total_lancamentos", $total_lancamentos);
            $this->view->offsetSet("acolhido", $acolhido);
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'cantina/lancamentos/listar.html', [
            'Titulo_Pagina_Mae' => 'Cantina - ',
            'Titulo_Pagina' => 'Lançamentos',
            'titulo' => 'Listagem dos lançamentos nos últimos 7 dias ' . date('d/m/Y', strtotime($de)) . ' a ' . date('d/m/Y', strtotime($ate)),
            'subtitulo' => 'Todos os lançamentos deste acolhido na sua cantina nos últimos 7 dias.',
            'view' => 'listar',
            'flash' => $mensagem,
            'itens' => $itens,
            'itens_lancamento' => $itens_lancamento,
            'produtos' => $produtos,
            'acolhidos' => $acolhidos,
        ]);
    }

    public function lancamentosAcolhidoMes ($request, $response, $args)
    {   
        if($this->acesso_usuario['cantina']['lancamentos']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $acolhido = Acolhidos::find($args['id']);
        $acolhidos = Acolhidos::orderBy('nome', 'ASC')->get()->toArray();
        $itens = Lancamentos::where('acolhido', $args['id'])->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->orderBy('data','DESC')->get()->toArray();
        $lancamentos_em_aberto = Lancamentos::where('acolhido', $args['id'])->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->where('status', 0)->sum('valor_total');
        
        $itens_lancamento = ItensLancamento::orderBy('id','ASC')->get()->toArray();

        $produtos = Produtos::where('status', 1)->orderBy('nome','ASC')->get()->toArray();

        for($i = 0; $i < count($itens); $i++){
            $id_lancamento = $itens[$i]['id'];
            $acolhido[$id_lancamento] = Acolhidos::find($itens[$i]['acolhido']);
            $total_lancamento = $itens[$i]['valor_total'];
            $total_lancamentos += $total_lancamento;

            $this->view->offsetSet("total_lancamentos", $total_lancamentos);
            $this->view->offsetSet("acolhido", $acolhido);
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'cantina/lancamentos/listarAcolhido.html', [
            'Titulo_Pagina_Mae' => 'Cantina - ',
            'Titulo_Pagina' => 'Lançamentos por acolhido',
            'titulo' => 'Listagem dos lançamentos do mês ' . date('m/Y'),
            'subtitulo' => 'Todos os lançamentos deste acolhido na sua cantina neste mês.',
            'view' => 'listar',
            'flash' => $mensagem,
            'itens' => $itens,
            'itens_lancamento' => $itens_lancamento,
            'produtos' => $produtos,
            'acolhidos' => $acolhidos,
            'acolhido' => $acolhido,
            'lancamentos_em_aberto' => $lancamentos_em_aberto,
        ]);
    }

    public function lancamentosAcolhidoPeriodo ($request, $response, $args)
    {   
        if($this->acesso_usuario['cantina']['lancamentos']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $de = explode("/", $request->getParam('de'));
        $de = $de[2] . "-" . $de[1] . "-" . $de[0] . " 00:00:00";
        $de = date($de);
        $ate = explode("/", $request->getParam('ate'));
        $ate = $ate[2] . "-" . $ate[1] . "-" . $ate[0] . " 23:59:59";
        $ate = date($ate);

        $acolhido = Acolhidos::find($args['id']);
        $acolhidos = Acolhidos::orderBy('nome', 'ASC')->get()->toArray();
        $itens = Lancamentos::where('acolhido', $args['id'])->whereBetween('created_at', [$de, $ate])->orderBy('data','DESC')->get()->toArray();
        $lancamentos_em_aberto = Lancamentos::where('acolhido', $args['id'])->whereBetween('created_at', [$de, $ate])->where('status', 0)->sum('valor_total');
        
        $itens_lancamento = ItensLancamento::orderBy('id','ASC')->get()->toArray();

        $produtos = Produtos::where('status', 1)->orderBy('nome','ASC')->get()->toArray();

        for($i = 0; $i < count($itens); $i++){
            $id_lancamento = $itens[$i]['id'];
            $acolhido[$id_lancamento] = Acolhidos::find($itens[$i]['acolhido']);
            $total_lancamento = $itens[$i]['valor_total'];
            $total_lancamentos += $total_lancamento;

            $this->view->offsetSet("total_lancamentos", $total_lancamentos);
            $this->view->offsetSet("acolhido", $acolhido);
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'cantina/lancamentos/listarAcolhido.html', [
            'Titulo_Pagina_Mae' => 'Cantina - ',
            'Titulo_Pagina' => 'Lançamentos por acolhido',
            'titulo' => 'Listagem dos lançamentos no período de ' . $request->getParam('de') . ' até ' . $request->getParam('ate'),
            'subtitulo' => 'Todos os lançamentos deste acolhido na sua cantina neste período.',
            'view' => 'listar',
            'flash' => $mensagem,
            'itens' => $itens,
            'itens_lancamento' => $itens_lancamento,
            'produtos' => $produtos,
            'acolhidos' => $acolhidos,
            'acolhido' => $acolhido,
            'lancamentos_em_aberto' => $lancamentos_em_aberto,
        ]);
    }

#####
##### LIMITE CANTINA
#####
    public function postLimiteCantina ($request, $response, $args)
    {
        $item = Acolhidos::find($args['id']);
        $item->update([
            'limite_cantina' => $request->getParam('limite_cantina')
        ]);

        if($item)
        {
            $this->flash->addMessage('success', 'Limite de consumo na cantina atualizado!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao atualizar limite de consumo na cantina!');
        }

        return $response->withRedirect($this->router->pathFor('lancamentos_acolhido', ['id' => $args['id']]));
    }
}