<?php

namespace App\Controllers;

use Slim\Views\Twig as View;
use App\Models\Acolhidos;
use App\Models\Acolhimentos;
use App\Models\FichaEntrevista;
use App\Models\Unidades;
use App\Models\Convenios;
// use \DateTime;

class GraficosController extends Controller
{

    public function index ($request, $response)
    {
        if($this->acesso_usuario['graficos']['index']['r'] != 'on'){
            return $this->container->view->render($response->withStatus(403), 'error/403.html');
        }

        $mensagem = $this->flash->getMessages();

        // TIPOS DE ALTA
        $alta_terapeutica = Acolhimentos::where('status', 11)->count();
        $alta_solicitada = Acolhimentos::where('status', 12)->count();
        $alta_administrativa = Acolhimentos::where('status', 13)->count();
        $alta_evasao = Acolhimentos::where('status', 14)->count();

        $acolhimentos = Acolhimentos::orderBy('id', 'desc')->get()->toArray();

        /* gráfico faixa etária */
        $idade_menor_18 = '';
        $idade_18_24 = '';
        $idade_25_30 = '';
        $idade_31_40 = '';
        $idade_41_50 = '';
        $idade_51_60 = '';
        $idade_maior_60 = '';
        $idade_count_acolhimentos = 0;
        /* end faixa etária */

        /* gráfico drogas de abuso */
        $nao_tem_droga_abuso = '';
        $alcool = '';
        $maconha = '';
        $sedativos = '';
        $cocaina = '';
        $crack = '';
        $estimulantes = '';
        $alucinogenos = '';
        $heroina = '';
        $solventes = '';
        $outros = '';
        /* end drogas de abuso */

        /* gráfico religião */
        $religiao_nao_especificada = FichaEntrevista::where('religiao', null)->count();
        $nao_tem_religiao = FichaEntrevista::where('religiao', 0)->count();
        $catolica = FichaEntrevista::where('religiao', 1)->count();
        $evangelica = FichaEntrevista::where('religiao', 2)->count();
        $espirita = FichaEntrevista::where('religiao', 3)->count();
        $judaica = FichaEntrevista::where('religiao', 4)->count();
        $afro = FichaEntrevista::where('religiao', 5)->count();
        $orientais = FichaEntrevista::where('religiao', 6)->count();
        $outra = FichaEntrevista::where('religiao', 7)->count();
        $nao_sabe = FichaEntrevista::where('religiao', 8)->count();
        /* end religião */

        /* gráfico escolaridade */
        $escolaridade_nao_especificada = FichaEntrevista::where('escolaridade', null)->orWhere('escolaridade', 0)->count();
        $analfabeto = FichaEntrevista::where('escolaridade', 1)->count();
        $efi = FichaEntrevista::where('escolaridade', 2)->count();
        $efc = FichaEntrevista::where('escolaridade', 3)->count();
        $emi = FichaEntrevista::where('escolaridade', 4)->count();
        $emc = FichaEntrevista::where('escolaridade', 5)->count();
        $esi = FichaEntrevista::where('escolaridade', 6)->count();
        $esc = FichaEntrevista::where('escolaridade', 7)->count();
        /* end escolaridade */


        foreach ($acolhimentos as $key => $acolhimento) {
        
        /* gráfico faixa etária */
            $acolhido = Acolhidos::find($acolhimento['acolhido']);
            $ficha_entrevista = FichaEntrevista::where('acolhimento', $acolhimento['id'])->first();

            $data_atual = new \DateTime( date("Y-m-d") );
            $data = new \DateTime( $acolhido['data_nascimento'] );

            $intervalo = $data_atual->diff( $data );
            
            if($intervalo->y > 1)
            {
                $idade_count_acolhimentos++;

                $idade = $intervalo->y;

                $count_menor_18 = 0;
                if($idade < 18){
                    $count_menor_18++;
                    $idade_menor_18 += $count_menor_18++;
                }

                $count_18_24 = 0;
                if($idade >= 18 && $idade <= 24){
                    $count_18_24++;
                    $idade_18_24 += $count_18_24++;
                }

                $count_25_30 = 0;
                if($idade >= 25 && $idade <= 30){
                    $count_25_30++;
                    $idade_25_30 += $count_25_30++;
                }

                $count_31_40 = 0;
                if($idade >= 31 && $idade <= 40){
                    $count_31_40++;
                    $idade_31_40 += $count_31_40++;
                }

                $count_41_50 = 0;
                if($idade >= 41 && $idade <= 50){
                    $count_41_50++;
                    $idade_41_50 += $count_41_50++;
                }

                $count_51_60 = 0;
                if($idade >= 51 && $idade <= 60){
                    $count_51_60++;
                    $idade_51_60 += $count_51_60++;
                }

                $count_maior_60 = 0;
                if($idade > 60){
                    $count_maior_60++;
                    $idade_maior_60 += $count_maior_60++;
                }
            }
        /* end faixa etária */


        /* gráfico drogas de abuso */
            $dependencia_quimica = unserialize($ficha_entrevista['dependencia_quimica']);

            // droga de abuso (não marcado no form do acolhimento)
            $count_0 = 0;
            if($dependencia_quimica['droga_de_abuso'] == ''){
                $count_0++;
                $nao_tem_droga_abuso += $count_0;
            }
            // alcool
            $count_1 = 0;
            if($dependencia_quimica['droga_de_abuso'] == '1'){
                $count_1++;
                $alcool += $count_1;
            }
            // maconha
            $count_2 = 0;
            if($dependencia_quimica['droga_de_abuso'] == '2'){
                $count_2++;
                $maconha += $count_2;
            }
            // sedativos
            $count_3 = 0;
            if($dependencia_quimica['droga_de_abuso'] == '3'){
                $count_3++;
                $sedativos += $count_3;
            }
            // cocaina
            $count_4 = 0;
            if($dependencia_quimica['droga_de_abuso'] == '4'){
                $count_4++;
                $cocaina += $count_4;
            }
            // crack
            $count_5 = 0;
            if($dependencia_quimica['droga_de_abuso'] == '5'){
                $count_5++;
                $crack += $count_5;
            }
            // estimulantes
            $count_6 = 0;
            if($dependencia_quimica['droga_de_abuso'] == '6'){
                $count_6++;
                $estimulantes += $count_6;
            }
            // alucinogenos
            $count_7 = 0;
            if($dependencia_quimica['droga_de_abuso'] == '7'){
                $count_7++;
                $alucinogenos += $count_7;
            }
            // heroina
            $count_8 = 0;
            if($dependencia_quimica['droga_de_abuso'] == '8'){
                $count_8++;
                $heroina += $count_8;
            }
            // solventes
            $count_9 = 0;
            if($dependencia_quimica['droga_de_abuso'] == '9'){
                $count_9++;
                $solventes += $count_9;
            }
            $count_10 = 0;
            if($dependencia_quimica['droga_de_abuso'] == '10'){
                $count_10++;
                $outros += $count_10;
            }
        /* end drogas de abuso */

        }
        
        // echo "<hr />";

        // echo "< 18 anos: " . $idade_menor_18 . "<br />";
        // echo "18-24 anos: " . $idade_18_24 . "<br />";
        // echo "25-30 anos: " . $idade_25_30 . "<br />";
        // echo "31-39 anos: " . $idade_31_39 . "<br />";
        // echo "40-49 anos: " . $idade_40_49 . "<br />";
        // echo "50-60 anos: " . $idade_50_60 . "<br />";
        // echo "> 60 anos: " . $idade_maior_60 . "<br />";
        
        // echo "<hr />";

        // echo $idade_count_acolhimentos;

        // exit;

        return $this->view->render($response, 'graficos/index.html', [
            'Titulo_Pagina' => 'Gráficos',
            'titulo' => 'Gráficos',
            'flash' => $mensagem,
            'alta_terapeutica' => $alta_terapeutica,
            'alta_solicitada' => $alta_solicitada,
            'alta_administrativa' => $alta_administrativa,
            'alta_evasao' => $alta_evasao,
            'nao_tem_droga_abuso' => $nao_tem_droga_abuso,
            'alcool' => $alcool,
            'maconha' => $maconha,
            'sedativos' => $sedativos,
            'cocaina' => $cocaina,
            'crack' => $crack,
            'estimulantes' => $estimulantes,
            'alucinogenos' => $alucinogenos,
            'heroina' => $heroina,
            'solventes' => $solventes,
            'outros' => $outros,
            'idade_menor_18' => $idade_menor_18,
            'idade_18_24' => $idade_18_24,
            'idade_25_30' => $idade_25_30,
            'idade_31_40' => $idade_31_40,
            'idade_41_50' => $idade_41_50,
            'idade_51_60' => $idade_51_60,
            'idade_maior_60' => $idade_maior_60,
            'idade_count_acolhimentos' => $idade_count_acolhimentos,
            'religiao_nao_especificada' => $religiao_nao_especificada,
            'nao_tem_religiao' => $nao_tem_religiao,
            'catolica' => $catolica,
            'evangelica' => $evangelica,
            'espirita' => $espirita,
            'judaica' => $judaica,
            'afro' => $afro,
            'orientais' => $orientais,
            'outra' => $outra,
            'nao_sabe' => $nao_sabe,
            'escolaridade_nao_especificada' => $escolaridade_nao_especificada,
            'analfabeto' => $analfabeto,
            'efi' => $efi,
            'efc' => $efc,
            'emi' => $emi,
            'emc' => $emc,
            'esi' => $esi,
            'esc' => $esc,
        ]);
    }

}
