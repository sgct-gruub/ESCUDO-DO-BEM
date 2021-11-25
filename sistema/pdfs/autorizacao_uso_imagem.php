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

	if(isset($_GET['acolhimento']) && $_GET['acolhimento'] != ''){

		/* Seleciona as configurações (nome da ct, cnpj, logo, timbre) */
		$config = db()->query("SELECT * FROM configuracoes")->fetch_assoc();

        /* Seleciona o acolhimento */
        $acolhimento = db()->query("SELECT * FROM acolhimentos_novo WHERE id = {$_GET['acolhimento']} LIMIT 1")->fetch_assoc();

        /* Seleciona o acolhido */
        $acolhido = db()->query("SELECT * FROM acolhidos WHERE id = {$acolhimento['acolhido']} LIMIT 1")->fetch_assoc();

        /* Seleciona o convênio (se tiver) */
        if($acolhimento['tipo_acolhimento'] == 0){
            $convenio = db()->query("SELECT * FROM convenios WHERE id = {$acolhimento['convenio']} LIMIT 1")->fetch_assoc();
        }

        // $url_timbre = "http://maequeacolhe.com.br/sistema/public/uploads/config/timbre/" . $config['timbre'];
        $url_timbre = "https://escudodobem.gruub.com.br/public/uploads/config/timbre/" . $config['timbre'];
        
        $data_acolhimento = explode('-', $acolhimento['data_inicio']);
        $mes_hoje = $data_acolhimento[1];
        $dia = explode(' ', $data_acolhimento[2]);
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
		$data_hoje = $dia . ' de ' . $nome_mes . ' de ' . $data_acolhimento[0];
        
        // $data_hoje = strftime('%d de %B de %Y', strtotime('today'));

        /* Se encontrar o acolhimento */
        if ($acolhimento > 0) {

        	$e = '<html>
			<head>
				<meta charset="utf-8">
				<title>Termo de Autorização de Imagem - ' . strtoupper($acolhido["nome"]) . '</title>

				<style type="text/css">
					@page {
						margin: 200px 30px 30px 30px;
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
            $e .= "<h2 align='center' style='margin-bottom: -20px;'>Termo de Autorização de Imagem - TAI</h2> <br />";

			$e .= "<p>
                Eu <strong>". $acolhido['nome'] ."</strong> portador do RG de nº <strong>" . $acolhido['rg'] . "</strong> e CPF <strong>" . $acolhido['cpf'] . "</strong>, <u>AUTORIZO</u> esta entidade a disponibilizar conteúdos eletrônicos contendo minha imagem (foto) em todas as vias de comunicação eletrônica de que a mesma se utilize, sabendo também que tenho plena liberdade de não assinar este termo caso deseje preservar o meu anonimato, sem ser alvo, por isto, de nenhuma forma de constrangimento, assim como também posso cancelar esta autorização a qualquer momento, por escrito, caso considere inapropriado o conteúdo divulgado.</p>";
            
            $e .= "<br />";
            $e .= "<br />";

            $e .= "<p align='right'>". $config['cidade'] .", ". $data_hoje ."</p>";

			$e .= "<br />";
			$e .= "<br />";
			$e .= "<br />";

			// assinaturas
			$e .= "<table width='100%' align='center' class='no-border'>";
            $e .= "<tr>";
            $e .= "<td align='center'>_______________________________________</td>";
            $e .= "</tr>";
			$e .= "<tr>";
			$e .= "<td align='center'>". $acolhido['nome'] ."</td>";
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
            "AUTORIZACAO USO DE IMAGEM - " . strtoupper($acolhido["nome"]) . ".pdf", /* Nome do arquivo de saída */
            array(
                "Attachment" => false /* Para download, altere para true */
            )
        );
    }
?>