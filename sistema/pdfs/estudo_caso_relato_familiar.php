<?php
	setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

	date_default_timezone_set('America/Sao_Paulo');

	session_start();

	if(isset($_GET['id']) && $_GET['id'] != ''){
		/* Carrega a classe DOMPdf */
		require_once("../public/assets/dompdf/dompdf_config.inc.php");

		/* Conecta no banco de dados */
		$servername = "localhost";
		$username = "root";
		$password = "!agruub273823@";
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

		/* Seleciona o Estudo de Caso */
        $sql_estudo_caso = "SELECT * FROM estudo_caso WHERE id = {$_GET['id']} LIMIT 1";
        $stmt_estudo_caso = $conn->query($sql_estudo_caso);
        $registros_estudo_caso = $stmt_estudo_caso->num_rows;
        $estudo_caso = $stmt_estudo_caso->fetch_assoc();

	    if ($meta['mes'] == 1) {
	    	$nome_mes = 'Janeiro';
	    }
	    if ($meta['mes'] == 2) {
	    	$nome_mes = 'Fevereiro';
	    }
	    if ($meta['mes'] == 3) {
	    	$nome_mes = 'Março';
	    }
	    if ($meta['mes'] == 4) {
	    	$nome_mes = 'Abril';
	    }
	    if ($meta['mes'] == 5) {
	    	$nome_mes = 'Maio';
	    }
	    if ($meta['mes'] == 6) {
	    	$nome_mes = 'Junho';
	    }
	    if ($meta['mes'] == 7) {
	    	$nome_mes = 'Julho';
	    }
	    if ($meta['mes'] == 8) {
	    	$nome_mes = 'Agosto';
	    }
	    if ($meta['mes'] == 9) {
	    	$nome_mes = 'Setembro';
	    }
	    if ($meta['mes'] == 10) {
	    	$nome_mes = 'Outubro';
	    }
	    if ($meta['mes'] == 11) {
	    	$nome_mes = 'Novembro';
	    }
	    if ($meta['mes'] == 12) {
	    	$nome_mes = 'Dezembro';
	    }

        // $url_timbre = "http://maequeacolhe.com.br/sistema/public/uploads/config/timbre/" . $config['timbre'];
        $url_timbre = "https://escudodobem.gruub.com.br/public/uploads/config/timbre/" . $config['timbre'];

        /* Se encontrar o ESTUDO DE CASO */
        if ($registros_estudo_caso > 0) {
        	$e = '<html>
			<head>
				<meta charset="utf-8">
				<title>Estudo de Caso - Relato Familiar</title>

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
            $e .= "<h2 align='center' style='margin-top: 40px;'>Estudo de Caso - Relato Familiar</h2> <br />";

	        $e .= '<strong>Relato familiar (Problemáticas apontadas pela família)</strong>';
	        $e .= '<p>'.nl2br($estudo_caso['relato_familiar']).'</p>';

			$e .= '</body></html>';
        } else {
        	echo "Estudo de Caso não encontrado!";
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
            "Estudo de Caso - Relato Familiar.pdf", /* Nome do arquivo de saída */
            array(
                "Attachment" => false /* Para download, altere para true */
            )
        );
	} else {
		echo "Escolha o mês e ano da evolução!";
	}

?>