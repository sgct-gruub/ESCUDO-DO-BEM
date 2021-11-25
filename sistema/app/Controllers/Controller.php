<?php

namespace App\Controllers;

use App\Models\Unidades;
use App\Models\Quartos;
use App\Models\Acolhidos;
use App\Models\Acolhimentos;
use App\Models\Ressocializacao;
use App\Models\Config;
use App\Models\Roles;
use App\Models\Mensalidades;
use App\Models\Users;
use App\Models\Mensagens;

class Controller
{
  protected $container;

    public function __construct($container)
    {
      $this->container = $container;

      setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

      date_default_timezone_set('America/Sao_Paulo');

      // ROLES
      $role = Roles::find($_SESSION['Role']);
      $acesso_usuario = unserialize($role['acesso']);
      $this->acesso_usuario = $acesso_usuario;
      $this->view->offsetSet("acesso_usuario", $acesso_usuario);
      // END ROLES

      // CONFIG
      $config = Config::find(1);
      $this->view->offsetSet("config", $config);
      $users = Users::orderBy('name', 'ASC')->get()->toArray();
      $this->view->offsetSet("users", $users);
      $mensagens = Mensagens::where('destinatario', $_SESSION['Auth'])->get()->toArray();
      for ($i=0; $i < count($mensagens) ; $i++) { 
        $remetente[$mensagens[$i]['id']] = Users::find($mensagens[$i]['remetente']);
        $this->view->offsetSet("remetente", $remetente);
      }
      $this->view->offsetSet("mensagens", $mensagens);
      // END CONFIG

      // TOP BAR
      $topbar = [
        'unidades' => Unidades::orderBy('id','ASC')->get()->toArray(),
        'quartos' => Quartos::where('unidade', $_SESSION['Unidade'])->orderBy('id','ASC')->get()->toArray(),
        'ressocializacoes' => Ressocializacao::get()->toArray(),
        'mensalidades' => Mensalidades::get()->toArray(),
        'count_mensagens' => Mensagens::where('destinatario', $_SESSION['Auth'])->where('data_leitura', '==', '0000-00-00 00:00:00')->count()
      ];

      for($i = 0; $i <= (count($topbar['unidades'])); $i++){
        @$id_unidade = $topbar['unidades'][$i]['id'];
        @$nome_unidade[$id_unidade] = Unidades::where('id', $id_unidade)->first();
        @$vagas_ocupadas_unidade[$id_unidade] = Acolhimentos::whereIn('status', [0, 1])->where('unidade', $id_unidade)->count();
        $this->view->offsetSet("vagas_ocupadas_unidade", $vagas_ocupadas_unidade);
        $this->view->offsetSet("nome_unidade", $nome_unidade);

        $vagas[$id_unidade] = Quartos::where('unidade', $id_unidade)->sum('vagas');
        $this->view->offsetSet("vagas", $vagas);
      }

      for($i = 0; $i <= (count($topbar['quartos'])); $i++){
        @$id_quarto = $topbar['quartos'][$i]['id'];
        @$vagas_ocupadas_quarto[$id_quarto] = Acolhimentos::whereIn('status', [0, 1])->where('unidade', $_SESSION['Unidade'])->where('quarto', $id_quarto)->count();
       
        $this->view->offsetSet("vagas_ocupadas_quarto", $vagas_ocupadas_quarto);

        @$vagas_quarto[$id_quarto] = Quartos::where('id', $id_quarto)->sum('vagas');
        $this->view->offsetSet("vagas_quarto", $vagas_quarto);
      }

      for ($i=0; $i < count($topbar['ressocializacoes']) ; $i++) { 
        @$id_resso = $topbar['ressocializacoes'][$i]['id'];
        @$acolhimento = Acolhimentos::find($topbar['ressocializacoes'][$i]['acolhimento']);
        @$acolhido[$id_resso] = Acolhidos::find($acolhimento['acolhido']);
            
        $this->view->offsetSet("topbar_acolhido", $acolhido);
      }

      for ($i=0; $i < count($topbar['mensalidades']) ; $i++) { 
        @$id_mensalidade = $topbar['mensalidades'][$i]['id'];
        @$acolhimento = Acolhimentos::find($topbar['mensalidades'][$i]['acolhimento']);
        @$acolhido[$id_mensalidade] = Acolhidos::find($acolhimento['acolhido']);
            
        $this->view->offsetSet("topbar_acolhido_mensalidade", $acolhido);
      }

      $this->view->offsetSet("topbar", $topbar);
      // END TOP BAR
    }

    public function __get($attribute)
    {
      if($this->container->{$attribute}){
        return $this->container->{$attribute};
      }
    }
}
