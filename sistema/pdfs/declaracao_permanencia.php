<?php
	setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

	date_default_timezone_set('America/Sao_Paulo');

	session_start();

    if(isset($_GET['acolhimento']) && $_GET['acolhimento'] != ''){

        /* Carrega a classe DOMPdf */
        require_once("../public/assets/dompdf/dompdf_config.inc.php");

        /* Conecta no banco de dados */
        $servername = "localhost";
        $username = "root";
        $password = "bb744e9e47";
        $dbname = "escudodobem";

        /* Cria a conexão */
        $conn = new mysqli($servername, $username, $password, $dbname);

        /* Checa a conexão */
        if ($conn->connect_error) {
            die("Conexão com o banco de dados falhou: " . $conn->connect_error);
        }

        /* Seleciona as configurações (nome da ct, cnpj, logo, timbre) */
        $sql_config = "SELECT * FROM configuracoes";
        $stmt_config = $conn->query($sql_config);
        $config = $stmt_config->fetch_assoc();

        /* Seleciona o acolhimento */
        $sql_acolhimento = "SELECT * FROM acolhimentos WHERE id = {$_GET['acolhimento']} LIMIT 1";
        $stmt_acolhimento = $conn->query($sql_acolhimento);
        $registros_acolhimento = $stmt_acolhimento->num_rows;
        $acolhimento = $stmt_acolhimento->fetch_assoc();

        /* Seleciona o acolhido */
        $sql_acolhido = "SELECT * FROM acolhidos WHERE id = {$acolhimento['acolhido']} LIMIT 1";
        $stmt_acolhido = $conn->query($sql_acolhido);
        $registros_acolhido = $stmt_acolhido->num_rows;
        $acolhido = $stmt_acolhido->fetch_assoc();

        /* Seleciona o convênio (se tiver) */
        if($acolhimento['tipo_acolhimento'] == 0){
            $sql_convenio = "SELECT * FROM convenios WHERE id = {$acolhimento['convenio']} LIMIT 1";
            $stmt_convenio = $conn->query($sql_convenio);
            $registros_convenio = $stmt_convenio->num_rows;
            $convenio = $stmt_convenio->fetch_assoc();

            if($convenio['id'] == 3){
                $tempo_tratamento = 12;
            } else {
                $tempo_tratamento = $config['tempo_tratamento'];
            }
        } else {
            $tempo_tratamento = $config['tempo_tratamento'];
        }

        // $url_timbre = "http://maequeacolhe.com.br/sistema/public/uploads/config/timbre/" . $config['timbre'];
        $url_timbre = "https://escudodobem.gruub.com.br/sistema/public/uploads/config/timbre/" . $config['timbre'];

        $data_acolhimento = explode('-', $acolhimento['data_inicio']);
        // $mes_hoje = $data_acolhimento[1];
        // $dia = explode(' ', $data_acolhimento[2]);
        // $dia = $dia[0];
        
        $mes_hoje = date('m');

        if ($mes_hoje == 1) {
            $nome_mes = 'Janeiro';
        }
        if ($mes_hoje == 2) {
            $nome_mes = 'Fevereiro';
        }
        if ($mes_hoje == 3) {
            $nome_mes = 'Março';
        }
        if ($mes_hoje == 4) {
            $nome_mes = 'Abril';
        }
        if ($mes_hoje == 5) {
            $nome_mes = 'Maio';
        }
        if ($mes_hoje == 6) {
            $nome_mes = 'Junho';
        }
        if ($mes_hoje == 7) {
            $nome_mes = 'Julho';
        }
        if ($mes_hoje == 8) {
            $nome_mes = 'Agosto';
        }
        if ($mes_hoje == 9) {
            $nome_mes = 'Setembro';
        }
        if ($mes_hoje == 10) {
            $nome_mes = 'Outubro';
        }
        if ($mes_hoje == 11) {
            $nome_mes = 'Novembro';
        }
        if ($mes_hoje == 12) {
            $nome_mes = 'Dezembro';
        }

        $data_hoje = date('d') . ' de ' . $nome_mes . ' de ' . date('Y');

        // data de acolhimento
        // $data_hoje = $dia . ' de ' . $nome_mes . ' de ' . $data_acolhimento[0];
        
        // $data_hoje = strftime('%d de %B de %Y', strtotime('today'));

        /* Se encontrar o acolhimento */
        if ($registros_acolhimento > 0) {

            $e = '<html>
            <head>
                <meta charset="utf-8">
                <title>Declaração de Permanência em Tratamento Terapêutico - ' . strtoupper($acolhido["nome"]) . '</title>

                <style type="text/css">
                    @page {
                        margin: 180px 30px 30px 30px;
                        text-transform: uppercase;
                    }

                    html {
                      -webkit-text-size-adjust: 100%;
                          -ms-text-size-adjust: 100%;
                    }

                    body {
                        font-family: helvetica;
                        font-size: 12px;
                        line-height: 1.96;
                    }

                    #head{
                        background-image: url(' . $url_timbre . ');
                        background-repeat: no-repeat;
                        height: 180px;
                        width: 100%;
                        position: fixed;
                        top: -180px;
                        left: 0;
                        right: 0;
                        margin: 0 auto;
                    }

                    table, td, th {    
                        border: 1px solid #ddd;
                        text-align: left;
                    }

                    table {
                        border-collapse: collapse;
                    }

                    th, td {
                        padding: 8px;
                    }

                    table.no-border, .no-border td, .no-border th {
                        border: 0;
                    }

                    thead:before, thead:after { display: none; }
                    tbody:before, tbody:after { display: none; }
                </style>
            </head>

            <body>';

            $e .= "<div id='head'></div>";
            $e .= "<p align='right'>Juquitiba, ". $data_hoje ."</p>";
            
            // $e .= "<p style='font-weight: bold;'>
   //              A\C <br />
   //              MM. Juíz de Direito da Titular na Comarca de Itapecerica da Serra/SP <br />
   //              4ª Vara Criminal – Itapecerica da Serra <br />
   //              Dr. Luiz Gustavo de Oliveira Martins Pereira <br />
   //              Processo Execução nº 1.185.294 <br />
   //          </p>";
            $e .= "<h2 align='center' style='margin-top: 40px;'>Declaração</h2> <br />";

            $e .= "<p>
                Eu Thiago Buscarioli Colares (Procurador) inscrito sob o CPF de Nº 315.190.258-
                82 e sob o RG de Nº 35.028.774-0, declaro que o Sr. ". $acolhido['nome'] ." portador do RG de nº " . $acolhido['rg'] . ", ingressou de livre e espontânea vontade em nosso Programa Terapêutico de Recuperação de dependentes de drogas psicoativas, na data de ". date('d/m/Y', strtotime($acolhimento['data_inicio'])) ." e tem duração de " . $tempo_tratamento ." meses, segundo o convênio com o ". $convenio['nome'] ." no Estado de São Paulo.</p>";
            $e .= "<p>
                Nosso programa conta com auxilio de Psicólogas, Atividades Terapêuticas,
                Espiritualidade, variados cursos e do Esporte e Cultura.
                Sendo assim no período que o mesmo estiver sobre os cuidados de nossa
                instituição se colocamos a disposição para dar parecer mensal sobre o mesmo, caso
                haja o desligamento imediatamente comunicaremos a mesma.</p>";
            // $e .= "<p>
            //     Em anexo, cópia do Documento e Procuração do Thiago Buscarioli Colares, do RG,
            //     do Termo de Comparecimento Mensal e do Termo de Adesão e Gratuidade do
            //     ". $convenio['nome'] ." do Acolhido ". $acolhido['nome'] .".
            // </p>";

            $e .= "<br />";
            $e .= "<br />";
            $e .= "<br />";

            // assinaturas
            $e .= "<table width='100%' align='center' class='no-border'>";
            $e .= "<tr>";
            $e .= "<td align='left'>Sem mais a declarar,</td>";
            $e .= "</tr>";
            $e .= "<tr>";
            $e .= "<td align='center'>_______________________________________</td>";
            $e .= "</tr>";
            $e .= "<tr>";
            $e .= "<td align='center'>Thiago Buscarioli Colares<br />DIRETOR</td>";
            $e .= "</tr>";
            $e .= "</table>";
            // assinaturas

            $e .= '</body></html>';

        } else {
            echo "Acolhimento não encontrado!";
        }


        /* Cria a instância */
        $dompdf = new DOMPDF();

        /* Define o tipo de orientação da página */
        $dompdf->set_paper('A4', 'portrait');

        /* Carrega seu HTML */
        $dompdf->load_html($e);

        /* Renderiza */
        $dompdf->render();

        /* Exibe */
        $dompdf->stream(
            "DECLARACAO DE PERMANENCIA - " . strtoupper($acolhido["nome"]) . ".pdf", /* Nome do arquivo de saída */
            array(
                "Attachment" => false /* Para download, altere para true */
            )
        );
    }

    // for ($i=0; $i < 3; $i++) {
    //     $repetir_a_cada = 3;
    //     $tipo_repeticao = 'years';
    //     $data = strtotime('2019-01-28');

    //     for ($x=0; $x < $repetir_a_cada; $x++) { 
    //         $nova_data =  date('Y-m-d', strtotime('+'.$repetir_a_cada.' '.$tipo_repeticao, $data));
    //     }

    //     $data_vencimento = date('Y-m-d', strtotime('+'.$i.' '.$tipo_repeticao, $data));

    //     $teste = date('Y-m-d', strtotime('+'.$repetir_a_cada.' '.$tipo_repeticao));

    //     $parcela = $i + 1;

    //     $recorrencia = [
    //         'parcela' => $parcela,
    //         'repetir_a_cada' => $repetir_a_cada,
    //         'data_vencimento' => $data_vencimento,
    //     ];
        
    //     echo "<pre>";
    //     print_r($data_vencimento);
    //     echo "<br />";
    //     print_r($nova_data);
    //     echo "</pre>";
    // }
?>