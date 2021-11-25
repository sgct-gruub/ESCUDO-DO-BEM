<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Respect\Validation\Validator as validate;
use Slim\Views\Twig as View;

use App\Models\TecnicosReferencia;
use App\Models\Acolhidos;
use App\Models\Acolhimentos;
use App\Models\Unidades;
use App\Models\Timeline;

class TecnicosReferenciaController extends Controller
{

	// EXIBE A LISTAGEM DOS TÉCNICOS DE REFERÊNCIA
    public function listar ($request, $response, $args)
    {   
        if($this->acesso_usuario['tecnicos_referencia']['index']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = TecnicosReferencia::orderBy('id','ASC')->get()->toArray();

        $acolhimentos = Acolhimentos::whereIn('status', [0, 1])->get()->toArray();
        $unidades = Unidades::orderBy('nome', 'ASC')->get()->toArray();

        for($i = 0; $i < count($acolhimentos); $i++){
            $id_acolhido = $acolhimentos[$i]['acolhido'];
            $acolhido[$id_acolhido] = Acolhidos::where('id', $acolhimentos[$i]['acolhido'])->first();
            $oficios = TecnicosReferencia::whereIn('acolhimentos', [$acolhimentos[$i]['id']])->get()->toArray();

            $this->view->offsetSet("acolhido", $acolhido);
            $this->view->offsetSet("oficios", $oficios);
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'tecnicos_referencia/listar.html', [
            'Titulo_Pagina' => 'Terapeutas',
            'titulo'    => 'Listagem dos terapeutas',
            'subtitulo' => 'Todos os terapeutas da sua instituição.',
            'view'      => 'listar',
            'flash'     => $mensagem,
            'itens'     => $item,
            'acolhimentos' => $acolhimentos,
            'unidades' => $unidades,
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
        if($this->acesso_usuario['tecnicos_referencia']['index']['d'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = TecnicosReferencia::find($args['id']);

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
        // $acolhimentos = implode(',', $request->getParam('acolhimentos'));

	    $item = TecnicosReferencia::create([
            // 'unidade' => 0,
            'referencia' => $request->getParam('referencia'),
            // 'acolhimentos' => $acolhimentos,
            'ligacoes' => $request->getParam('ligacoes'),
            // 'cor' => $request->getParam('cor'),
        ]);

        if($item)
        {
            foreach ($request->getParam('acolhimentos') as $key => $value) {
                $acolhimento = Acolhimentos::find($value);
                $acolhido = Acolhidos::find($acolhimento['acolhido']);
                $descricao = $acolhido['nome'] . ' foi incluído na Referência de ' . $request->getParam('referencia') . '. Ligações: ' . $request->getParam('ligacoes');
                $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
                $color = array_rand($colors);

                $timeline = Timeline::create([
                    'acolhimento' => $value,
                    'usuario' => $_SESSION['Auth'],
                    'titulo' => 'Terapeutas',
                    'descricao' => $descricao,
                    'color' => $color,
                    'icon' => 'fa fa-users',
                ]);
            }

            return true;
        }
        else
        {
            return false;
        }
    }

    // SALVA O REGISTRO NO BANCO DE DADOS
    public function postAcolhidos ($request, $response, $args)
    {
        $acolhimentos = implode(',', $request->getParam('acolhimentos'));
        $acolhimentos_old = explode(',', $request->getParam('acolhimentos_old'));

        $item = TecnicosReferencia::find($args['id']);

        $item->update([
            'acolhimentos' => $acolhimentos,
        ]);

        if($item)
        {
            foreach ($request->getParam('acolhimentos') as $key => $value) {
                $acolhimento = Acolhimentos::find($value);

                $acolhimento->update([
                    'tecnico_referencia' => $item->id
                ]);

                $acolhido = Acolhidos::find($acolhimento['acolhido']);

                if($acolhimento){
                if(!in_array($value, $acolhimentos_old)){
                    $descricao = $acolhido['nome'] . ' foi incluído na Referência de ' . $item->referencia;
                    $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
                    $color = array_rand($colors);

                    $timeline = Timeline::create([
                        'acolhimento' => $value,
                        'usuario' => $_SESSION['Auth'],
                        'titulo' => 'Terapeutas',
                        'descricao' => $descricao,
                        'color' => $color,
                        'icon' => 'mdi mdi-briefcase',
                    ]);
                }
                } else {
                    $this->flash->addMessage('error', 'Erro ao editar acolhimentos!');
                }
            }

            if($request->getParam('acolhimentos_old') != '' && $acolhimentos != $request->getParam('acolhimentos_old')){
            foreach ($acolhimentos_old as $key => $value) {
                if(!in_array($value, $request->getParam('acolhimentos'))){
                    $acolhimento = Acolhimentos::find($value);

                    $acolhimento->update([
                        'tecnico_referencia' => null
                    ]);

                    $acolhido = Acolhidos::find($acolhimento['acolhido']);
                    if($acolhimento){
                    $descricao = $acolhido['nome'] . ' foi retirado da Referência de ' . $item->referencia;
                    $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
                    $color = array_rand($colors);

                    $timeline = Timeline::create([
                        'acolhimento' => $value,
                        'usuario' => $_SESSION['Auth'],
                        'titulo' => 'Terapeutas',
                        'descricao' => $descricao,
                        'color' => $color,
                        'icon' => 'fa fa-users',
                    ]);
                    } else {
                        $this->flash->addMessage('error', 'Erro ao editar acolhimentos!');
                    }
                }
            }
            }

            $this->flash->addMessage('success', 'Acolhimento(s) referenciado(s) com sucesso!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao incluír terapeuta no(s) acolhimento(s) selecionado(s)!');
        }
        
        return $response->withRedirect($this->router->pathFor('tecnicos_referencia'));
    }

    // EDITA O REGISTRO
    public function postEditar ($request, $response, $args)
    {
        $acolhimentos = implode(',', $request->getParam('acolhimentos'));
        $acolhimentos_old = explode(',', $request->getParam('acolhimentos_old'));

        $item = TecnicosReferencia::find($args['id']);

    	$item->update([
            'unidade' => $request->getParam('unidade'),
            'referencia' => $request->getParam('referencia'),
            'acolhimentos' => $acolhimentos,
            'ligacoes' => $request->getParam('ligacoes'),
            'cor' => $request->getParam('cor'),
        ]);

        if($item)
        {
            foreach ($request->getParam('acolhimentos') as $key => $value) {
                $acolhimento = Acolhimentos::find($value);
                $acolhido = Acolhidos::find($acolhimento['acolhido']);

                if(!in_array($value, $acolhimentos_old)){
                    $descricao = $acolhido['nome'] . ' foi incluído na Referência de ' . $request->getParam('referencia') . '. Ligações: ' . $request->getParam('ligacoes');
                    $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
                    $color = array_rand($colors);

                    $timeline = Timeline::create([
                        'acolhimento' => $value,
                        'usuario' => $_SESSION['Auth'],
                        'titulo' => 'Terapeutas',
                        'descricao' => $descricao,
                        'color' => $color,
                        'icon' => 'mdi mdi-briefcase',
                    ]);
                }
            }

            foreach ($acolhimentos_old as $key => $value) {
                $acolhimento = Acolhimentos::find($value);
                $acolhido = Acolhidos::find($acolhimento['acolhido']);

                if(!in_array($value, $request->getParam('acolhimentos'))){
                    $descricao = $acolhido['nome'] . ' foi retirado da Referência de ' . $request->getParam('referencia');
                    $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
                    $color = array_rand($colors);

                    $timeline = Timeline::create([
                        'acolhimento' => $value,
                        'usuario' => $_SESSION['Auth'],
                        'titulo' => 'Terapeutas',
                        'descricao' => $descricao,
                        'color' => $color,
                        'icon' => 'fa fa-users',
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
}