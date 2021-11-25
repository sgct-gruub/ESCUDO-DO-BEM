<?php

namespace App\Controllers;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Controllers\Controller;

use Slim\Views\Twig as View;
use App\Models\Config;
use App\Models\Users;
use App\Models\Roles;
use App\Models\CronogramaAtividades;
use App\Models\GruposCronogramaAtividades;


class ConfigController extends Controller
{

    public function index ($request, $response)
    {
        if($this->acesso_usuario['config']['index']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

    	$item = Config::find(1);

    	$mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'config/index.html', [
            'Titulo_Pagina' => 'Configurações',
            'titulo'    => 'Configurações',
            'subtitulo' => 'Gerencie as informações da sua instituição no sistema.',
            'view'      => 'listar',
            'flash'     => $mensagem,
            'item'     => $item,
        ]);
    }

    public function salvar ($request, $response, $args)
    {
    	$item = Config::find(1);

    	if(isset($item)){
    		$logo_directory = '/var/www/escudodobem.gruub.com.br/sistema/public/uploads/config/logo';
    		$timbre_directory = '/var/www/escudodobem.gruub.com.br/sistema/public/uploads/config/timbre';
	        $uploadedFiles = $request->getUploadedFiles();

	        // handle single input with single file upload
	        $logo_uploadedFile = $uploadedFiles['logo'];

	        // handle single input with single file upload
	        $timbre_uploadedFile = $uploadedFiles['timbre'];

			if ($logo_uploadedFile->getError() === UPLOAD_ERR_OK) {
				$logo_filename = moveUploadedFile($logo_directory, $logo_uploadedFile);
				$item->update([
    				'logo' => $logo_filename,
    			]);
			}

			if ($timbre_uploadedFile->getError() === UPLOAD_ERR_OK) {
				$timbre_filename = moveUploadedFile($timbre_directory, $timbre_uploadedFile);
				$item->update([
    				'timbre' => $timbre_filename,
    			]);
			}

    		$item->update([
    			'razao_social' => $request->getParam('razao_social'),
    			'nome_fantasia' => $request->getParam('nome_fantasia'),
    			'cnpj' => $request->getParam('cnpj'),
                'tempo_tratamento' => $request->getParam('tempo_tratamento'),
                'cep' => $request->getParam('cep'),
                'endereco' => $request->getParam('endereco'),
                'num' => $request->getParam('num'),
                'complemento' => $request->getParam('complemento'),
                'bairro' => $request->getParam('bairro'),
                'cidade' => $request->getParam('cidade'),
    			'uf' => $request->getParam('uf'),
    		]);
    	} else {
    		$logo_directory = '/var/www/escudodobem.gruub.com.br/sistema/public/uploads/config/logo';
    		$timbre_directory = '/var/www/escudodobem.gruub.com.br/sistema/public/uploads/config/timbre';
	        $uploadedFiles = $request->getUploadedFiles();

	        // handle single input with single file upload
	        $logo_uploadedFile = $uploadedFiles['logo'];

	        // handle single input with single file upload
	        $timbre_uploadedFile = $uploadedFiles['timbre'];

			if ($logo_uploadedFile->getError() === UPLOAD_ERR_OK) {
				$logo_filename = moveUploadedFile($logo_directory, $logo_uploadedFile);
				$item = Config::create([
    				'logo' => $logo_filename,
    			]);
			}

			if ($timbre_uploadedFile->getError() === UPLOAD_ERR_OK) {
				$timbre_filename = moveUploadedFile($timbre_directory, $timbre_uploadedFile);
				$item = Config::create([
    				'timbre' => $timbre_filename,
    			]);
			}

    		$item = Config::create([
    			'razao_social' => $request->getParam('razao_social'),
    			'nome_fantasia' => $request->getParam('nome_fantasia'),
    			'cnpj' => $request->getParam('cnpj'),
    			'tempo_tratamento' => $request->getParam('tempo_tratamento'),
                'cep' => $request->getParam('cep'),
                'endereco' => $request->getParam('endereco'),
                'num' => $request->getParam('num'),
                'complemento' => $request->getParam('complemento'),
                'bairro' => $request->getParam('bairro'),
                'cidade' => $request->getParam('cidade'),
                'uf' => $request->getParam('uf'),
    		]);
    	}

    	if($item)
        {
            $this->flash->addMessage('success', 'Configurações editadas com sucesso!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao editar configurações!');
        }

        return $response->withRedirect($this->router->pathFor('config'));
    }

    /*
        Cronograma de atividades
    */
    public function cronograma_atividades ($request, $response)
    {
        if($this->acesso_usuario['config']['index']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = CronogramaAtividades::orderBy('id', 'asc')->get()->toArray();
        $grupos = GruposCronogramaAtividades::orderBy('id', 'asc')->get()->toArray();

        for($i = 0; $i < count($grupos); $i++){
            $count_atividades[$grupos[$i]['id']] = CronogramaAtividades::where('grupo', $grupos[$i]['id'])->count();
            $this->view->offsetSet("count_atividades", $count_atividades);
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'config/index.html', [
            'Titulo_Pagina' => 'Configurações - Cronograma de Atividades',
            'titulo'    => 'Cronograma de atividades',
            'subtitulo' => 'Gerencie o cronograma de atividades da sua instituição.',
            'view'      => 'cronograma_atividades',
            'flash'     => $mensagem,
            'itens'     => $item,
            'grupos'    => $grupos,
        ]);
    }

    public function criar_atividade ($request, $response)
    {
        if($request->getParam('is_grupo') == 'on'){
            $item = GruposCronogramaAtividades::create([
                'nome' => $request->getParam('nome'),
                'periodo' => $request->getParam('periodo'),
            ]);
        } else {
            $item = CronogramaAtividades::create([
                'nome' => $request->getParam('nome'),
                'periodo' => $request->getParam('periodo'),
                'grupo' => $request->getParam('grupo'),
            ]);
        }

        if($item)
        {
            if($request->getParam('is_grupo') == 'on'){
                $this->flash->addMessage('success', 'Grupo de atividades cadastrado com sucesso!');
            } else {
                $this->flash->addMessage('success', 'Atividade cadastrada com sucesso!');
            }
        }
        else
        {
            if($request->getParam('is_grupo') == 'on'){
                $this->flash->addMessage('error', 'Erro ao criar grupo de atividades!');
            } else {
                $this->flash->addMessage('error', 'Erro ao criar atividade!');
            }
        }
        
        return $response->withRedirect($this->router->pathFor('config_cronograma_atividades'));
    }

    public function editar_atividade ($request, $response, $args)
    {
        $item = CronogramaAtividades::find($args['id']);
        $item->update([
            'nome' => $request->getParam('nome'),
            'periodo' => $request->getParam('periodo'),
            'grupo' => $request->getParam('grupo'),
        ]);

        if($item)
        {
            $this->flash->addMessage('success', 'Atividade editada com sucesso!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao editar atividade!');
        }
        
        return $response->withRedirect($this->router->pathFor('config_cronograma_atividades'));
    }

    public function editar_grupo_atividade ($request, $response, $args)
    {
        $item = GruposCronogramaAtividades::find($args['id']);
        $item->update([
            'nome' => $request->getParam('nome'),
        ]);

        if($item)
        {
            $this->flash->addMessage('success', 'Grupo de atividades editado com sucesso!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao editar grupo de atividades!');
        }
        
        return $response->withRedirect($this->router->pathFor('config_cronograma_atividades'));
    }

    public function remover_atividade ($request, $response, $args)
    {
        $item = CronogramaAtividades::find($args['id']);

        if($item->delete())
        {
            echo 'ok';
            return true;
        } else {
            echo 'erro';
            return false;
        }
    }

    public function remover_grupo_atividade ($request, $response, $args)
    {
        $check = CronogramaAtividades::where('grupo', $args['id'])->count();
        if($check == 0){
            $item = GruposCronogramaAtividades::find($args['id']);

            if($item->delete())
            {
                echo 'ok';
                return true;
            }
        } else {
            echo 'erro';
            return false;
        }
    }
    /* 
        END Cronograma de atividades
    */

    public function usuarios ($request, $response)
    {
        if($this->acesso_usuario['config']['index']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $item = Roles::orderBy('nome', 'asc')->get()->toArray();
        $color = "#".substr(md5(rand()), 0, 6);

        for($i = 0; $i < count($item); $i++){
            $id_role = $item[$i]['id'];
            $role = Roles::find($id_role);
            $acesso[$id_role] = unserialize($role['acesso']);

            $this->view->offsetSet("acesso", $acesso);

            // echo "<pre>";
            // print_r($acesso);
            // echo "</pre>"; exit;
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'config/index.html', [
            'Titulo_Pagina' => 'Configurações - Grupos de Usuários',
            'titulo'    => 'Grupos de usuários',
            'subtitulo' => 'Gerencie os níveis de acesso ao sistema por grupos de usuários.',
            'view'      => 'usuarios',
            'flash'     => $mensagem,
            'itens'     => $item,
            'color'     => $color,
        ]);
    }

    public function criar_grupo ($request, $response)
    {
        $item = Roles::create([
            'nome' => $request->getParam('nome'),
            'cor' => $request->getParam('cor'),
            'acesso' => serialize($request->getParam('acesso')),
        ]);

        if($item)
        {
            $this->flash->addMessage('success', 'Grupo de usuário cadastrado com sucesso!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao criar grupo de usuário!');
        }
        
        return $response->withRedirect($this->router->pathFor('config_usuarios'));
    }

    public function editar_grupo ($request, $response, $args)
    {
        $item = Roles::find($args['id']);
        $item->update([
            'nome' => $request->getParam('nome'),
            'cor' => $request->getParam('cor'),
            'acesso' => serialize($request->getParam('acesso')),
        ]);

        if($item)
        {
            $this->flash->addMessage('success', 'Grupo de usuário editado com sucesso!');
        }
        else
        {
            $this->flash->addMessage('error', 'Erro ao editar grupo de usuário!');
        }
        
        return $response->withRedirect($this->router->pathFor('config_usuarios'));
    }

    public function remover_grupo ($request, $response, $args)
    {
        $check = Users::where('role', $args['id'])->count();
        if($check == 0){
            $item = Roles::find($args['id']);

            if($item->delete())
            {
                echo 'ok';
                return true;
            }
        } else {
            echo 'erro';
            return false;
        }
    }
}
