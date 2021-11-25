<?php
    use Dompdf\Dompdf;
    use Dompdf\Options;
    use Dompdf\Canvas;

    // error_reporting(E_ALL); 
    // ini_set('display_errors', 1);

	setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

	date_default_timezone_set('America/Sao_Paulo');

	session_start();

		/* Carrega a classe DOMPdf */
		require_once("../../public/assets/dompdf/autoload.inc.php");

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

        /* Seleciona a unidade */
        $sql_unidade = "SELECT * FROM unidades WHERE id = {$_POST['unidade']} LIMIT 1";
		$stmt_unidade = $conn->query($sql_unidade);
		$unidade = $stmt_unidade->fetch_assoc();

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

        	$e = '<html>
			<head>
				<meta charset="utf-8">
				<title>LISTA DE CHAMADA - '.strtoupper($unidade['nome']).'</title>

				<style type="text/css">
					@page {
						margin: 200px 30px 30px 30px;
						text-transform: uppercase;
					}

					html {
					  -webkit-text-size-adjust: 100%;
					      -ms-text-size-adjust: 100%;
					}

					body {
						font-family: helvetica;
						font-size: 0.6em;
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
					    padding: 2px;
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
            $e .= "<h2 align='center' style='margin-bottom: -20px;'>LISTA DE MEDICAÇÃO DIÁRIA - ".$unidade['nome']."</h2> <br />";
            $e .= "<p align='center'>".$data_hoje."</p>";

            $e .= "<table width='100%'>";
            $e .= "<thead>";
            $e .= "<tr style='font-weight: bold'>";
            $e .= "<td width='1%'>#</td>";
            $e .= "<td width='35%'>Acolhido</td>";
            $e .= "<td style='text-align: center'>MANHÃ</td>";
            $e .= "<td style='text-align: center'>ASSINATURA</td>";
            $e .= "<td style='text-align: center'>TARDE</td>";
            $e .= "<td style='text-align: center'>ASSINATURA</td>";
            $e .= "<td style='text-align: center'>NOITE</td>";
            $e .= "<td style='text-align: center'>ASSINATURA</td>";
            $e .= "</tr>";
            $e .= "</thead>";
            $e .= "<tbody>";

			/* Seleciona os acolhidos */
			$sql_acolhido = "SELECT * FROM acolhidos WHERE status = 1 ORDER BY nome ASC";
			$stmt_acolhido = $conn->query($sql_acolhido);
			$registros_acolhido = $stmt_acolhido->num_rows;
            $count = 0;
            while($acolhido = $stmt_acolhido->fetch_assoc()){

                /* Seleciona os acolhimentos */
				$sql_acolhimento = "SELECT * FROM acolhimentos_novo WHERE acolhido = {$acolhido['id']}";
				$stmt_acolhimento = $conn->query($sql_acolhimento);
				$registros_acolhimento = $stmt_acolhimento->num_rows;
				$acolhimento = $stmt_acolhimento->fetch_assoc();
				
				if($acolhimento['unidade'] == $_POST['unidade']){
                	$count++;
					$e .= "<tr>";
					$e .= "<td>".$count."</td>";
					$e .= "<td>".$acolhido['nome']."</td>";
					$e .= "<td></td>";
					$e .= "<td></td>";
					$e .= "<td></td>";
					$e .= "<td></td>";
					$e .= "<td></td>";
					$e .= "<td></td>";
					$e .= "</tr>";
            	}

            }

            $e .= "</tbody>";
            $e .= "</table>";

			$e .= '</body></html>';

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

        // $canvas = $dompdf->get_canvas();
        // $font = 'helvetica';
        // $canvas->page_text(25, 810, "Elaboração: Ana Paula Goes", $font, 5.4, array(0,0,0)); //footer
        // $canvas->page_text(185, 810, "Elaborado em 10/12/2020 - Revisão nº 01 em 10/12/2020 - Próxima revisão em 10/12/2021", $font, 5.4, array(0,0,0)); //footer
        // $canvas->page_text(480, 810, "Aprovação: Marcio Roberto Calbente", $font, 5.4, array(0,0,0)); //footer
        // $canvas->page_text(270, 820, "PÁGINA {PAGE_NUM}/{PAGE_COUNT}", $font, 5.4, array(0,0,0)); //footer

        /* Exibe */
        $dompdf->stream(
            "LISTA DE CHAMADA - " . strtoupper($unidade['nome']) . " - " . strtoupper($data_hoje) . ".pdf", /* Nome do arquivo de saída */
            array(
                "Attachment" => false /* Para download, altere para true */
            )
        );
?>