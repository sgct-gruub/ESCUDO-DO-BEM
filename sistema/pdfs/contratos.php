<?php
	use Dompdf\Dompdf;
    use Dompdf\Options;

	// error_reporting(E_ALL); 
	// ini_set('display_errors', 1);

	setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

	date_default_timezone_set('America/Sao_Paulo');

	session_start();

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
	 
		return(@$rt ? @$rt : "zero");
	}

	/* Conecta no banco de dados */
	$servername = "localhost";
	$username = "root";
	$password = "bb744e9e47";
	$dbname = "escudodobem";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	/* Carrega a classe DOMPdf */
	require_once("../public/assets/dompdf/autoload.inc.php");

	$sql_ajustes = "SELECT * FROM configuracoes";
	$stmt_ajustes = $conn->query($sql_ajustes);
	$ajustes = $stmt_ajustes->fetch_assoc();

	// identifica o acolhimento
	$sql_acolhimento = "SELECT * FROM acolhimentos_novo WHERE id = {$_GET['acolhimento']}";
	$stmt_acolhimento = $conn->query($sql_acolhimento);
	$acolhimento = $stmt_acolhimento->fetch_assoc();

	// identifica o acolhido
	$sql_acolhido = "SELECT * FROM acolhidos WHERE id = {$acolhimento['acolhido']}";
	$stmt_acolhido = $conn->query($sql_acolhido);
	$acolhido = $stmt_acolhido->fetch_assoc();

	// identifica o contato principal do acolhido
	$sql_contato_acolhido = "SELECT * FROM contatos_acolhidos WHERE acolhido = {$acolhimento['acolhido']} AND status = 1";
	$stmt_contato_acolhido = $conn->query($sql_contato_acolhido);
	$registros_contato_acolhido = $stmt_contato_acolhido->num_rows;
	// se tiver pelo menos um contato, puxa as informações do mesmo
	if($registros_contato_acolhido > 0){
		$contato_acolhido = $stmt_contato_acolhido->fetch_assoc();
	} else { // se não tiver, inclui as informações do acolhido
		$contato_acolhido['nome'] = $acolhido['nome'];
		$contato_acolhido['rg'] = $acolhido['rg'];
		$contato_acolhido['cpf'] = $acolhido['cpf'];
		$contato_acolhido['data_nascimento'] = $acolhido['data_nascimento'];
		$contato_acolhido['grau_parentesco'] = 'O mesmo';
		$contato_acolhido['telefone'] = '';
		$contato_acolhido['celular'] = '';
	}

	// valor do contrato
	$valor_total_contrato = '';
	$valor_mensalidade = '';
	$data_vencimento_mensalidade = '';

	// pega o valor da matrícula
	@$valor_matricula = "R$" . number_format($acolhimento['valor_matricula'], 2, ',', '.') . " (". valorPorExtenso($acolhimento['valor_matricula']) ." )";

	// identifica o contrato
	if(isset($_GET['tipo']) && $_GET['tipo'] == 0){ // convenio
		$sql_contratos = "SELECT * FROM modelos_contratos WHERE tipo = {$_GET['tipo']} AND convenio = {$acolhimento['convenio']}";

		// verifica se a há data de início pelo convênio
		// se tiver, substitui a data de início pela data de início convênio
		if($acolhimento['data_inicio_convenio'] != '' && $acolhimento['data_inicio_convenio'] != '0000-00-00'){
			$acolhimento['data_inicio'] = $acolhimento['data_inicio_convenio'];
		}
	}

	if(isset($_GET['tipo']) && $_GET['tipo'] == 1){ // particular
		$sql_contratos = "SELECT * FROM modelos_contratos WHERE tipo = {$_GET['tipo']} AND voluntario = {$_GET['voluntario']} AND terceirizado = {$_GET['terceirizado']} ";

		if($acolhimento['tipo_acolhimento'] == 1){
			// soma as mensalidades para gerar o valor total do contrato
			// $sql_total_contrato = "SELECT SUM(valor) AS total FROM mensalidades WHERE acolhimento = {$_GET['acolhimento']}";
			// $stmt_total_contrato = $conn->query($sql_total_contrato);
			// $row_total_contrato = $stmt_total_contrato->fetch_assoc();
			// @$valor_total_contrato = $row_total_contrato['total'] + $acolhimento['valor_matricula'];
			// $valor_total_contrato = "R$" . number_format($valor_total_contrato, 2, ',', '.');
			
			// $valor_mensalidade = "R$" . number_format($acolhimento['valor_mensalidade'], 2, ',', '.') . " (". valorPorExtenso($acolhimento['valor_mensalidade']) ." )";


			// pega os valores e vencimentos das mensalidades
			$sql_mensalidade = "SELECT * FROM mensalidades WHERE acolhimento = {$_GET['acolhimento']}";
			$stmt_mensalidade = $conn->query($sql_mensalidade);
			$valor_mensalidade = '';
			$data_vencimento_mensalidade = '';
			$parcela = '';
			while($mensalidade = $stmt_mensalidade->fetch_assoc()){
				$parcela++;

				// $valor_mensalidade = "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Entrada de: "."R$" . number_format($mensalidade['valor'], 2, ',', '.') . " (". valorPorExtenso($mensalidade['valor']) ." )" . " com vencimento para: ".date('d/m/Y', strtotime($acolhimento['data_inicio']))."<br />";
				$data_vencimento_mensalidade .= "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;". $mensalidade['parcela'] ." - R$" . number_format($mensalidade['valor'], 2, ',', '.') . " (". valorPorExtenso($mensalidade['valor']) ." )" . " com vencimento para: " . date('d/m/Y', strtotime($mensalidade['data_vencimento'])) . "<br />";
			}
		}
	}

	if(isset($_GET['tipo']) && $_GET['tipo'] == 2){ // vaga social
		$sql_contratos = "SELECT * FROM modelos_contratos WHERE tipo = {$_GET['tipo']}";
	}

	$stmt_contratos = $conn->query($sql_contratos);
	$contratos = $stmt_contratos->fetch_assoc();

	$c_a = "<table>";
	$sql_cronograma_atividades = "SELECT * FROM cronograma_atividades";
	$stmt_cronograma_atividades = $conn->query($sql_cronograma_atividades);
	while($atividades = $stmt_cronograma_atividades->fetch_assoc()){
		if($atividades['grupo'] == 0 or $atividades['grupo'] == ''){
			$c_a .= "<tr>";
			$c_a .= "<td>". $atividades['nome'] ."</td>";
			$c_a .= "<td>". $atividades['periodo'] ."</td>";
			$c_a .= "</tr>";
		}
	}

	$sql_grupos_cronograma_atividades = "SELECT * FROM grupos_cronograma_atividades";
	$stmt_grupos_cronograma_atividades = $conn->query($sql_grupos_cronograma_atividades);
	while($grupos_cronograma_atividades = $stmt_grupos_cronograma_atividades->fetch_assoc()){
		$c_a .= "<tr>";
		$c_a .= "<td colspan='2'><strong>". $grupos_cronograma_atividades['nome'] ."</strong></td>";
		$c_a .= "</tr>";

		$sql_cronograma_atividades = "SELECT * FROM cronograma_atividades WHERE grupo = {$grupos_cronograma_atividades['id']}";
		$stmt_cronograma_atividades = $conn->query($sql_cronograma_atividades);
		while($atividades = $stmt_cronograma_atividades->fetch_assoc()){
			$c_a .= "<tr>";
			$c_a .= "<td>- - - ". $atividades['nome'] ."</td>";
			$c_a .= "<td>". $atividades['periodo'] ."</td>";
			$c_a .= "</tr>";
		}
	}
	// puxa o cronograma de atividades
	// $sql_grupos_cronograma_atividades = "SELECT * FROM grupos_cronograma_atividades";
	// $stmt_grupos_cronograma_atividades = $conn->query($sql_grupos_cronograma_atividades);
	// while($grupos_cronograma_atividades = $stmt_grupos_cronograma_atividades->fetch_assoc()){
	// 	$sql_cronograma_atividades = "SELECT * FROM cronograma_atividades WHERE grupo = {$grupos_cronograma_atividades['id']}";
	// 	$stmt_cronograma_atividades = $conn->query($sql_cronograma_atividades);
	// 	while($cronograma_atividades = $stmt_cronograma_atividades->fetch_assoc()){
	// 		$atividades = ;
	// 	}
	// }
	$c_a .= "</table>";

	// $url_timbre = "http://maequeacolhe.com.br/sistema/public/uploads/config/timbre/" . $ajustes['timbre'];
	$url_timbre = "https://escudodobem.gruub.com.br/public/uploads/config/timbre/" . $ajustes['timbre'];

	$e = '<html>
			<head>
				<meta charset="utf-8">
				<title>CONTRATO - ' . strtoupper($acolhido["nome"]) . '</title>
				<style type="text/css">
					@page {
						margin: 200px 30px 30px 30px;
					}

					html {
					  -webkit-text-size-adjust: 100%;
					      -ms-text-size-adjust: 100%;
					}

					body {
						font-family: helvetica;
						font-size: 11px;
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
					    width: 100%;
					}

					th, td {
					    padding: 5px;
					}

					table.no-border, .no-border td, .no-border th {
						border: 0;
					}
				</style>
			</head>

			<body>';
	$e .= "<div id='head'></div>";

	$data_acolhimento = explode('-', $acolhimento['data_inicio']);
    $mes_hoje = $data_acolhimento[1];
    $dia = explode(' ', $data_acolhimento[2]);
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

	// data de acolhimento
	$data_hoje = $dia . ' de ' . $nome_mes . ' de ' . $data_acolhimento[0];

	// $data_hoje = date('d') . ' de ' . $nome_mes . ' de ' . date('Y');

	//replace template var with value
	$token = array(
		// ACOLHIDO
		'ACO_NOME' => $acolhido['nome'],
	    'ACO_RG' => $acolhido['rg'],
	    'ACO_CPF' => $acolhido['cpf'],
	    'ACO_DATA_NASCIMENTO' => date('d/m/Y', strtotime($acolhido['data_nascimento'])),
	    'ACO_NATURALIDADE' => $acolhido['naturalidade'],
	    'ACO_UF_NATURALIDADE' => $acolhido['uf_naturalidade'],
	    'ACO_CAD_UNICO' => $acolhido['cad_unico'],
	    'ACO_CARTAO_SUS' => $acolhido['cartao_sus'],
	    'ACO_PIS' => $acolhido['pis'],
	    'ACO_NOME_PAI' => $acolhido['nome_pai'],
	    'ACO_DATA_NASCIMENTO_PAI' => $acolhido['data_nascimento_pai'],
	    'ACO_PROFISSAO_PAI' => $acolhido['profissao_pai'],
	    'ACO_NOME_MAE' => $acolhido['nome_mae'],
	    'ACO_DATA_NASCIMENTO_MAE' => $acolhido['data_nascimento_mae'],
	    'ACO_PROFISSAO_MAE' => $acolhido['profissao_mae'],
	    'ACO_CEP' => $acolhido['cep'],
	    'ACO_ENDERECO' => $acolhido['endereco'],
	    'ACO_NUM' => $acolhido['num'],
	    'ACO_COMPLEMENTO' => $acolhido['complemento'],
	    'ACO_BAIRRO' => $acolhido['bairro'],
	    'ACO_CIDADE' => $acolhido['cidade'],
	    'ACO_UF' => $acolhido['uf'],
		// CONTATO PRINCIPAL
	    'ACO_NOME_CONTATO_PRINCIPAL' => $contato_acolhido['nome'],
	    'ACO_RG_CONTATO_PRINCIPAL' => $contato_acolhido['rg'],
	    'ACO_CPF_CONTATO_PRINCIPAL' => $contato_acolhido['cpf'],
	    'ACO_DATA_NASCIMENTO_CONTATO_PRINCIPAL' => date('d/m/Y', strtotime($contato_acolhido['data_nascimento'])),
	    'ACO_TEL_CONTATO_PRINCIPAL' => $contato_acolhido['telefone'],
	    'ACO_CEL_CONTATO_PRINCIPAL' => $contato_acolhido['celular'],
	    'ACO_PARENTESCO_CONTATO_PRINCIPAL' => $contato_acolhido['grau_parentesco'],
	    'ACO_EMAIL_CONTATO_PRINCIPAL' => $contato_acolhido['email'],
	    'ACO_ENDERECO_CONTATO_PRINCIPAL' => $contato_acolhido['endereco'],
	    'ACO_NUM_CONTATO_PRINCIPAL' => ', '.$contato_acolhido['num'],
	    'ACO_CEP_CONTATO_PRINCIPAL' => $contato_acolhido['cep'],
	    'ACO_COMPLEMENTO_CONTATO_PRINCIPAL' => $contato_acolhido['complemento'],
	    'ACO_BAIRRO_CONTATO_PRINCIPAL' => $contato_acolhido['bairro'],
	    'ACO_CIDADE_CONTATO_PRINCIPAL' => $contato_acolhido['cidade'],
	    'ACO_UF_CONTATO_PRINCIPAL' => $contato_acolhido['uf'],
	    // CRONOGRAMA DE ATIVIDADES
	    'CRONOGRAMA_ATIVIDADES' => $c_a,
		// TRATAMENTO
	    'ACO_PREVISAO_SAIDA' => date('d/m/Y', strtotime($acolhimento['previsao_saida'])),
	    'ACO_DATA_INICIO_ACOLHIMENTO' => date('d/m/Y'),
	    'VALOR_TOTAL_CONTRATO' => $valor_total_contrato,
	    'VALOR_MATRICULA' => $valor_matricula,
	    'VALOR_MENSALIDADE' => $valor_mensalidade,
	    'DATA_VENCIMENTO_MENSALIDADE' => $data_vencimento_mensalidade,
		// AJUSTES/CT				
	    'CT_RAZAO_SOCIAL' => $ajustes['razao_social'],
	    'CT_NOME_FANTASIA' => $ajustes['nome_fantasia'],
	    'CT_CNPJ' => $ajustes['cnpj'],
	    'CT_CEP' => $ajustes['cep'],
	    'CT_ENDERECO' => $ajustes['endereco'],
	    'CT_NUM' => $ajustes['num'],
	    'CT_BAIRRO' => $ajustes['bairro'],
	    'CT_CIDADE' => $ajustes['cidade'],
	    'CT_UF' => $ajustes['uf'],
	    'CT_TEMPO_TRATAMENTO' => $ajustes['tempo_tratamento'],
	    // OUTROS
	    // 'DATA_HOJE' => strftime('%d de %B de %Y', strtotime('today')),
	    'DATA_HOJE' => $data_hoje,
	);

	$pattern = '[%s]';

	foreach($token as $key=>$val){
	    $varMap[sprintf($pattern,$key)] = $val;
	}

	$e .= strtr($contratos['conteudo'], $varMap);

	$e .= '</body></html>';

	$conn->close();

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

	// $canvas = $dompdf->get_canvas(); 
 //    $font = 'Helvetica';
	// $canvas->page_text(25, 810, "Elaboração: Ana Paula Goes", $font, 5.4, array(0,0,0)); //footer
	// $canvas->page_text(185, 810, "Elaborado em 10/12/2020 - Revisão nº 01 em 10/12/2020 - Próxima revisão em 10/12/2021", $font, 5.4, array(0,0,0)); //footer
	// $canvas->page_text(480, 810, "Aprovação: Marcio Roberto Calbente", $font, 5.4, array(0,0,0)); //footer
	// $canvas->page_text(270, 820, "PÁGINA {PAGE_NUM}/{PAGE_COUNT}", $font, 5.4, array(0,0,0)); //footer

	/* Exibe */
	$dompdf->stream(
		"CONTRATO - " . strtoupper($acolhido['nome']) . ".pdf", /* Nome do arquivo de saída */
		array(
			"Attachment" => false /* Para download, altere para true */
		)
	);
?>