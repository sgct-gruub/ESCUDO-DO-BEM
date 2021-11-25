<?php
	error_reporting(E_ALL); 
	ini_set('display_errors', 1);

	/* Conecta no banco de dados */
	$servername = "localhost";
	$username = "root";
	$password = "bb744e9e47";
	$dbname = "renovacao";

	/* Cria a conexão */
	$conn = new mysqli($servername, $username, $password, $dbname);

	/* Checa a conexão */
	if ($conn->connect_error) {
		die("Conexão com o banco de dados falhou: " . $conn->connect_error);
	}

	$sql_acolhimentos = "SELECT * FROM acolhimentos_novo WHERE tipo_acolhimento = 3 AND status IN (0, 1)";
	$stmt_acolhimentos = $conn->query($sql_acolhimentos);
	$count = 0;
	while($acolhimentos = $stmt_acolhimentos->fetch_assoc()){

		$mes_hoje = date('m');
		$ano_hoje = date('Y');

		$sql_mensalidades = "SELECT * FROM mensalidades WHERE acolhimento = '{$acolhimentos['id']}' ORDER BY data_vencimento DESC";
		$stmt_mensalidades = $conn->query($sql_mensalidades);
		$mensalidades = $stmt_mensalidades->fetch_assoc();

		$data_hoje = new DateTime(date('Y-m-d'));
		$data_ultima_mensalidade = new DateTime($mensalidades['data_vencimento']);

		$diferenca = $data_hoje->diff($data_ultima_mensalidade);
		
		$mes_mensalidade = explode('-', $mensalidades['data_vencimento']);
		$mes_mensalidade = $mes_mensalidade[1];
		
		// se o mês da mensalidade não for igual ao mês corrente
		if($mes_mensalidade != $mes_hoje){		
			// se tiver no range de até 1 mês de diferença, gera a nova mensalidade
			if($diferenca->m == 0 or $diferenca->days == 30 or $diferenca->days == 31){
				$count++;

				$parcela = $mensalidades['parcela'] + 1;
				$data_vencimento = date('Y-m-d', strtotime('+1 month', strtotime($mensalidades['data_vencimento'])));
				$created_at = date('Y-m-d H:i:s');

				$sql_mensalidade = "INSERT INTO mensalidades (acolhimento, parcela, valor, data_vencimento, data_pagamento, status, created_at) VALUES ('{$acolhimentos['id']}', '{$parcela}', '{$mensalidades['valor']}', '{$data_vencimento}', '', 0, '{$created_at}')";


				if($conn->query($sql_mensalidade) === TRUE){
					$descricao = 'Nova mensalidade gerada para o acolhimento #'.$acolhimentos['id'];
					$sql_log = "INSERT INTO logs_cron (descricao, created_at) VALUES ('{$descricao}', '{$created_at}')";
					if($conn->query($sql_log) !== TRUE){
						echo 'Erro ao gerar log!';
					}
				} else {
					$descricao = $conn->error;
					$sql_log = "INSERT INTO logs_cron (descricao, created_at) VALUES ('{$descricao}', '{$created_at}')";
					if($conn->query($sql_log) !== TRUE){                                                                            
						echo 'Erro ao gerar log!';
					}
				}

			}
		}
		// echo $acolhimentos['id'] . "-";
		// echo $mensalidades['data_vencimento'] . "-";
		// echo $diferenca->days . "<br />";
	}
	// echo $count . " registros";
?>