<?php

namespace App\Controllers;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Controllers\Controller;

use Slim\Views\Twig as View;
use App\Models\Config;
use App\Models\Users;
use App\Models\Roles;
use App\Models\Acolhidos;
use App\Models\Acolhimentos;
use App\Models\Unidades;
use App\Models\CronogramaAtividades;
use App\Models\GruposCronogramaAtividades;


class RelatoriosController extends Controller
{
    /*
        Lista dinâmica
    */
    public function lista_dinamica ($request, $response)
    {
        if($this->acesso_usuario['relatorios']['lista_dinamica']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $acolhidos = Acolhidos::orderBy('nome', 'ASC')->get()->toArray();

        foreach ($acolhidos as $acolhido) {
            $verifica_acolhimento = Acolhimentos::where('acolhido', $acolhido['id'])->whereIn('status', [0, 1])->count();

            if($verifica_acolhimento >= 1){
                $id_acolhido = $acolhido['id'];
                $acolhimento[$id_acolhido] = Acolhimentos::where('acolhido', $acolhido['id'])->whereIn('status', [0, 1])->get()->toArray();
                
                // echo "<pre>";
                // print_r($acolhimento[$id_acolhido][0]['id']);
                // echo "</pre>";
                
                $this->view->offsetSet("acolhimento", $acolhimento);
                $this->view->offsetSet("id_acolhido", $id_acolhido);
            }
        }
        // exit;
        // $acolhimentos = Acolhimentos::whereIn('status', [0, 1])->get()->toArray();

        // for($i = 0; $i < count($acolhimentos); $i++){
        //     $id_acolhido = $acolhimentos[$i]['acolhido'];
        //     $acolhido[$id_acolhido] = Acolhidos::where('id', $acolhimentos[$i]['acolhido'])->first();

        //     $this->view->offsetSet("acolhido", $acolhido);
        // }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'relatorios/lista_dinamica/form.html', [
            'Titulo_Pagina' => 'Relatórios - Lista Dinâmica',
            'titulo'    => 'Lista dinâmica',
            'subtitulo' => 'Preencha o formulário abaixo para gerar uma lista dinâmica com as informações solicitadas.',
            // 'acolhimentos' => $acolhimentos,
            'acolhidos' => $acolhidos,
        ]);
    }
    /* 
        END Lista dinâmica
    */

    /*
        Altas por período
    */
    public function altas ($request, $response)
    {
        if($this->acesso_usuario['relatorios']['altas_periodo']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'relatorios/altas/form.html', [
            'Titulo_Pagina' => 'Relatórios - Altas por Período',
            'titulo'    => 'Altas por Período',
            'subtitulo' => 'Preencha o formulário abaixo para gerar um relatório de altas por período.',
        ]);
    }
    /*
        END Altas por período
    */

    /*
        Acolhimentos por período
    */
    public function acolhimentos_por_periodo ($request, $response)
    {
        if($this->acesso_usuario['relatorios']['altas_periodo']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'relatorios/acolhimentos_por_periodo/form.html', [
            'Titulo_Pagina' => 'Relatórios - Acolhimentos por Período',
            'titulo'    => 'Acolhimentos por Período',
            'subtitulo' => 'Preencha o formulário abaixo para gerar um relatório dos acolhimentos por período.',
        ]);
    }
    /*
        END Acolhimentos por período
    */

    /*
        Acolhimentos p/ região
    */
    public function acolhimentos_por_regiao ($request, $response)
    {
        if($this->acesso_usuario['relatorios']['acolhidos_regiao']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'relatorios/acolhidos_por_regiao/form.html', [
            'Titulo_Pagina' => 'Relatórios - Acolhidos por Região',
            'titulo'    => 'Acolhidos por Região',
            'subtitulo' => 'Preencha o formulário abaixo para gerar um relatório dos acolhidos por região.',
        ]);
    }
    /*
        END Acolhimentos p/ região
    */

    /*
        Lista de chamada
    */
    public function lista_chamada ($request, $response)
    {
        if($this->acesso_usuario['listas']['lista_chamada']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $unidades = Unidades::orderBy('nome', 'ASC')->get()->toArray();

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'relatorios/lista_chamada/form.html', [
            'Titulo_Pagina' => 'Listas - Lista de Chamada',
            'titulo'    => 'Lista de chamada',
            'subtitulo' => 'Preencha o formulário abaixo para gerar uma lista de chamada por unidade.',
            'unidades' => $unidades,
        ]);
    }
    /* 
        END Lista de chamada
    */

    /*
        Lista de medicação diária
    */
    public function lista_medicacao_diaria ($request, $response)
    {
        if($this->acesso_usuario['listas']['lista_medicacao_diaria']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $unidades = Unidades::orderBy('nome', 'ASC')->get()->toArray();

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'relatorios/lista_medicacao_diaria/form.html', [
            'Titulo_Pagina' => 'Listas - Lista de Medicação Diária',
            'titulo'    => 'Lista de Medicação Diária',
            'subtitulo' => 'Preencha o formulário abaixo para gerar uma lista de medicação diária por unidade.',
            'unidades' => $unidades,
        ]);
    }
    /* 
        END Lista de chamada
    */
}