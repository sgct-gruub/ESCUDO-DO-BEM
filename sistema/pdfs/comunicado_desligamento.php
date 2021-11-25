<?php
    use Dompdf\Dompdf;
    use Dompdf\Options;
    use Dompdf\Canvas;

    error_reporting(E_ALL); 
    ini_set('display_errors', 1);

	setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

	date_default_timezone_set('America/Sao_Paulo');

	session_start();

	if(isset($_GET['acolhimento']) && $_GET['acolhimento'] != ''){

		/* Carrega a classe DOMPdf */
		require_once("../public/assets/dompdf/autoload.inc.php");

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
        $sql_acolhimento = "SELECT * FROM acolhimentos_novo WHERE id = {$_GET['acolhimento']} LIMIT 1";
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
        }

        $sql_convenios = "SELECT * FROM convenios";
        $stmt_convenios = $conn->query($sql_convenios);
        $registros_convenios = $stmt_convenios->num_rows;

        // $url_timbre = "http://maequeacolhe.com.br/sistema/public/uploads/config/timbre/" . $config['timbre'];
        $url_timbre = "https://escudodobem.gruub.com.br/public/uploads/config/timbre/" . $config['timbre'];

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
        
        // $data_hoje = strftime('%d de %B de %Y', strtotime('today'));

        /* Se encontrar o acolhimento */
        if ($registros_acolhimento > 0) {

        	$e = '<html>
			<head>
				<meta charset="utf-8">
				<title>Comunicado de Desligamento - ' . strtoupper($acolhido["nome"]) . '</title>

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
            $e .= "<p align='right'>". $config['cidade'] .", ". $data_hoje ."</p>";
            
			$e .= "<p>
                A\C: <br />
                Órgãos de Assistência Social(CREAS/CRAS) <br />
                Unidades de Saúde (CAPS OU SECRETARIA DE SAÚDE) <br />
                <strong>Assunto: <u>COMUNICADO DE DESLIGAMENTO</u></strong> <br />
                Prezados, <br />
                Informo a vossa senhoria o desligamento, conforme discriminado a seguir, referente ao mês de ". $nome_mes .".
            </p>";

			$e .= "<table width='100%' align='center'>";
            $e .= "<tr>";
            $e .= "<th align='center'>Nome</th>";
            $e .= "<th align='center'>Documento de Identificação</th>";
            $e .= "<th align='center'>Desligamento na CT</th>";
            $e .= "</tr>";
            $e .= "<tr>";
            $e .= "<td align='center'>". $acolhido['nome'] ."</td>";
            $e .= "<td align='center'>". $acolhido['rg'] ."</td>";
            $e .= "<td align='center'>". date('d/m/Y', strtotime($acolhimento['data_saida'])) ."</td>";
            $e .= "</tr>";
            $e .= "</table>";

            $e .= "<br />";

            $e .= "<table width='100%' align='center' class='no-border'>";
            $e .= "<tr>";
            $e .= "<th colspan='4'>Tipo de Alta</th>";
            $e .= "</tr>";
            $e .= "<tr>";
            if($acolhimento['status'] == 11){
                $e .= "<td>( X ) Terapêutica</td>";
            } else {
                $e .= "<td>( ) Terapêutica</td>";
            }
            if($acolhimento['status'] == 13){
                $e .= "<td>( X ) Administrativa</td>";
            } else {
                $e .= "<td>( ) Administrativa</td>";
            }
            if($acolhimento['status'] == 12){
                $e .= "<td>( X ) Solicitada</td>";
            } else {
                $e .= "<td>( ) Solicitada</td>";
            }
            if($acolhimento['status'] == 14){
                $e .= "<td>( X ) Evasão</td>";
            } else {
                $e .= "<td>( ) Evasão</td>";
            }
            $e .= "</tr>";
            $e .= "</table>";

            $e .= "<br />";

            $e .= "<table width='100%' align='center'>";
            $e .= "<tr>";
            $e .= "<td>OBS.:<br />". nl2br($_GET['obs']) ."<br /></td>";
            $e .= "</tr>";
            $e .= "</table>";

            $e .= "<br />";
            $e .= "<br />";
            
            // assinaturas
            $e .= "<table width='100%' class='no-border'>";
            $e .= "<tr>";
            $e .= "<td align='right'>_______________________________________</td>";
            $e .= "</tr>";
            $e .= "<tr>";
            $e .= "<td align='right'>Assinatura Representante legal ou<br />Procurador do Representante</td>";
            $e .= "</tr>";
            $e .= "</table>";
            // assinaturas

            $e .= "<br />";
            
            // carimbos
            $e .= "<table width='49%' align='left'>";
            $e .= "<tr>";
            $e .= "<td align='center'>Órgão de Assistência Social <br />Recebido em _____/_____/_____ <br /><br /><br /><br /><br /><br />CARIMBO E ASSINATURA</td>";
            $e .= "</tr>";
            $e .= "</table>";
            // carimbos
            
            // carimbos
            $e .= "<table width='49%' align='right'>";
            $e .= "<tr>";
            $e .= "<td align='center'>Unidade de Saúde <br />Recebido em _____/_____/_____ <br /><br /><br /><br /><br /><br />CARIMBO E ASSINATURA</td>";
            $e .= "</tr>";
            $e .= "</table>";
            // carimbos

			$e .= '</body></html>';

        } else {
        	echo "Acolhimento não encontrado!";
        }


		/* Cria a instância */
        $options = new Options();
        $options->setIsRemoteEnabled(true);
        $dompdf = new Dompdf($options);

        /* Define o tipo de orientação da página */
        $dompdf->set_paper('A4', 'portrait');

        /* Carrega seu HTML */
        $dompdf->load_html($e);

        /* Renderiza */
        $dompdf->render();

        /* Exibe */
        $dompdf->stream(
            "COMUNICADO DE DESLIGAMENTO - " . strtoupper($acolhido["nome"]) . ".pdf", /* Nome do arquivo de saída */
            array(
                "Attachment" => false /* Para download, altere para true */
            )
        );
    }
?>