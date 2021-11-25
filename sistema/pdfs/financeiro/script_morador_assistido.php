<?php
	error_reporting(E_ALL); 
	ini_set('display_errors', 1);

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

	$sql_acolhimentos = "SELECT * FROM acolhimentos WHERE tipo_acolhimento = 3 AND status IN (0, 1)";
	$stmt_acolhimentos = $conn->query($sql_acolhimentos);
	while($acolhimentos = $stmt_acolhimentos->fetch_assoc()){

		$sql_mensalidades = "SELECT * FROM mensalidades WHERE acolhimento = '{$acolhimentos['id']}'";
		$stmt_mensalidades = $conn->query($sql_mensalidades);
		$mensalidades

	}
?>