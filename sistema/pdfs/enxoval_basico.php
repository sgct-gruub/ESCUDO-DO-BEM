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

        // $url_timbre = "http://maequeacolhe.com.br/sistema/public/uploads/config/timbre/" . $config['timbre'];
        $url_timbre = "https://escudodobem.gruub.com.br/public/uploads/config/timbre/" . $config['timbre'];

        $data_inicio = explode('-', $acolhimento['data_inicio']);
        $mes_hoje = $data_inicio[1];
        $dia = explode(' ', $data_inicio[2]);
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
		$data_hoje = $dia . ' de ' . $nome_mes . ' de ' . $data_inicio[0];
        
        // $data_hoje = strftime('%d de %B de %Y', strtotime('today'));

        /* Se encontrar o acolhimento */
        if ($registros_acolhimento > 0) {

        	$e = '<html>
			<head>
				<meta charset="utf-8">
				<title>Enxoval Básico</title>

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
            $e .= "<h2 align='center' style='margin-bottom: -20px;'>Enxoval Básico</h2> <br />";

            $e .= "<table width='100%' align='left' class='no-border'>";
            $e .= "<tr><td><strong>Material de Estudo</strong></td></tr>";
            $e .= "<tr><td>1 Bíblia<br />";
            $e .= "2 Cadernos Grandes<br />";
            $e .= "1 Documento Pessoal Original<br />";
            $e .= "2 Canetas<br />";
            $e .= "2 Pastas Plásticas</td></tr>";
            $e .= "</table>";

            $e .= "<table width='100%' align='left' class='no-border' style='margin-top: 140px'>";
            $e .= "<tr><td colspan='3'><strong>Roupas</strong></td></tr>";
            $e .= "<tr><td>1 Mala grande ou 2 pequenas<br />";
            $e .= "2 Travesseiros<br />";
            $e .= "2 Cobertores<br />";
            $e .= "2 Jogos de cama<br />";
            $e .= "2 Toalhas de banho</td>";

            $e .= "<td>4 Bermudas<br />";
            $e .= "3 Calças de moletom<br />";
            $e .= "4 Calças jeans (ou outras)<br />";
            $e .= "10 Camisetas<br />";
            $e .= "5 Agasalhos incluindo jaquetas</td>";

            $e .= "<td>7 Cuecas<br />";
            $e .= "5 Pares de meias<br />";
            $e .= "2 Chinelos de dedo<br />";
            $e .= "3 Calçados (sapato, tênis, botina)<br />";
            $e .= "2 Toalhas de rosto</td></tr>";
            $e .= "<tr><td colspan='3' style='font-size: 0.75em'>Obs.: As quantidades acima representam <strong><u> o máximo </u></strong> de roupas de cada tipo que poderão ser trazidas para não abarrotar os armários, não significa que tenham que trazer exatamente esta quantidade.</td></tr>";
            $e .= "</table>";

            $e .= "<table width='100%' align='left' class='no-border' style='margin-top: 320px'>";
            $e .= "<tr><td colspan='3'><strong>Higiene Pessoal</strong></td></tr>";
            $e .= "<tr><td>1 Balde<br />";
            $e .= "1 Escova de roupa<br />";
            $e .= "5 Sabonetes<br />";
            $e .= "1 Escova dental<br />";
            $e .= "4 Aparelhos de barbear<br />";
            $e .= "1 Rodo<br />";
            $e .= "2 Pct. papel higiênico<br</td>";

            $e .= "<td>1 corda de varal<br />";
            $e .= "2kg sabão em pó<br />";
            $e .= "1 Bucha de banho<br />";
            $e .= "3 Pasta de dentes<br />";
            $e .= "1 Pct. saco de lixo 50l<br />";
            $e .= "1 Vassoura<br /><br /></td>";

            $e .= "<td>2 pct. prendedores<br />";
            $e .= "1 Pct. sabão em pedra<br />";
            $e .= "2 Shampoo<br />";
            $e .= "2 Desodorante roll-on<br />";
            $e .= "3 Bucha para louça<br />";
            $e .= "5 Panos de prato<br /><br /></td></tr>";
            $e .= "<tr><td colspan='3' style='font-size: 0.75em'>Obs.: se trouxerem perfume, este ficará sob cuidado da coordenação e será utilizado com a devida autorização.</td></tr>";
            $e .= "</table>";

            $e .= "<table width='100%' align='left' class='no-border' style='margin-top: 530px;'>";
            $e .= "<tr><td><strong>Cigarro:</strong> Só podem ser trazidos cigarros de valor máximo igual ao mais barato nacional, acompanhados de isqueiros unicamente a gás. Devem ser trazidos cigarros para um mês, aproximadamente 4 pacotes</td></tr>";
            $e .= "</table>";

            $e .= "<table width='100%' align='left' class='no-border' style='margin-top: 580px;'>";
            $e .= "<tr><td><strong>Custos:</strong> Deve ser trazido no dia da internação: Taxa de matrícula (combinado na entrevista). Dinheiro para o caixa de gastos pessoais (R$50,00)</td></tr>";
            $e .= "</table>";

            $e .= "<table width='100%' align='left' class='no-border' style='margin-top: 630px;'>";
            $e .= "<tr><td><strong>Exames Médicos:</strong> HIV, Hemograma completo, Fezes, Urina I, RX Tórax ou Exame de Escarro.</td></tr>";
            $e .= "</table>";

            $e .= "<table width='100%' align='left' class='no-border' style='margin-top: 660px;'>";
            $e .= "<tr><td><strong>Medicação de Uso Comunitário:</strong> Dipirona ou paracetamol, dorflex, nimesulida ou diclofenaco e multigripal</td></tr>";
            $e .= "</table>";

            $e .= "<table width='100%' align='left' class='no-border' style='margin-top: 690px;'>";
            $e .= "<tr><td><strong>Não Pode:</strong> Revistas ou fotografias pornográficas, camiseta de bandas ou com imagens de bebida/drogas, remédios sem a correspondente receita médica, piercings, óculos escuros, fumo de corda ou tabaco solto, objetos de valor diversos.</td></tr>";
            $e .= "</table>";

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