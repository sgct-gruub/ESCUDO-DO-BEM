<?php
error_reporting(E_ALL); 
ini_set('display_errors', 1);

	setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

	date_default_timezone_set('America/Sao_Paulo');

	session_start();

	if($_GET['atividades_desenvolvidas_mes'] != '' && $_GET['atividades_desenvolvidas_ano'] != ''){
		/* Carrega a classe DOMPdf */
		require_once("../public/assets/dompdf/dompdf_config.inc.php");

		/* Conecta no banco de dados */
		$servername = "localhost";
		$username = "root";
		$password = "!agruub273823@";
		$dbname = "pmqa";

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

		/* Seleciona o PAS */
        $sql_pas = "SELECT * FROM pas WHERE id = {$_GET['id']} LIMIT 1";
        $stmt_pas = $conn->query($sql_pas);
        $registros_pas = $stmt_pas->num_rows;
        $pas = $stmt_pas->fetch_assoc();

        /* Seleciona a Evolução */
	    $sql_atividade = "SELECT * FROM pas_atividades WHERE pas = {$_GET['id']} AND ano = {$_GET['atividades_desenvolvidas_ano']} AND mes = {$_GET['atividades_desenvolvidas_mes']} LIMIT 1";
	    $stmt_atividade = $conn->query($sql_atividade);
        $registros_atividade = $stmt_atividade->num_rows;
	    $atividade = $stmt_atividade->fetch_assoc();

	    if ($atividade['mes'] == 1) {
	    	$nome_mes = 'Janeiro';
	    }
	    if ($atividade['mes'] == 2) {
	    	$nome_mes = 'Fevereiro';
	    }
	    if ($atividade['mes'] == 3) {
	    	$nome_mes = 'Março';
	    }
	    if ($atividade['mes'] == 4) {
	    	$nome_mes = 'Abril';
	    }
	    if ($atividade['mes'] == 5) {
	    	$nome_mes = 'Maio';
	    }
	    if ($atividade['mes'] == 6) {
	    	$nome_mes = 'Junho';
	    }
	    if ($atividade['mes'] == 7) {
	    	$nome_mes = 'Julho';
	    }
	    if ($atividade['mes'] == 8) {
	    	$nome_mes = 'Agosto';
	    }
	    if ($atividade['mes'] == 9) {
	    	$nome_mes = 'Setembro';
	    }
	    if ($atividade['mes'] == 10) {
	    	$nome_mes = 'Outubro';
	    }
	    if ($atividade['mes'] == 11) {
	    	$nome_mes = 'Novembro';
	    }
	    if ($atividade['mes'] == 12) {
	    	$nome_mes = 'Dezembro';
	    }

        // $url_timbre = "http://maequeacolhe.com.br/sistema/public/uploads/config/timbre/" . $config['timbre'];
        $url_timbre = "../public/uploads/config/timbre/" . $config['timbre'];

        /* Se encontrar o PAS */
        if ($registros_pas > 0) {
        	if ($registros_atividade > 0) {
        	$e = '<html>
			<head>
				<meta charset="utf-8">
				<title>Plano de Atendimento Singular - Atividades</title>

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
            $e .= "<h2 align='center' style='margin-top: 40px;'>Plano de Atendimento Singular - Atividades</h2> <br />";

	        $e .= '<strong>Atividades desenvolvidas no mês de: </strong>' . $nome_mes . '/' . $atividade['ano'] . '<br />';

	        $e .= "<table width='100%'>";
	        $e .= "<thead>";
	        $e .= "<tr>";
	        $e .= "<th>Atividade</th>";
	        $e .= "<th>Período</th>";
	        $e .= "<th>Participa</th>";
	        $e .= "<th>Dispensado</th>";
	        $e .= "</tr>";
	        $e .= "</thead>";
			$sql_cronograma_atividades = "SELECT * FROM cronograma_atividades";
			$stmt_cronograma_atividades = $conn->query($sql_cronograma_atividades);
			while($atividades = $stmt_cronograma_atividades->fetch_assoc()){
				if($atividades['grupo'] == 0 or $atividades['grupo'] == ''){
					$sql_verifica_atividade = "SELECT * FROM pas_atividades WHERE pas = {$_GET['id']} AND ano = {$_GET['atividades_desenvolvidas_ano']} AND mes = {$_GET['atividades_desenvolvidas_mes']} AND atividade = {$atividades['id']}";
					$stmt_verifica_atividade = $conn->query($sql_verifica_atividade);
					$verifica_atividade = $stmt_verifica_atividade->fetch_assoc();
					if($verifica_atividade['status'] == 0){
						$participa = '';
						$dispensado = 'X';
					} else {
						$participa = 'X';
						$dispensado = '';
					}
					$e .= "<tr>";
					$e .= "<td>". $atividades['nome'] ."</td>";
					$e .= "<td>". $atividades['periodo'] ."</td>";
					$e .= "<td align='center'>". $participa ."</td>";
					$e .= "<td align='center'>". $dispensado ."</td>";
					$e .= "</tr>";
				}
			}

			$sql_grupos_cronograma_atividades = "SELECT * FROM grupos_cronograma_atividades";
			$stmt_grupos_cronograma_atividades = $conn->query($sql_grupos_cronograma_atividades);
			while($grupos_cronograma_atividades = $stmt_grupos_cronograma_atividades->fetch_assoc()){
				$e .= "<tr>";
				$e .= "<td colspan='4'><strong>". $grupos_cronograma_atividades['nome'] ."</strong></td>";
				$e .= "</tr>";

				$sql_cronograma_atividades = "SELECT * FROM cronograma_atividades WHERE grupo = {$grupos_cronograma_atividades['id']}";
				$stmt_cronograma_atividades = $conn->query($sql_cronograma_atividades);
				while($atividades = $stmt_cronograma_atividades->fetch_assoc()){
					$sql_verifica_atividade = "SELECT * FROM pas_atividades WHERE pas = {$_GET['id']} AND ano = {$_GET['atividades_desenvolvidas_ano']} AND mes = {$_GET['atividades_desenvolvidas_mes']} AND atividade = {$atividades['id']}";
					$stmt_verifica_atividade = $conn->query($sql_verifica_atividade);
					$verifica_atividade = $stmt_verifica_atividade->fetch_assoc();
					if($verifica_atividade['status'] == 0){
						$participa = '';
						$dispensado = 'X';
					} else {
						$participa = 'X';
						$dispensado = '';
					}
					$e .= "<tr>";
					$e .= "<td>- - - ". $atividades['nome'] ."</td>";
					$e .= "<td>". $atividades['periodo'] ."</td>";
					$e .= "<td align='center'>". $participa ."</td>";
					$e .= "<td align='center'>". $dispensado ."</td>";
					$e .= "</tr>";
				}
			}
			$e .= "</table>";

			$e .= '</body></html>';

	        } else {
	        	echo "Atividades de <strong>".$_GET['atividades_desenvolvidas_mes']."/".$_GET['atividades_desenvolvidas_ano']."</strong> não encontradas!";
	        }
        } else {
        	echo "P.A.S não encontrado!";
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
            "Plano de Atendimento Singular.pdf", /* Nome do arquivo de saída */
            array(
                "Attachment" => false /* Para download, altere para true */
            )
        );
	} else {
		echo "Escolha o mês e ano da atividade!";
	}

?>