<?php
	setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

	date_default_timezone_set('America/Sao_Paulo');

	session_start();

	function calculaDataAno($data)
	{
		$data_atual = new DateTime( date("Y-m-d") );
		$data = new DateTime( $data );

		$intervalo = $data_atual->diff( $data );
		
		if($intervalo->y > 1)
		{
			return $intervalo->y . " anos ";
		}
		elseif($intervalo->y > 0)
		{
			return $intervalo->y . " ano ";
		}
	}


	if(isset($_GET['acolhimento']) && $_GET['acolhimento'] != ''){

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

        /* Seleciona o acolhimento */
        $sql_acolhimento = "SELECT * FROM acolhimentos_novo WHERE id = {$_GET['acolhimento']} LIMIT 1";
        $stmt_acolhimento = $conn->query($sql_acolhimento);
        $registros_acolhimento = $stmt_acolhimento->num_rows;
        $acolhimento = $stmt_acolhimento->fetch_assoc();

        /* Drogadição */
        if($acolhimento['dependencia_quimica'] != 'a:4:{s:16:"tempo_uso_tabaco";s:0:"";s:16:"tempo_uso_alcool";s:0:"";s:23:"tempo_uso_outras_drogas";s:0:"";s:21:"tempo_uso_droga_abuso";s:0:"";}' && $acolhimento['dependencia_quimica'] != 'a:5:{s:22:"drogas_utilizadas_vida";a:5:{i:0;s:1:"0";i:1;s:1:"1";i:2;s:1:"2";i:3;s:1:"4";i:4;s:1:"5";}s:16:"tempo_uso_tabaco";s:2:"13";s:16:"tempo_uso_alcool";s:2:"14";s:23:"tempo_uso_outras_drogas";s:2:"13";s:21:"tempo_uso_droga_abuso";s:0:"";}'){
            $dq = unserialize($acolhimento['dependencia_quimica']);

            $tempo_uso_tabaco = $dq['tempo_uso_tabaco'] . " anos";
            $tempo_uso_alcool = $dq['tempo_uso_alcool'] . " anos";
            $tempo_uso_outras_drogas = $dq['tempo_uso_outras_drogas'] . " anos";
            $tempo_uso_droga_abuso = $dq['tempo_uso_droga_abuso'] . " anos";

            // DROGAS UTILIADAS NA VIDA
            $drogas_utilizadas_vida = '';

            if(@in_array(0, $dq['drogas_utilizadas_vida'])){
            	$drogas_utilizadas_vida .= 'Tábaco, ';
            }

            if(@in_array(1, $dq['drogas_utilizadas_vida'])){
            	$drogas_utilizadas_vida .= 'Álcool, ';
            }

            if(@in_array(2, $dq['drogas_utilizadas_vida'])){
            	$drogas_utilizadas_vida .= 'Maconha, ';
            }

            if(@in_array(3, $dq['drogas_utilizadas_vida'])){
            	$drogas_utilizadas_vida .= 'Sedativos, ';
            }

            if(@in_array(4, $dq['drogas_utilizadas_vida'])){
            	$drogas_utilizadas_vida .= 'Cocaína, ';
            }

            if(@in_array(5, $dq['drogas_utilizadas_vida'])){
            	$drogas_utilizadas_vida .= 'Crack, ';
            }

            if(@in_array(6, $dq['drogas_utilizadas_vida'])){
            	$drogas_utilizadas_vida .= 'Estimulantes, ';
            }

            if(@in_array(7, $dq['drogas_utilizadas_vida'])){
            	$drogas_utilizadas_vida .= 'Alucinógenos, ';
            }

            if(@in_array(8, $dq['drogas_utilizadas_vida'])){
            	$drogas_utilizadas_vida .= 'Heroína / Opioides, ';
            }

            if(@in_array(9, $dq['drogas_utilizadas_vida'])){
            	$drogas_utilizadas_vida .= 'Solventes / Inalantes, ';
            }

            if(@in_array(10, $dq['drogas_utilizadas_vida'])){
            	$drogas_utilizadas_vida .= 'Outros';
            }
            // DROGAS UTILIZADAS NA VIDA

            // PRINCIPAL DROGA DE ABUSO
            if($dq['droga_de_abuso'] == 1){
            	$droga_de_abuso = 'Álcool';
            }

            if($dq['droga_de_abuso'] == 2){
            	$droga_de_abuso = 'Maconha';
            }

            if($dq['droga_de_abuso'] == 3){
            	$droga_de_abuso = 'Sedativos';
            }

            if($dq['droga_de_abuso'] == 4){
            	$droga_de_abuso = 'Cocaína';
            }

            if($dq['droga_de_abuso'] == 5){
            	$droga_de_abuso = 'Crack';
            }

            if($dq['droga_de_abuso'] == 6){
            	$droga_de_abuso = 'Estimulantes';
            }

            if($dq['droga_de_abuso'] == 7){
            	$droga_de_abuso = 'Alucinógenos';
            }

            if($dq['droga_de_abuso'] == 8){
            	$droga_de_abuso = 'Heroína / Opioides';
            }

            if($dq['droga_de_abuso'] == 9){
            	$droga_de_abuso = 'Solventes / Inalantes';
            }

            if($dq['droga_de_abuso'] == 10){
            	$droga_de_abuso = 'Outros';
            }
            // PRINCIPAL DROGA DE ABUSO
        } else {
            $tempo_uso_tabaco = '';
            $tempo_uso_alcool = '';
            $tempo_uso_outras_drogas = '';
            $tempo_uso_droga_abuso = '';
            $drogas_utilizadas_vida = '';
            $droga_de_abuso = '';
        }
        /* Drogadição */

        /* Saúde */
        if($acolhimento['saude'] != 'a:2:{s:25:"uso_atual_medicacao_geral";s:0:"";s:30:"uso_atual_medicacao_psicoativa";s:0:"";}' && $acolhimento['saude'] != 'a:5:{s:25:"uso_atual_medicacao_geral";s:0:"";s:30:"uso_atual_medicacao_psicoativa";s:0:"";s:19:"incapacidade_fisica";s:1:"0";s:39:"hospitalizado_internado_problemas_saude";s:1:"0";s:18:"desconforto_fisico";s:1:"0";}'){
            $saude = unserialize($acolhimento['saude']);

            $uso_atual_medicacao_geral = $saude['uso_atual_medicacao_geral'];
            $uso_atual_medicacao_psicoativa = $saude['uso_atual_medicacao_psicoativa'];

            // JÁ TEVE OU TEM
            $ja_teve_ou_tem = '';
            if(@in_array(1, $saude['ja_teve_ou_tem'])){
            	$ja_teve_ou_tem .= 'Pressão alta, ';
            }
            if(@in_array(2, $saude['ja_teve_ou_tem'])){
            	$ja_teve_ou_tem .= 'Diabetes, ';
            }
            if(@in_array(3, $saude['ja_teve_ou_tem'])){
            	$ja_teve_ou_tem .= 'Doença cardíaca, ';
            }
            if(@in_array(4, $saude['ja_teve_ou_tem'])){
            	$ja_teve_ou_tem .= 'Derrame / Isquemia (AVC), ';
            }
            if(@in_array(5, $saude['ja_teve_ou_tem'])){
            	$ja_teve_ou_tem .= 'Epilepsia ou convulsões, ';
            }
            if(@in_array(6, $saude['ja_teve_ou_tem'])){
            	$ja_teve_ou_tem .= 'Câncer, ';
            }
            if(@in_array(7, $saude['ja_teve_ou_tem'])){
            	$ja_teve_ou_tem .= 'HIV / AIDS, ';
            }
            if(@in_array(8, $saude['ja_teve_ou_tem'])){
            	$ja_teve_ou_tem .= 'Outras DSTs (';
            	$ja_teve_ou_tem .= $saude['ja_teve_ou_tem']['qual'] . "), ";
            }
            if(@in_array(9, $saude['ja_teve_ou_tem'])){
            	$ja_teve_ou_tem .= 'Tuberculose, ';
            }
            if(@in_array(10, $saude['ja_teve_ou_tem'])){
            	$ja_teve_ou_tem .= 'Hepatite A, ';
            }
            if(@in_array(11, $saude['ja_teve_ou_tem'])){
            	$ja_teve_ou_tem .= 'Hepatite B, ';
            }
            if(@in_array(12, $saude['ja_teve_ou_tem'])){
            	$ja_teve_ou_tem .= 'Hepatite C, ';
            }
            if(@in_array(13, $saude['ja_teve_ou_tem'])){
            	$ja_teve_ou_tem .= 'Cirrose ou outra doença crônica do fígado, ';
            }
            if(@in_array(14, $saude['ja_teve_ou_tem'])){
            	$ja_teve_ou_tem .= 'Doença renal crônica, ';
            }
            if(@in_array(15, $saude['ja_teve_ou_tem'])){
            	$ja_teve_ou_tem .= 'Problema respiratório crónico, ';
            }
            if(@in_array(16, $saude['ja_teve_ou_tem'])){
            	$ja_teve_ou_tem .= 'Outros problemas de saúde (';
            	$ja_teve_ou_tem .= $saude['ja_teve_ou_tem']['qual_2'] . ")";
            }
            // JÁ TEVE OU TEM

            // JÁ TEVE OU TEM 2
            $ja_teve_ou_tem_2 = '';

            if(@in_array(1, $saude['ja_teve_ou_tem_2'])){
            	$ja_teve_ou_tem_2 .= 'Ansiedade, ';
            }
            if(@in_array(2, $saude['ja_teve_ou_tem_2'])){
            	$ja_teve_ou_tem_2 .= 'Depressão, ';
            }
            if(@in_array(3, $saude['ja_teve_ou_tem_2'])){
            	$ja_teve_ou_tem_2 .= 'Esquizofrenia, ';
            }
            if(@in_array(4, $saude['ja_teve_ou_tem_2'])){
            	$ja_teve_ou_tem_2 .= 'Alucinações (sem álcool/drogas), ';
            }
            if(@in_array(5, $saude['ja_teve_ou_tem_2'])){
            	$ja_teve_ou_tem_2 .= 'Ideações suicidas, ';
            }
            if(@in_array(6, $saude['ja_teve_ou_tem_2'])){
            	$ja_teve_ou_tem_2 .= 'Tentativas de suicídio, ';
            }
            if(@in_array(7, $saude['ja_teve_ou_tem_2'])){
            	$ja_teve_ou_tem_2 .= 'Comportamento violento, ';
            }
            if(@in_array(8, $saude['ja_teve_ou_tem_2'])){
            	$ja_teve_ou_tem_2 .= 'Dificuldade de concentração, ';
            }
            if(@in_array(9, $saude['ja_teve_ou_tem_2'])){
            	$ja_teve_ou_tem_2 .= 'Insônia, ';
            }
            if(@in_array(10, $saude['ja_teve_ou_tem_2'])){
            	$ja_teve_ou_tem_2 .= 'Transtornos alimentares, ';
            }
            if(@in_array(11, $saude['ja_teve_ou_tem_2'])){
            	$ja_teve_ou_tem_2 .= 'Transtorno bipolar, ';
            }
            if(@in_array(16, $saude['ja_teve_ou_tem_2'])){
            	$ja_teve_ou_tem_2 .= 'Outros (';
            	$ja_teve_ou_tem_2 .= $saude['ja_teve_ou_tem_2']['qual'] . ")";
            }
            // JÁ TEVE OU TEM
        } else {
            $uso_atual_medicacao_geral = '';
            $uso_atual_medicacao_psicoativa = '';
            $ja_teve_ou_tem = '';
            $ja_teve_ou_tem_2 = '';
        }
        /* Saúde */

        /* Inicação Urgência */
        if($acolhimento['indicacao_urgencia'] != 'N;'){
            $iu = unserialize($acolhimento['indicacao_urgencia']);

            $indicacao_urgencia = '';

            if(@in_array(1, $iu)){
            	$indicacao_urgencia .= 'Encaminhamento médico, ';
            }
            if(@in_array(2, $iu)){
            	$indicacao_urgencia .= 'Encaminhamento odontológico, ';
            }
            if(@in_array(3, $iu)){
            	$indicacao_urgencia .= 'Encaminhamento CAPS, ';
            }
            if(@in_array(4, $iu)){
            	$indicacao_urgencia .= 'Providenciar documentos pessoais, ';
            }
            if(@in_array(5, $iu)){
            	$indicacao_urgencia .= 'Busca ativa familiar, ';
            }
            if(@in_array(6, $iu)){
            	$indicacao_urgencia .= 'Providenciar roupas e higiene pessoal, ';
            }
            if(@in_array(7, $iu)){
            	$indicacao_urgencia .= 'Encaminhar declaração por motivos judiciais, ';
            }
            if(@in_array(8, $iu)){
            	$indicacao_urgencia .= 'Outros (';
            	$indicacao_urgencia .= $iu['qual'] . ")";
            }
        } else {
            $indicacao_urgencia = '';
        }
        /* Inicação Urgência */

        /* Seleciona o acolhido */
        $sql_acolhido = "SELECT * FROM acolhidos WHERE id = {$acolhimento['acolhido']} LIMIT 1";
        $stmt_acolhido = $conn->query($sql_acolhido);
        $registros_acolhido = $stmt_acolhido->num_rows;
        $acolhido = $stmt_acolhido->fetch_assoc();

        /* Seleciona o convênio (se tiver) */
        if($acolhimento['tipo_acolhimento'] == 0){
            $sql_convenio = "SELECT * FROM convenios WHERE id = {$acolhimento['convenio']} LIMIT 1";
            $stmt_convenio = $conn->query($sql_convenio);
            $registros_convenio = $stmt_convenio->num_rows;
            $convenio = $stmt_convenio->fetch_assoc();
        }

        /* Seleciona a foto do acolhido */
        $sql_snapshot = "SELECT * FROM snapshots WHERE acolhido = {$acolhimento['acolhido']} ORDER BY id DESC LIMIT 1";
        $stmt_snapshot = $conn->query($sql_snapshot);
        $registros_snapshot = $stmt_snapshot->num_rows;
        $snapshot = $stmt_snapshot->fetch_assoc();

        // $url_timbre = "http://maequeacolhe.com.br/sistema/public/uploads/config/timbre/" . $config['timbre'];
        $url_timbre = "https://escudodobem.gruub.com.br/public/uploads/config/timbre/" . $config['timbre'];

        /* Se encontrar o acolhimento */
        if ($registros_acolhimento > 0) {

        	$e = '<html>
			<head>
				<meta charset="utf-8">
				<title>FICHA CADASTRAL - ' . strtoupper($acolhido["nome"]) . '</title>

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
  						line-height: 1.66;
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
            if($acolhimento['numero_manual'] != '' AND $acolhimento['numero_manual'] != 0){
			    $e .= "<h1 align='center'>FICHA CADASTRAL #". $acolhimento['numero_manual'] ."</h1> <br />";
            } else {
                $e .= "<h1 align='center'>FICHA CADASTRAL #". $acolhimento['id'] ."</h1> <br />";
            }

			// informações do acolhido
			$e .= "<table width='28%' align='left'>";
			$e .= "<tr bgcolor='#f0f0f0'>";
            // $e .= "<td align='center'><img src='http://maequeacolhe.com.br/sistema/public/uploads/acolhidos/fotos/". $snapshot['imagem'] ."' width='202' /></td>";
			if($snapshot['imagem'] != ''){
                $e .= "<td align='center'><img src='../public/uploads/acolhidos/fotos/". $snapshot['imagem'] ."' width='202' /></td>";
            } else {
                $e .= "<td align='center'><img src='https://via.placeholder.com/200?text=Sem+Imagem' width='205' /></td>";
            }
			$e .= "</tr>";
			$e .= "</table>";

			$e .= "<table width='69%' align='right'>";
			$e .= "<tr bgcolor='#f0f0f0'>";
			$e .= "<td align='center' colspan='2'><strong>Informações do acolhido</strong></td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>Nome</strong></td>";
			$e .= "<td>". $acolhido['nome'] ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>Profissão</strong></td>";
			$e .= "<td>". $acolhimento['profissao'] ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>Nascimento</strong></td>";
			$e .= "<td>". date('d/m/Y', strtotime($acolhido['data_nascimento'])) ." - " . calculaDataAno($acolhido['data_nascimento']) . "</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>RG</strong></td>";
			$e .= "<td>". $acolhido['rg'] ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>CPF</strong></td>";
			$e .= "<td>". $acolhido['cpf'] ."</td>";
			$e .= "</tr>";
			$e .= "</table>";
			// informações do acolhido

			$e .= "<br />";
			$e .= "<br />";
			$e .= "<br />";
			$e .= "<br />";
			$e .= "<br />";
			$e .= "<br />";
			$e .= "<br />";
			$e .= "<br />";
			$e .= "<br />";
			$e .= "<br />";
			$e .= "<br />";
			$e .= "<br />";

			// informações do acolhimento
			$e .= "<table width='100%' align='center'>";
			$e .= "<tr bgcolor='#f0f0f0'>";
			$e .= "<td align='center' colspan='4'><strong>Informações do acolhimento</strong></td>";
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
			$e .= "<td colspan='3'>". $status_acolhimento ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>Data de entrada</strong></td>";
			$e .= "<td>". date('d/m/Y', strtotime($acolhimento['data_inicio'])) ."</td>";

			if($acolhimento['status'] == 11 OR $acolhimento['status'] == 12 OR $acolhimento['status'] == 13 OR $acolhimento['status'] == 14){
				$e .= "<td align='left'><strong>Data de saída</strong></td>";
				$e .= "<td>". date('d/m/Y', strtotime($acolhimento['data_saida'])) ."</td>";
			} else {
				$e .= "<td align='left'><strong>Previsão de saída</strong></td>";
				$e .= "<td>". date('d/m/Y', strtotime($acolhimento['previsao_saida'])) ."</td>";
			}

			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>Tipo de acolhimento</strong></td>";
			if($acolhimento['tipo_acolhimento'] == 0){
				$tipo_acolhimento = $convenio['nome'];
			}

			if($acolhimento['tipo_acolhimento'] == 1){
				$tipo_acolhimento = 'Particular';
			}

			if($acolhimento['tipo_acolhimento'] == 2){
				$tipo_acolhimento = 'Vaga social';
			}
			$e .= "<td colspan='3'>". $tipo_acolhimento ."</td>";
			$e .= "</tr>";
			$e .= "</table>";
			// informações do acolhimento

			$e .= "<br />";

			// filiação
			$e .= "<table width='100%' align='center'>";
			$e .= "<tr bgcolor='#f0f0f0'>";
			$e .= "<td align='center' colspan='4'><strong>Filiação</strong></td>";
			$e .= "</tr>";
			$e .= "<tr>";
			$e .= "<td align='right'><strong>Nome do pai</strong></td>";
			$e .= "<td colspan='3'>". $acolhido['nome_pai'] ."</td>";
			$e .= "</tr>";
			$e .= "<tr>";
			$e .= "<td align='right'><strong>Nascimento</strong></td>";
            if($acolhido['data_nascimento_pai'] != '' and $acolhido['data_nascimento_pai'] != '0000-00-00'){
                $e .= "<td>". date('d/m/Y', strtotime($acolhido['data_nascimento_pai'])) ." - " . calculaDataAno($acolhido['data_nascimento_pai']) ."</td>";
            } else {
                $e .= "<td></td>";
            }
			$e .= "<td align='left'><strong>Profissão</strong></td>";
			$e .= "<td>". $acolhido['profissao_pai'] ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>Nome da mãe</strong></td>";
			$e .= "<td colspan='3'>". $acolhido['nome_mae'] ."</td>";
			$e .= "</tr>";
			$e .= "<tr>";
			$e .= "<td align='right'><strong>Nascimento</strong></td>";
			if($acolhido['data_nascimento_mae'] != '' and $acolhido['data_nascimento_mae'] != '0000-00-00'){
                $e .= "<td>". date('d/m/Y', strtotime($acolhido['data_nascimento_mae'])) ." - " . calculaDataAno($acolhido['data_nascimento_mae']) ."</td>";
            } else {
                $e .= "<td></td>";
            }
			$e .= "<td align='left'><strong>Profissão</strong></td>";
			$e .= "<td>". $acolhido['profissao_mae'] ."</td>";
			$e .= "</tr>";
			$e .= "</table>";
			// filiação

			$e .= "<br />";

			// endereço
			$e .= "<table width='100%' align='center'>";
			$e .= "<tr bgcolor='#f0f0f0'>";
			$e .= "<td align='center' colspan='4'><strong>Localização</strong></td>";
			$e .= "</tr>";
			$e .= "<tr>";
			$e .= "<td align='right'><strong>Endereço</strong></td>";
			if($acolhido['complemento'] != ''){
				$e .= "<td colspan='3'>". $acolhido['endereco'] .", " . $acolhido['num'] . " - " . $acolhido['complemento'] . "</td>";
			} else {
				$e .= "<td colspan='3'>". $acolhido['endereco'] .", " . $acolhido['num'] . "</td>";
			}
			$e .= "</tr>";
			$e .= "<tr>";
			$e .= "<td align='right'><strong>Bairro</strong></td>";
			$e .= "<td>". $acolhido['bairro'] ."</td>";
			$e .= "<td align='left'><strong>Cidade</strong></td>";
			$e .= "<td>". $acolhido['cidade'] ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>UF</strong></td>";
			$e .= "<td>". $acolhido['uf'] ."</td>";
			$e .= "<td align='left'><strong>CEP</strong></td>";
			$e .= "<td>". $acolhido['cep'] ."</td>";
			$e .= "</tr>";
			$e .= "</table>";
			// endereço

			$e .= "<br />";

			// drogadição
			$e .= "<table width='100%' align='center'>";
			$e .= "<tr bgcolor='#f0f0f0'>";
			$e .= "<td align='center' colspan='4'><strong>Drogadição</strong></td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>Drogas utilizadas</strong></td>";
			$e .= "<td colspan='3'>". $drogas_utilizadas_vida ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>Droga de abuso</strong></td>";
			$e .= "<td colspan='3'>". $droga_de_abuso ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>Tempo de uso tabaco</strong></td>";
			$e .= "<td colspan='3'>". $tempo_uso_tabaco ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>Tempo de uso álcool</strong></td>";
			$e .= "<td colspan='3'>". $tempo_uso_alcool ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>Tempo de uso outras drogas</strong></td>";
			$e .= "<td colspan='3'>". $tempo_uso_outras_drogas ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>Tempo de uso droga de abuso</strong></td>";
			$e .= "<td colspan='3'>". $tempo_uso_droga_abuso ."</td>";
			$e .= "</tr>";
			$e .= "</table>";
			// drogadição

			$e .= "<br />";

			// saúde física
			$e .= "<table width='100%' align='center'>";
			$e .= "<tr bgcolor='#f0f0f0'>";
			$e .= "<td align='center' colspan='4'><strong>Saúde física</strong></td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>Uso atual de medicação geral</strong></td>";
			$e .= "<td colspan='3'>". $uso_atual_medicacao_geral ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>Já teve ou tem</strong></td>";
			$e .= "<td colspan='3'>". $ja_teve_ou_tem ."</td>";
			$e .= "</tr>";
			$e .= "</table>";
			// saúde física

			$e .= "<br />";

			// saúde mental
			$e .= "<table width='100%' align='center'>";
			$e .= "<tr bgcolor='#f0f0f0'>";
			$e .= "<td align='center' colspan='4'><strong>Saúde mental</strong></td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>Uso atual de medicação psicoativa</strong></td>";
			$e .= "<td colspan='3'>". $uso_atual_medicacao_psicoativa ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>Já teve ou tem</strong></td>";
			$e .= "<td colspan='3'>". $ja_teve_ou_tem_2 ."</td>";
			$e .= "</tr>";
			$e .= "</table>";
			// saúde mental

			$e .= "<br />";

			// encaminhamentos
			// $e .= "<table width='100%' align='center'>";
			// $e .= "<tr bgcolor='#f0f0f0'>";
			// $e .= "<td align='center' colspan='4'><strong>Encaminhamentos</strong></td>";
			// $e .= "</tr>";

			// $e .= "<tr>";
			// $e .= "<td align='right'><strong>Indicações com urgência</strong></td>";
			// $e .= "<td colspan='3'>". $indicacao_urgencia ."</td>";
			// $e .= "</tr>";
			// $e .= "</table>";
			// encaminhamentos

			$e .= "<br />";
			$e .= "<br />";
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

			$e .= '</body></html>';

        } else {
        	echo "Acolhimento não encontrado!";
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
            "FICHA DE ACOLHIMENTO - " . strtoupper($acolhido["nome"]) . ".pdf", /* Nome do arquivo de saída */
            array(
                "Attachment" => false /* Para download, altere para true */
            )
        );
    }
?>