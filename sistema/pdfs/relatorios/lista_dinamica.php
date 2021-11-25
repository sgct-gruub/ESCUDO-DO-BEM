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
				<title>LISTA DINÂMICA</title>

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
            if(isset($_POST['atividade']) && $_POST['atividade'] != ''){
            	$e .= "<h2 align='center'>". $_POST['atividade'] ."</h2> <br />";
            }

            if(!isset($_POST['data_entrada'])){
				$e .= "<table width='100%' align='center'>";
	            $e .= "<thead>";
	            $e .= "<tr>";
	            $e .= "<th width='1%' align='center'>#</th>";
	            $e .= "<th width='50%'>ACOLHIDOS</th>";
	            if(isset($_POST['cpf']) && $_POST['cpf'] == 'on'){
	            	$e .= "<th>CPF</th>";
	            }
	            if(isset($_POST['aniversariantes_mes']) && $_POST['aniversariantes_mes'] == 'on'){
	            	$e .= "<th>ANIVERSÁRIO</th>";
	            }
	            if(isset($_POST['data_nascimento']) && $_POST['data_nascimento'] == 'on'){
	            	$e .= "<th>NASCIMENTO</th>";
	            	$e .= "<th>IDADE</th>";
	            }
	            if(isset($_POST['assinatura']) && $_POST['assinatura'] == 'on'){
	            	$e .= "<th>ASSINATURA</th>";
	            }
	            $e .= "</tr>";
	            $e .= "</thead>";
	            $e .= "<tbody>";
	            $count = 0;
	            $acolhimentos = implode(',', $_POST['acolhimentos']);

	            if(!isset($_POST['data_nascimento']) && !isset($_POST['aniversariantes_mes'])){
	            	$sql_acolhido = "SELECT * FROM acolhidos WHERE status = 1 ORDER BY nome ASC";
	            }

	            if(isset($_POST['data_nascimento']) && $_POST['data_nascimento'] == 'on'){
	            	$sql_acolhido = "SELECT * FROM acolhidos WHERE status = 1 ORDER BY data_nascimento ASC";
	            }

	            if(isset($_POST['aniversariantes_mes']) && $_POST['aniversariantes_mes'] == 'on'){
					$sql_acolhido = "SELECT * FROM acolhidos WHERE MONTH(data_nascimento) = MOD(MONTH(CURDATE()), 12) AND status = 1 ORDER BY DAY(data_nascimento) ASC";
				}

				$stmt_acolhido = $conn->query($sql_acolhido);
				while($acolhido = $stmt_acolhido->fetch_assoc()){
					$count++; 

					$sql_acolhimento = "SELECT * FROM acolhimentos_novo WHERE acolhido = {$acolhido['id']}";
					$stmt_acolhimento = $conn->query($sql_acolhimento);
					$acolhimento = $stmt_acolhimento->fetch_assoc();

					$nome_acolhido = $acolhido['nome'];
					$cpf_acolhido = $acolhido['cpf'];

					$e .= "<tr>";
					$e .= "<td align='center'>". $count ."</td>";
					$e .= "<td>". $nome_acolhido ."</td>";
					if(isset($_POST['cpf']) && $_POST['cpf'] == 'on'){
						$e .= "<td>". $cpf_acolhido ."</td>";
					}	
					if(isset($_POST['aniversariantes_mes']) && $_POST['aniversariantes_mes'] == 'on'){
						$e .= "<td>". date('d/m', strtotime($acolhido['data_nascimento'])) ."</td>";
					}
					if(isset($_POST['data_nascimento']) && $_POST['data_nascimento'] == 'on'){
						$data_atual = new DateTime( date("Y-m-d") );
						$data = new DateTime( $acolhido['data_nascimento'] );

						$intervalo = $data_atual->diff( $data );

						$e .= "<td>". date('d/m/Y', strtotime($acolhido['data_nascimento'])) ."</td>";
						$e .= "<td>". $intervalo->y ." anos</td>";
					}
					if(isset($_POST['data_entrada']) && $_POST['data_entrada'] == 'on'){
						$e .= "<td>". date('d/m/Y', strtotime($acolhimento['data_inicio'])) ."</td>";
					}
					if(isset($_POST['assinatura']) && $_POST['assinatura'] == 'on'){
						if($acolhimento['status'] == 1){
							$e .= "<td><u>RESSOCIALIZAÇÃO</u></td>";
						} else {
							$e .= "<td></td>";
						}
					}
					$e .= "</tr>";
				}
				$e .= "</tbody>";
				$e .= "</table>";
			}


			if(isset($_POST['data_entrada']) && $_POST['data_entrada'] == 'on'){
            	$e .= "<table width='100%' align='center'>";
            	$e .= "<thead>";
	            $e .= "<tr>";
	            $e .= "<th width='1%' align='center'>#</th>";
	            $e .= "<th width='35%'>ACOLHIDOS</th>";
	            if(isset($_POST['cpf']) && $_POST['cpf'] == 'on'){
	            	$e .= "<th>CPF</th>";
	            }
	            $e .= "<th>Data de Entrada</th>";
	            if(isset($_POST['aniversariantes_mes']) && $_POST['aniversariantes_mes'] == 'on'){
	            	$e .= "<th>ANIVERSÁRIO</th>";
	            }
	            if(isset($_POST['data_nascimento']) && $_POST['data_nascimento'] == 'on'){
	            	$e .= "<th>NASCIMENTO</th>";
	            	$e .= "<th>IDADE</th>";
	            }
	            if(isset($_POST['assinatura']) && $_POST['assinatura'] == 'on'){
	            	$e .= "<th>ASSINATURA</th>";
	            }
	            $e .= "</tr>";
	            $e .= "</thead>";
	            $e .= "<tbody>";
	            $count = 0;
	            $acolhimentos = implode(',', $_POST['acolhimentos']);

	            $sql_acolhimento = "SELECT * FROM acolhimentos_novo WHERE acolhido IN ({$acolhimentos}) AND status IN (0, 1) ORDER BY data_inicio DESC";
				$stmt_acolhimento = $conn->query($sql_acolhimento);
				while($acolhimento = $stmt_acolhimento->fetch_assoc()){
					$count++;
					if(isset($_POST['aniversariantes_mes']) && $_POST['aniversariantes_mes'] == 'on'){
						$sql_acolhido = "SELECT * FROM acolhidos WHERE MONTH(data_nascimento) = MOD(MONTH(CURDATE()), 12) AND id IN ({$acolhimentos})";
					} else {
						$sql_acolhido = "SELECT * FROM acolhidos WHERE id IN ({$acolhimentos})";
					}
					$stmt_acolhido = $conn->query($sql_acolhido);
					while($acolhido = $stmt_acolhido->fetch_assoc()){
						$nome_acolhido = $acolhido['nome'];
						$cpf_acolhido = $acolhido['cpf'];
						if($acolhido['id'] == $acolhimento['acolhido']){
							$e .= "<tr>";
							$e .= "<td align='center'>". $count ."</td>";
							$e .= "<td>". $nome_acolhido ."</td>";
							if(isset($_POST['cpf']) && $_POST['cpf'] == 'on'){
								$e .= "<td>". $cpf_acolhido ."</td>";
							}
							$e .= "<td>". date('d/m/Y', strtotime($acolhimento['data_inicio'])) ."</td>";
							if(isset($_POST['aniversariantes_mes']) && $_POST['aniversariantes_mes'] == 'on'){
								$e .= "<td>". date('d/m', strtotime($acolhido['data_nascimento'])) ."</td>";
							}
							if(isset($_POST['data_nascimento']) && $_POST['data_nascimento'] == 'on'){
								$data_atual = new DateTime( date("Y-m-d") );
								$data = new DateTime( $acolhido['data_nascimento'] );

								$intervalo = $data_atual->diff( $data );

								$e .= "<td>". date('d/m/Y', strtotime($acolhido['data_nascimento'])) ."</td>";
								$e .= "<td>". $intervalo->y ." anos</td>";
							}
							if(isset($_POST['assinatura']) && $_POST['assinatura'] == 'on'){
				            	if($acolhimento['status'] == 1){
				            		$e .= "<td><u>RESSOCIALIZAÇÃO</u></td>";
				            	} else {
				            		$e .= "<td></td>";
				            	}
				            }
				            $e .= "</tr>";
				        }
				    }
				}
            	$e .= "</tbody>";
				$e .= "</table>";
            }

			$e .= "<table width='100%' align='center' class='no-border'>";
			if(isset($_POST['observacoes']) && $_POST['observacoes'] != ''){
				$e .= "<tr>";
				$e .= "<td><i>OBSERVAÇÕES: ". nl2br($_POST['observacoes']) ."</i></td>";
				$e .= "</tr>";
			}
			$e .= "<tr>";
			if(isset($_POST['quem_aplicou']) && $_POST['quem_aplicou'] != ''){
				$e .= "<td style='size: 12px; font-weight: bold;'>QUEM APLICOU: ". $_POST['quem_aplicou'] ."</td>";
			}

			if(isset($_POST['data_aplicada']) && $_POST['data_aplicada'] != ''){
				$e .= "<td style='size: 12px; font-weight: bold;' align='right'>DATA APLICAÇÃO: ". $_POST['data_aplicada'] ."</td>";
			}
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
            "LISTA DINÂMICA.pdf", /* Nome do arquivo de saída */
            array(
                "Attachment" => false /* Para download, altere para true */
            )
        );
?>