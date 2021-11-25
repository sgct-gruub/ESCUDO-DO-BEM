<?php
	use Dompdf\Dompdf;
    use Dompdf\Options;
    use Dompdf\Canvas;
    
    /* Carrega a classe DOMPdf */
	require_once("../../public/assets/dompdf/autoload.inc.php");

	error_reporting(E_ALL); 
	ini_set('display_errors', 1);

	setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

	date_default_timezone_set('America/Sao_Paulo');

	session_start();

	if(isset($_GET['mes']) && $_GET['mes'] != ''){		

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

        /* Seleciona as mensalidades, de acordo com o mês e ano */
        if(isset($_GET['ano']) && $_GET['ano'] != ''){
			$sql_mensalidades = "SELECT * FROM mensalidades WHERE MONTH(data_vencimento) = '{$_GET['mes']}' AND YEAR(data_vencimento) = '{$_GET['ano']}' AND status != '99' ORDER BY data_vencimento ASC";
		} else {
			$ano = date('Y');
			$sql_mensalidades = "SELECT * FROM mensalidades WHERE MONTH(data_vencimento) = '{$_GET['mes']}' AND YEAR(data_vencimento) = '{$ano}' AND status != '99' ORDER BY data_vencimento ASC";
		}
		
		$stmt_mensalidades = $conn->query($sql_mensalidades);

		$url_timbre = "https://escudodobem.gruub.com.br/public/uploads/config/timbre/" . $config['timbre'];

		if ($_GET['mes'] == 1) {
			$nome_mes = 'Janeiro';
		}
		if ($_GET['mes'] == 2) {
			$nome_mes = 'Fevereiro';
		}
		if ($_GET['mes'] == 3) {
			$nome_mes = 'Março';
		}
		if ($_GET['mes'] == 4) {
			$nome_mes = 'Abril';
		}
		if ($_GET['mes'] == 5) {
			$nome_mes = 'Maio';
		}
		if ($_GET['mes'] == 6) {
			$nome_mes = 'Junho';
		}
		if ($_GET['mes'] == 7) {
			$nome_mes = 'Julho';
		}
		if ($_GET['mes'] == 8) {
			$nome_mes = 'Agosto';
		}
		if ($_GET['mes'] == 9) {
			$nome_mes = 'Setembro';
		}
		if ($_GET['mes'] == 10) {
			$nome_mes = 'Outubro';
		}
		if ($_GET['mes'] == 11) {
			$nome_mes = 'Novembro';
		}
		if ($_GET['mes'] == 12) {
			$nome_mes = 'Dezembro';
		}

		$e = '<html>
			<head>
				<meta charset="utf-8">
				<title>MENSALIDADES</title>

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
		$e .= "<table width='100%' align='center'>";
		$e .= "<thead>";
		$e .= "<tr>";
		if(isset($_GET['ano']) && $_GET['ano'] != ''){
			$e .= "<th style='text-align: center' colspan='6'>MENSALIDADES ".$nome_mes."/".$_GET['ano']."</th>";
		} else {
			$e .= "<th style='text-align: center' colspan='6'>MENSALIDADES ".$nome_mes."/".date('Y')."</th>";
		}
		$e .= "</tr>";
		$e .= "<tr>";
		$e .= "<th>#</th>";
		$e .= "<th>Data de vencimento</th>";
		$e .= "<th>Acolhido</th>";
		$e .= "<th>Nº Mensalidade</th>";
		$e .= "<th>Valor</th>";
		$e .= "<th>Status</th>";
		$e .= "</tr>";
		$e .= "</thead>";

		$e .= "<tbody>";
		$count = 0;
		while($mensalidades = $stmt_mensalidades->fetch_assoc()){
			$count++;

			// pega o acolhimento da mensalidade
			$sql_acolhimento = "SELECT * FROM acolhimentos_novo WHERE id = '{$mensalidades['acolhimento']}'";
			$stmt_acolhimento = $conn->query($sql_acolhimento);
			$acolhimento = $stmt_acolhimento->fetch_assoc();

			// pega o acolhido da mensalidade
			$sql_acolhido = "SELECT * FROM acolhidos WHERE id = '{$acolhimento['acolhido']}'";
			$stmt_acolhido = $conn->query($sql_acolhido);
			$acolhido = $stmt_acolhido->fetch_assoc();

			if(date('Y-m-d') <= $mensalidades['data_vencimento'] and $mensalidades['status'] == 0){
				$status = 'A receber';
			}

			if(date('Y-m-d') > $mensalidades['data_vencimento'] and $mensalidades['status'] == 0){
				$status = 'Em atraso';
			}

			if($mensalidades['status'] == 1){
				$status = 'Recebido';
			}

			if($mensalidades['status'] == 99){
				$status = 'Cancelado';
			}

			if($acolhimento['tipo_acolhimento'] == 3){
				$e .= "<tr bgcolor='yellow'>";
			} else {
				$e .= "<tr>";
			}
			$e .= "<td>".$count."</td>";
			$e .= "<td>".date('d/m/Y', strtotime($mensalidades['data_vencimento']))."</td>";
			$e .= "<td>".$acolhido['nome']."</td>";
			if($acolhimento['tipo_acolhimento'] == 3){
				// pega o total de mensalidades deste acolhimento
				$sql_total_mensalidades = "SELECT * FROM mensalidades WHERE acolhimento = '{$acolhimento['id']}'";
				$stmt_total_mensalidades = $conn->query($sql_total_mensalidades);
				$total_mensalidades = $stmt_total_mensalidades->num_rows;
				$e .= "<td>".$mensalidades['parcela']." de ".$total_mensalidades."</td>";
			} else {
				$e .= "<td>".$mensalidades['parcela']." de ".$config['tempo_tratamento']."</td>";
			}
			$e .= "<td>R$".number_format($mensalidades['valor'], 2, ',', '.')."</td>";
			$e .= "<td>".$status."</td>";
			$e .= "</tr>";
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

		/* Exibe */
		$dompdf->stream(
			"MENSALIDADES.pdf", /* Nome do arquivo de saída */
			array(
				"Attachment" => false /* Para download, altere para true */
			)
		);

	} else {

		echo "Nenhum mês foi selecionado!";
	
	}
?>