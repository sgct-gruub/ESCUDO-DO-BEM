<?php
	error_reporting(E_ALL); 
	ini_set('display_errors', 1);
	
	setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

	date_default_timezone_set('America/Sao_Paulo');

	session_start();


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

        /* Seleciona os ofícios */
        $sql = "SELECT * FROM oficios";
        $stmt = $conn->query($sql);
        $registros = $stmt->num_rows;

        // /* Seleciona o acolhimento */
        // $sql_acolhimento = "SELECT * FROM acolhimentos WHERE id = {$_GET['acolhimento']} LIMIT 1";
        // $stmt_acolhimento = $conn->query($sql_acolhimento);
        // $registros_acolhimento = $stmt_acolhimento->num_rows;
        // $acolhimento = $stmt_acolhimento->fetch_assoc();

        // /* Seleciona o acolhido */
        // $sql_acolhido = "SELECT * FROM acolhidos WHERE id = {$acolhimento['acolhido']} LIMIT 1";
        // $stmt_acolhido = $conn->query($sql_acolhido);
        // $registros_acolhido = $stmt_acolhido->num_rows;
        // $acolhido = $stmt_acolhido->fetch_assoc();

        // $url_timbre = "http://maequeacolhe.com.br/sistema/public/uploads/config/timbre/" . $config['timbre'];
        $url_timbre = "https://escudodobem.gruub.com.br/public/uploads/config/timbre/" . $config['timbre'];

        /* Se encontrar o acolhimento */
        if ($registros > 0) {

        	$e = '<html>
			<head>
				<meta charset="utf-8">
				<title>Atividades Terapêuticas de Cuidado</title>

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
						font-size: 10px;
					}

					#head{
						background-image: url(' . $url_timbre . ');
						background-repeat: no-repeat;
						height: 180px;
						width: 100%;
						position: fixed;
						top: -180px;
						left: 12.5%;
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
					    padding: 3px;
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
            $e .= "<h2 align='center' style='margin-top: 40px;'>Atividades Terapêuticas de Cuidado ". $config['nome_fantasia'] . "</h2> <br />";
            // $e .= "<h4 align='left'>MEDIADOR GERAL NÃO FAZ OFÍCIO FIXO</h4> <br />";

			$e .= "<table width='100%' align='center'>";
            $e .= "<thead>";
            $e .= "<tr>";
            $e .= "<th width='15%'>A.T.C</th>";
            $e .= "<th width='60%'>ATIVIDADES</th>";
            $e .= "<th width='25%'>ACOLHIDOS</th>";
            $e .= "</tr>";
            $e .= "</thead>";
            $e .= "<tbody>";
            while($oficio = $stmt->fetch_assoc()){
            	$nome_acolhido = '';
            	// $responsaveis = explode(',' , $oficio['acolhimentos']);

            	$sql_acolhimento = "SELECT * FROM acolhimentos WHERE id IN ({$oficio['acolhimentos']})";
		        $stmt_acolhimento = $conn->query($sql_acolhimento);
		        

            	$e .= "<tr>";
	            $e .= "<td>". $oficio['nome'] ."</td>";
	            $e .= "<td>". $oficio['atividades'] ."</td>";
	            $e .= "<td>";
	            while($acolhimento = $stmt_acolhimento->fetch_assoc()){
		        	$sql_acolhido = "SELECT * FROM acolhidos WHERE id = '{$acolhimento['acolhido']}'";
		        	$stmt_acolhido = $conn->query($sql_acolhido);
		        	$acolhido = $stmt_acolhido->fetch_assoc();
		        	if($oficio['responsavel'] == $acolhimento['id']){
	            		$e .= "<strong style='color: red;'>". $acolhido['nome'] . "</strong><br />";
		        	} else {
	            		$e .= $acolhido['nome'] . "<br />";
		        	}
		        	// $nome_acolhido .= $acolhido['nome'] . "<br />";
		        }

	            // $e .= "<td>". $nome_acolhido ."</td>";
	            $e .= "</td>";
	            $e .= "</tr>";
            }
			$e .= "</tbody>";
			$e .= "</table>";

			$e .= '</body></html>';


		/* Cria a instância */
        $dompdf = new DOMPDF();

        /* Define o tipo de orientação da página */
        $dompdf->set_paper('A4', 'landscape');

        /* Carrega seu HTML */
        $dompdf->load_html($e);

        /* Renderiza */
        $dompdf->render();

        /* Exibe */
        $dompdf->stream(
            "Atividades Terapêuticas de Cuidado.pdf", /* Nome do arquivo de saída */
            array(
                "Attachment" => false /* Para download, altere para true */
            )
        );
    }
?>