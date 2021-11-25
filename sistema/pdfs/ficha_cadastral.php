<?php
	use Dompdf\Dompdf;
    use Dompdf\Options;
    use Dompdf\Canvas;

    error_reporting(E_ALL); 
    ini_set('display_errors', 1);

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

        /* Seleciona o acolhimento */
        $sql_acolhimento = "SELECT * FROM acolhimentos_novo WHERE id = {$_GET['acolhimento']} LIMIT 1";
        $stmt_acolhimento = $conn->query($sql_acolhimento);
        $registros_acolhimento = $stmt_acolhimento->num_rows;
        $acolhimento = $stmt_acolhimento->fetch_assoc();

        /* Seleciona a unidade */
        $sql_unidade = "SELECT * FROM unidades WHERE id = {$acolhimento['unidade']} LIMIT 1";
        $stmt_unidade = $conn->query($sql_unidade);
        $registros_unidade = $stmt_unidade->num_rows;
        $unidade = $stmt_unidade->fetch_assoc();

        /* Seleciona o quarto */
        $sql_quarto = "SELECT * FROM quartos WHERE id = {$acolhimento['quarto']} LIMIT 1";
        $stmt_quarto = $conn->query($sql_quarto);
        $registros_quarto = $stmt_quarto->num_rows;
        $quarto = $stmt_quarto->fetch_assoc();

        /* Seleciona o acolhido */
        $sql_acolhido = "SELECT * FROM acolhidos WHERE id = {$acolhimento['acolhido']} LIMIT 1";
        $stmt_acolhido = $conn->query($sql_acolhido);
        $registros_acolhido = $stmt_acolhido->num_rows;
        $acolhido = $stmt_acolhido->fetch_assoc();

        /* Seleciona os contatos do acolhido */
        $sql_contato = "SELECT * FROM contatos_acolhidos WHERE acolhido = {$acolhimento['acolhido']} ORDER BY id DESC LIMIT 1";
        $stmt_contato = $conn->query($sql_contato);
        $registros_contato = $stmt_contato->num_rows;
        $contato = $stmt_contato->fetch_assoc();

        if($registros_contato == 0){
        	$contato['nome'] = $acolhido['nome'];
        	$contato['rg'] = $acolhido['rg'];
        	$contato['cpf'] = $acolhido['cpf'];
        	$contato['grau_parentesco'] = 'O mesmo';
        	$contato['telefone'] = '';
        	$contato['celular'] = '';
        	$contato['endereco'] = $acolhido['endereco'];
        	$contato['num'] = $acolhido['num'];
        	$contato['complemento'] = $acolhido['complemento'];
        	$contato['bairro'] = $acolhido['bairro'];
        	$contato['cidade'] = $acolhido['cidade'];
        	$contato['uf'] = $acolhido['uf'];
        	$contato['cep'] = $acolhido['cep'];
        }

        if($acolhido['num'] == 0)
        {
        	$acolhido['num'] = '';
        } else {
        	$acolhido['num'] = ', ' . $acolhido['num'];
        }

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
            
            $e .= "<h1 align='center'>FICHA CADASTRAL - FC</h1> <br />";

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
			$e .= "<td align='right' colspan='2'><strong>Data de entrada</strong></td>";
			$e .= "<td>". date('d/m/Y', strtotime($acolhimento['data_inicio'])) ."</td>";

			if($acolhimento['status'] == 11 OR $acolhimento['status'] == 12 OR $acolhimento['status'] == 13 OR $acolhimento['status'] == 14){
				$e .= "<td align='left' colspan='2'><strong>Data de saída</strong></td>";
				$e .= "<td>". date('d/m/Y', strtotime($acolhimento['data_saida'])) ."</td>";
			} else {
				$e .= "<td align='left' colspan='2'><strong>Previsão de saída</strong></td>";
				$e .= "<td>". date('d/m/Y', strtotime($acolhimento['previsao_saida'])) ."</td>";
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
                $e .= "<td colspan='3'>". $acolhido['endereco'] . " - " . $acolhido['complemento'] . "</td>";
            } else {
                $e .= "<td colspan='3'>". $acolhido['endereco'] . "</td>";
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
			$e .= "<td colspan='2'>". $contato['nome'] ."</td>";
			$e .= "<td align='right'><strong>Parentesco</strong></td>";
			$e .= "<td colspan='2'>". $contato['grau_parentesco'] ."</td>";
			$e .= "</tr>";
			$e .= "<tr>";
			$e .= "<td align='right'><strong>RG</strong></td>";
			$e .= "<td colspan='2'>". $contato['rg'] ."</td>";
			$e .= "<td align='right'><strong>CPF</strong></td>";
			$e .= "<td colspan='2'>". $contato['cpf'] ."</td>";
			$e .= "</tr>";
			$e .= "<tr>";
			$e .= "<td align='right'><strong>Telefone</strong></td>";
			$e .= "<td colspan='2'>". $contato['telefone'] ."</td>";
			$e .= "<td align='right'><strong>Celular</strong></td>";
			$e .= "<td colspan='2'>". $contato['celular'] ."</td>";
			$e .= "</tr>";
			$e .= "<tr>";
            $e .= "<td align='right'><strong>Endereço</strong></td>";
            if($contato['complemento'] != ''){
                $e .= "<td colspan='3'>". $contato['endereco'] .", " . $contato['num'] . " - " . $contato['complemento'] . "</td>";
            } else {
                $e .= "<td colspan='3'>". $contato['endereco'] .", " . $contato['num'] . "</td>";
            }
            $e .= "<td align='right'><strong>CEP</strong></td>";
            $e .= "<td>". $contato['cep'] ."</td>";
            $e .= "</tr>";
            $e .= "<tr>";
            $e .= "<td align='right'><strong>Bairro</strong></td>";
            $e .= "<td>". $contato['bairro'] ."</td>";
            $e .= "<td align='right'><strong>Cidade</strong></td>";
            $e .= "<td>". $contato['cidade'] ."</td>";
            $e .= "<td align='right'><strong>UF</strong></td>";
            $e .= "<td>". $contato['uf'] ."</td>";
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

        // $canvas = $dompdf->get_canvas();
        // $font = 'helvetica';
        // $canvas->page_text(25, 810, "Elaboração: Ana Paula Goes", $font, 5.4, array(0,0,0)); //footer
        // $canvas->page_text(185, 810, "Elaborado em 10/12/2020 - Revisão nº 01 em 10/12/2020 - Próxima revisão em 10/12/2021", $font, 5.4, array(0,0,0)); //footer
        // $canvas->page_text(480, 810, "Aprovação: Marcio Roberto Calbente", $font, 5.4, array(0,0,0)); //footer
        // $canvas->page_text(270, 820, "PÁGINA {PAGE_NUM}/{PAGE_COUNT}", $font, 5.4, array(0,0,0)); //footer

        /* Exibe */
        $dompdf->stream(
            "FICHA DE ACOLHIMENTO - " . strtoupper($acolhido["nome"]) . ".pdf", /* Nome do arquivo de saída */
            array(
                "Attachment" => false /* Para download, altere para true */
            )
        );
    }
?>