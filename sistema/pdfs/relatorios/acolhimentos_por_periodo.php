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
		require_once("../../public/assets/dompdf/autoload.inc.php");

		/* Conecta no banco de dados */
		$servername = "localhost";
		$username = "root";
		$password = "bb744e9e47";
		$dbname = "clinicasomega";

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
        $url_timbre = "https://clinicasomega.gruub.com.br/public/uploads/config/timbre/" . $config['timbre'];

        /* Se encontrar o acolhimento */

        	$e = '<html>
			<head>
				<meta charset="utf-8">
				<title>ACOLHIMENTOS POR PERÍODO</title>

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
					    padding: 5px;
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
            $e .= "<h2 align='center'>RELATÓRIO DE ACOLHIMENTOS POR PERÍODO<br/>". date('d/m/Y', strtotime($_POST['start'])) ." a ". date('d/m/Y', strtotime($_POST['end'])) ."</h2> <br />";

			$e .= "<table width='100%' align='center'>";
            $e .= "<thead>";
            $e .= "<tr>";
            $e .= "<th>NOME</th>";
            $e .= "<th>DATA DE ENTRADA</th>";
            $e .= "<th>PERÍODO</th>";
            $e .= "<th>TIPO DE ACOLHIMENTO</th>";
            $e .= "</tr>";
            $e .= "</thead>";
            $e .= "<tbody>";

			// $formata_data_1 = explode("/", $_POST['start']);
			// $formata_data_1 = $formata_data_1[2] . "-" . $formata_data_1[1] . "-" . $formata_data_1[0];

			// $formata_data_2 = explode("/", $_POST['end']);
			// $formata_data_2 = $formata_data_2[2] . "-" . $formata_data_2[1] . "-" . $formata_data_2[0];

            $sql_acolhimento = "SELECT * FROM acolhimentos_novo WHERE data_inicio BETWEEN '{$_POST['start']}' AND '{$_POST['end']}' AND status IN (0, 1) ORDER BY data_inicio";
			$stmt_acolhimento = $conn->query($sql_acolhimento);
			$count_acolhimentos = $stmt_acolhimento->num_rows;

			while($acolhimento = $stmt_acolhimento->fetch_assoc()){

				// Seleciona o acolhido
				$sql_acolhido = "SELECT * FROM acolhidos WHERE id = '{$acolhimento['acolhido']}'";
				$stmt_acolhido = $conn->query($sql_acolhido);
				$acolhido = $stmt_acolhido->fetch_assoc();

            	$e .= "<tr>";
            		$e .= "<td>". $acolhido['nome'] ."</td>";

            		// se o tipo_acolhimento for == Convênio
            		if($acolhimento['tipo_acolhimento'] == 0){
            			// se tiver data de inicio pelo convenio
            			if($acolhimento['data_inicio_convenio'] != null AND $acolhimento['data_inicio_convenio'] != '0000-00-00'){
            				$e .= "<td>". date('d/m/Y', strtotime($acolhimento['data_inicio_convenio'])) ."</td>";
            			} else {
            				$e .= "<td>". date('d/m/Y', strtotime($acolhimento['data_inicio'])) ."</td>";
            			}
            		}

            		// se o tipo_acolhimento for == Vaga social ou particular
            		if($acolhimento['tipo_acolhimento'] != 0){
            			$e .= "<td>". date('d/m/Y', strtotime($acolhimento['data_inicio'])) ."</td>";
            		}

            		if($acolhimento['tipo_acolhimento'] == 0){
            			$sql_convenio = "SELECT * FROM convenios WHERE id = {$acolhimento['convenio']}";
						$stmt_convenio = $conn->query($sql_convenio);
						$convenio = $stmt_convenio->fetch_assoc();

						$tipo_acolhimento = $convenio['nome'];
					}

            		if($acolhimento['tipo_acolhimento'] == 1){
						$tipo_acolhimento = 'Particular';
					}

                    if($acolhimento['tipo_acolhimento'] == 2){
                        $tipo_acolhimento = 'Vaga Social';
                    }

                    if($acolhimento['tipo_acolhimento'] == 3){
                        $tipo_acolhimento = 'Morador Assistido';
                    }

            		$data1 = new DateTime(date('Y-m-d'));
            		$data2 = new DateTime($acolhimento['data_inicio']);
            		$intervalo = $data1->diff($data2);

            		// if($intervalo->m > 1 && $intervalo->d > 1){
            		// 	$e .= "<td>". $intervalo->m ." meses ". $intervalo->d ." dias</td>";
            		// }
            		// if($intervalo->m > 1 && $intervalo->d == 1){
            		// 	$e .= "<td>". $intervalo->m ." meses ". $intervalo->d ." dia</td>";
            		// }
            		// if($intervalo->m > 1 && $intervalo->d < 1){
            		// 	$e .= "<td>". $intervalo->m ." meses</td>";
            		// }
            		// if($intervalo->m < 1 && $intervalo->d > 1){
            		// 	$e .= "<td>". $intervalo->d ." dias</td>";
            		// }
            		// if($intervalo->m == 1 && $intervalo->d > 1){
            		// 	$e .= "<td>". $intervalo->m ." mês ". $intervalo->d ." dias</td>";
            		// }
            		// if($intervalo->m < 1 && $intervalo->d <= 1){
            		// 	$e .= "<td>". $intervalo->d ." dia</td>";
            		// }

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
                    
            		$e .= "<td>". $tipo_acolhimento ."</td>";
            	$e .= "</tr>";
			}

			$e .= "</tbody>";
			$e .= "</table>";

			$e .= "<table width='50%' align='center' style='margin-top: 50px;'>";
            $e .= "<tr>";
            $e .= "<td>Total de registros</td>";
            $e .= "<td>".$count_acolhimentos."</td>";
            $e .= "</tr>";
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
            "ACOLHIMENTOS POR PERÍODO (". $_POST['start'] ." a ". $_POST['end'] .").pdf", /* Nome do arquivo de saída */
            array(
                "Attachment" => false /* Para download, altere para true */
            )
        );
?>