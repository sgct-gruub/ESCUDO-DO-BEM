<?php
	use Dompdf\Dompdf;
    use Dompdf\Options;
    use Dompdf\Canvas;

    // error_reporting(E_ALL); 
    // ini_set('display_errors', 1);

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

        // $url_timbre = "http://maequeacolhe.com.br/sistema/public/uploads/config/timbre/" . $config['timbre'];
        $url_timbre = "https://escudodobem.gruub.com.br/public/uploads/config/timbre/" . $config['timbre'];

        $data_saida = explode('-', $acolhimento['data_saida']);
        $mes_hoje = $data_saida[1];
        $dia = explode(' ', $data_saida[2]);
        $dia = $dia[0];

        // $mes_hoje = date('m');

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

		// $data_hoje = date('d') . ' de ' . $nome_mes . ' de ' . date('Y');

		// data de acolhimento
		$data_hoje = $dia . ' de ' . $nome_mes . ' de ' . $data_saida[0];
        
        // $data_hoje = strftime('%d de %B de %Y', strtotime('today'));

        /* Se encontrar o acolhimento */
        if ($registros_acolhimento > 0) {

        	$e = '<html>
			<head>
				<meta charset="utf-8">
				<title>Termo de Desligamento - ' . strtoupper($acolhido["nome"]) . '</title>

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

			if($acolhimento['status'] == 11){
				$alta = 'Alta Terapêutica';
			}
			if($acolhimento['status'] == 12){
				$alta = 'Alta Solicitada';
				$sigla = 'TDAS';
			}
			if($acolhimento['status'] == 13){
				$alta = 'Alta Administrativa';
				$sigla = 'TDAA';
			}
			if($acolhimento['status'] == 14){
				$alta = 'Evasão';
				$sigla = 'TDE';
			}

            $e .= "<div id='head'></div>";
            if($acolhimento['status'] == 11){
            	$e .= "<h2 align='center' style='margin-bottom: -20px;'>Termo de Graduação - TG</h2> <br />";
            } else {
            	$e .= "<h2 align='center' style='margin-bottom: -20px;'>Termo de Desligamento (".$alta.") - ".$sigla."</h2> <br />";
            }

            if($acolhimento['status'] == 11){
				$e .= "<p>
                Eu, <strong>". $acolhido['nome'] ."</strong>, após ter completado satisfatoriamente o tempo exigido pelo programa de recuperação desta Comunidade Terapêutica, me encontro no dia de hoje realizando a minha Cerimônia de Graduação, acompanhado de meus entes queridos, com a qual encerro meu tratamento para a recuperação da dependência química do álcool e das drogas, a qual já me dominou por tempo suficiente.</p>";

                $e .= "<p>Sem nada ter a declarar contra esta instituição que me acolheu e me ajudou na minha recuperação, me retiro deste lugar muito satisfeito com tudo o que aqui tenho vivido e experimentado.</p>";
            }

            if($acolhimento['status'] == 12){
				$e .= "<p>
                Eu, <strong>". $acolhido['nome'] ."</strong>, desisto do tratamento para a recuperação da Dependência Química do álcool e das drogas na ". $config['nome_fantasia'] .", por livre e espontânea vontade, e nada tenho a declarar contra a esta instituição.</p>";
            }

            if($acolhimento['status'] == 13){
				$e .= "<p>
                Eu, <strong>". $acolhido['nome'] ."</strong>, estou sendo desligado da ". $config['nome_fantasia'] .", pelos motivos abaixo, e nada tenho a declarar contra esta instituição.</p>";
            }

            if($acolhimento['motivo'] != ''){
	            $e .= "<p>Motivos: <br />";
	            $e .= "<strong>".nl2br($acolhimento['motivo'])."</strong></p>";
        	}

            if($_GET['pertences']){
	            $e .= "<p>Declaro também: <br />";
	            if($_GET['pertences'] == 1){
	            	$e .= "<strong>Estar levando comigo todos os meus pertences, nada deixando que possa vir a ser retirado posteriormente pelos meus familiares.</strong></p>";
	            }
	            if($_GET['pertences'] == 2){
	            	$e .= "<strong>Estar deixando na Comunidade os itens abaixo, a serem retirados posteriormente, num prazo máximo de três (3) dias, unicamente pelos meus familiares ou responsável pela internação. Estou ciente que, após o término deste prazo, estes pertences serão entregues para doação.</strong></p>";
	            }
            }

            if($acolhimento['pertences'] != ''){
	            $e .= "<p>Lista de Pertences: <br />";
	            $e .= "<strong>".nl2br($acolhimento['pertences'])."</strong></p>";
	        }

            $e .= "<br />";
            $e .= "<br />";

            $e .= "<p align='right'>". $config['cidade'] .", ". $data_hoje ."</p>";

			$e .= "<br />";
			$e .= "<br />";
			$e .= "<br />";

			// assinaturas
			$e .= "<table width='49%' align='left' class='no-border' style='margin-top: 100px;'>";
			$e .= "<tr>";
			$e .= "<td align='center'>_______________________________________</td>";
			$e .= "</tr>";
			$e .= "<tr>";
			$e .= "<td align='center'>". $acolhido['nome'] ."<br />". $acolhido['cpf'] ."</td>";
			$e .= "</tr>";
			$e .= "</table>";

			$e .= "<table width='49%' align='right' class='no-border' style='padding-top: 100px;'>";
			$e .= "<tr>";
			$e .= "<td align='center'>_______________________________________</td>";
			$e .= "</tr>";
			$e .= "<tr>";
			$e .= "<td align='center'>". $config['razao_social'] ."<br />". $config['cnpj'] ."</td>";
			$e .= "</table>";
			// assinaturas

			//rodapé
			// $e .= "<div style='width: 100%; font-size: 0.6em; position: absolute; bottom: 0; left: 0;'>";
			// $e .= "<table width='100%' border='0'>";
			// $e .= "<tr>";
			// $e .= "<td align='left'>Elaboração: Ana Paula Goes</td>";
			// $e .= "<td align='center'>Elaborado em 10/12/2020 - Revisão nº 01 em 10/12/2020 - Próxima revisão em 10/12/2021</td>";
			// $e .= "<td align='right'>Aprovação: Marcio Roberto Calbente</td>";
			// $e .= "</tr>";
			// $e .= "</table>";
			// $e .= "</div>";
			//rodapé

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

        $canvas = $dompdf->get_canvas();
        $font = 'helvetica';
        $canvas->page_text(25, 810, "Elaboração: Ana Paula Goes", $font, 5.4, array(0,0,0)); //footer
        $canvas->page_text(185, 810, "Elaborado em 10/12/2020 - Revisão nº 01 em 10/12/2020 - Próxima revisão em 10/12/2021", $font, 5.4, array(0,0,0)); //footer
        $canvas->page_text(480, 810, "Aprovação: Marcio Roberto Calbente", $font, 5.4, array(0,0,0)); //footer
        $canvas->page_text(270, 820, "PÁGINA {PAGE_NUM}/{PAGE_COUNT}", $font, 5.4, array(0,0,0)); //footer

        /* Exibe */
        $dompdf->stream(
            "TERMO DE DESLIGAMENTO - " . strtoupper($acolhido["nome"]) . ".pdf", /* Nome do arquivo de saída */
            array(
                "Attachment" => false /* Para download, altere para true */
            )
        );
    }
?>