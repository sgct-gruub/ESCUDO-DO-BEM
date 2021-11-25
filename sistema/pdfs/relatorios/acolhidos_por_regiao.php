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
				<title>ACOLHIMENTOS POR REGIÃO</title>

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

            if(!isset($_POST['cidade']) && isset($_POST['uf']) && $_POST['uf'] != ''){
				$e .= "<table width='100%' align='center'>";
	            $e .= "<thead>";
	            $e .= "<tr>";
	            $e .= "<th>REGISTROS</th>";
	            $e .= "<th>CIDADE</th>";
	            $e .= "<th>ESTADO</th>";
	            $e .= "</tr>";
	            $e .= "</thead>";
	            $e .= "<tbody>";
	            $sql = "SELECT COUNT(DISTINCT nome) as Total, id, cidade, uf FROM acolhidos WHERE uf = '{$_POST['uf']}' GROUP BY cidade ORDER BY Total DESC";
		        $stmt = $conn->query($sql);
		        $total = 0;
		        while($row = $stmt->fetch_assoc()){
		        	$total += $row['Total'];
					$e .= "<tr>";
					$e .= "<td>".$row['Total']."</th>";
					$e .= "<td>".$row['cidade']."</th>";
					$e .= "<td>".$row['uf']."</th>";
					$e .= "</tr>";
		        }
				$e .= "<tr>";
				$e .= "<td colspan='3'><strong>TOTAL DE ".$total." acolhidos</strong></td>";
				$e .= "</tr>";
				$e .= "</tbody>";
				$e .= "</table>";
			}

			if(isset($_POST['cidade']) && $_POST['cidade'] != '' && isset($_POST['uf']) && $_POST['uf'] != ''){
				$e .= "<table width='100%' align='center'>";
	            $e .= "<thead>";
	            $e .= "<tr>";
	            $e .= "<th>ACOLHIDO</th>";
	            $e .= "<th>CIDADE</th>";
	            $e .= "<th>ESTADO</th>";
	            $e .= "<th>STATUS</th>";
	            $e .= "</tr>";
	            $e .= "</thead>";
	            $e .= "<tbody>";
	            $sql = "SELECT * FROM acolhidos WHERE uf = '{$_POST['uf']}' AND cidade LIKE '%{$_POST['cidade']}%' ORDER BY nome ASC";
		        $stmt = $conn->query($sql);
		        $total = 0;
		        while($row = $stmt->fetch_assoc()){
		        	if(isset($_POST['status']) && $_POST['status'] != ''){
		        		$arr_status = implode(',', $_POST['status']);
		        		$sql_acolhimento = "SELECT * FROM acolhimentos WHERE acolhido = {$row['id']} AND status IN ({$arr_status})";
		        	} else {
		        		$sql_acolhimento = "SELECT * FROM acolhimentos WHERE acolhido = {$row['id']}";
		        	}
			        $stmt_acolhimento = $conn->query($sql_acolhimento);
			        $acolhimento = $stmt_acolhimento->fetch_assoc();
			        $verifica_acolhimento = $stmt_acolhimento->num_rows;
		        	if($verifica_acolhimento >= 1){
		        	
			        	$total++;

			        	if($acolhimento['status'] == '0'){
			        		$status = 'Em acolhimento';
			        	}
			        	if($acolhimento['status'] == '1'){
			        		$status = 'Ressocialização';
			        	}
			        	if($acolhimento['status'] == '11'){
			        		$status = 'Alta Terapêutica';
			        	}
			        	if($acolhimento['status'] == '12'){
			        		$status = 'Alta Solicitada';
			        	}
			        	if($acolhimento['status'] == '13'){
			        		$status = 'Alta Administrativa';
			        	}
			        	if($acolhimento['status'] == '14'){
			        		$status = 'Evasão';
			        	}

						$e .= "<tr>";
						$e .= "<td>".$row['nome']."</th>";
						$e .= "<td>".$row['cidade']."</th>";
						$e .= "<td>".$row['uf']."</th>";
						$e .= "<td>".$status."</th>";
						$e .= "</tr>";

					}
		        }
				$e .= "<tr>";
				$e .= "<td colspan='4'><strong>TOTAL DE ".$total." acolhidos</strong></td>";
				$e .= "</tr>";
				$e .= "</tbody>";
				$e .= "</table>";
			}

			if(!isset($_POST['cidade']) && !isset($_POST['uf']) && isset($_POST['status']) && $_POST['status'] != ''){
				$e .= "<table width='100%' align='center'>";
	            $e .= "<thead>";
	            $e .= "<tr>";
	            $e .= "<th>ACOLHIDO</th>";
	            $e .= "<th>CIDADE</th>";
	            $e .= "<th>ESTADO</th>";
	            $e .= "<th>STATUS</th>";
	            $e .= "</tr>";
	            $e .= "</thead>";
	            $e .= "<tbody>";
	            $sql = "SELECT * FROM acolhidos ORDER BY nome ASC";
		        $stmt = $conn->query($sql);
		        $total = 0;
		        while($row = $stmt->fetch_assoc()){
		        	$arr_status = implode(',', $_POST['status']);
		        	$sql_acolhimento = "SELECT * FROM acolhimentos WHERE acolhido = {$row['id']} AND status IN ({$arr_status})";
			        $stmt_acolhimento = $conn->query($sql_acolhimento);
			        $acolhimento = $stmt_acolhimento->fetch_assoc();
			        $verifica_acolhimento = $stmt_acolhimento->num_rows;
		        	if($verifica_acolhimento >= 1){
		        	
			        	$total++;

			        	if($acolhimento['status'] == '0'){
			        		$status = 'Em acolhimento';
			        	}
			        	if($acolhimento['status'] == '1'){
			        		$status = 'Ressocialização';
			        	}
			        	if($acolhimento['status'] == '11'){
			        		$status = 'Alta Terapêutica';
			        	}
			        	if($acolhimento['status'] == '12'){
			        		$status = 'Alta Solicitada';
			        	}
			        	if($acolhimento['status'] == '13'){
			        		$status = 'Alta Administrativa';
			        	}
			        	if($acolhimento['status'] == '14'){
			        		$status = 'Evasão';
			        	}

						$e .= "<tr>";
						$e .= "<td>".$row['nome']."</th>";
						$e .= "<td>".$row['cidade']."</th>";
						$e .= "<td>".$row['uf']."</th>";
						$e .= "<td>".$status."</th>";
						$e .= "</tr>";

					}
		        }
				$e .= "<tr>";
				$e .= "<td colspan='4'><strong>TOTAL DE ".$total." acolhidos</strong></td>";
				$e .= "</tr>";
				$e .= "</tbody>";
				$e .= "</table>";

				$e .= "<br />";
				$e .= "<br />";
				$e .= "<br />";

				$e .= "<table width='50%' align='center'>";
	            $e .= "<thead>";
	            $e .= "<tr>";
	            $e .= "<th>CIDADE</th>";
	            $e .= "<th>ESTADO</th>";
	            $e .= "<th>TOTAL</th>";
	            $e .= "</tr>";
		        $arr_status = implode(',', $_POST['status']);
	            $sql2 = "SELECT COUNT(a.id) as Total, a.cidade, a.uf FROM acolhidos a, acolhimentos ac WHERE ac.acolhido = a.id AND ac.status IN ({$arr_status}) GROUP BY a.cidade";
	            $stmt2 = $conn->query($sql2);
	            $total = 0;
	            while($row2 = $stmt2->fetch_assoc()){
	            	$total += $row2['Total'];
		            $e .= "<tr>";
		            $e .= "<td>".$row2['cidade']."</td>";
		            $e .= "<td>".$row2['uf']."</td>";
		            $e .= "<td>".$row2['Total']."</td>";
		            $e .= "</tr>";
	        	}
	        	$e .= "<tr>";
				$e .= "<td></td>";
				$e .= "<td></td>";
				$e .= "<td><strong>".$total."</strong></td>";
				$e .= "</tr>";
	            $e .= "</thead>";
	            $e .= "<tbody>";
			}
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
            "ACOLHIMENTOS POR REGIÃO.pdf", /* Nome do arquivo de saída */
            array(
                "Attachment" => false /* Para download, altere para true */
            )
        );
?>