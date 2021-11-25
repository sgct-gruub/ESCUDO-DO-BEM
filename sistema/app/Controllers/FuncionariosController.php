<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Respect\Validation\Validator as validate;
use Slim\Views\Twig as View;

use App\Models\Funcionarios\Funcionarios;
use App\Models\Funcionarios\FuncionariosArquivos;
use App\Models\Funcionarios\FuncionariosAdvertencias;
use App\Models\Funcionarios\FuncionariosDadosRegistro;
use App\Models\Funcionarios\FuncionariosTimeline;

class FuncionariosController extends Controller
{

	// EXIBE A LISTAGEM DOS FUNCIONÁRIOS
    public function listar ($request, $response, $args)
    {   
        if($this->acesso_usuario['funcionarios']['index']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Funcionarios::orderBy('id','ASC')->get()->toArray();

        for($i = 0; $i < count($item); $i++){
            $dados_registro[$item[$i]['id']] = FuncionariosDadosRegistro::where('funcionario', $item[$i]['id'])->where('status', 1)->first();
            $advertencias[$item[$i]['id']] = FuncionariosAdvertencias::where('funcionario', $item[$i]['id'])->count();

            $this->view->offsetSet("dados_registro", $dados_registro);
            $this->view->offsetSet("advertencias", $advertencias);
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'funcionarios/listar.html', [
            'Titulo_Pagina' => 'Funcionários',
            'titulo'    => 'Listagem dos funcionários',
            'subtitulo' => 'Todos os funcionários da sua instituição.',
            'view'      => 'listar',
            'flash'     => $mensagem,
            'itens'     => $item
        ]);
    }

    // // EXIBE O FORMULÁRIO DE CADASTRO
    public function getCadastrar ($request, $response)
    {
    	return $this->view->render($response, 'funcionarios/form.html', [
        	'Titulo_Pagina' => 'Novo registro',
            'titulo' => 'Cadastrar novo funcionário',
            'subtitulo' => 'Preencha o formulário abaixo para inserir um novo funcionário',
        	'view' => 'cadastrar'
    	]);
    }

    // // EXIBE O FORMULÁRIO DE EDIÇÃO
    public function getEditar ($request, $response, $args)
    {
        if($this->acesso_usuario['funcionarios']['index']['u'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Funcionarios::find($args['id']);
    	$dados_registro = FuncionariosDadosRegistro::where('funcionario', $args['id'])->where('status', 1)->first();
        $arquivos = FuncionariosArquivos::orderBy('id','desc')->where('funcionario', $args['id'])->get()->toArray();
        $timeline = FuncionariosTimeline::where('funcionario', $args['id'])->orderBy('id', 'DESC')->get()->toArray();
        $advertencias = FuncionariosAdvertencias::where('funcionario', $args['id'])->orderBy('id', 'DESC')->get()->toArray();
    	
        $filhos = '';
        $quantidadeFilhos = '';
        $nome_filho = '';
        $data_nascimento_filho = '';
        $cpf_filho = '';

        if($item['possui_filhos'] != '' && $item['possui_filhos'] > 0){
            $filhos = unserialize($item['filhos']);
            $quantidadeFilhos = count($filhos['nome']);
        }

        for($i = 0; $i < $quantidadeFilhos; $i++){
            $nome_filho = $filhos['nome'];
            $data_nascimento_filho = $filhos['data_nascimento'];
            $cpf_filho = $filhos['cpf'];
        }

        $mensagem = $this->flash->getMessages();

    	return $this->view->render($response, 'funcionarios/form.html', [
            'Titulo_Pagina' => 'Editar registro',
        	'titulo' => 'Editar funcionário',
            'subtitulo' => 'Preencha o formulário abaixo para editar este funcionário',
        	'view' => 'editar',
        	'item' => $item,
            'filhos' => $filhos,
            'quantidadeFilhos' => $quantidadeFilhos,
            'nome_filho' => $nome_filho,
            'data_nascimento_filho' => $data_nascimento_filho,
            'cpf_filho' => $cpf_filho,
            'dados_registro' => $dados_registro,
            'arquivos' => $arquivos,
            'timeline' => $timeline,
            'advertencias' => $advertencias,
            'flash' => $mensagem,
        ]);
    }

    // REMOVER
    public function getRemover ($request, $response, $args)
    {
        if($this->acesso_usuario['funcionarios']['index']['d'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Funcionarios::find($args['id']);

        if($item->delete())
        {
            $dados_registro = FuncionariosDadosRegistro::where('funcionario', $args['id'])->where('status', 1)->first();
            if($dados_registro->delete()){
                return true;
            } else {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    // SALVA O REGISTRO NO BANCO DE DADOS
    public function postCadastrar ($request, $response, $args)
    {
        if($this->acesso_usuario['funcionarios']['index']['c'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $data_nascimento = explode("/", $request->getParam('data_nascimento'));
        $data_nascimento = $data_nascimento[2]."-".$data_nascimento[1]."-".$data_nascimento[0];

        $rg_dt_expedicao = explode("/", $request->getParam('rg_dt_expedicao'));
        $rg_dt_expedicao = $rg_dt_expedicao[2]."-".$rg_dt_expedicao[1]."-".$rg_dt_expedicao[0];

        // VERIFICA SE JÁ TEM UM FUNCIONÁRIO COM ESTE NOME
        $funcionario = Funcionarios::where('nome', $request->getParam('nome'))->count();
        
        if($funcionario >= 1){
            $this->flash->addMessage('error', 'Já existe um funcionário cadastrado com este nome!');
            return $response->withRedirect($this->router->pathFor('funcionarios'));
        }

	    $item = Funcionarios::create([
	    	'nome' => $request->getParam('nome'),
            'data_nascimento' => $data_nascimento,
            'naturalidade' => $request->getParam('naturalidade'),
            'uf_naturalidade' => $request->getParam('uf_naturalidade'),
            'pais' => $request->getParam('pais'),
            'cep' => $request->getParam('cep'),
            'endereco' => $request->getParam('endereco'),
            'num' => $request->getParam('num'),
            'complemento' => $request->getParam('complemento'),
            'bairro' => $request->getParam('bairro'),
            'cidade' => $request->getParam('cidade'),
            'uf' => $request->getParam('uf'),
            'telefone' => $request->getParam('telefone'),
            'celular' => $request->getParam('celular'),
            'estado_civil' => $request->getParam('estado_civil'),
            'nome_conjuge' => $request->getParam('nome_conjuge'),
            'cabelos' => $request->getParam('cabelos'),
            'olhos' => $request->getParam('olhos'),
            'altura' => $request->getParam('altura'),
            'peso' => $request->getParam('peso'),
            'deficiente_fisico' => $request->getParam('deficiente_fisico'),
            'tipo_deficiencia' => $request->getParam('tipo_deficiencia'),
            'cor_raca' => $request->getParam('cor_raca'),
            'escolaridade' => $request->getParam('escolaridade'),
            'rg' => $request->getParam('rg'),
            'rg_dt_expedicao' => $rg_dt_expedicao,
            'cpf' => $request->getParam('cpf'),
            'pis' => $request->getParam('pis'),
            'reservista' => $request->getParam('reservista'),
            'cnh' => $request->getParam('cnh'),
            'titulo_eleitor' => $request->getParam('titulo_eleitor'),
            'titulo_eleitor_zona' => $request->getParam('titulo_eleitor_zona'),
            'titulo_eleitor_sessao' => $request->getParam('titulo_eleitor_sessao'),
            'ctps' => $request->getParam('ctps'),
            'ctps_serie' => $request->getParam('ctps_serie'),
            'possui_filhos' => $request->getParam('possui_filhos'),
            'filhos' => serialize($request->getParam('filhos')),
	       	'status' => 1,
	    ]);

    	if($item)
        {
            $id_funcionario = $item->id;

            $data_admissao = explode("/", $request->getParam('data_admissao'));
            $data_admissao = $data_admissao[2]."-".$data_admissao[1]."-".$data_admissao[0];

            $exame_admissional = explode("/", $request->getParam('exame_admissional'));
            $exame_admissional = $exame_admissional[2]."-".$exame_admissional[1]."-".$exame_admissional[0];

            $dados_registro = FuncionariosDadosRegistro::create([
                'funcionario' => $id_funcionario,
                'unidade' => $request->getParam('unidade'),
                'cnpj' => $request->getParam('cnpj'),
                'data_admissao' => $data_admissao,
                'exame_admissional' => $exame_admissional,
                'tipo_contrato' => $request->getParam('tipo_contrato'),
                'vale_transporte' => $request->getParam('vale_transporte'),
                'salario' => $request->getParam('salario'),
                'horario_trabalho' => $request->getParam('horario_trabalho'),
                'funcao' => $request->getParam('funcao'),
                'descricao_funcao' => $request->getParam('descricao_funcao'),
                'status' => $request->getParam('status'),
            ]);

            $descricao = "Funcionário foi adicionado. Dados do registro:\n";
            $descricao .= "Função: " . $request->getParam('funcao') . "\n";
            $descricao .= "Unidade: " . $request->getParam('unidade') . "\n";
            $descricao .= "CNPJ: " . $request->getParam('cnpj') . "\n";
            $descricao .= "Data de Admissão: " . $request->getParam('data_admissao') . "\n";
            $descricao .= "Exame Admissional: " . $request->getParam('exame_admissional');
            $colors = ['info' => 'info'];
            $color = array_rand($colors);

            $timeline = FuncionariosTimeline::create([
                'funcionario' => $id_funcionario,
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Novo funcionário',
                'descricao' => $descricao,
                'color' => $color,
                'icon' => 'mdi mdi-account-box',
            ]);

            if($dados_registro){
                return true;
            } else {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    // EDITA O REGISTRO
    public function postEditar ($request, $response, $args)
    {
        if($this->acesso_usuario['funcionarios']['index']['u'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

    	$item = Funcionarios::find($args['id']);

        $data_nascimento = explode("/", $request->getParam('data_nascimento'));
        $data_nascimento = $data_nascimento[2]."-".$data_nascimento[1]."-".$data_nascimento[0];

        $rg_dt_expedicao = explode("/", $request->getParam('rg_dt_expedicao'));
        $rg_dt_expedicao = $rg_dt_expedicao[2]."-".$rg_dt_expedicao[1]."-".$rg_dt_expedicao[0];

    	$item->update([
            'nome' => $request->getParam('nome'),
            'data_nascimento' => $data_nascimento,
            'naturalidade' => $request->getParam('naturalidade'),
            'uf_naturalidade' => $request->getParam('uf_naturalidade'),
            'pais' => $request->getParam('pais'),
            'cep' => $request->getParam('cep'),
            'endereco' => $request->getParam('endereco'),
            'num' => $request->getParam('num'),
            'complemento' => $request->getParam('complemento'),
            'bairro' => $request->getParam('bairro'),
            'cidade' => $request->getParam('cidade'),
            'uf' => $request->getParam('uf'),
            'telefone' => $request->getParam('telefone'),
            'celular' => $request->getParam('celular'),
            'estado_civil' => $request->getParam('estado_civil'),
            'nome_conjuge' => $request->getParam('nome_conjuge'),
            'cabelos' => $request->getParam('cabelos'),
            'olhos' => $request->getParam('olhos'),
            'altura' => $request->getParam('altura'),
            'peso' => $request->getParam('peso'),
            'deficiente_fisico' => $request->getParam('deficiente_fisico'),
            'tipo_deficiencia' => $request->getParam('tipo_deficiencia'),
            'cor_raca' => $request->getParam('cor_raca'),
            'escolaridade' => $request->getParam('escolaridade'),
            'rg' => $request->getParam('rg'),
            'rg_dt_expedicao' => $rg_dt_expedicao,
            'cpf' => $request->getParam('cpf'),
            'pis' => $request->getParam('pis'),
            'reservista' => $request->getParam('reservista'),
            'cnh' => $request->getParam('cnh'),
            'titulo_eleitor' => $request->getParam('titulo_eleitor'),
            'titulo_eleitor_zona' => $request->getParam('titulo_eleitor_zona'),
            'titulo_eleitor_sessao' => $request->getParam('titulo_eleitor_sessao'),
            'ctps' => $request->getParam('ctps'),
            'ctps_serie' => $request->getParam('ctps_serie'),
            'possui_filhos' => $request->getParam('possui_filhos'),
            'filhos' => serialize($request->getParam('filhos')),
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

##### TIMELINE #####
    // Remove um registro da timeline
    public function getRemoverTimeline ($request, $response, $args)
    {
        $item = FuncionariosTimeline::find($args['id']);

        if($item->delete())
        {
            return true;
        }
        else
        {
            return false;
        }
    }
##### END TIMELINE #####

##### ARQUIVOS #####
    // Cadastra um arquivo para o funcionario no banco de dados
    public function postCadastrarArquivo ($request, $response, $args)
    {   
        $directory = '/var/www/maequeacolhe.com.br/httpdocs/sistema/public/uploads/funcionarios/arquivos'; ///var/www/dommeoficial.com.br/httpdocs/uploads/slides
        $uploadedFiles = $request->getUploadedFiles();

        // handle single input with single file upload
        $uploadedFile = $uploadedFiles['arquivo'];

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = moveUploadedFile($directory, $uploadedFile);

            $item = FuncionariosArquivos::create([
                'arquivo' => $filename,
                'descricao' => $request->getParam('descricao'),
                'funcionario' => $request->getParam('funcionario'),
            ]);
        }

        if($item)
        {
            $descricao = "Arquivo: " . $request->getParam('descricao') . " foi adicionado";
            $colors = ['success' => 'success'];
            $color = array_rand($colors);

            $timeline = FuncionariosTimeline::create([
                'funcionario' => $args['id'],
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Arquivo adicionado',
                'descricao' => $descricao,
                'color' => $color,
                'icon' => 'ti ti-files',
            ]);

            $this->flash->addMessage('success', 'O arquivo foi adicionado para a pasta deste funcionário.');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao adicionar arquivo para este funcionário.');
        }
        
        return $response->withRedirect($this->router->pathFor('editar_funcionario', ['id' => $args['id']]));
    }

    // Editar a descrição do arquivo
    public function postEditarArquivo (Request $request, Response $response, $args)
    {   
        $item = FuncionariosArquivos::find($args['id']);

        $item->update([
            'descricao' => $request->getParam('descricao'),
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

    // Remove um arquivo do acolhido
    public function getRemoverArquivo ($request, $response, $args)
    {
        $item = FuncionariosArquivos::find($args['id']);
        $descricao = $item['descricao'];

        if(unlink('public/uploads/funcionarios/arquivos/'.$item['arquivo'])){
            if($item->delete())
            {
                $descricao = "Arquivo: " . $descricao . " foi removido";
                $colors = ['danger' => 'danger'];
                $color = array_rand($colors);

                $timeline = FuncionariosTimeline::create([
                    'funcionario' => $item['funcionario'],
                    'usuario' => $_SESSION['Auth'],
                    'titulo' => 'Arquivo removido',
                    'descricao' => $descricao,
                    'color' => $color,
                    'icon' => 'ti ti-files',
                ]);

                return true;
            }
            else
            {
                return false;
            }
        } else {
            return false;
        }
    }
##### END ARQUIVOS #####  

##### ADVERTENCIAS #####
    // Cadastra uma advertencia para o funcionario
    public function postCadastrarAdvertencia ($request, $response, $args)
    {   
        $data = explode("/", $request->getParam('data'));
        $data = $data[2]."-".$data[1]."-".$data[0];

        $item = FuncionariosAdvertencias::create([
            'funcionario' => $args['id'],
            'data' => $data,
            'usuario' => $_SESSION['Auth'],
            'quem_aplicou' => $request->getParam('quem_aplicou'),
            'condicao_quem_aplicou' => $request->getParam('condicao_quem_aplicou'),
            'motivo' => $request->getParam('motivo'),
        ]);

        if($item)
        {
            $descricao = $request->getParam('quem_aplicou') . " (".$request->getParam('condicao_quem_aplicou').") " . "incluiu uma nova advertência.\n";
            $descricao .= "Data: " . $request->getParam('data') . "\n";
            $descricao .= "Motivo: " . $request->getParam('motivo') . "\n";
            $colors = ['warning' => 'warning'];
            $color = array_rand($colors);

            $timeline = FuncionariosTimeline::create([
                'funcionario' => $args['id'],
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Advertência',
                'descricao' => $descricao,
                'color' => $color,
                'icon' => 'fa fa-warning',
            ]);

            $this->flash->addMessage('success', 'Advertência adicionada com sucesso para este funcionário.');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao adicionar advertência para este funcionário.');
        }
        
        return $response->withRedirect($this->router->pathFor('funcionarios'));
    }

    // Remove uma advertencia do funcionario
    public function getRemoverAdvertencia ($request, $response, $args)
    {
        $item = FuncionariosAdvertencias::find($args['id']);

        if($item->delete())
        {
            $descricao = "Advertência removida";
            $colors = ['warning' => 'warning'];
            $color = array_rand($colors);

            $timeline = FuncionariosTimeline::create([
                'funcionario' => $item['funcionario'],
                'usuario' => $_SESSION['Auth'],
                'titulo' => 'Advertência',
                'descricao' => $descricao,
                'color' => $color,
                'icon' => 'fa fa-warning',
            ]);

            return true;
        } else {
            return false;
        }
    }
##### END ADVERTENCIAS #####  
}