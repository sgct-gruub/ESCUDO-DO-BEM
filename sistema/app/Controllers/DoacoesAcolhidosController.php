<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Respect\Validation\Validator as validate;
use Slim\Views\Twig as View;

use App\Models\Despensa\Compras;
use App\Models\Despensa\Doacoes;
use App\Models\Despensa\Produtos;
use App\Models\Despensa\Estoque;
use App\Models\Despensa\Saida;
use App\Models\Despensa\ItensCompra;
use App\Models\Despensa\ItensDoacao;
use App\Models\ContasBancarias;
use App\Models\Movimentos;
use App\Models\Fornecedores;
use App\Models\Doadores;
use App\Models\Acolhidos;
use App\Models\Acolhimentos;

class DoacoesAcolhidosController extends Controller
{

    public function listar ($request, $response, $args)
    {   
        if($this->acesso_usuario['despensa']['entrada']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        // $compras = Compras::orderBy('id','ASC')->get()->toArray();
        $acolhidos = Acolhidos::find($args['id']);
        $doacoes = Doacoes::where('acolhido', $args['id'])->get()->toArray();
        
        $itens_compra = ItensCompra::orderBy('id','ASC')->get()->toArray();
        $itens_doacao = ItensDoacao::orderBy('id','ASC')->get()->toArray();

        $produtos = Produtos::orderBy('id','ASC')->get()->toArray();

        // $contas_bancarias = ContasBancarias::orderBy('id','ASC')->get()->toArray();

        // for($i = 0; $i < count($compras); $i++){
        //     $id_compra = $compras[$i]['id'];
        //     $nome_fornecedor[$id_compra] = Fornecedores::find($compras[$i]['fornecedor']);

        //     $this->view->offsetSet("nome_fornecedor", $nome_fornecedor);
        // }

        // for($i = 0; $i < count($doacoes); $i++){
        //     $id_doacao = $doacoes[$i]['id'];
        //     if($doacoes[$i]['doador'] != NULL && $doacoes[$i]['doador'] != 0 && $doacoes[$i]['acolhido'] == 0 OR $doacoes[$i]['acolhido'] == NULL){
        //         $nome_doador[$id_doacao] = Doadores::find($doacoes[$i]['doador']);
        //     }
        //     if($doacoes[$i]['acolhido'] != NULL && $doacoes[$i]['acolhido'] != 0 && $doacoes[$i]['doador'] == 0 OR $doacoes[$i]['doador'] == NULL){
        //         $nome_doador[$id_doacao] = Acolhidos::find($doacoes[$i]['acolhido']);
        //     }
        //     $this->view->offsetSet("nome_doador", $nome_doador);
        // }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'acolhidos/listar_doacoes.html', [
            'Titulo_Pagina_Mae' => 'Despensa - ',
            'Titulo_Pagina' => 'Entrada de produtos',
            'titulo' => 'Listagem das entradas de produtos',
            'subtitulo' => 'Todas as compras ou doações de produtos para a sua instituição.',
            'view' => 'listar',
            'flash' => $mensagem,
            // 'compras' => $compras,
            'doacoes' => $doacoes,
            'itens_compra' => $itens_compra,
            'itens_doacao' => $itens_doacao,
            'produtos' => $produtos,
            // 'contas_bancarias' => $contas_bancarias,
            'acolhidos' => $acolhidos,
        ]);
    }

}