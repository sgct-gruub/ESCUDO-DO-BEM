<?php
	use Dompdf\Dompdf;
    use Dompdf\Options;
    use Dompdf\Canvas;

    error_reporting(E_ALL); 
    ini_set('display_errors', 1);

	setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

	date_default_timezone_set('America/Sao_Paulo');

	session_start();

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
        if ($registros_acolhimento > 0) {

        	$e = '<html>
			<head>
				<meta charset="utf-8">
				<title>Regulamento de Visitas</title>

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
        $font = 'helvetica';
        $canvas->page_text(25, 810, "Elaboração: Ana Paula Goes", $font, 5.4, array(0,0,0)); //footer
        $canvas->page_text(185, 810, "Elaborado em 10/12/2020 - Revisão nº 01 em 10/12/2020 - Próxima revisão em 10/12/2021", $font, 5.4, array(0,0,0)); //footer
        $canvas->page_text(480, 810, "Aprovação: Marcio Roberto Calbente", $font, 5.4, array(0,0,0)); //footer
        $canvas->page_text(270, 820, "PÁGINA {PAGE_NUM}/{PAGE_COUNT}", $font, 5.4, array(0,0,0)); //footer

        /* Exibe */
        $dompdf->stream(
            "TERMO DE ADESAO AO SERVICO - " . strtoupper($acolhido["nome"]) . ".pdf", /* Nome do arquivo de saída */
            array(
                "Attachment" => false /* Para download, altere para true */
            )
        );
    }
?>