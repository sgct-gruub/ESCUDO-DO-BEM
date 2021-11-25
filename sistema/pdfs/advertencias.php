<?php
	use Dompdf\Dompdf;
    use Dompdf\Options;
    use Dompdf\Canvas;

    require_once "Config.php";

    /* Carrega a classe DOMPdf */
	require_once("../public/assets/dompdf/autoload.inc.php");

    error_reporting(E_ALL); 
    ini_set('display_errors', 1);

	setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

	date_default_timezone_set('America/Sao_Paulo');

	session_start();

	if(isset($_GET['imprimir']) && $_GET['imprimir'] != ''){

		/* Seleciona as configurações (nome da ct, cnpj, logo, timbre) */
		$config = db()->query("SELECT * FROM configuracoes")->fetch_assoc();

        /* Seleciona a advertencia */
		$advertencia = db()->query("SELECT * FROM funcionarios_advertencias WHERE id = {$_GET['imprimir']} LIMIT 1")->fetch_assoc();

        /* Seleciona o funcionario */
        $funcionario = db()->query("SELECT * FROM funcionarios WHERE id = {$advertencia['funcionario']} LIMIT 1")->fetch_assoc();

        // $url_timbre = "http://maequeacolhe.com.br/sistema/public/uploads/config/timbre/" . $config['timbre'];
        $url_timbre = "https://escudodobem.gruub.com.br/sistema/public/uploads/config/timbre/" . $config['timbre'];

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
        if ($advertencia) {

        	$e = '<html>
			<head>
				<meta charset="utf-8">
				<title>ADVERTÊNCIA - ' . strtoupper($funcionario["nome"]) . '</title>

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
            $e .= "<h2 align='center' style='margin-top: 40px;'>ADVERTÊNCIA</h2> <br />";

			$e .= "<p>
                Eu, ". $advertencia['quem_aplicou'] ." na condição de " . $advertencia['condicao_quem_aplicou'] . " desta instituição, venho por meio desta, advertir o colaborador ". $funcionario['nome'] .", ". $advertencia['motivo'] . "</p>";
            
            $e .= "<p>Estando de acordo e ciente as orientações, assina ciência.</p>";

            $e .= "<br />";
            $e .= "<br />";

            $e .= "<p align='right'>Juquitiba, ". $data_hoje ."</p>";

			$e .= "<br />";
			$e .= "<br />";
			$e .= "<br />";

			// assinaturas
			$e .= "<table width='100%' align='center' class='no-border'>";
            $e .= "<tr>";
            $e .= "<td align='center'>_______________________________________</td>";
            $e .= "</tr>";
			$e .= "<tr>";
			$e .= "<td align='center'>". $funcionario['nome'] ."<br />". $funcionario['cpf'] ."</td>";
			$e .= "</tr>";
			$e .= "</table>";

			$e .= "<br />";
			$e .= "<br />";
			$e .= "<br />";

			$e .= "<table width='49%' align='left' class='no-border'>";
            $e .= "<tr>";
            $e .= "<td align='center'>_______________________________________</td>";
            $e .= "</tr>";
			$e .= "<tr>";
			$e .= "<td align='center'>Thiago Buscarioli Colares<br />(Diretor)</td>";
			$e .= "</tr>";
			$e .= "</table>";

			$e .= "<table width='49%' align='right' class='no-border'>";
            $e .= "<tr>";
            $e .= "<td align='center'>_______________________________________</td>";
            $e .= "</tr>";
			$e .= "<tr>";
			$e .= "<td align='center'>". $advertencia['quem_aplicou'] ."<br />(". $advertencia['condicao_quem_aplicou'] .")</td>";
			$e .= "</tr>";
			$e .= "</table>";
			// assinaturas

			$e .= '</body></html>';

        } else {
        	echo "Advertência não encontrada!";
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
            "ADVERTENCIA - " . strtoupper($funcionario["nome"]) . ".pdf", /* Nome do arquivo de saída */
            array(
                "Attachment" => false /* Para download, altere para true */
            )
        );
    }
?>