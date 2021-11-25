<?php

namespace App\Controllers;

use App\Controllers\Controller ;
use Respect\Validation\Validator as validate;
use Slim\Views\Twig as View;

use App\Models\Unidades;
use App\Models\Quartos;
use App\Models\Acolhidos;
use App\Models\Acolhimentos;

class UnidadesController extends Controller
{

	// EXIBE A LISTAGEM DAS UNIDADES
    public function listar ($request, $response, $args)
    {   
        if($this->acesso_usuario['unidades']['index']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Unidades::orderBy('id','ASC')->get()->toArray();
        $quartos = Quartos::orderBy('id','ASC')->get()->toArray();

        for($i = 0; $i <= (count($item)); $i++){
            $id_unidade = $item[$i]['id'];
            $vagas_ocupadas[$id_unidade] = Acolhimentos::whereIn('status', [0, 1])->where('unidade', $id_unidade)->count();
            $this->view->offsetSet("vagas_ocupadas", $vagas_ocupadas);

            $vagas[$id_unidade] = Quartos::where('unidade', $id_unidade)->sum('vagas');
            $this->view->offsetSet("vagas", $vagas);
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'unidades/listar.html', [
            'Titulo_Pagina' => 'Unidades',
            'titulo'    => 'Listagem das unidades',
            'subtitulo' => 'Todas as unidades cadastradas no sistema',
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
    public function getRemover ($request, $response, $args)
    {
        if($this->acesso_usuario['unidades']['index']['d'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Unidades::find($args['id']);

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
	    $item = Unidades::create([
	    	'nome' 		=> $request->getParam('nome'),
	       	'publico' 	=> $request->getParam('publico'),
	       	'vagas' 	=> $request->getParam('vagas'),
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
    	$item = Unidades::find($args['id']);
    	$item->update([
			'nome'       => $request->getParam('nome'),
            'publico'   => $request->getParam('publico'),
            'vagas'     => $request->getParam('vagas'),
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

    // ACESSA A UNIDADE
    public function getAcessar ($request, $response, $args)
    {
        if(!isset($_SESSION['Unidade'])){
            $_SESSION['Unidade'] = $args['id'];
        }

        if(isset($_SESSION['Unidade']) AND $_SESSION['Unidade'] != $args['id']){
            $_SESSION['Unidade'] = $args['id'];
        } else {
            $_SESSION['Unidade'] = $_SESSION['Unidade'];
        }

        return true;
    }

    // SAI DA UNIDADE
    public function getSair ($request, $response, $args)
    {
        if(isset($_SESSION['Unidade'])){
            unset($_SESSION['Unidade']);
        }

        return true;
    }

    // ACESSA OS QUARTOS DA UNIDADE
    public function getQuartosUnidade ($request, $response, $args)
    {
        $unidade = Unidades::find($_SESSION['Unidade']);
        $item = Quartos::where('unidade', $_SESSION['Unidade'])->get()->toArray();

        for($i = 0; $i <= (count($item)); $i++){
            $id_quarto = $item[$i]['id'];
            $vagas_ocupadas[$id_quarto] = Acolhimentos::whereIn('status', [0, 1])->where('unidade', $_SESSION['Unidade'])->where('quarto', $id_quarto)->count();
            $this->view->offsetSet("vagas_ocupadas", $vagas_ocupadas);
        }


        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'unidades/quartos.html', [
            'Titulo_Pagina' => 'Quartos',
            'titulo'    => 'Listagem dos quartos - ' . $unidade['nome'],
            'subtitulo' => 'Todos os quartos cadastrados para esta unidade',
            'view'      => 'listar',
            'unidade'   => $unidade,
            'flash'     => $mensagem,
            'itens'     => $item
        ]);
    }

    // ACESSA TODOS OS QUARTOS DO SISTEMA
    public function getQuartos ($request, $response, $args)
    {
        $item = Quartos::get()->toArray();

        for($i = 0; $i <= (count($item)); $i++){
            $id_quarto = $item[$i]['id'];
            $vagas_ocupadas[$id_quarto] = Acolhimentos::whereIn('status', [0, 1])->where('quarto', $id_quarto)->count();
            $this->view->offsetSet("vagas_ocupadas", $vagas_ocupadas);
        }


        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'unidades/quartos.html', [
            'Titulo_Pagina' => 'Quartos',
            'titulo'    => 'Listagem de todos os quartos',
            'subtitulo' => 'Todos os quartos cadastrados no sistema',
            'view'      => 'listar',
            'unidade'   => $unidade,
            'flash'     => $mensagem,
            'itens'     => $item
        ]);
    }

    public function postCadastrarQuarto ($request, $response, $args)
    {
        $item = Quartos::create([
            'nome'      => $request->getParam('nome'),
            'unidade'   => $_SESSION['Unidade'],
            'vagas'     => $request->getParam('vagas'),
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

    public function postEditarQuarto ($request, $response, $args)
    {
        $item = Quartos::find($args['id']);
        $item->update([
            'nome'       => $request->getParam('nome'),
            'vagas'     => $request->getParam('vagas'),
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

    public function getRemoverQuarto ($request, $response, $args)
    {
        if($this->acesso_usuario['unidades']['index']['d'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Quartos::find($args['id']);

        // verifica se possui acolhimentos ativos neste quarto
        $verifica_acolhimentos = Acolhimentos::whereIn('status', [0, 1])->where('quarto', $args['id'])->count();
        if($verifica_acolhimentos >= 1){
            echo 'erro';
            return false;
        } else {
            if($item->delete())
            {
                return true;
            }
            else
            {
                return false;
            }
        }        
    }

    public function buscar_quarto ($request, $response, $args)
    {
        $id = $request->getQueryParams()['id'];
        $data = Quartos::where('unidade', $id)->orderBy('nome','ASC')->get()->toArray();
        $array = [];
        for ($i=0; $i < count($data) ; $i++) {
            $id_quarto = $data[$i]['id'];
            $acolhimentos[$id_quarto] = Acolhimentos::whereIn('status', [0, 1])->where('quarto', $data[$i]['id'])->count();
            $vagas[$id_quarto] = $data[$i]['vagas'] - $acolhimentos[$id_quarto];
            if($vagas[$id_quarto] > 0){
                if($vagas[$id_quarto] > 1){
                    $vagas[$id_quarto] = $vagas[$id_quarto] . " vagas disponíveis";
                } else {
                    $vagas[$id_quarto] = $vagas[$id_quarto] . " vaga disponível";
                }
                $array[] = [
                    'id' => $id_quarto,
                    'vagas' => $vagas[$id_quarto],
                    'nome' => $data[$i]['nome'],
                ];
            }
        }

        echo json_encode($array);
    }
}