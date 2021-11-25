<?php

namespace App\Controllers;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Controllers\Controller;
use Slim\Views\Twig as View;
use App\Models\Acolhidos;
use App\Models\CaixaAcolhidos;
use App\Models\Acolhimentos;
use App\Models\Snapshots;
use App\Models\Contatos;
use App\Models\Arquivos;
use App\Models\Unidades;
use App\Models\Quartos;
use App\Models\Users;

class AcolhidosController extends Controller
{

##### ACOLHIDOS #####
    // Exibe listagem dos acolhidos
    public function listar ($request, $response, $args)
    {   
        if($this->acesso_usuario['acolhidos']['index']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        // if(isset($_SESSION['Unidade'])){
        //     $item = Acolhidos::orderBy('id','ASC')->get()->toArray();
        //     $unidade = Unidades::find($_SESSION['Unidade']);
        //     $unidades = Unidades::orderBy('nome', 'ASC')->get()->toArray();

        //     for($i = 0; $i < count($item); $i++){
        //         $id_acolhido = $item[$i]['id'];
        //         $verifica_acolhimento[$id_acolhido] = Acolhimentos::whereIn('status', [0, 1])->where('acolhido', $id_acolhido)->count();
                
        //         $this->view->offsetSet("verifica_acolhimento", $verifica_acolhimento);

        //         // Verifica se o acolhido tem algum lançamento no CaixaAcolhidos
        //         $verifica_caixa[$id_acolhido] = CaixaAcolhidos::where('acolhido', $id_acolhido)->count();
        //         $this->view->offsetSet("verifica_caixa", $verifica_caixa);

        //         // Pega o valor total de crédito CaixaAcolhidos
        //         $credito_caixa = CaixaAcolhidos::where('acolhido', $id_acolhido)->where('tipo', 1)->sum('valor');

        //         // Pega o valor total de débito CaixaAcolhidos
        //         $debito_caixa = CaixaAcolhidos::where('acolhido', $id_acolhido)->where('tipo', 0)->sum('valor');

        //         // Calcula o saldo final CaixaAcolhidos
        //         $saldo_caixa[$id_acolhido] = $credito_caixa - $debito_caixa;
        //         $this->view->offsetSet("saldo_caixa", $saldo_caixa);
        //     }

        //     $titulo = 'Listagem dos acolhidos - ' . $unidade['nome'];
        //     $subtitulo = 'Todos os acolhidos que estão em acolhimento nesta unidade';
        // } else {
        //     $item = Acolhidos::orderBy('id','ASC')->get()->toArray();

        //     for($i = 0; $i < count($item); $i++){
        //         $id_acolhido = $item[$i]['id'];
        //         $unidades[$id_acolhido] = Unidades::where('id', $item[$i]['unidade'])->first();
        //         $this->view->offsetSet("unidades", $unidades);

        //         $verifica_acolhimento[$id_acolhido] = Acolhimentos::whereIn('status', [0, 1])->where('acolhido', $id_acolhido)->count();
        //         $this->view->offsetSet("verifica_acolhimento", $verifica_acolhimento);
        //     }
        //     $titulo = 'Listagem de todos os acolhidos';
        //     $subtitulo = 'Todos os acolhidos que estão em acolhimento em sua instituição';
        // }


        if($args['status'] == 'acolhimento'){
            $item = Acolhidos::where('status', 1)->get()->toArray();
        } else {
            $item = Acolhidos::where('status', 0)->get()->toArray();
        }

        for($i = 0; $i < count($item); $i++){
            $id_acolhido = $item[$i]['id'];
            if($args['status'] == 'acolhimento'){
                $acolhimento = Acolhimentos::whereIn('status', [0, 1])->where('acolhido', $id_acolhido)->first();
                
                $id_unidade = $acolhimento['unidade'];
                $unidade[$id_acolhido] = Unidades::find($id_unidade);
                $this->view->offsetSet("unidade", $unidade);

                $id_quarto = $acolhimento['quarto'];
                $quarto[$id_acolhido] = Quartos::find($id_quarto);
                $this->view->offsetSet("quarto", $quarto);
            } else {
                $acolhimento = Acolhimentos::where('acolhido', $id_acolhido)->first();
            }
            $this->view->offsetSet("acolhimento", $acolhimento);
        }

        if(isset($_SESSION['Unidade'])){
            $unid = Unidades::find($_SESSION['Unidade']);
            $titulo = 'Listagem dos acolhidos - ' . $unid['nome'];
            $subtitulo = 'Todos os acolhidos que estão em acolhimento nesta unidade';
        } else {
            $titulo = 'Listagem de todos os acolhidos';
            $subtitulo = 'Todos os acolhidos que estão em acolhimento em sua instituição';
        }


        // Puxa os lançamentos do CaixaAcolhidos
        $caixa = CaixaAcolhidos::get()->toArray();

        foreach ($caixa as $c) {
            $id_caixa = $c['id'];
            // Pega o nome do usuário que fez o lançamento
            $usuario[$id_caixa] = Users::find($c['usuario']);
            
            $this->view->offsetSet("usuario", $usuario);
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'acolhidos/listar.html', [
            'Titulo_Pagina' => 'Acolhidos',
            'titulo'    => $titulo,
            'subtitulo' => $subtitulo,
            'view'      => 'listar',
            'flash'     => $mensagem,
            'itens'     => $item,
            'unid'      => $unid,
            'caixa'     => $caixa,
            'status'    => $args['status'],
        ]);
    }

    // Exibe formulário de cadastro dos acolhidos
    public function getCadastrar ($request, $response)
    {
        if($this->acesso_usuario['acolhidos']['index']['c'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        // if(isset($_SESSION['Unidade'])){
        //     $unidade = Unidades::find($_SESSION['Unidade']);
        // } else {
        //     $this->flash->addMessage('error', 'Você precisa selecionar uma unidade para incluir este registro!');

        //     return $response->withRedirect($this->router->pathFor('listar_acolhidos'));
        // }

        return $this->view->render($response, 'acolhidos/form.html', [
            'Titulo_Pagina' => 'Novo Registro',
            'titulo'    => 'Cadastrar novo acolhido',
            'subtitulo' => 'Preencha o formulário abaixo para inserir um novo acolhido na unidade - ' . $unidade['nome'],
            'view'      => 'cadastrar'
        ]);
    }

    // Cadastra um acolhido no banco de dados
    public function postCadastrar ($request, $response, $args)
    {   
        $data_nascimento = $request->getParam('data_nascimento');
        // $data_nascimento = explode("/", $request->getParam('data_nascimento'));
        // $data_nascimento = $data_nascimento[2]."-".$data_nascimento[1]."-".$data_nascimento[0];
        
        $data_nascimento_pai = explode("/", $request->getParam('data_nascimento_pai'));
        $data_nascimento_pai = $data_nascimento_pai[2]."-".$data_nascimento_pai[1]."-".$data_nascimento_pai[0];
        
        $data_nascimento_mae = explode("/", $request->getParam('data_nascimento_mae'));
        $data_nascimento_mae = $data_nascimento_mae[2]."-".$data_nascimento_mae[1]."-".$data_nascimento_mae[0];

        if($request->getParam('situacao_rua') == 1){
            $cidade = $request->getParam('cidade2');
        } else {
            $cidade = $request->getParam('cidade');
        }

        $verifica_acolhido = Acolhidos::where('nome', $request->getParam('nome'))->orWhere('cpf', $request->getParam('cpf'))->count();

        if($verifica_acolhido >= 1){
            $this->flash->addMessage('error', 'Já existe um acolhido cadastrado com este NOME ou CPF!');
            return $response->withRedirect($this->router->pathFor('acolhidos'));
        }

        $item = Acolhidos::create([
            'nome' => $request->getParam('nome'),
            'rg' => $request->getParam('rg'),
            'cpf' => $request->getParam('cpf'),
            'data_nascimento' => $data_nascimento,
            'naturalidade' => $request->getParam('naturalidade'),
            'uf_naturalidade' => $request->getParam('uf_naturalidade'),
            'cad_unico' => $request->getParam('cad_unico'),
            'cartao_sus' => $request->getParam('cartao_sus'),
            'pis' => $request->getParam('pis'),
            'cor_raca' => $request->getParam('cor_raca'),
            'possui_filhos' => $request->getParam('possui_filhos'),
            'filhos' => serialize($request->getParam('filhos')),
            'situacao_rua' => $request->getParam('situacao_rua'),
            'cep' => $request->getParam('cep'),
            'endereco' => $request->getParam('endereco'),
            'num' => $request->getParam('num'),
            'complemento' => $request->getParam('complemento'),
            'bairro' => $request->getParam('bairro'),
            'cidade' => $cidade,
            'uf' => $request->getParam('uf'),
            'nome_pai' => $request->getParam('nome_pai'),
            'data_nascimento_pai' => $data_nascimento_pai,
            'profissao_pai' => $request->getParam('profissao_pai'),
            'nome_mae' => $request->getParam('nome_mae'),
            'data_nascimento_mae' => $data_nascimento_mae,
            'profissao_mae' => $request->getParam('profissao_mae'),
            'pais_separados' => $request->getParam('pais_separados'),
            'unidade' => $request->getParam('unidade'),
        ]);

        if($item)
        {
            if($request->getParam('nome_contato') != ''){
                $data_nascimento_contato = explode("/", $request->getParam('data_nascimento_contato'));
                $data_nascimento_contato = $data_nascimento_contato[2]."-".$data_nascimento_contato[1]."-".$data_nascimento_contato[0];

                $contato = Contatos::create([
                    'acolhido' => $item->id,
                    'nome' => $request->getParam('nome_contato'),
                    'grau_parentesco' => $request->getParam('grau_parentesco'),
                    'rg' => $request->getParam('rg_contato'),
                    'cpf' => $request->getParam('cpf_contato'),
                    'data_nascimento' => $data_nascimento_contato,
                    'telefone' => $request->getParam('telefone_contato'),
                    'celular' => $request->getParam('celular_contato'),
                    'email' => $request->getParam('email_contato'),
                    'cep' => $request->getParam('cep_contato'),
                    'endereco' => $request->getParam('endereco_contato'),
                    'num' => $request->getParam('num_contato'),
                    'complemento' => $request->getParam('complemento_contato'),
                    'bairro' => $request->getParam('bairro_contato'),
                    'cidade' => $request->getParam('cidade_contato'),
                    'uf' => $request->getParam('uf_contato'),
                    'status' => 1,
                ]);
            }

            echo $item->id;
            return true;
        }
        else
        {
            return false;
        }
    }

    // Exibe formulário de edição dos acolhidos
    public function getEditar ($request, $response, $args)
    {   
        if($this->acesso_usuario['acolhidos']['index']['u'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $this->view->offsetSet("flash", $this->flash->getMessages());

        $item = Acolhidos::find($args['id']);
        $unidade = Unidades::find($item['unidade']);
        $snapshots = Snapshots::orderBy('id','desc')->where('acolhido', $args['id'])->get()->toArray();
        $contatos = Contatos::orderBy('id','desc')->where('acolhido', $args['id'])->get()->toArray();
        $arquivos = Arquivos::orderBy('id','desc')->where('acolhido', $args['id'])->get()->toArray();

        // VERIFICA SE JÁ TEM UM CONTATO PRINCIPAL
        $contato_principal = Contatos::where('acolhido', $args['id'])->where('status', 1)->count();
        if($contato_principal >= 1){
            $this->view->offsetSet("tem_contato_principal", true);
        }
        
        $filhos = '';
        $quantidadeFilhos = '';
        $nome_filho = '';
        $data_nascimento_filho = '';
        $genero_filho = '';

        if($item['possui_filhos'] != '' && $item['possui_filhos'] > 0){
            $filhos = unserialize($item['filhos']);
            $quantidadeFilhos = count($filhos['nome']);
        }

        for($i = 0; $i < $quantidadeFilhos; $i++){
            $nome_filho = $filhos['nome'];
            $data_nascimento_filho = $filhos['data_nascimento'];
            $genero_filho = $filhos['genero'];
        }

        return $this->view->render($response, 'acolhidos/editar.html', [
            'Titulo_Pagina' => 'Editar Registro',
            'titulo'    => 'Editar acolhido - ' . $item['nome'],
            'subtitulo' => 'Preencha o formulário abaixo para editar este acolhido na unidade - ' . $unidade['nome'],
            'view' => 'editar',
            'item' => $item,
            'filhos' => $filhos,
            'quantidadeFilhos' => $quantidadeFilhos,
            'nome_filho' => $nome_filho,
            'data_nascimento_filho' => $data_nascimento_filho,
            'genero_filho' => $genero_filho,
            'snapshots' => $snapshots,
            'contatos' => $contatos,
            'arquivos' => $arquivos,
            'unidade' => $unidade,
        ]);
    }

    // Edita um acolhido no banco de dados
    public function postEditar ($request, $response, $args)
    {
        $item = Acolhidos::find($args['id']);

        $data_nascimento = explode("/", $request->getParam('data_nascimento'));
        $data_nascimento = $data_nascimento[2]."-".$data_nascimento[1]."-".$data_nascimento[0];
        
        $data_nascimento_pai = explode("/", $request->getParam('data_nascimento_pai'));
        $data_nascimento_pai = $data_nascimento_pai[2]."-".$data_nascimento_pai[1]."-".$data_nascimento_pai[0];
        
        $data_nascimento_mae = explode("/", $request->getParam('data_nascimento_mae'));
        $data_nascimento_mae = $data_nascimento_mae[2]."-".$data_nascimento_mae[1]."-".$data_nascimento_mae[0];

        if($request->getParam('situacao_rua') == 1){
            $cidade = $request->getParam('cidade2');
        } else {
            $cidade = $request->getParam('cidade');
        }
        
        $item->update([
            'nome' => $request->getParam('nome'),
            'rg' => $request->getParam('rg'),
            'cpf' => $request->getParam('cpf'),
            'data_nascimento' => $data_nascimento,
            'naturalidade' => $request->getParam('naturalidade'),
            'uf_naturalidade' => $request->getParam('uf_naturalidade'),
            'cad_unico' => $request->getParam('cad_unico'),
            'cartao_sus' => $request->getParam('cartao_sus'),
            'pis' => $request->getParam('pis'),
            'cor_raca' => $request->getParam('cor_raca'),
            'possui_filhos' => $request->getParam('possui_filhos'),
            'filhos' => serialize($request->getParam('filhos')),
            'situacao_rua' => $request->getParam('situacao_rua'),
            'cep' => $request->getParam('cep'),
            'endereco' => $request->getParam('endereco'),
            'num' => $request->getParam('num'),
            'complemento' => $request->getParam('complemento'),
            'bairro' => $request->getParam('bairro'),
            'cidade' => $request->getParam('cidade'),
            'uf' => $request->getParam('uf'),
            'nome_pai' => $request->getParam('nome_pai'),
            'data_nascimento_pai' => $data_nascimento_pai,
            'profissao_pai' => $request->getParam('profissao_pai'),
            'nome_mae' => $request->getParam('nome_mae'),
            'data_nascimento_mae' => $data_nascimento_mae,
            'profissao_mae' => $request->getParam('profissao_mae'),
            'pais_separados' => $request->getParam('pais_separados'),
            'unidade' => $request->getParam('unidade'),
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

    // Remove um acolhido
    public function getRemover ($request, $response, $args)
    {
        if($this->acesso_usuario['acolhidos']['index']['d'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Acolhidos::find($args['id']);

        if($item->delete())
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    // Remove uma foto do acolhido
    public function getRemoverFoto ($request, $response, $args)
    {
        $item = Snapshots::find($args['id']);

        if(unlink('public/uploads/acolhidos/fotos/'.$item['imagem'])){
            if($item->delete())
            {
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
##### END ACOLHIDOS #####

##### CONTATOS #####
    // Cadastra um contato para o acolhido no banco de dados
    public function postCadastrarContato ($request, $response, $args)
    {   
        $data_nascimento = explode("/", $request->getParam('data_nascimento'));
        $data_nascimento = $data_nascimento[2]."-".$data_nascimento[1]."-".$data_nascimento[0];

        $item = Contatos::create([
            'nome' => $request->getParam('nome'),
            'acolhido' => $request->getParam('acolhido'),
            'grau_parentesco' => $request->getParam('grau_parentesco'),
            'rg' => $request->getParam('rg'),
            'cpf' => $request->getParam('cpf'),
            'data_nascimento' => $data_nascimento,
            'naturalidade' => $request->getParam('naturalidade'),
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
            'status' => $request->getParam('status'),
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

    // Edita um contato para o acolhido no banco de dados
    public function postEditarContato (Request $request, Response $response, $args)
    {   
        $item = Contatos::find($args['id']);

        $data_nascimento = explode("/", $request->getParam('data_nascimento'));
        $data_nascimento = $data_nascimento[2]."-".$data_nascimento[1]."-".$data_nascimento[0];

        if($request->getParam('status') == 'on'){
            $status = 1;
        } else {
            $status = 0;
        }

        $item->update([
            'nome' => $request->getParam('nome'),
            'acolhido' => $request->getParam('acolhido'),
            'grau_parentesco' => $request->getParam('grau_parentesco'),
            'rg' => $request->getParam('rg'),
            'cpf' => $request->getParam('cpf'),
            'data_nascimento' => $data_nascimento,
            'naturalidade' => $request->getParam('naturalidade'),
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
            'status' => $status,
        ]);

        if($item)
        {
            $this->flash->addMessage('success', 'Contato editado com sucesso!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao editar contato!');
        }
        
        return $response->withRedirect($this->router->pathFor('editar_acolhido', ['id' => $item['acolhido']]));
    }

    // Remove um acolhido
    public function getRemoverContato ($request, $response, $args)
    {
        $item = Contatos::find($args['id']);

        if($item->delete())
        {
            return true;
        }
        else
        {
            return false;
        }
    }
##### END CONTATOS #####

##### ARQUIVOS #####
    // Cadastra um arquivo para o acolhido no banco de dados
    public function postCadastrarArquivo ($request, $response, $args)
    {   
        $directory = '/var/www/escudodobem.gruub.com.br/sistema/public/uploads/acolhidos/arquivos';
        $uploadedFiles = $request->getUploadedFiles();

        // handle single input with single file upload
        $uploadedFile = $uploadedFiles['arquivo'];

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = moveUploadedFile($directory, $uploadedFile);

            $item = Arquivos::create([
                'arquivo' => $filename,
                'descricao' => $request->getParam('descricao'),
                'acolhido' => $request->getParam('acolhido'),
            ]);
        }

        if($item)
        {
            $this->flash->addMessage('success', 'O arquivo foi adicionado para a pasta deste acolhido.');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao adicionar arquivo para este acolhido.');
        }
        
        return $response->withRedirect($this->router->pathFor('editar_acolhido', ['id' => $args['id']]));
    }

    // Editar a descrição do arquivo
    public function postEditarArquivo (Request $request, Response $response, $args)
    {   
        $item = Arquivos::find($args['id']);

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
        $item = Arquivos::find($args['id']);

        if(unlink('public/uploads/acolhidos/arquivos/'.$item['arquivo'])){
            if($item->delete())
            {
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

##### ALTERAR UNIDADE #####
    // public function postAlterarUnidade ($request, $response, $args)
    // {
    //     $unidade_anterior = Unidades::find($request->getParam('unidade_anterior'));
    //     $nova_unidade = Unidades::find($request->getParam('nova_unidade'));

    //     $acolhido = Acolhidos::find($args['id']);
    //     $acolhido->update([
    //         'unidade' => $request->getParam('nova_unidade'),
    //     ]);

    //     if($acolhido)
    //     {
    //         // $descricao = $acolhido['nome'] . ' foi transferido da unidade ' . $unidade_anterior['nome'] . ' para a unidade ' . $nova_unidade['nome'];
    //         // $colors = ['success' => 'success', 'primary' => 'primary', 'warning' => 'warning', 'info' => 'info', 'danger' => 'danger', 'default' => 'default'];
    //         // $color = array_rand($colors);

    //         // $timeline = Timeline::create([
    //         //     'acolhimento' => $args['id'],
    //         //     'usuario' => $_SESSION['Auth'],
    //         //     'titulo' => 'Mudança de unidade',
    //         //     'descricao' => $descricao,
    //         //     'color' => $color,
    //         //     'icon' => 'ti-exchange-vertical',
    //         // ]);

    //         // return true;

    //         $this->flash->addMessage('success', 'Acolhido alterado de unidade com sucesso!');
    //     }
    //     else
    //     {
    //         // return false;

    //         $this->flash->addMessage('error', 'Erro ao alterar acolhido de unidade!');
    //     }

    //     return $response->withRedirect($this->router->pathFor('listar_acolhidos'));
    // }
##### END ALTERAR UNIDADE #####  
}