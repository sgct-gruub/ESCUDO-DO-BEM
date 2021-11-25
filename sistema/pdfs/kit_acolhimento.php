<?php
	use Dompdf\Dompdf;
    use Dompdf\Options;
    use Dompdf\Canvas;

    require_once "Config.php";
    
    /* Carrega a classe DOMPdf */
	require_once("../public/assets/dompdf/autoload.inc.php");

	// error_reporting(E_ALL); 
	// ini_set('display_errors', 1);

	setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

	date_default_timezone_set('America/Sao_Paulo');

	session_start();

	if(isset($_GET['acolhimento']) && $_GET['acolhimento'] != ''){		

        /* Seleciona as configurações (nome da ct, cnpj, logo, timbre) */
		$config = db()->query("SELECT * FROM configuracoes")->fetch_assoc();

        /* Seleciona o acolhimento */
        $acolhimento = db()->query("SELECT * FROM acolhimentos_novo WHERE id = {$_GET['acolhimento']} LIMIT 1")->fetch_assoc();

        /* Seleciona a unidade */
        $unidade = db()->query("SELECT * FROM unidades WHERE id = {$acolhimento['unidade']} LIMIT 1")->fetch_assoc();

        /* Seleciona o quarto */
        $quarto = db()->query("SELECT * FROM quartos WHERE id = {$acolhimento['quarto']} LIMIT 1")->fetch_assoc();

        /* Seleciona o acolhido */
        $acolhido = db()->query("SELECT * FROM acolhidos WHERE id = {$acolhimento['acolhido']} LIMIT 1")->fetch_assoc();

        // identifica o contato principal do acolhido		
		$contato_acolhido = db()->query("SELECT * FROM contatos_acolhidos WHERE acolhido = {$acolhimento['acolhido']} AND status = 1")->fetch_assoc();
		
		// se não tiver, inclui as informações do acolhido
		if(!$contato_acolhido){
			$contato_acolhido['nome'] = $acolhido['nome'];
			$contato_acolhido['rg'] = $acolhido['rg'];
			$contato_acolhido['cpf'] = $acolhido['cpf'];
			$contato_acolhido['data_nascimento'] = $acolhido['data_nascimento'];
			if($contato_acolhido['data_nascimento'] == '' or $contato_acolhido['data_nascimento'] == '0000-00-00'){
				$contato_acolhido_data_nascimento = '';
			} else {
				$contato_acolhido_data_nascimento = date('d/m/Y', strtotime($contato_acolhido['data_nascimento']));
			}
			$contato_acolhido['grau_parentesco'] = 'O mesmo';
			$contato_acolhido['telefone'] = '';
			$contato_acolhido['celular'] = '';
			$contato_acolhido['endereco'] = $acolhido['endereco'];
        	$contato_acolhido['num'] = $acolhido['num'];
        	$contato_acolhido['complemento'] = $acolhido['complemento'];
        	$contato_acolhido['bairro'] = $acolhido['bairro'];
        	$contato_acolhido['cidade'] = $acolhido['cidade'];
        	$contato_acolhido['uf'] = $acolhido['uf'];
        	$contato_acolhido['cep'] = $acolhido['cep'];
		}

		// valor do contrato
		$valor_total_contrato = '';
		$valor_mensalidade = '';
		$data_vencimento_mensalidade = '';

		// pega o valor da matrícula
		@$valor_matricula = "R$" . number_format($acolhimento['valor_matricula'], 2, ',', '.') . " (". valorPorExtenso($acolhimento['valor_matricula']) ." )";

		// identifica o contrato
		if(isset($acolhimento['tipo_acolhimento']) && $acolhimento['tipo_acolhimento'] == 0){ // convenio
			$sql_contratos = "SELECT * FROM modelos_contratos WHERE tipo = {$acolhimento['tipo_acolhimento']} AND convenio = {$acolhimento['convenio']}";

			// verifica se a há data de início pelo convênio
			// se tiver, substitui a data de início pela data de início convênio
			if($acolhimento['data_inicio_convenio'] != '' && $acolhimento['data_inicio_convenio'] != '0000-00-00'){
				$acolhimento['data_inicio'] = $acolhimento['data_inicio_convenio'];
			}
		}

		if(isset($acolhimento['tipo_acolhimento']) && $acolhimento['tipo_acolhimento'] == 1){ // particular
			// soma as mensalidades para gerar o valor total do contrato
			$row_total_contrato = db()->query("SELECT SUM(valor) AS total FROM mensalidades WHERE acolhimento = {$_GET['acolhimento']}")->fetch_assoc();
			@$valor_total_contrato = $row_total_contrato['total'] + $acolhimento['valor_matricula'];
			$valor_total_contrato = "R$" . number_format($valor_total_contrato, 2, ',', '.');
				
			$valor_mensalidade = "R$" . number_format($acolhimento['valor_mensalidade'], 2, ',', '.') . " (". valorPorExtenso($acolhimento['valor_mensalidade']) ." )";


			// pega os valores e vencimentos das mensalidades
			// $valor_mensalidade = '';
			// $data_vencimento_mensalidade = '';
			// while($mensalidade = db()->query("SELECT * FROM mensalidades WHERE acolhimento = {$_GET['acolhimento']}")->fetch_assoc()){
			// 	$valor_mensalidade = "R$" . number_format($mensalidade['valor'], 2, ',', '.') . " (". valorPorExtenso($mensalidade['valor']) ." )";
			// 	// $data_vencimento_mensalidade .= "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;". $mensalidade['parcela'] ." - " . date('d/m/Y', strtotime($mensalidade['data_vencimento']));
			// 	$data_vencimento_mensalidade .= "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;". $mensalidade['parcela'] ." - " . date('d/m/Y', strtotime($mensalidade['data_vencimento'])) . "<br />";
			// }
		}

		$contratos = db()->query("SELECT * FROM modelos_contratos WHERE tipo = {$acolhimento['tipo_acolhimento']}")->fetch_assoc();

        /* Seleciona o convênio (se tiver) */
        if($acolhimento['tipo_acolhimento'] == 0){
            $convenio = db()->query("SELECT * FROM convenios WHERE id = {$acolhimento['convenio']} LIMIT 1")->fetch_assoc();
        }

        // $url_timbre = "http://maequeacolhe.com.br/sistema/public/uploads/config/timbre/" . $config['timbre'];
        $url_timbre = "https://escudodobem.gruub.com.br/public/uploads/config/timbre/" . $config['timbre'];

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

        // $data_hoje = date('d') . ' de ' . $nome_mes . ' de ' . date('Y');

        // data de acolhimento
		$data_hoje = $dia . ' de ' . $nome_mes . ' de ' . $data_acolhimento[0];
		
        // $data_hoje = strftime('%d de %B de %Y', strtotime('today'));

        /* Se encontrar o acolhimento */
        if ($acolhimento) {

        	$e = '<html>
			<head>
				<meta charset="utf-8">
				<title>Kit Acolhimento - ' . strtoupper($acolhido["nome"]) . '</title>

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
					    padding: 4px;
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

            if($acolhido['data_nascimento'] == '' or $acolhido['data_nascimento'] == '0000-00-00'){
            	$acolhido_data_nascimento = '';
            } else {
            	$acolhido_data_nascimento = date('d/m/Y', strtotime($acolhido['data_nascimento']));
            }

            // CONTRATO
            $token = array(
				// ACOLHIDO
				'ACO_NOME' => $acolhido['nome'],
			    'ACO_RG' => $acolhido['rg'],
			    'ACO_CPF' => $acolhido['cpf'],
			    'ACO_DATA_NASCIMENTO' => $acolhido_data_nascimento,
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
			    'ACO_DATA_NASCIMENTO_CONTATO_PRINCIPAL' => $contato_acolhido_data_nascimento,
			    'ACO_TEL_CONTATO_PRINCIPAL' => $contato_acolhido['telefone'],
			    'ACO_CEL_CONTATO_PRINCIPAL' => $contato_acolhido['celular'],
			    'ACO_PARENTESCO_CONTATO_PRINCIPAL' => $contato_acolhido['grau_parentesco'],
			    // 'ACO_EMAIL_CONTATO_PRINCIPAL' => $acolhido['email_contato_principal'],
			    // CRONOGRAMA DE ATIVIDADES
			    // 'CRONOGRAMA_ATIVIDADES' => $c_a,
				// TRATAMENTO
			    'ACO_PREVISAO_SAIDA' => date('d/m/Y', strtotime($acolhimento['previsao_saida'])),
			    'ACO_DATA_INICIO_ACOLHIMENTO' => date('d/m/Y'),
			    'VALOR_TOTAL_CONTRATO' => $valor_total_contrato,
			    'VALOR_MATRICULA' => $valor_matricula,
			    'VALOR_MENSALIDADE' => $valor_mensalidade,
			    'DATA_VENCIMENTO_MENSALIDADE' => $data_vencimento_mensalidade,
				// AJUSTES/CT				
			    'CT_RAZAO_SOCIAL' => $config['razao_social'],
			    'CT_NOME_FANTASIA' => $config['nome_fantasia'],
			    'CT_CNPJ' => $config['cnpj'],
			    'CT_CEP' => $config['cep'],
			    'CT_ENDERECO' => $config['endereco'],
			    'CT_NUM' => $config['num'],
			    'CT_BAIRRO' => $config['bairro'],
			    'CT_CIDADE' => $config['cidade'],
			    'CT_UF' => $config['uf'],
			    'CT_TEMPO_TRATAMENTO' => $config['tempo_tratamento'],
			    // OUTROS
			    // 'DATA_HOJE' => strftime('%d de %B de %Y', strtotime('today')),
			    'DATA_HOJE' => $data_hoje,
			);

			$pattern = '[%s]';

			foreach($token as $key=>$val){
			    $varMap[sprintf($pattern,$key)] = $val;
			}

			$e .= strtr($contratos['conteudo'], $varMap);

			$e .= "<div style='page-break-after: always;'></div>";


			// FICHA CADASTRAL
			$e .= "<h2 align='center' style='margin-bottom: -10px;'>Ficha Cadastral - FC</h2> <br />";

			// informações do acolhido
			$e .= "<table width='100%'>";
			$e .= "<tr bgcolor='#f0f0f0'>";
			$e .= "<td align='center' colspan='4'><strong>Informações do acolhido</strong></td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>Nome</strong></td>";
			$e .= "<td>". $acolhido['nome'] ."</td>";

			$e .= "<td align='right'><strong>Nascimento</strong></td>";
			$e .= "<td>". date('d/m/Y', strtotime($acolhido['data_nascimento'])) ." - " . calculaDataAno($acolhido['data_nascimento']) . "</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>RG</strong></td>";
			$e .= "<td>". $acolhido['rg'] ."</td>";

			$e .= "<td align='right'><strong>CPF</strong></td>";
			$e .= "<td>". $acolhido['cpf'] ."</td>";
			$e .= "</tr>";
			$e .= "</table>";
			// informações do acolhido

			$e .= "<br />";

			// informações do acolhimento
			$e .= "<table width='100%' align='center'>";
			$e .= "<tr bgcolor='#f0f0f0'>";
			$e .= "<td align='center' colspan='6'><strong>Informações do acolhimento</strong></td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>Status</strong></td>";
			if($acolhimento['status'] == 0){
				$status_acolhimento = 'Em acolhimento';
			}

			if($acolhimento['status'] == 1){
				$status_acolhimento = 'Ressocialização';
			}

			if($acolhimento['status'] == 11){
				$status_acolhimento = 'Alta Terapêutica';
			}

			if($acolhimento['status'] == 12){
				$status_acolhimento = 'Alta Administrativa';
			}

			if($acolhimento['status'] == 13){
				$status_acolhimento = 'Alta Solicitada';
			}

			if($acolhimento['status'] == 14){
				$status_acolhimento = 'Evasão';
			}
			$e .= "<td>". $status_acolhimento ."</td>";
			$e .= "<td align='right'><strong>Casa</strong></td>";
            $e .= "<td>". $unidade['nome'] ."</td>";
			$e .= "<td align='right'><strong>Quarto</strong></td>";
            $e .= "<td>". $quarto['nome'] ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>Data de entrada</strong></td>";
			$e .= "<td colspan='2'>". date('d/m/Y', strtotime($acolhimento['data_inicio'])) ."</td>";

			if($acolhimento['status'] == 11 OR $acolhimento['status'] == 12 OR $acolhimento['status'] == 13 OR $acolhimento['status'] == 14){
				$e .= "<td align='left'><strong>Data de saída</strong></td>";
				$e .= "<td colspan='2'>". date('d/m/Y', strtotime($acolhimento['data_saida'])) ."</td>";
			} else {
				$e .= "<td align='left'><strong>Previsão de saída</strong></td>";
				$e .= "<td colspan='2'>". date('d/m/Y', strtotime($acolhimento['previsao_saida'])) ."</td>";
			}

			$e .= "</tr>";

			// $e .= "<tr>";
			// $e .= "<td align='right'><strong>Tipo de acolhimento</strong></td>";
			// if($acolhimento['tipo_acolhimento'] == 0){
			// 	$tipo_acolhimento = $convenio['nome'];
			// }

			// if($acolhimento['tipo_acolhimento'] == 1){
			// 	$tipo_acolhimento = 'Particular';
			// }

			// if($acolhimento['tipo_acolhimento'] == 2){
			// 	$tipo_acolhimento = 'Vaga social';
			// }
			// $e .= "<td colspan='5'>". $tipo_acolhimento ."</td>";
			// $e .= "</tr>";
			$e .= "</table>";
			// informações do acolhimento

			$e .= "<br />";

            // endereço
            $e .= "<table width='100%' align='center'>";
            $e .= "<tr bgcolor='#f0f0f0'>";
            $e .= "<td align='center' colspan='6'><strong>Localização do acolhido</strong></td>";
            $e .= "</tr>";
            $e .= "<tr>";
            $e .= "<td align='right'><strong>Endereço</strong></td>";
            if($acolhido['complemento'] != ''){
                $e .= "<td colspan='3'>". $acolhido['endereco'] .", " . $acolhido['num'] . " - " . $acolhido['complemento'] . "</td>";
            } else {
                $e .= "<td colspan='3'>". $acolhido['endereco'] .", " . $acolhido['num'] . "</td>";
            }
            $e .= "<td align='right'><strong>CEP</strong></td>";
            $e .= "<td>". $acolhido['cep'] ."</td>";
            $e .= "</tr>";
            $e .= "<tr>";
            $e .= "<td align='right'><strong>Bairro</strong></td>";
            $e .= "<td>". $acolhido['bairro'] ."</td>";
            $e .= "<td align='right'><strong>Cidade</strong></td>";
            $e .= "<td>". $acolhido['cidade'] ."</td>";
            $e .= "<td align='right'><strong>UF</strong></td>";
            $e .= "<td>". $acolhido['uf'] ."</td>";
            $e .= "</tr>";
            $e .= "</table>";
            // endereço

			$e .= "<br />";

			// responsavel
			$e .= "<table width='100%' align='center'>";
			$e .= "<tr bgcolor='#f0f0f0'>";
			$e .= "<td align='center' colspan='6'><strong>Responsável</strong></td>";
			$e .= "</tr>";
			$e .= "<tr>";
			$e .= "<td align='right'><strong>Nome</strong></td>";
			$e .= "<td colspan='2'>". $contato_acolhido['nome'] ."</td>";
			$e .= "<td align='right'><strong>Parentesco</strong></td>";
			$e .= "<td colspan='2'>". $contato_acolhido['grau_parentesco'] ."</td>";
			$e .= "</tr>";
			$e .= "<tr>";
			$e .= "<td align='right'><strong>RG</strong></td>";
			$e .= "<td colspan='2'>". $contato_acolhido['rg'] ."</td>";
			$e .= "<td align='right'><strong>CPF</strong></td>";
			$e .= "<td colspan='2'>". $contato_acolhido['cpf'] ."</td>";
			$e .= "</tr>";
			$e .= "<tr>";
			$e .= "<td align='right'><strong>Telefone</strong></td>";
			$e .= "<td colspan='2'>". $contato_acolhido['telefone'] ."</td>";
			$e .= "<td align='right'><strong>Celular</strong></td>";
			$e .= "<td colspan='2'>". $contato_acolhido['celular'] ."</td>";
			$e .= "</tr>";
			$e .= "<tr>";
            $e .= "<td align='right'><strong>Endereço</strong></td>";
            if($contato_acolhido['complemento'] != ''){
                $e .= "<td colspan='3'>". $contato_acolhido['endereco'] .", " . $contato_acolhido['num'] . " - " . $contato_acolhido['complemento'] . "</td>";
            } else {
                $e .= "<td colspan='3'>". $contato_acolhido['endereco'] .", " . $contato_acolhido['num'] . "</td>";
            }
            $e .= "<td align='right'><strong>CEP</strong></td>";
            $e .= "<td>". $contato_acolhido['cep'] ."</td>";
            $e .= "</tr>";
            $e .= "<tr>";
            $e .= "<td align='right'><strong>Bairro</strong></td>";
            $e .= "<td>". $contato_acolhido['bairro'] ."</td>";
            $e .= "<td align='right'><strong>Cidade</strong></td>";
            $e .= "<td>". $contato_acolhido['cidade'] ."</td>";
            $e .= "<td align='right'><strong>UF</strong></td>";
            $e .= "<td>". $contato_acolhido['uf'] ."</td>";
            $e .= "</tr>";
			$e .= "</table>";
			// responsavel

			$e .= "<br />";
			$e .= "<br />";
			$e .= "<br />";

			// assinaturas
			$e .= "<table width='49%' align='left' class='no-border'>";
			$e .= "<tr>";
			$e .= "<td align='center'>_______________________________________</td>";
			$e .= "</tr>";
			$e .= "<tr>";
			$e .= "<td align='center'>". $acolhido['nome'] ."<br />". $acolhido['cpf'] ."</td>";
			$e .= "</tr>";
			$e .= "</table>";

			$e .= "<table width='49%' align='right' class='no-border'>";
			$e .= "<tr>";
			$e .= "<td align='center'>_______________________________________</td>";
			$e .= "</tr>";
			$e .= "<tr>";
			$e .= "<td align='center'>". $config['razao_social'] ."<br />". $config['cnpj'] ."</td>";
			$e .= "</table>";
			// assinaturas

			$e .= "<div style='page-break-after: always;'></div>";


            // TERMO DE ADESÃO
            $e .= "<h2 align='center' style='margin-bottom: -20px;'>Termo de Adesão - TA</h2> <br />";

			$e .= "<p>Eu <strong>". $acolhido['nome'] ."</strong>, solicito ser acolhido na ". $config['nome_fantasia'] ." para recuperação da dependência química, e declaro voluntariamente o seguinte:</p>";

			$e .= "<p>";
			$e .= "1 - Estou aderindo voluntariamente ao programa proposto pela CTNJ, sabendo que posso desistir do mesmo a qualquer momento, sem risco de constrangimento, agressões e pressão de qualquer espécie.<br />";
			$e .= "2 - Concordo com os critérios de readmissão propostos no <strong>Manual de Rotinas e Procedimentos</strong>.<br />";
			$e .= "3 - Estou ciente de que ao ingressar na CTNJ será realizada a revista dos meus pertences, para evitar a entrada de objetos não permitidos.<br />";
			$e .= "4 - Concordo em receber visitas conforme estabelecido no <strong>Regulamento de Visita</strong>.<br />";
			$e .= "5 - As saídas acontecerão de acordo com o <strong>PAS (Plano de Atendimento Singular)</strong>.<br />";
			$e .= "6 - Concordo que as minhas correspondências, assim como os volumes a enviar ou receber, sejam abertos e fechados na presença da equipe, a fim de evitar a entrada e saída de objetos não permitidos. A correspondência não será lida pela equipe da CTNJ.<br />";
			$e .= "7 - Participarei ativamente das atividades do Cronograma, de acordo com o estabelecido em meu <strong>PAS</strong>.<br />";
			$e .= "8 - O dinheiro e objetos de valor ficarão sob responsabilidade da coordenação, por motivo de segurança, tendo acesso aos mesmos quando necessário.<br />";
			$e .= "9 - A comunicação com a família fora dos dias de visita poderá ser realizada por meio de ligações telefônicas ou outros meios digitais, de acordo com meu <strong>PAS</strong> e com o estabelecido nas <strong>Normas de Moradia</strong>.<br />";
			$e .= "10 - Agressões físicas, uso de álcool e/ou drogas, roubos e recusa constante de participação nas atividades propostas, podem levar ao desligamento (Alta Administrativa) do programa de acolhimento<br />";
			$e .= "11 - O término do programa de acolhimento acontecerá de acordo com o meu <strong>PAS</strong>.<br />";
			$e .= "</p>";
            
            $e .= "<br />";

            $e .= "<p align='right'>". $config['cidade'] .", ". $data_hoje ."</p>";

			$e .= "<br />";

			// assinaturas
			$e .= "<table width='100%' align='center' class='no-border'>";
            $e .= "<tr>";
            $e .= "<td align='center'>_______________________________________";
			$e .= "<br />". $acolhido['nome'] ."</td>";
			$e .= "</table>";
			// assinaturas

			$e .= "<div style='page-break-after: always;'></div>";

			
			// TERMO DE AUTORIZAÇÃO DE IMAGEM
			$e .= "<h2 align='center' style='margin-bottom: -20px;'>Termo de Autorização de Imagem - TAI</h2> <br />";

			$e .= "<p>
                Eu <strong>". $acolhido['nome'] ."</strong> portador do RG de nº <strong>" . $acolhido['rg'] . "</strong> e CPF <strong>" . $acolhido['cpf'] . "</strong>, <u>AUTORIZO</u> esta entidade a disponibilizar conteúdos eletrônicos contendo minha imagem (foto) em todas as vias de comunicação eletrônica de que a mesma se utilize, sabendo também que tenho plena liberdade de não assinar este termo caso deseje preservar o meu anonimato, sem ser alvo, por isto, de nenhuma forma de constrangimento, assim como também posso cancelar esta autorização a qualquer momento, por escrito, caso considere inapropriado o conteúdo divulgado.</p>";
            
            $e .= "<br />";
            $e .= "<br />";

            $e .= "<p align='right'>". $config['cidade'] .", ". $data_hoje ."</p>";

			$e .= "<br />";
			$e .= "<br />";
			$e .= "<br />";

			// assinaturas
			$e .= "<table width='100%' align='center' class='no-border'>";
            $e .= "<tr>";
            $e .= "<td align='center'>_______________________________________</td>";
            $e .= "</tr>";
			$e .= "<tr>";
			$e .= "<td align='center'>". $acolhido['nome'] ."</td>";
			$e .= "</table>";
			// assinaturas

			$e .= "<div style='page-break-after: always;'></div>";


			// TERMO DE PERTENCES PESSOAIS
			$e .= "<h2 align='center' style='margin-bottom: -20px;'>Termo de Pertences Pessoais - TPP</h2> <br />";

			$e .= "<p>
                Eu <strong>". $acolhido['nome'] ."</strong> portador do RG de nº <strong>" . $acolhido['rg'] . "</strong> e CPF <strong>" . $acolhido['cpf'] . "</strong>, acolhido na ". $config['nome_fantasia'] ." desde o dia <strong>" . date('d/m/Y', strtotime($acolhimento['data_inicio'])) . "</strong>, declaro para todos os fins de direito, na forma da lei, sem qualquer tipo de constrangimento e de livre e espontânea vontade, que meus pertences abaixo descritos, são de minha responsabilidade exclusiva.</p>";

        	$e .= "<p>". nl2br($acolhimento['pertences']) ."</p>";

            $e .= "<p>Declaro ainda, que estou ciente que em caso de extravio, danos, ou omissões de qualquer natureza que o valha, a ". $config['nome_fantasia'] ." não se responsabiliza de nenhuma forma, direta ou indireta.</p>";
            
            $e .= "<br />";
            $e .= "<br />";

            $e .= "<p align='right'>". $config['cidade'] .", ". $data_hoje ."</p>";

			$e .= "<br />";
			$e .= "<br />";
			$e .= "<br />";

			// assinaturas
			$e .= "<table width='100%' align='center' class='no-border'>";
            $e .= "<tr>";
            $e .= "<td align='center'>_______________________________________</td>";
            $e .= "</tr>";
			$e .= "<tr>";
			$e .= "<td align='center'>". $acolhido['nome'] ."</td>";
			$e .= "</table>";
			// assinaturas

			$e .= "<div style='page-break-after: always;'></div>";


			// ENXOVAL BÁSICO
			$e .= "<h2 align='center' style='margin-bottom: -20px;'>Enxoval Básico</h2> <br />";

			$e .="<strong>Material de Estudo</strong><br />";
            $e .= "<table width='100%' align='left' class='no-border'>";
            $e .= "<tr><td>1 Bíblia<br />";
            $e .= "2 Cadernos Grandes<br />";
            $e .= "1 Documento Pessoal Original<br />";
            $e .= "2 Canetas<br />";
            $e .= "2 Pastas Plásticas</td></tr>";
            $e .= "</table>";

            $e .= "<br />";
            $e .= "<br />";
            $e .= "<br />";
            $e .= "<br />";
            $e .= "<br />";

            $e .="<strong>Roupas</strong><br />";
            $e .= "<table width='100%' align='left' class='no-border'>";
            $e .= "<tr><td>1 Mala grande ou 2 pequenas<br />";
            $e .= "2 Travesseiros<br />";
            $e .= "2 Cobertores<br />";
            $e .= "2 Jogos de cama<br />";
            $e .= "2 Toalhas de banho</td>";

            $e .= "<td>4 Bermudas<br />";
            $e .= "3 Calças de moletom<br />";
            $e .= "4 Calças jeans (ou outras)<br />";
            $e .= "10 Camisetas<br />";
            $e .= "5 Agasalhos incluindo jaquetas</td>";

            $e .= "<td>7 Cuecas<br />";
            $e .= "5 Pares de meias<br />";
            $e .= "2 Chinelos de dedo<br />";
            $e .= "3 Calçados (sapato, tênis, botina)<br />";
            $e .= "2 Toalhas de rosto</td></tr>";
            $e .= "<tr><td colspan='3' style='font-size: 0.75em'>Obs.: As quantidades acima representam <strong><u> o máximo </u></strong> de roupas de cada tipo que poderão ser trazidas para não abarrotar os armários, não significa que tenham que trazer exatamente esta quantidade.</td></tr>";
            $e .= "</table>";

            $e .= "<br />";
            $e .= "<br />";
            $e .= "<br />";
            $e .= "<br />";
            $e .= "<br />";
            $e .= "<br />";
            $e .= "<br />";

            $e .="<strong>Higiene Pessoal</strong><br />";
            $e .= "<table width='100%' align='left' class='no-border'>";
            $e .= "<tr><td>1 Balde<br />";
            $e .= "1 Escova de roupa<br />";
            $e .= "5 Sabonetes<br />";
            $e .= "1 Escova dental<br />";
            $e .= "4 Aparelhos de barbear<br />";
            $e .= "1 Rodo<br />";
            $e .= "2 Pct. papel higiênico<br</td>";

            $e .= "<td>1 corda de varal<br />";
            $e .= "2kg sabão em pó<br />";
            $e .= "1 Bucha de banho<br />";
            $e .= "3 Pasta de dentes<br />";
            $e .= "1 Pct. saco de lixo 50l<br />";
            $e .= "1 Vassoura<br /><br /></td>";

            $e .= "<td>2 pct. prendedores<br />";
            $e .= "1 Pct. sabão em pedra<br />";
            $e .= "2 Shampoo<br />";
            $e .= "2 Desodorante roll-on<br />";
            $e .= "3 Bucha para louça<br />";
            $e .= "5 Panos de prato<br /><br /></td></tr>";
            $e .= "<tr><td colspan='3' style='font-size: 0.75em'>Obs.: se trouxerem perfume, este ficará sob cuidado da coordenação e será utilizado com a devida autorização.</td></tr>";
            $e .= "</table>";

            $e .= "<br />";
            $e .= "<br />";
            $e .= "<br />";
            $e .= "<br />";
            $e .= "<br />";
            $e .= "<br />";
            $e .= "<br />";
            $e .= "<br />";
            $e .= "<br />";

            $e .= "<p><strong>Cigarro:</strong> Só podem ser trazidos cigarros de valor máximo igual ao mais barato nacional, acompanhados de isqueiros unicamente a gás. Devem ser trazidos cigarros para um mês, aproximadamente 4 pacotes</td></tr>";
            $e .= "</p>";

            $e .= "<p><strong>Custos:</strong> Deve ser trazido no dia da internação: Taxa de matrícula (combinado na entrevista). Dinheiro para o caixa de gastos pessoais (R$50,00)</td></tr>";
            $e .= "</p>";

            $e .= "<p><strong>Exames Médicos:</strong> HIV, Hemograma completo, Fezes, Urina I, RX Tórax ou Exame de Escarro.</td></tr>";
            $e .= "</p>";

            $e .= "<p><strong>Medicação de Uso Comunitário:</strong> Dipirona ou paracetamol, dorflex, nimesulida ou diclofenaco e multigripal</td></tr>";
            $e .= "</p>";

            $e .= "<p><strong>Não Pode:</strong> Revistas ou fotografias pornográficas, camiseta de bandas ou com imagens de bebida/drogas, remédios sem a correspondente receita médica, piercings, óculos escuros, fumo de corda ou tabaco solto, objetos de valor diversos.</td></tr>";
            $e .= "</p>";

            $e .= "<div style='page-break-after: always;'></div>";


			// REGULAMENTO DE VISITAS
			$e .= "<h2 align='center' style='margin-bottom: -20px;'>Regulamento de Visita para Familiares</h2> <br />";

			$e .= "<p>";
			$e .= "1 -  É necessário que os familiares e/ou responsáveis participem das visitas mensais para o bom desenvolvimento do programa terapêutico da Comunidade Terapêutica Nova Jornada (CTNJ).<br />";
			$e .= "2 - É muito importante que os familiares e/ou responsáveis que forem participar do dia de visita frequentem grupos de apoio para familiares de dependentes químicos, para melhor compreender as particularidades do processo de recuperação.<br />";
			$e .= "3 - As visitas serão realizadas no 2º Domingo de cada mês.<br />";
			$e .= "4 - As visitas começarão a partir das 9:00hs.; será realizada a Celebração Religiosa e Graduação pontualmente às 11:00hs., e o almoço comunitário será às 13:00hs. A visita será finalizada às 18:00hs.<br />";
			$e .= "5 - Pede-se que os familiares e/ou responsáveis compareçam antes do início da Celebração Religiosa e que participem do atendimento psicológico, que será realizado de acordo com a ordem de chegada.<br />";
			$e .= "6 - A Comunidade fornecerá o almoço, e será cobrada uma taxa de R$ 20,00 por pessoa, crianças abaixo de 12 anos de idade não pagam. Nesta taxa não se inclui o acolhido. Ao ingressar no refeitório deverá apresentar-se o comprovante de pagamento da refeição.<br />";
			$e .= "7 - Os visitantes devem zelar pelo cumprimento dos horários do dia de visita, dirigindo-se imediatamente aos locais de atividades quando for dado o sinal.<br />";
			$e .= "8 - É rigorosamente proibida a entrada de bebidas alcoólicas e\ou drogas na CTNJ, assim como a participação de visitantes que estejam sob o efeito de álcool e\ou drogas. Falhas neste sentido poderá representar a proibição da participação do visitante em todas as próximas visitas.<br />";
			$e .= "9 - As cartas, dinheiro ou qualquer volume que seja trazido para o acolhido devem ser encaminhados para a coordenação, e nunca devem ser entregues diretamente ao acolhido.<br />";
			$e .= "10 - Pede-se que as visitantes do sexo feminino tenham cuidado com a vestimenta. É proibido comparecer com minissaias, shorts curtos, roupas decotadas ou transparentes.<br />";
			$e .= "11 - É proibida a entrada na cozinha durante o dia de visita.<br />";
			$e .= "12 - É proibido colher hortaliças e frutas.<br />";
			$e .= "13 - É proibida a utilização de telefones celulares pelos residentes, salvo com autorização específica da equipe de trabalho.<br />";
			$e .= "14 - É terminantemente proibido manter qualquer tipo de relação sexual durante o dia de visita. Informamos que qualquer falha neste sentido poderá implicar o desligamento do acolhido do programa de recuperação (Alta Administrativa), ou a mesma penalidade do item nº 8.<br />";
			$e .= "15 - O acolhido não poderá permanecer dentro do veículo da família durante o dia de visita, nem dirigir o mesmo.<br />";
			$e .= "16 - É proibido sair da área central da CTNJ durante o dia de visita, exceto com autorização específica da equipe de trabalho.<br />";
			$e .= "</p>";

			$e .= "<p>Obs.: Pedimos a todas as famílias que zelem para cumprir com todas estas exigências básicas, para que o dia de visita possa ser desfrutado por todos. Lembramos também, que o tratamento é bilateral, isto significa que, para que aconteça a recuperação, é importante que os dois lados se dediquem para a mudança.</p>";

			$e .= '</body></html>';

        } else {
        	echo "Acolhimento não encontrado!";
        }


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

        $canvas = $dompdf->get_canvas();
        $font = 'Helvetica';
		$canvas->page_text(25, 810, "Elaboração: Ana Paula Goes", $font, 5.4, array(0,0,0)); //footer
		$canvas->page_text(185, 810, "Elaborado em 10/12/2020 - Revisão nº 01 em 10/12/2020 - Próxima revisão em 10/12/2021", $font, 5.4, array(0,0,0)); //footer
		$canvas->page_text(480, 810, "Aprovação: Marcio Roberto Calbente", $font, 5.4, array(0,0,0)); //footer
		$canvas->page_text(270, 820, "PÁGINA {PAGE_NUM}/{PAGE_COUNT}", $font, 5.4, array(0,0,0)); //footer

        /* Exibe */
        $dompdf->stream(
            "KIT ACOLHIMENTO - " . strtoupper($acolhido["nome"]) . ".pdf", /* Nome do arquivo de saída */
            array(
                "Attachment" => false /* Para download, altere para true */
            )
        );
    }
?>