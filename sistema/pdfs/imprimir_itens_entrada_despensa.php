<?php
	setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

	date_default_timezone_set('America/Sao_Paulo');

	session_start();

	if(isset($_GET['tipo']) && $_GET['tipo'] != ''){

		/* Carrega a classe DOMPdf */
		require_once("../public/assets/dompdf/dompdf_config.inc.php");

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

        /* Verifica o tipo de entrada */
		if($_GET['tipo'] == 'compra'){
	        /* Seleciona a compra */
	        $sql = "SELECT * FROM compras WHERE id = {$_GET['compra']} LIMIT 1";
	        $stmt = $conn->query($sql);
	        $registros = $stmt->num_rows;
	        $compra = $stmt->fetch_assoc();

	        /* Seleciona o fornecedor (se tiver) */
	        $sql_fornecedor = "SELECT * FROM fornecedores WHERE id = {$compra['fornecedor']} LIMIT 1";
	        $stmt_fornecedor = $conn->query($sql_fornecedor);
	        $registros_fornecedor = $stmt_fornecedor->num_rows;
	        $fornecedor = $stmt_fornecedor->fetch_assoc();
	    }

	    if($_GET['tipo'] == 'doacao'){
	        /* Seleciona a doação */
	        $sql = "SELECT * FROM doacoes WHERE id = {$_GET['doacao']} LIMIT 1";
	        $stmt = $conn->query($sql);
	        $registros = $stmt->num_rows;
	        $doacao = $stmt->fetch_assoc();

	        /* Seleciona o doador (se tiver) */
	        $sql_doador = "SELECT * FROM doadores WHERE id = {$doacao['doador']} LIMIT 1";
	        $stmt_doador = $conn->query($sql_doador);
	        $registros_doador = $stmt_doador->num_rows;
	        $doador = $stmt_doador->fetch_assoc();
	    }

        // $url_timbre = "http://maequeacolhe.com.br/sistema/public/uploads/config/timbre/" . $config['timbre'];
        $url_timbre = "https://escudodobem.gruub.com.br/public/uploads/config/timbre/" . $config['timbre'];

        $mes_hoje = date('m');

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

		$data_hoje = date('d') . ' de ' . $nome_mes . ' de ' . date('Y');
        
        // $data_hoje = strftime('%d de %B de %Y', strtotime('today'));

        /* Se encontrar o acolhimento */
        if ($registros > 0) {

        	$e = '<html>
			<head>
				<meta charset="utf-8">
				<title>'. strtoupper($_GET['tipo']) .'</title>

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
            
            if($_GET['tipo'] == 'compra'){
            	$e .= "<h2 align='center' style='margin-top: 40px;'>Compra #". $compra['numero_pedido'] ."</h2> <br />";

	            $e .= "<table width='100%' align='center'>";
				$e .= "<tr bgcolor='#f0f0f0'>";
				$e .= "<td align='center'><strong>Data da compra</strong></td>";
				$e .= "<td align='center' colspan='2'><strong>Fornecedor</strong></td>";
				$e .= "<td align='center'><strong>Nº do pedido</strong></td>";
				$e .= "</tr>";

				$e .= "<tr>";
				$e .= "<td align='center'>". date('d/m/Y', strtotime($compra['data_compra'])) ."</td>";
				$e .= "<td align='center' colspan='2'>". $fornecedor['nome_fantasia'] ."</td>";
				$e .= "<td align='center'>". $compra['numero_pedido'] ."</td>";
				$e .= "</tr>";

				$e .= "<tr bgcolor='#f0f0f0'>";
				$e .= "<td align='center'><strong>Vendedor</strong></td>";
				$e .= "<td align='center'><strong>Tipo de frete</strong></td>";
				$e .= "<td align='center'><strong>Valor do frete</strong></td>";
				$e .= "<td align='center'><strong>Previsão de entrega</strong></td>";
				$e .= "</tr>";

				$e .= "<tr>";
				$e .= "<td align='center'>". $compra['vendedor'] ."</td>";
				$e .= "<td align='center'>". $compra['tipo_frete'] ."</td>";
				$e .= "<td align='center'>R$". number_format($compra['valor_frete'], 2, ',', '.') ."</td>";
				$e .= "<td align='center'>". date('d/m/Y', strtotime($compra['previsao_entrega'])) ."</td>";
				$e .= "</tr>";
				$e .= "</table>";

				$e .= "<table width='100%' align='center' style='margin-top: 25px; margin-bottom: 50px;'>";
				$e .= "<tr bgcolor='#f0f0f0'>";
				$e .= "<td align='center'><strong>Produto</strong></td>";
				$e .= "<td align='center'><strong>Quantidade</strong></td>";
				$e .= "<td align='center' width='20%'><strong>Valor unitário</strong></td>";
				$e .= "<td align='center'><strong>Total</strong></td>";
				$e .= "</tr>";

				/* Seleciona os itens */
		        $sql_itens = "SELECT * FROM itens_compra WHERE compra = {$compra['id']} LIMIT 1";
		        $stmt_itens = $conn->query($sql_itens);
		        $registros_itens = $stmt_itens->num_rows;
		        while($item = $stmt_itens->fetch_assoc()){
					/* Seleciona os produtos */
					$sql_produtos = "SELECT * FROM produtos WHERE id = {$item['produto']} LIMIT 1";
		        	$stmt_produtos = $conn->query($sql_produtos);
		        	$registros_produtos = $stmt_produtos->num_rows;
		        	$produto = $stmt_produtos->fetch_assoc();

		        	$item_total = $item['valor_unitario'] * $item['quantidade'];

					$e .= "<tr>";
					$e .= "<td align='center'>". $produto['nome'] ."</td>";
					$e .= "<td align='center'>". $item['quantidade'] ." (". $produto['unidade'] .")</td>";
					$e .= "<td align='center'>R$". number_format($item['valor_unitario'], 2, ',', '.') ."</td>";
					$e .= "<td align='center'>R$". number_format($item_total, 2, ',', '.') ."</td>";
					$e .= "</tr>";
				}

				$e .= "<tr>";
				$e .= "<td></td>";
				$e .= "<td></td>";
				$e .= "<td bgcolor='#f0f0f0' align='right'><strong>Valor total</strong></td>";
				$e .= "<td align='center'>R$". number_format($compra['valor_total'], 2, ',', '.') ."</td>";
				$e .= "</tr>";

				$e .= "<tr>";
				$e .= "<td></td>";
				$e .= "<td class='no-border'></td>";
				$e .= "<td bgcolor='#f0f0f0' align='right'><strong>Pagamento</strong></td>";
				$e .= "<td align='center'>". $compra['forma_pagamento'] ."</td>";
				$e .= "</tr>";

				$e .= "</table>";
			} // end if tipo == compra

			if($_GET['tipo'] == 'doacao'){
            	$e .= "<h2 align='center' style='margin-top: 40px;'>Doação #". $doacao['id'] ."</h2> <br />";

	            $e .= "<table width='100%' align='center'>";
				$e .= "<tr bgcolor='#f0f0f0'>";
				$e .= "<td align='center'><strong>Data da doação</strong></td>";
				$e .= "<td align='center'><strong>doador</strong></td>";
				$e .= "</tr>";

				$e .= "<tr>";
				$e .= "<td align='center'>". date('d/m/Y', strtotime($doacao['data_doacao'])) ."</td>";
				$e .= "<td align='center'>". $doador['nome'] ."</td>";
				$e .= "</tr>";
				$e .= "</table>";

				$e .= "<table width='100%' align='center' style='margin-top: 25px; margin-bottom: 50px;'>";
				$e .= "<tr bgcolor='#f0f0f0'>";
				$e .= "<td align='center'><strong>Produto</strong></td>";
				$e .= "<td align='center'><strong>Quantidade</strong></td>";
				$e .= "<td align='center' width='20%'><strong>Valor unitário</strong></td>";
				$e .= "<td align='center'><strong>Total</strong></td>";
				$e .= "</tr>";

				/* Seleciona os itens */
		        $sql_itens = "SELECT * FROM itens_doacao WHERE doacao = {$doacao['id']} LIMIT 1";
		        $stmt_itens = $conn->query($sql_itens);
		        $registros_itens = $stmt_itens->num_rows;
		        while($item = $stmt_itens->fetch_assoc()){
					/* Seleciona os produtos */
					$sql_produtos = "SELECT * FROM produtos WHERE id = {$item['produto']} LIMIT 1";
		        	$stmt_produtos = $conn->query($sql_produtos);
		        	$registros_produtos = $stmt_produtos->num_rows;
		        	$produto = $stmt_produtos->fetch_assoc();

		        	$item_total = $item['valor_unitario'] * $item['quantidade'];

					$e .= "<tr>";
					$e .= "<td align='center'>". $produto['nome'] ."</td>";
					$e .= "<td align='center'>". $item['quantidade'] ." (". $produto['unidade'] .")</td>";
					$e .= "<td align='center'>R$0,00</td>";
					$e .= "<td align='center'>R$0,00</td>";
					$e .= "</tr>";
				}

				$e .= "<tr>";
				$e .= "<td></td>";
				$e .= "<td></td>";
				$e .= "<td bgcolor='#f0f0f0' align='right'><strong>Valor total</strong></td>";
				$e .= "<td align='center'>R$0,00</td>";
				$e .= "</tr>";

				$e .= "</table>";
			} // end if tipo == doacao

            $e .= "<p align='right'>Juquitiba, ". $data_hoje ."</p>";
			$e .= '</body></html>';

        } else {
        	echo "Nada encontrado!";
        }


		/* Cria a instância */
        $dompdf = new DOMPDF();

        /* Define o tipo de orientação da página */
        $dompdf->set_paper('A4', 'portrait');

        /* Carrega seu HTML */
        $dompdf->load_html($e);

        /* Renderiza */
        $dompdf->render();

        /* Exibe */
        $dompdf->stream(
            strtoupper($_GET['tipo']).".pdf", /* Nome do arquivo de saída */
            array(
                "Attachment" => false /* Para download, altere para true */
            )
        );
    }
?>