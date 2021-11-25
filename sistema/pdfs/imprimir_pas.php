<?php
	setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

	date_default_timezone_set('America/Sao_Paulo');

	session_start();

	if($_GET['evolucao_mes'] != '' && $_GET['evolucao_ano'] != ''){
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

		/* Seleciona o PAS */
        $sql_pas = "SELECT * FROM pas WHERE id = {$_GET['id']} LIMIT 1";
        $stmt_pas = $conn->query($sql_pas);
        $registros_pas = $stmt_pas->num_rows;
        $pas = $stmt_pas->fetch_assoc();

        // $url_timbre = "http://maequeacolhe.com.br/sistema/public/uploads/config/timbre/" . $config['timbre'];
        $url_timbre = "https://escudodobem.gruub.com.br/public/uploads/config/timbre/" . $config['timbre'];

        /* Se encontrar o PAS */
        if ($registros_pas > 0) {
        	$e = '<html>
			<head>
				<meta charset="utf-8">
				<title>Plano de Atendimento Singular</title>

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
            $e .= "<h2 align='center' style='margin-top: 40px;'>Plano de Atendimento Singular - Evolução</h2> <br />";

			/* Seleciona a Evolução */
	        $sql_evolucao = "SELECT * FROM pas_evolucao WHERE pas = {$_GET['id']} AND ano = {$_GET['evolucao_ano']} AND mes = {$_GET['evolucao_mes']} LIMIT 1";
	        $stmt_evolucao = $conn->query($sql_evolucao);
	        $evolucao = $stmt_evolucao->fetch_assoc();

	        if ($evolucao['mes'] == 1) {
				$nome_mes = 'Janeiro';
			}
			if ($evolucao['mes'] == 2) {
				$nome_mes = 'Fevereiro';
			}
			if ($evolucao['mes'] == 3) {
				$nome_mes = 'Março';
			}
			if ($evolucao['mes'] == 4) {
				$nome_mes = 'Abril';
			}
			if ($evolucao['mes'] == 5) {
				$nome_mes = 'Maio';
			}
			if ($evolucao['mes'] == 6) {
				$nome_mes = 'Junho';
			}
			if ($evolucao['mes'] == 7) {
				$nome_mes = 'Julho';
			}
			if ($evolucao['mes'] == 8) {
				$nome_mes = 'Agosto';
			}
			if ($evolucao['mes'] == 9) {
				$nome_mes = 'Setembro';
			}
			if ($evolucao['mes'] == 10) {
				$nome_mes = 'Outubro';
			}
			if ($evolucao['mes'] == 11) {
				$nome_mes = 'Novembro';
			}
			if ($evolucao['mes'] == 12) {
				$nome_mes = 'Dezembro';
			}

	        $e .= '<strong>Evolução no mês de: </strong>' . $nome_mes . '/' . $evolucao['ano'] . '<br />';

	        $e .= '<strong>Descrever o que o Acolhido evoluiu, ocorrências do Mês e o que precisa ser trabalhado para o próximo mês</strong>';
	        $e .= '<p>'.nl2br($evolucao['descricao']).'</p>';

			$e .= '</body></html>';

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
		echo "Escolha o mês e ano da evolução!";
	}

?>