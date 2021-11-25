<?php
	use Dompdf\Dompdf;
    use Dompdf\Options;
    use Dompdf\Canvas;

	setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

	date_default_timezone_set('America/Sao_Paulo');

	session_start();

	error_reporting(E_ALL); 
    ini_set('display_errors', 1);

	function valorPorExtenso($valor=0) {
		$singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
		$plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões","quatrilhões");
	 
		$c = array("", "cem", "duzentos", "trezentos", "quatrocentos","quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
		$d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta","sessenta", "setenta", "oitenta", "noventa");
		$d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze","dezesseis", "dezesete", "dezoito", "dezenove");
		$u = array("", "um", "dois", "três", "quatro", "cinco", "seis","sete", "oito", "nove");
	 
		$z=0;
	 
		$valor = number_format($valor, 2, ".", ".");
		$inteiro = explode(".", $valor);
		for($i=0;$i<count($inteiro);$i++)
			for($ii=strlen($inteiro[$i]);$ii<3;$ii++)
				$inteiro[$i] = "0".$inteiro[$i];
	 
		// $fim identifica onde que deve se dar junção de centenas por "e" ou por ","
		$fim = count($inteiro) - ($inteiro[count($inteiro)-1] > 0 ? 1 : 2);
		for ($i=0;$i<count($inteiro);$i++) {
			$valor = $inteiro[$i];
			$rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
			$rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
			$ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";
	 
			$r = $rc.(($rc && ($rd || $ru)) ? " e " : "").$rd.(($rd && $ru) ? " e " : "").$ru;
			$t = count($inteiro)-1-$i;
			$r .= $r ? " ".($valor > 1 ? $plural[$t] : $singular[$t]) : "";
			if ($valor == "000")$z++; elseif ($z > 0) $z--;
			if (($t==1) && ($z>0) && ($inteiro[0] > 0)) $r .= (($z>1) ? " de " : "").$plural[$t]; 
			if ($r) @$rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r;
		}
	 
		return($rt ? $rt : "zero");
	}

	if(isset($_POST['gerar_recibo'])){

		extract($_POST);

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

        $url_timbre = "https://escudodobem.gruub.com.br/public/uploads/config/timbre/" . $config['timbre'];

        $data = explode('/', $data);
        // $data = $data[2].'-'.$data[1].'-'.$data[0];

        $mes_hoje = $data[1];

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

		$data_hoje = $data[0] . ' de ' . $nome_mes . ' de ' . $data[2];
        
        // $data_hoje = strftime('%d de %B de %Y', strtotime($data));

        $e = '<html>
			<head>
				<meta charset="utf-8">
				<title>Recibo de Pagamento de Mensalidade</title>

				<style type="text/css">
					@page {
						margin: 180px 30px 30px 30px;
					}

					html {
					  -webkit-text-size-adjust: 100%;
					      -ms-text-size-adjust: 100%;
					}

					body {
						font-family: helvetica;
						font-size: 13px;
  						line-height: 1.42;
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

					#conteudo {
						border: 1px solid #d0d0d0;
						padding: 10px;
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

            $e .= "<div id='conteudo'>";
            $e .= "<h2 align='center'>Recibo de Pagamento de Mensalidade</h2> <br />";

            $e .= "<table width='100%' align='center' class='no-border'>";
            $e .= "<tr>";
            if(isset($duas_vias) && $duas_vias == 'sim'){
            	$e .= "<td align='left'><u>Mensalidade Nº #". $id_mensalidade ." - 1a Via</u></td>";
            } else {
            	$e .= "<td align='left'><u>Mensalidade Nº #". $id_mensalidade ."</u></td>";
            }
            $e .= "<td align='right' width='10%' style='border: 1px solid #000; padding: 6px;'><strong>R$". number_format($valor, 2, ',', '.') ."</strong></td>";
            $e .= "</tr>";
			$e .= "</table>";
            
            $e .= "<p>Recebi(emos) de <strong>". $nome_pagador ."</strong> - CPF/CNPJ nº <strong>". $cpf_cnpj ."</strong>, a importância de <strong>". valorPorExtenso($valor) ."</strong> referente à <strong>". $referente_a ."</strong>.</p>";
            $e .= "<p>Para maior clareza firmo(amos) o presente recibo para que produza os seus efeitos, dando plena, rasa e irrevogável quitação, pelo valor recebido.</p>";

            $e .= "<br />";

            $e .= "<p align='right'>". $cidade .", ". $data_hoje ."</p>";

			$e .= "<br />";
			$e .= "<br />";

			// assinaturas
			$e .= "<table width='100%' align='center' class='no-border'>";
            $e .= "<tr>";
            $e .= "<td align='center'>_______________________________________</td>";
            $e .= "</tr>";
            if($telefone == ''){
	            $e .= "<tr>";
	            $e .= "<td align='center'>". $nome_emissor ."<br />". $cpf_cnpj_emissor ."</td>";
	            $e .= "</tr>";
	        } else {
	        	$e .= "<tr>";
	            $e .= "<td align='center'>". $nome_emissor ."<br />". $cpf_cnpj_emissor ."<br />". $telefone ."</td>";
	            $e .= "</tr>";
	        }
			$e .= "</table>";
			// assinaturas
			$e .= "</div>";

			if(isset($duas_vias) && $duas_vias == 'sim'){
				$e .= "<div id='conteudo' style='margin-top: 20px'>";
	            $e .= "<h2 align='center'>Recibo de Mensalidade</h2> <br />";

	            $e .= "<table width='100%' align='center' class='no-border'>";
	            $e .= "<tr>";
	            $e .= "<td align='left'><u>Mensalidade Nº #". $id_mensalidade ." - 2a Via</u></td>";
            	$e .= "<td align='right' width='10%' style='border: 1px solid #000; padding: 6px;'><strong>R$". number_format($valor, 2, ',', '.') ."</strong></td>";
	            $e .= "</tr>";
				$e .= "</table>";
	            
	            $e .= "<p>Recebi(emos) de <strong>". $nome_pagador ."</strong> - CPF/CNPJ nº <strong>". $cpf_cnpj ."</strong>, a importância de <strong>". valorPorExtenso($valor) ."</strong> referente à <strong>". $referente_a ."</strong>.</p>";
	            $e .= "<p>Para maior clareza firmo(amos) o presente recibo para que produza os seus efeitos, dando plena, rasa e irrevogável quitação, pelo valor recebido.</p>";

	            $e .= "<br />";

	            $e .= "<p align='right'>". $cidade .", ". $data_hoje ."</p>";

				$e .= "<br />";
				$e .= "<br />";

				// assinaturas
				$e .= "<table width='100%' align='center' class='no-border'>";
	            $e .= "<tr>";
	            $e .= "<td align='center'>_______________________________________</td>";
	            $e .= "</tr>";
	            if($telefone == ''){
		            $e .= "<tr>";
		            $e .= "<td align='center'>". $nome_emissor ."<br />". $cpf_cnpj_emissor ."</td>";
		            $e .= "</tr>";
		        } else {
		        	$e .= "<tr>";
		            $e .= "<td align='center'>". $nome_emissor ."<br />". $cpf_cnpj_emissor ."<br />". $telefone ."</td>";
		            $e .= "</tr>";
		        }
				$e .= "</table>";
				// assinaturas
				$e .= "</div>";
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
            "RECIBO ". strtoupper($referente_a) .".pdf", /* Nome do arquivo de saída */
            array(
                "Attachment" => false /* Para download, altere para true */
            )
        );
    }
?>