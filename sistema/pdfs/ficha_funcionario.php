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


	if(isset($_GET['funcionario']) && $_GET['funcionario'] != ''){

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

        /* Seleciona o funcionario */
        $sql = "SELECT * FROM funcionarios WHERE id = {$_GET['funcionario']} LIMIT 1";
        $stmt = $conn->query($sql);
        $registros = $stmt->num_rows;
        $funcionario = $stmt->fetch_assoc();

        /* Seleciona os dados de registro do funcionario */
        $sql2 = "SELECT * FROM funcionarios_dados_registro WHERE funcionario = {$_GET['funcionario']} AND status = 1 ORDER BY id DESC LIMIT 1";
        $stmt2 = $conn->query($sql2);
        $registros2 = $stmt2->num_rows;
        $dados_registro = $stmt2->fetch_assoc();

        // $url_timbre = "http://maequeacolhe.com.br/sistema/public/uploads/config/timbre/" . $config['timbre'];
        $url_timbre = "https://escudodobem.gruub.com.br/public/uploads/config/timbre/" . $config['timbre'];

        /* Se encontrar o acolhimento */
        if ($registros > 0) {

        	$e = '<html>
			<head>
				<meta charset="utf-8">
				<title>FICHA DE REGISTRO - ' . strtoupper($funcionario["nome"]) . '</title>

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
  						line-height: 1.66;
					}

					#head{
						background-image: url(' . $url_timbre . ');
						background-repeat: no-repeat;
						height: 150px;
						width: 100%;
						position: fixed;
						top: -150px;
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
					    padding: 2.5px;
					}

					table.no-border, .no-border td, .no-border th {
						border: 0;
					}

					thead:before, thead:after { display: none; }
					tbody:before, tbody:after { display: none; }
				</style>
			</head>

			<body>';

            if($funcionario['estado_civil'] == 1){
                $estado_civil = 'Solteiro';
            }
            if($funcionario['estado_civil'] == 2){
                $estado_civil = 'Casado/União Estável';
            }
            if($funcionario['estado_civil'] == 3){
                $estado_civil = 'Separado/Divorciado';
            }
            if($funcionario['estado_civil'] == 4){
                $estado_civil = 'Amasiado';
            }
            if($funcionario['estado_civil'] == 5){
                $estado_civil = 'Viúvo';
            }
            if($funcionario['estado_civil'] == 6){
                $estado_civil = 'Outros';
            }

            if($funcionario['cor_raca'] == 1){
                $cor = 'Branca';
            }
            if($funcionario['cor_raca'] == 2){
                $cor = 'Preta';
            }
            if($funcionario['cor_raca'] == 3){
                $cor = 'Parda';
            }
            if($funcionario['cor_raca'] == 4){
                $cor = 'Amarela (Asiático, Japonês)';
            }
            if($funcionario['cor_raca'] == 5){
                $cor = 'Indígena';
            }
            if($funcionario['cor_raca'] == 6){
                $cor = 'Outros';
            }

            if($funcionario['deficiente_fisico'] == 1){
                $deficiente_fisico = 'Sim - ' . $funcionario['tipo_deficiencia'];
            } else {
                $deficiente_fisico = 'Não';
            }

            if($funcionario['escolaridade'] == 1){
                $escolaridade = 'Analfabeto';
            }
            if($funcionario['escolaridade'] == 2){
                $escolaridade = 'Ensino fundamental incompleto';
            }
            if($funcionario['escolaridade'] == 3){
                $escolaridade = 'Ensino fundamental completo';
            }
            if($funcionario['escolaridade'] == 4){
                $escolaridade = 'Ensino médio incompleto';
            }
            if($funcionario['escolaridade'] == 5){
                $escolaridade = 'Ensino médio completo';
            }
            if($funcionario['escolaridade'] == 6){
                $escolaridade = 'Ensino superior incompleto';
            }
            if($funcionario['escolaridade'] == 7){
                $escolaridade = 'Ensino superior completo';
            }

			$e .= "<div id='head'></div>";
            $e .= "<h2 align='center'>FICHA PARA REGISTRO DE FUNCIONÁRIOS</h2> <br />";

			// informações do funcionario
			$e .= "<table width='100%'>";
			$e .= "<tr bgcolor='#f0f0f0'>";
			$e .= "<td align='center' colspan='6'><strong>Dados pessoais</strong></td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>Nome</strong></td>";
			$e .= "<td colspan='5'>". $funcionario['nome'] ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='right'><strong>Dt. Nascimento</strong></td>";
			$e .= "<td colspan='2'>". date('d/m/Y', strtotime($funcionario['data_nascimento'])) ." - " . calculaDataAno($funcionario['data_nascimento']) . "</td>";
            $e .= "<td align='right'><strong>Estado civil</strong></td>";
            $e .= "<td colspan='2'>". $estado_civil ."</td>";
			$e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td align='right'><strong>Natural</strong></td>";
            $e .= "<td colspan='3'>". $funcionario['naturalidade'] ."/". $funcionario['uf_naturalidade'] ."</td>";
            $e .= "<td align='right'><strong>País</strong></td>";
            $e .= "<td>". $funcionario['pais'] ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td align='right'><strong>Nome Conjugê</strong></td>";
            $e .= "<td colspan='5'>". $funcionario['nome_conjuge'] ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td align='right'><strong>Endereço</strong></td>";
            $e .= "<td colspan='3'>". $funcionario['endereco'] .", ". $funcionario['num'] ."</td>";
            $e .= "<td align='right'><strong>Complemento</strong></td>";
            $e .= "<td>". $funcionario['complemento'] ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td align='right'><strong>Bairro</strong></td>";
            $e .= "<td colspan='3'>". $funcionario['bairro'] ."</td>";
            $e .= "<td align='right'><strong>Cidade</strong></td>";
            $e .= "<td>". $funcionario['cidade'] ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td align='right'><strong>Estado</strong></td>";
            $e .= "<td colspan='3'>". $funcionario['uf'] ."</td>";
            $e .= "<td align='right'><strong>CEP</strong></td>";
            $e .= "<td>". $funcionario['cep'] ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td align='right'><strong>Telefone</strong></td>";
            $e .= "<td colspan='3'>". $funcionario['telefone'] ."</td>";
            $e .= "<td align='right'><strong>Celular</strong></td>";
            $e .= "<td>". $funcionario['celular'] ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td align='right'><strong>Cabelos</strong></td>";
            $e .= "<td colspan='3'>". $funcionario['cabelos'] ."</td>";
            $e .= "<td align='right'><strong>Olhos</strong></td>";
            $e .= "<td>". $funcionario['olhos'] ."</td>";
            $e .= "</tr>";
            $e .= "<tr>";
            $e .= "<td align='right'><strong>Altura</strong></td>";
            $e .= "<td colspan='3'>". $funcionario['altura'] ."</td>";
            $e .= "<td align='right'><strong>Peso</strong></td>";
            $e .= "<td>". $funcionario['peso'] ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td align='right'><strong>Cor</strong></td>";
            $e .= "<td colspan='3'>". $cor ."</td>";
            $e .= "<td align='right'><strong>Deficiente Físico</strong></td>";
            $e .= "<td>". $deficiente_fisico ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td align='right'><strong>Escolaridade</strong></td>";
            $e .= "<td colspan='5'>". $escolaridade ."</td>";
            $e .= "</tr>";

            $e .= "<tr bgcolor='#f0f0f0'>";
            $e .= "<td align='center' colspan='6'><strong>Documentos</strong></td>";
            $e .= "</tr>";
            $e .= "<tr>";
            $e .= "<td align='right'><strong>RG</strong></td>";
            $e .= "<td>". $funcionario['rg'] ."</td>";
            $e .= "<td align='right'><strong>Dt. Exp.</strong></td>";
            $e .= "<td>". date('d/m/Y', strtotime($funcionario['rg_dt_expedicao'])) ."</td>";
            $e .= "<td align='right'><strong>CPF</strong></td>";
            $e .= "<td>". $funcionario['cpf'] ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td align='right'><strong>PIS</strong></td>";
            $e .= "<td>". $funcionario['pis'] ."</td>";
            $e .= "<td align='right'><strong>Reservista</strong></td>";
            $e .= "<td>". $funcionario['reservista'] ."</td>";
            $e .= "<td align='right'><strong>CNH</strong></td>";
            $e .= "<td>". $funcionario['cnh'] ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td align='right'><strong>Título Eleitor</strong></td>";
            $e .= "<td>". $funcionario['titulo_eleitor'] ."</td>";
            $e .= "<td align='right'><strong>Zona</strong></td>";
            $e .= "<td>". $funcionario['titulo_eleitor_zona'] ."</td>";
            $e .= "<td align='right'><strong>Sessão</strong></td>";
            $e .= "<td>". $funcionario['titulo_eleitor_sessao'] ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td align='right'><strong>CTPS</strong></td>";
            $e .= "<td colspan='3'>". $funcionario['ctps'] ."</td>";
            $e .= "<td align='right'><strong>Série</strong></td>";
            $e .= "<td>". $funcionario['ctps_serie'] ."</td>";
            $e .= "</tr>";

            $filhos = '';
            $quantidadeFilhos = '';
            $nome_filho = '';
            $data_nascimento_filho = '';
            $cpf_filho = '';

            if($funcionario['possui_filhos'] != '' && $funcionario['possui_filhos'] > 0){
                $filhos = unserialize($funcionario['filhos']);
                $quantidadeFilhos = count($filhos['nome']);
            $e .= "<tr bgcolor='#f0f0f0'>";
            $e .= "<td align='center' colspan='6'><strong>Dependentes</strong></td>";
            $e .= "</tr>";
            for($i = 0; $i < $quantidadeFilhos; $i++){
                $nome_filho = $filhos['nome'][$i];
                $data_nascimento_filho = $filhos['data_nascimento'][$i];
                $cpf_filho = $filhos['cpf'][$i];
            $e .= "<tr>";
            $e .= "<td align='right'><strong>Nome</strong></td>";
            $e .= "<td>". $nome_filho ."</td>";
            $e .= "<td align='right'><strong>Nascimento</strong></td>";
            $e .= "<td>". $data_nascimento_filho  ."</td>";
            $e .= "<td align='right'><strong>CPF</strong></td>";
            $e .= "<td>". $cpf_filho  ."</td>";
            $e .= "</tr>";
            }}

            if($dados_registro['tipo_contrato'] == 1){
                $tipo_contrato = 'CLT';
            }
            if($dados_registro['tipo_contrato'] == 2){
                $tipo_contrato = 'Contrato';
            }
            if($dados_registro['tipo_contrato'] == 3){
                $tipo_contrato = 'Voluntário';
            }

            if($dados_registro['vale_transporte'] == 1){
                $vale_transporte = 'Sim';
            } else {
                $vale_transporte = 'Não';
            }

            $e .= "<tr bgcolor='#f0f0f0'>";
            $e .= "<td align='center' colspan='6'><strong>Dados de Registro</strong></td>";
            $e .= "</tr>";
            $e .= "<tr>";
            $e .= "<td align='right'><strong>Unidade</strong></td>";
            $e .= "<td colspan='2'>". $dados_registro['unidade'] ."</td>";
            $e .= "<td align='right'><strong>CNPJ</strong></td>";
            $e .= "<td colspan='2'>". $dados_registro['cnpj'] ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td align='right'><strong>Data de Admissão</strong></td>";
            $e .= "<td colspan='2'>". date('d/m/Y', strtotime($dados_registro['data_admissao'])) ."</td>";
            $e .= "<td align='right'><strong>Exame Admissional</strong></td>";
            $e .= "<td colspan='2'>". date('d/m/Y', strtotime($dados_registro['exame_admissional'])) ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td align='right'><strong>Tipo Contrato</strong></td>";
            $e .= "<td colspan='2'>". $tipo_contrato ."</td>";
            $e .= "<td align='right'><strong>Vale Transporte</strong></td>";
            $e .= "<td colspan='2'>". $vale_transporte ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td align='right'><strong>Salário</strong></td>";
            $e .= "<td colspan='2'>R$". number_format($dados_registro['salario'],2,",",".") ."</td>";
            $e .= "<td align='right'><strong>Horário Trabalho</strong></td>";
            $e .= "<td colspan='2'>". $dados_registro['horario_trabalho'] ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td align='right'><strong>Função</strong></td>";
            $e .= "<td colspan='2'>". $dados_registro['funcao'] ."</td>";
            $e .= "<td align='right'><strong>Descrição da Função</strong></td>";
            $e .= "<td colspan='2'>". $dados_registro['descricao_funcao'] ."</td>";
            $e .= "</tr>";
            $e .= "</table>";
			// informações do funcionario

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
			$e .= "<td align='center'>". $funcionario['nome'] ."<br />". $funcionario['cpf'] ."</td>";
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
        	echo "Funcionário não encontrado!";
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
            "FICHA DE REGISTRO - " . strtoupper($funcionario["nome"]) . ".pdf", /* Nome do arquivo de saída */
            array(
                "Attachment" => false /* Para download, altere para true */
            )
        );
    }
?>