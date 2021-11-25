<?php
	use Dompdf\Dompdf;
    use Dompdf\Options;
    use Dompdf\Canvas;

    error_reporting(E_ALL); 
    ini_set('display_errors', 1);

	setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

	date_default_timezone_set('America/Sao_Paulo');

	session_start();


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

        /* Seleciona os registros */
        $sql = "SELECT * FROM tecnicos_referencia";
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
				<title>TERAPEUTAS</title>

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
						font-size: 11px;
  						line-height: 1.96;
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
            $e .= "<h2 align='center' style='margin-top: 0px;'>TERAPEUTAS ". $config['nome_fantasia'] . "</h2> <br />";

            while($registro = $stmt->fetch_assoc()){
				$e .= "<table width='100%' style='page-break-after: always;'>";
				$e .= "<thead>";
				$e .= "<tr>";
				if(isset($_GET['ligacoes']) && $_GET['ligacoes'] == 'true'){
					$e .= "<th width='100%' style='text-align: center' colspan='7'>". $registro['referencia'] ." (".date('d/m/Y').")</th>";
				} else {
					$e .= "<th width='100%' style='text-align: center' colspan='6'>". $registro['referencia'] ." (".date('d/m/Y').")</th>";
				}
				$e .= "</tr>";
				$e .= "<tr>";
				$e .= "<th>#</th>";
				$e .= "<th>Acolhido</th>";
				$e .= "<th>Contato</th>";
				$e .= "<th>Entrada</th>";
				$e .= "<th>Período</th>";
				if(isset($_GET['ligacoes']) && $_GET['ligacoes'] == 'true'){
					$e .= "<th>Data & Responsável</th>";
					if(isset($_GET['residente']) && $_GET['residente'] == 'true'){
						$e .= "<th>Assinatura Acolhido</th>";
					} else {
						$e .= "<th>Efetuado?</th>";						
					}
				} else {
					$e .= "<th>Previsão de Saída</th>";
				}
				$e .= "</tr>";
				$e .= "</thead>";
				$e .= "<tbody>";
				$sql_acolhimento = "SELECT * FROM acolhimentos_novo WHERE tecnico_referencia = {$registro['id']} AND status IN (0,1) ORDER BY data_inicio";
				$stmt_acolhimento = $conn->query($sql_acolhimento);
				$count = 0;
				while($acolhimento = $stmt_acolhimento->fetch_assoc()){
					$count++;
					$sql_acolhido = "SELECT * FROM acolhidos WHERE id = '{$acolhimento['acolhido']}'";
					$stmt_acolhido = $conn->query($sql_acolhido);
					$acolhido = $stmt_acolhido->fetch_assoc();

					$sql_contato = "SELECT * FROM contatos_acolhidos WHERE acolhido = '{$acolhido['id']}' AND status = 1";
					$stmt_contato = $conn->query($sql_contato);
					$contato = $stmt_contato->fetch_assoc();
					$nome_contato = explode(' ', $contato['nome']);
					$nome_contato = $nome_contato[0];

					$data_atual = new DateTime( date("Y-m-d") );
					$data = new DateTime( $acolhimento['data_inicio'] );

					$intervalo = $data_atual->diff( $data );

					if($acolhimento['tipo_acolhimento'] == 3){
						$e .= "<tr bgcolor='yellow'>";
					} else {
						$e .= "<tr>";
					}
					$e .= "<td>".$count."</td>";
					$e .= "<td>".$acolhido['nome']."</td>";
					$e .= "<td>".$contato['celular']." ".$nome_contato." (".$contato['grau_parentesco'].")</td>";
					$e .= "<td>".date('d/m/Y', strtotime($acolhimento['data_inicio']))."</td>";
					
					$anos = $intervalo->y;
					$meses = $intervalo->m;
					$dias = $intervalo->d;

					if($anos > 1){
						$anos = $anos . " anos";
					} elseif($anos == 1) {
						$anos = $anos . " ano";
					} else {
						$anos = '';
					}

					if($meses > 1){
						$meses = $meses . " meses";
					} elseif($meses == 1) {
						$meses = $meses . " mes";
					} else {
						$meses = '';
					}

					if($dias > 1){
						$dias = $dias . " dias";
					} elseif($dias == 1) {
						$dias = $dias . " dia";
					} else {
						$dias = '';
					}

					if($anos == 0 && $meses == 0 && $dias == 0){
						$anos = '0';
					}

					$e .= "<td>".$anos." ".$meses." ".$dias."</td>";

					if(isset($_GET['ligacoes']) && $_GET['ligacoes'] == 'true'){
						$e .= "<td></td>";
						if(isset($_GET['residente']) && $_GET['residente'] == 'true'){
							$e .= "<td></td>";
						} else {
							$e .= "<td></td>";
						}
					} else {
						$e .= "<td>".date('d/m/Y', strtotime($acolhimento['previsao_saida']))."</td>";
					}
					$e .= "</tr>";
				}
				$e .= "</tbody>";
				$e .= "</table>";
			}
			$e .= '</body></html>';


		/* Cria a instância */
        $options = new Options();
        $options->setIsRemoteEnabled(true);
        $dompdf = new Dompdf($options);

        /* Define o tipo de orientação da página */
        $dompdf->set_paper('A4', 'landscape');

        /* Carrega seu HTML */
        $dompdf->load_html($e);

        /* Renderiza */
        $dompdf->render();

        $canvas = $dompdf->get_canvas();
        // $font = 'helvetica';
        // $canvas->page_text(25, 810, "Elaboração: Ana Paula Goes", $font, 5.4, array(0,0,0)); //footer
        // $canvas->page_text(185, 810, "Elaborado em 10/12/2020 - Revisão nº 01 em 10/12/2020 - Próxima revisão em 10/12/2021", $font, 5.4, array(0,0,0)); //footer
        // $canvas->page_text(480, 810, "Aprovação: Marcio Roberto Calbente", $font, 5.4, array(0,0,0)); //footer
        // $canvas->page_text(270, 820, "PÁGINA {PAGE_NUM}/{PAGE_COUNT}", $font, 5.4, array(0,0,0)); //footer

        /* Exibe */
        $dompdf->stream(
            "TERAPEUTAS.pdf", /* Nome do arquivo de saída */
            array(
                "Attachment" => false /* Para download, altere para true */
            )
        );
    }
?>