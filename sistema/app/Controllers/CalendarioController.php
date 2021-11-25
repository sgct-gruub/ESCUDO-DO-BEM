<?php

namespace App\Controllers;

use App\Controllers\Controller;
use Respect\Validation\Validator as validate;
use Slim\Views\Twig as View;

use App\Models\Acolhidos;
use App\Models\Acolhimentos;
use App\Models\Ressocializacao;
use App\Models\Encaminhamentos;
use App\Models\Timeline;

class CalendarioController extends Controller
{

	// Exibe o calendário de ressocializações
    public function listar ($request, $response, $args)
    {   
        $item = Ressocializacao::orderBy('id','ASC')->get()->toArray();
        
        $acolhimentos = Acolhimentos::whereIn('status', [0, 1])->get()->toArray();

        for($i = 0; $i < count($acolhimentos); $i++){
            $id_acolhido = $acolhimentos[$i]['acolhido'];
            $acolhido[$id_acolhido] = Acolhidos::find($id_acolhido);

            $this->view->offsetSet("acolhido", $acolhido); 
        }
        
        foreach($item as $row)
        {
            $data_hoje = date('Y-m-d');
            $time_data_hoje = strtotime($data_hoje);

            $data_retorno = $row['data_retorno'];
            $time_data_retorno = strtotime($data_retorno);

            if( ($row['status'] != 2) and ($time_data_hoje >= $time_data_retorno) ){
                $color = '#ffbb44';
            }

            if( ($row['status'] != 2) and ($time_data_hoje < $time_data_retorno) ){
                $color = '#41B3F9';
            }

            if($row['status'] == 1){
                $color = '#f33155';
            }

            if($row['status'] == 2){
                $color = '#7ace4c';
            }

            $inicio = date('d/m/Y', strtotime($row["data_saida"]));
            $termino = date('d/m/Y', strtotime($row["data_retorno"]));
            $data[] = array(
                'id' => $row["id"],
                'title' => $row["title"],
                'start' => $row["start"],
                'end' => $row["end"],
                'acolhimento' => $row["acolhimento"],
                'inicio' => $inicio,
                'termino' => $termino,
                'observacoes' => $row["observacoes"],
                'status' => $row["status"],
                'className' => $row["category"],
                'color' => $color
            );
        }

        $item_e = Encaminhamentos::orderBy('id','ASC')->get()->toArray();
        
        foreach($item_e as $row_e)
        {
         $inicio = date('d/m/Y', strtotime($row_e["start"]));
         $hora = date('H:i', strtotime($row_e["start"]));
         // $termino = date('d/m/Y', strtotime($row["end"]));
         // if($termino = '30/11/-0001' OR $termino = '' OR $termino = '0000-00-00'){
         //    $termino = '';
         // }
         $data_e[] = array(
          'id' => $row_e["id"],
          'title' => $row_e["title"],
          'start' => $row_e["start"],
          'end' => $row_e["end"],
          'acolhimento' => $row_e["acolhimento"],
          'tipo' => $row_e["tipo"],
          'motivo' => $row_e["motivo"],
          'local' => $row_e["local"],
          'cep' => $row_e["cep"],
          'endereco' => $row_e["endereco"],
          'num' => $row_e["num"],
          'bairro' => $row_e["bairro"],
          'cidade' => $row_e["cidade"],
          'uf' => $row_e["uf"],
          'telefone' => $row_e["telefone"],
          'celular' => $row_e["celular"],
          'inicio' => $inicio,
          'hora' => $hora,
          'observacoes' => $row_e["observacoes"],
          'usuario' => $row_e["usuario"],
          'status' => $row_e["status"],
          'className' => $row_e["category"],
         );
        }

        $all_data = array_merge($data, $data_e);
        $this->view->offsetSet("item", $all_data); 

        $mensagem = $this->flash->getMessages();

        return $this->view->render($response, 'calendario/listar.html', [
            'Titulo_Pagina' => 'Calendário Geral',
            'view'      => 'listar',
            'flash'     => $mensagem,
            'itens'     => $item,
            'acolhimentos' => $acolhimentos,
        ]);
    }

}