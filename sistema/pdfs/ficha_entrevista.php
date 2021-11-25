<?php
    use Dompdf\Dompdf;
    use Dompdf\Options;

    // error_reporting(E_ALL); 
    // ini_set('display_errors', 1);

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

        /* Seleciona a ficha de entrevista */
        $sql_entrevista = "SELECT * FROM ficha_entrevista WHERE acolhimento = {$_GET['acolhimento']} AND status = 1 LIMIT 1";
        $stmt_entrevista = $conn->query($sql_entrevista);
        $registros_entrevista = $stmt_entrevista->num_rows;
        $entrevista = $stmt_entrevista ->fetch_assoc();

        /* Seleciona o usuário que preencheu a ficha */
        $sql_usuario = "SELECT * FROM users WHERE id = {$entrevista['usuario']} LIMIT 1";
        $stmt_usuario = $conn->query($sql_usuario);
        $registros_usuario = $stmt_usuario->num_rows;
        $usuario = $stmt_usuario ->fetch_assoc();

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

        /* cor/raça */
        if($acolhido['cor_raca'] == 1){
        	$acolhido['cor_raca'] = 'Branca';
        }
        if($acolhido['cor_raca'] == 2){
        	$acolhido['cor_raca'] = 'Preta';
        }
        if($acolhido['cor_raca'] == 3){
        	$acolhido['cor_raca'] = 'Parda';
        }
        if($acolhido['cor_raca'] == 4){
        	$acolhido['cor_raca'] = 'Amarela (Asiático, Japonês)';
        }
        if($acolhido['cor_raca'] == 5){
        	$acolhido['cor_raca'] = 'Indígena';
        }
        if($acolhido['cor_raca'] == 6){
        	$acolhido['cor_raca'] = 'Outros';
        }
        /* cor/raça */

        /* religião */
        if($entrevista['religiao'] == 0){
        	$entrevista['religiao'] = 'Não tem';
        }
        if($entrevista['religiao'] == 1){
        	$entrevista['religiao'] = 'Católica';
        }
        if($entrevista['religiao'] == 2){
        	$entrevista['religiao'] = 'Evangélica/Protestante';
        }
        if($entrevista['religiao'] == 3){
        	$entrevista['religiao'] = 'Espírita';
        }
        if($entrevista['religiao'] == 4){
        	$entrevista['religiao'] = 'Judaica';
        }
        if($entrevista['religiao'] == 5){
        	$entrevista['religiao'] = 'Afro-brasileira';
        }
        if($entrevista['religiao'] == 6){
        	$entrevista['religiao'] = 'Orientais/Budismo';
        }
        if($entrevista['religiao'] == 7){
        	$entrevista['religiao'] = 'Outra';
        }
        if($entrevista['religiao'] == 8){
        	$entrevista['religiao'] = 'Prejudicado/Não sabe';
        }
        /* religião */

        /* pratica religião */
        if($entrevista['pratica_religiao'] == 0){
        	$entrevista['pratica_religiao'] = 'Não tenho prática religiosa';
        }
        if($entrevista['pratica_religiao'] == 1){
        	$entrevista['pratica_religiao'] = 'Não frequento, porém oro/rezo ou acredito';
        }
        if($entrevista['pratica_religiao'] == 2){
        	$entrevista['pratica_religiao'] = 'Frequento menos que 1x/mês';
        }
        if($entrevista['pratica_religiao'] == 3){
        	$entrevista['pratica_religiao'] = 'Frequento pelo menos 2x/mês';
        }
        if($entrevista['pratica_religiao'] == 4){
        	$entrevista['pratica_religiao'] = 'Frequento 1x/semana';
        }
        if($entrevista['pratica_religiao'] == 5){
        	$entrevista['pratica_religiao'] = 'Frequento 2x/semana ou mais';
        }
        /* pratica religião */

        /* estado civil */
        if($entrevista['estado_civil'] == 1){
        	$entrevista['estado_civil'] = 'Solteiro';
        }
        if($entrevista['estado_civil'] == 2){
        	$entrevista['estado_civil'] = 'Casado/União Estável';
        }
        if($entrevista['estado_civil'] == 3){
        	$entrevista['estado_civil'] = 'Separado/Divorciado';
        }
        if($entrevista['estado_civil'] == 4){
        	$entrevista['estado_civil'] = 'Amasiado';
        }
        if($entrevista['estado_civil'] == 5){
        	$entrevista['estado_civil'] = 'Viúvo';
        }
        if($entrevista['estado_civil'] == 6){
        	$entrevista['estado_civil'] = 'Outros';
        }
        /* estado civil */

        /* trabalha? */
        if($entrevista['trabalha'] == 0){
        	$entrevista['trabalha'] = 'Não';
        } else {
        	$entrevista['trabalha'] = 'Sim';
        }
        /* trabalha? */

        /* codificação profissão */
        if($entrevista['codificacao_profissao'] == 1){
        	$entrevista['codificacao_profissao'] = 'Especialidades profissionais e ocupações técnicas';
        }
        if($entrevista['codificacao_profissao'] == 2){
        	$entrevista['codificacao_profissao'] = 'Ocupações executivas, administrativas, gerenciais';
        }
        if($entrevista['codificacao_profissao'] == 3){
        	$entrevista['codificacao_profissao'] = 'Vendas';
        }
        if($entrevista['codificacao_profissao'] == 4){
        	$entrevista['codificacao_profissao'] = 'Apoio administrativo e de escritório';
        }
        if($entrevista['codificacao_profissao'] == 5){
        	$entrevista['codificacao_profissao'] = 'Ocupações de produção de precisão, manufatura e conserto';
        }
        if($entrevista['codificacao_profissao'] == 6){
        	$entrevista['codificacao_profissao'] = 'Operadores de máquinas, montadores e inspetores';
        }
        if($entrevista['codificacao_profissao'] == 7){
        	$entrevista['codificacao_profissao'] = 'Ocupações de transporte e mudanças';
        }
        if($entrevista['codificacao_profissao'] == 8){
        	$entrevista['codificacao_profissao'] = 'Serviços gerais, limpeza de equipamentos, auxiliar, operário';
        }
        if($entrevista['codificacao_profissao'] == 9){
        	$entrevista['codificacao_profissao'] = 'Ocupações de serviços, exceto empregados domésticos';
        }
        if($entrevista['codificacao_profissao'] == 10){
        	$entrevista['codificacao_profissao'] = 'Fazendeiro ou gerente / Administrador de fazenda';
        }
        if($entrevista['codificacao_profissao'] == 11){
        	$entrevista['codificacao_profissao'] = 'Trabalhador rural';
        }
        if($entrevista['codificacao_profissao'] == 12){
        	$entrevista['codificacao_profissao'] = 'Militar';
        }
        if($entrevista['codificacao_profissao'] == 13){
        	$entrevista['codificacao_profissao'] = 'Empregados domésticos';
        }
        if($entrevista['codificacao_profissao'] == 14){
        	$entrevista['codificacao_profissao'] = 'Outro';
        }
        /* codificação profissão */

        /* estuda? */
        if($entrevista['estuda'] == 0){
        	$entrevista['estuda'] = 'Não';
        } else {
        	$entrevista['estuda'] = 'Sim';
        }
        /* estuda? */

        /* escolaridade */
        if($entrevista['escolaridade'] == 1){
        	$entrevista['escolaridade'] = 'Analfabeto';
        }
        if($entrevista['escolaridade'] == 2){
        	$entrevista['escolaridade'] = 'Ensino fundamental incompleto';
        }
        if($entrevista['escolaridade'] == 3){
        	$entrevista['escolaridade'] = 'Ensino fundamental completo';
        }
        if($entrevista['escolaridade'] == 4){
        	$entrevista['escolaridade'] = 'Ensino médio incompleto';
        }
        if($entrevista['escolaridade'] == 5){
        	$entrevista['escolaridade'] = 'Ensino médio completo';
        }
        if($entrevista['escolaridade'] == 6){
        	$entrevista['escolaridade'] = 'Ensino superior incompleto';
        }
        if($entrevista['escolaridade'] == 7){
        	$entrevista['escolaridade'] = 'Ensino superior completo';
        }
        /* escolaridade */

        $estrato_social = unserialize($entrevista['estrato_social']);

        /* escolaridade chefe da familia */
        if($estrato_social['chefe_familia'] == 1){
        	$estrato_social['chefe_familia'] = 'Analfabeto / E.F. I incompleto';
        }
        if($estrato_social['chefe_familia'] == 2){
        	$estrato_social['chefe_familia'] = 'E.F. I completo / E.F. II incompleto';
        }
        if($estrato_social['chefe_familia'] == 3){
        	$estrato_social['chefe_familia'] = 'E.F. II completo / E.M. incompleto';
        }
        if($estrato_social['chefe_familia'] == 4){
        	$estrato_social['chefe_familia'] = 'E.M. completo / E.S. incompleto';
        }
        if($estrato_social['chefe_familia'] == 5){
        	$estrato_social['chefe_familia'] = 'E.S. completo';
        }
        /* escolaridade chefe da familia */

        /* banheiros */
        if($estrato_social['banheiros'] == 0){
        	$banheiros = 0;
        }
        if($estrato_social['banheiros'] == 3){
        	$banheiros = 1;
        }
        if($estrato_social['banheiros'] == 7){
        	$banheiros = 2;
        }
        if($estrato_social['banheiros'] == 10){
        	$banheiros = 3;
        }
        if($estrato_social['banheiros'] == 14){
        	$banheiros = '4 ou +';
        }
        /* banheiros */

        /* empregados */
        if($estrato_social['empregados'] == 0){
        	$empregados = 0;
        }
        if($estrato_social['empregados'] == 3){
        	$empregados = 1;
        }
        if($estrato_social['empregados'] == 7){
        	$empregados = 2;
        }
        if($estrato_social['empregados'] == 10){
        	$empregados = 3;
        }
        if($estrato_social['empregados'] == 13){
        	$empregados = '4 ou +';
        }
        /* empregados */

        /* automóveis */
        if($estrato_social['automoveis'] == 0){
        	$automoveis = 0;
        }
        if($estrato_social['automoveis'] == 3){
        	$automoveis = 1;
        }
        if($estrato_social['automoveis'] == 5){
        	$automoveis = 2;
        }
        if($estrato_social['automoveis'] == 8){
        	$automoveis = 3;
        }
        if($estrato_social['automoveis'] == 11){
        	$automoveis = '4 ou +';
        }
        /* automóveis */

        /* microcomputador */
        if($estrato_social['microcomputador'] == 0){
        	$microcomputador = 0;
        }
        if($estrato_social['microcomputador'] == 3){
        	$microcomputador = 1;
        }
        if($estrato_social['microcomputador'] == 6){
        	$microcomputador = 2;
        }
        if($estrato_social['microcomputador'] == 8){
        	$microcomputador = 3;
        }
        if($estrato_social['microcomputador'] == 11){
        	$microcomputador = '4 ou +';
        }
        /* microcomputador */

        /* lava louça */
        if($estrato_social['lava_louca'] == 0){
        	$lava_louca = 0;
        }
        if($estrato_social['lava_louca'] == 3){
        	$lava_louca = 1;
        }
        if($estrato_social['lava_louca'] == 6){
        	$lava_louca = 2;
        }
        if($estrato_social['lava_louca'] == 61){
        	$lava_louca = 3;
        }
        if($estrato_social['lava_louca'] == 62){
        	$lava_louca = '4 ou +';
        }
        /* lava louça */

        /* geladeira */
        if($estrato_social['geladeira'] == 0){
        	$geladeira = 0;
        }
        if($estrato_social['geladeira'] == 2){
        	$geladeira = 1;
        }
        if($estrato_social['geladeira'] == 3){
        	$geladeira = 2;
        }
        if($estrato_social['geladeira'] == 5){
        	$geladeira = 3;
        }
        if($estrato_social['geladeira'] == 51){
        	$geladeira = '4 ou +';
        }
        /* geladeira */

        /* freezer */
        if($estrato_social['freezer'] == 0){
        	$freezer = 0;
        }
        if($estrato_social['freezer'] == 2){
        	$freezer = 1;
        }
        if($estrato_social['freezer'] == 4){
        	$freezer = 2;
        }
        if($estrato_social['freezer'] == 6){
        	$freezer = 3;
        }
        if($estrato_social['freezer'] == 61){
        	$freezer = '4 ou +';
        }
        /* freezer */

        /* lava roupas */
        if($estrato_social['lava_roupas'] == 0){
        	$lava_roupas = 0;
        }
        if($estrato_social['lava_roupas'] == 2){
        	$lava_roupas = 1;
        }
        if($estrato_social['lava_roupas'] == 4){
        	$lava_roupas = 2;
        }
        if($estrato_social['lava_roupas'] == 6){
        	$lava_roupas = 3;
        }
        if($estrato_social['lava_roupas'] == 61){
        	$lava_roupas = '4 ou +';
        }
        /* lava roupas */

        /* dvd */
        if($estrato_social['dvd'] == 0){
        	$dvd = 0;
        }
        if($estrato_social['dvd'] == 1){
        	$dvd = 1;
        }
        if($estrato_social['dvd'] == 3){
        	$dvd = 2;
        }
        if($estrato_social['dvd'] == 4){
        	$dvd = 3;
        }
        if($estrato_social['dvd'] == 6){
        	$dvd = '4 ou +';
        }
        /* dvd */

        /* microondas */
        if($estrato_social['microondas'] == 0){
        	$microondas = 0;
        }
        if($estrato_social['microondas'] == 2){
        	$microondas = 1;
        }
        if($estrato_social['microondas'] == 4){
        	$microondas = 2;
        }
        if($estrato_social['microondas'] == 41){
        	$microondas = 3;
        }
        if($estrato_social['microondas'] == 42){
        	$microondas = '4 ou +';
        }
        /* microondas */

        /* motocicleta */
        if($estrato_social['motocicleta'] == 0){
        	$motocicleta = 0;
        }
        if($estrato_social['motocicleta'] == 1){
        	$motocicleta = 1;
        }
        if($estrato_social['motocicleta'] == 3){
        	$motocicleta = 2;
        }
        if($estrato_social['motocicleta'] == 31){
        	$motocicleta = 3;
        }
        if($estrato_social['motocicleta'] == 32){
        	$motocicleta = '4 ou +';
        }
        /* motocicleta */

        /* secador de roupas */
        if($estrato_social['secador_de_roupas'] == 0){
        	$secador_de_roupas = 0;
        }
        if($estrato_social['secador_de_roupas'] == 1){
        	$secador_de_roupas = 1;
        }
        if($estrato_social['secador_de_roupas'] == 2){
        	$secador_de_roupas = 2;
        }
        if($estrato_social['secador_de_roupas'] == 21){
        	$secador_de_roupas = 3;
        }
        if($estrato_social['secador_de_roupas'] == 22){
        	$secador_de_roupas = '4 ou +';
        }
        /* secador de roupas */

        /* agua encanada */
        if($estrato_social['agua_encanada'] == 0){
        	$agua_encanada = 'Não';
        }
        if($estrato_social['agua_encanada'] == 4){
        	$agua_encanada = 'Sim';
        }
        /* agua encanada */

        /* rua pavimentada */
        if($estrato_social['rua_pavimentada'] == 0){
        	$rua_pavimentada = 'Não';
        }
        if($estrato_social['rua_pavimentada'] == 2){
        	$rua_pavimentada = 'Sim';
        }
        /* rua pavimentada */

        /* Drogadição */
        if($entrevista['dependencia_quimica'] != 'a:4:{s:16:"tempo_uso_tabaco";s:0:"";s:16:"tempo_uso_alcool";s:0:"";s:23:"tempo_uso_outras_drogas";s:0:"";s:21:"tempo_uso_droga_abuso";s:0:"";}' && $entrevista['dependencia_quimica'] != 'a:5:{s:22:"drogas_utilizadas_vida";a:5:{i:0;s:1:"0";i:1;s:1:"1";i:2;s:1:"2";i:3;s:1:"4";i:4;s:1:"5";}s:16:"tempo_uso_tabaco";s:2:"13";s:16:"tempo_uso_alcool";s:2:"14";s:23:"tempo_uso_outras_drogas";s:2:"13";s:21:"tempo_uso_droga_abuso";s:0:"";}'){
            $dq = unserialize($entrevista['dependencia_quimica']);

            if($dq['tempo_uso_tabaco'] != ''){
                $tempo_uso_tabaco = $dq['tempo_uso_tabaco'] . " anos";
            } else {
                $tempo_uso_tabaco = '-';
            }
            if($dq['tempo_uso_alcool'] != ''){
                $tempo_uso_alcool = $dq['tempo_uso_alcool'] . " anos";
            } else {
                $tempo_uso_alcool = '-';
            }
            if($dq['tempo_uso_outras_drogas'] != ''){
                $tempo_uso_outras_drogas = $dq['tempo_uso_outras_drogas'] . " anos";
            } else {
                $tempo_uso_outras_drogas = '-';
            }
            if($dq['tempo_uso_outras_drogas'] != ''){
                $tempo_uso_droga_abuso = $dq['tempo_uso_droga_abuso'] . " anos";
            } else {
                $tempo_uso_droga_abuso = '-';
            }

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


            if(@in_array(0, $dq['dependentes_quimicos_na_familia'])){
                $dependentes_quimicos_na_familia = 'Não';
            }
            if(@in_array(1, $dq['dependentes_quimicos_na_familia'])){
                $dependentes_quimicos_na_familia = 'Sim';
            }


            if(@in_array(0, $dq['internacoes_anteriores'])){
                $internacoes_anteriores = 'Não';
            }
            if(@in_array(1, $dq['internacoes_anteriores'])){
                $internacoes_anteriores = 'Sim';
            }


            if(@in_array(0, $dq['ja_foi_detido'])){
                $ja_foi_detido = 'Não';
            }
            if(@in_array(1, $dq['ja_foi_detido'])){
                $ja_foi_detido = 'Sim';
            }


            if(@in_array(0, $dq['ja_foi_preso'])){
                $ja_foi_preso = 'Não';
            }
            if(@in_array(1, $dq['ja_foi_preso'])){
                $ja_foi_preso = 'Sim';
            }


            if(@in_array(0, $dq['processos_em_andamento'])){
                $processos_em_andamento = 'Não';
            }
            if(@in_array(1, $dq['processos_em_andamento'])){
                $processos_em_andamento = 'Sim';
            }

            if(!isset($dq['processos_anteriores'])){
                $processos_anteriores = '';
            }

            if(@in_array(0, $dq['processos_anteriores'])){
                $processos_anteriores = 'Não';
            }
            if(@in_array(1, $dq['processos_anteriores'])){
                $processos_anteriores = 'Sim';
            }


            if(!isset($dq['morou_na_rua'])){
                $morou_na_rua = '';
            }
            if(@in_array(0, $dq['morou_na_rua'])){
                $morou_na_rua = 'Não';
            }
            if(@in_array(1, $dq['morou_na_rua'])){
                $morou_na_rua = 'Sim';
            }

            if(@in_array(0, $dq['tempo_morou_rua'])){
                $tempo_morou_rua = '';
            }
            if(@in_array(1, $dq['tempo_morou_rua'])){
                $tempo_morou_rua = 'Até 1 ano';
            }
            if(@in_array(2, $dq['tempo_morou_rua'])){
                $tempo_morou_rua = '1-3 anos';
            }
            if(@in_array(3, $dq['tempo_morou_rua'])){
                $tempo_morou_rua = '3-10 anos';
            }
            if(@in_array(4, $dq['tempo_morou_rua'])){
                $tempo_morou_rua = '+10 anos';
            }


            if(!isset($dq['problemas_laborais'])){
                $problemas_laborais = '';
            }
            if(@in_array(0, $dq['problemas_laborais'])){
                $problemas_laborais = 'Não';
            }
            if(@in_array(1, $dq['problemas_laborais'])){
                $problemas_laborais = 'Sim';
            }

            if(@in_array(0, $dq['problemas_laborais_quantas_vx'])){
                $problemas_laborais_quantas_vx = '';
            }
            if(@in_array(1, $dq['problemas_laborais_quantas_vx'])){
                $problemas_laborais_quantas_vx = '1 vez';
            }
            if(@in_array(2, $dq['problemas_laborais_quantas_vx'])){
                $problemas_laborais_quantas_vx = '2-3 vezes';
            }
            if(@in_array(3, $dq['problemas_laborais_quantas_vx'])){
                $problemas_laborais_quantas_vx = '4-10 vezes';
            }
            if(@in_array(4, $dq['problemas_laborais_quantas_vx'])){
                $problemas_laborais_quantas_vx = '+10 vezes';
            }


            if(@in_array(0, $dq['desemprego_vx_drogas'])){
                $desemprego_vx_drogas = 'Não';
            }
            if(@in_array(1, $dq['desemprego_vx_drogas'])){
                $desemprego_vx_drogas = 'Sim';
            }

            if(@in_array(0, $dq['desemprego_vx_drogas_qto'])){
                $desemprego_vx_drogas_qto = '';
            }
            if(@in_array(1, $dq['desemprego_vx_drogas_qto'])){
                $desemprego_vx_drogas_qto = 'Até 1 ano';
            }
            if(@in_array(2, $dq['desemprego_vx_drogas_qto'])){
                $desemprego_vx_drogas_qto = '1-3 anos';
            }
            if(@in_array(3, $dq['desemprego_vx_drogas_qto'])){
                $desemprego_vx_drogas_qto = '3-10 anos';
            }
            if(@in_array(4, $dq['desemprego_vx_drogas_qto'])){
                $desemprego_vx_drogas_qto = '+10 anos';
            }


            if(!isset($dq['familiares_vx_drogas'])){
                $familiares_vx_drogas = '';
            }

            if(@in_array(0, $dq['familiares_vx_drogas'])){
                $familiares_vx_drogas = 'Não';
            }
            if(@in_array(1, $dq['familiares_vx_drogas'])){
                $familiares_vx_drogas = 'Sim';
            }

            if(@in_array(0, $dq['familiares_vx_drogas_qto'])){
                $familiares_vx_drogas_qto = '';
            }
            if(@in_array(1, $dq['familiares_vx_drogas_qto'])){
                $familiares_vx_drogas_qto = '1 vez';
            }
            if(@in_array(2, $dq['familiares_vx_drogas_qto'])){
                $familiares_vx_drogas_qto = '2-3 vezes';
            }
            if(@in_array(3, $dq['familiares_vx_drogas_qto'])){
                $familiares_vx_drogas_qto = '4-10 vezes';
            }
            if(@in_array(4, $dq['familiares_vx_drogas_qto'])){
                $familiares_vx_drogas_qto = '+10 vezes';
            }


            if(@in_array(0, $dq['saude_vx_drogas'])){
                $saude_vx_drogas = 'Não';
            }
            if(@in_array(1, $dq['saude_vx_drogas'])){
                $saude_vx_drogas = 'Sim';
            }

            if(@in_array(0, $dq['saude_vx_drogas_qto'])){
                $saude_vx_drogas_qto = '';
            }
            if(@in_array(1, $dq['saude_vx_drogas_qto'])){
                $saude_vx_drogas_qto = '1 vez';
            }
            if(@in_array(2, $dq['saude_vx_drogas_qto'])){
                $saude_vx_drogas_qto = '2-3 vezes';
            }
            if(@in_array(3, $dq['saude_vx_drogas_qto'])){
                $saude_vx_drogas_qto = '4-10 vezes';
            }
            if(@in_array(4, $dq['saude_vx_drogas_qto'])){
                $saude_vx_drogas_qto = '+10 vezes';
            }


            if(@in_array(0, $dq['internacoes_vx_drogas'])){
                $internacoes_vx_drogas = 'Não';
            }
            if(@in_array(1, $dq['internacoes_vx_drogas'])){
                $internacoes_vx_drogas = 'Sim';
            }

            if(@in_array(0, $dq['internacoes_vx_drogas_qto'])){
                $internacoes_vx_drogas_qto = '';
            }
            if(@in_array(1, $dq['internacoes_vx_drogas_qto'])){
                $internacoes_vx_drogas_qto = '1 vez';
            }
            if(@in_array(2, $dq['internacoes_vx_drogas_qto'])){
                $internacoes_vx_drogas_qto = '2-3 vezes';
            }
            if(@in_array(3, $dq['internacoes_vx_drogas_qto'])){
                $internacoes_vx_drogas_qto = '4-10 vezes';
            }
            if(@in_array(4, $dq['internacoes_vx_drogas_qto'])){
                $internacoes_vx_drogas_qto = '+10 vezes';
            }
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
        if($entrevista['saude'] != 'a:2:{s:25:"uso_atual_medicacao_geral";s:0:"";s:30:"uso_atual_medicacao_psicoativa";s:0:"";}' && $entrevista['saude'] != 'a:5:{s:25:"uso_atual_medicacao_geral";s:0:"";s:30:"uso_atual_medicacao_psicoativa";s:0:"";s:19:"incapacidade_fisica";s:1:"0";s:39:"hospitalizado_internado_problemas_saude";s:1:"0";s:18:"desconforto_fisico";s:1:"0";}'){
            $saude = unserialize($entrevista['saude']);

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

            if(@in_array(0, $saude['incapacidade_fisica'])){
                $incapacidade_fisica = 'Não';
            }
            if(@in_array(1, $saude['incapacidade_fisica'])){
                $incapacidade_fisica = 'Sim';
            }


            if(@in_array(0, $saude['hospitalizado'])){
                $hospitalizado = 'Não';
            }
            if(@in_array(1, $saude['hospitalizado'])){
                $hospitalizado = 'Sim';
            }

            if(@in_array(0, $saude['hospitalizado_qtas_vx'])){
                $hospitalizado_qtas_vx = '';
            }
            if(@in_array(1, $saude['hospitalizado_qtas_vx'])){
                $hospitalizado_qtas_vx = '1 vez';
            }
            if(@in_array(2, $saude['hospitalizado_qtas_vx'])){
                $hospitalizado_qtas_vx = '2-3 vezes';
            }
            if(@in_array(3, $saude['hospitalizado_qtas_vx'])){
                $hospitalizado_qtas_vx = '4-10 vezes';
            }
            if(@in_array(4, $saude['hospitalizado_qtas_vx'])){
                $hospitalizado_qtas_vx = '+10 vezes';
            }

            if(@in_array(0, $saude['tempo_hospitalizacao'])){
                $tempo_hospitalizacao = '';
            }
            if(@in_array(1, $saude['tempo_hospitalizacao'])){
                $tempo_hospitalizacao = 'Até 1 ano';
            }
            if(@in_array(2, $saude['tempo_hospitalizacao'])){
                $tempo_hospitalizacao = '1-3 anos';
            }
            if(@in_array(3, $saude['tempo_hospitalizacao'])){
                $tempo_hospitalizacao = '3-10 anos';
            }
            if(@in_array(4, $saude['tempo_hospitalizacao'])){
                $tempo_hospitalizacao = '+10 anos';
            }


            if(@in_array(0, $saude['desconforto_fisico'])){
                $desconforto_fisico = 'Não';
            }
            if(@in_array(1, $saude['desconforto_fisico'])){
                $desconforto_fisico = 'Sim';
            }
        } else {
            $uso_atual_medicacao_geral = '';
            $uso_atual_medicacao_psicoativa = '';
            $ja_teve_ou_tem = '';
            $ja_teve_ou_tem_2 = '';
        }
        /* Saúde */


        /* Se encontrar o acolhimento */
        if ($registros_acolhimento > 0) {

        	$e = '<html>
			<head>
				<meta charset="utf-8">
				<title>FICHA DE ENTREVISTA - ' . strtoupper($acolhido["nome"]) . '</title>

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
						font-size: 10px;
  						line-height: 1.66;
					}

					#head{
						background-image: url('.$url_timbre.');
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
			$e .= "<h1 align='center' style=''>FICHA DE ENTREVISTA</h1> <br />";

			// dados sociodemográficos
			$e .= "<table width='100%'>";
			$e .= "<tr bgcolor='#f0f0f0'>";
			$e .= "<td align='center' colspan='4'><strong>Dados Sociodemográficos</strong></td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td><strong>Data da entrevista</strong></td>";
			$e .= "<td>". date('d/m/Y', strtotime($entrevista['data'])) ."</td>";

			$e .= "<td><strong>Entrevistador</strong></td>";
			$e .= "<td>". $usuario['name'] ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td><strong>Nome Completo</strong></td>";
			$e .= "<td>". $acolhido['nome'] ."</td>";

			$e .= "<td><strong>Nascimento</strong></td>";
			$e .= "<td>". date('d/m/Y', strtotime($acolhido['data_nascimento'])) ." - " . calculaDataAno($acolhido['data_nascimento']) . "</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td><strong>RG</strong></td>";
			$e .= "<td>". $acolhido['rg'] ."</td>";

			$e .= "<td><strong>CPF</strong></td>";
			$e .= "<td>". $acolhido['cpf'] ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td><strong>Naturalidade</strong></td>";
			$e .= "<td>". $entrevista['naturalidade'] ."</td>";

			$e .= "<td><strong>UF</strong></td>";
			$e .= "<td>". $entrevista['uf_naturalidade'] ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td><strong>Cidade de encaminhamento</strong></td>";
			$e .= "<td>". $entrevista['cidade_encaminhamento'] ."</td>";

			$e .= "<td><strong>UF</strong></td>";
			$e .= "<td>". $entrevista['uf_encaminhamento'] ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td><strong>Cor/Raça</strong></td>";
			$e .= "<td>". $acolhido['cor_raca'] ."</td>";

			$e .= "<td><strong>Religião</strong></td>";
			$e .= "<td>". $entrevista['religiao'] ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td><strong>Pratica a sua religião?</strong></td>";
			$e .= "<td>". $entrevista['pratica_religiao'] ."</td>";

			$e .= "<td><strong>Estado Civil</strong></td>";
			$e .= "<td>". $entrevista['estado_civil'] ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td><strong>Trabalha?</strong></td>";
			$e .= "<td>". $entrevista['trabalha'] ."</td>";

			$e .= "<td><strong>Profissão</strong></td>";
			$e .= "<td>". $entrevista['profissao'] ."</td>";
			$e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td><strong>Estuda?</strong></td>";
            $e .= "<td>". $entrevista['estuda'] ."</td>";

            $e .= "<td><strong>Escolaridade</strong></td>";
            $e .= "<td>". $entrevista['escolaridade'] ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td><strong>Codificação da Profissão</strong></td>";
            $e .= "<td colspan='3'>". $entrevista['codificacao_profissao'] ."</td>";
            $e .= "</tr>";
			$e .= "</table>";
			// dados sociodemográficos
			
			$e .= "<br />";

			// estrato social
			$e .= "<table width='100%'>";
			$e .= "<tr bgcolor='#f0f0f0'>";
			$e .= "<td align='center' colspan='6'><strong>Estrato Social</strong></td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td><strong>Chefe da Família</strong></td>";
			$e .= "<td colspan='2'>". $estrato_social['nome_chefe_familia'] ."</td>";

			$e .= "<td><strong>Grau de Instrução</strong></td>";
			$e .= "<td colspan='2'>". $estrato_social['chefe_familia'] ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='center'><strong>Banheiros</strong></td>";
			$e .= "<td align='center'><strong>Empregados</strong></td>";
			$e .= "<td align='center'><strong>Automóveis</strong></td>";
			$e .= "<td align='center'><strong>Microcomputador</strong></td>";
			$e .= "<td align='center'><strong>Lava-louça</strong></td>";
			$e .= "<td align='center'><strong>Geladeira</strong></td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='center'>". $banheiros ."</td>";
			$e .= "<td align='center'>". $empregados ."</td>";
			$e .= "<td align='center'>". $automoveis ."</td>";
			$e .= "<td align='center'>". $microcomputador ."</td>";
			$e .= "<td align='center'>". $lava_louca ."</td>";			
			$e .= "<td align='center'>". $geladeira ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='center'><strong>Freezer</strong></td>";
			$e .= "<td align='center'><strong>Lava-roupas</strong></td>";
			$e .= "<td align='center'><strong>DVD</strong></td>";
			$e .= "<td align='center'><strong>Microondas</strong></td>";
			$e .= "<td align='center'><strong>Motocicleta</strong></td>";
			$e .= "<td align='center'><strong>Secador de roupas</strong></td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td align='center'>". $freezer ."</td>";
			$e .= "<td align='center'>". $lava_roupas ."</td>";
			$e .= "<td align='center'>". $dvd ."</td>";
			$e .= "<td align='center'>". $microondas ."</td>";
			$e .= "<td align='center'>". $motocicleta ."</td>";
			$e .= "<td align='center'>". $secador_de_roupas ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td><strong>Água encanada?</strong></td>";
			$e .= "<td>". $agua_encanada ."</td>";
			$e .= "<td><strong>Rua pavimentada?</strong></td>";
			$e .= "<td>". $rua_pavimentada ."</td>";
			$e .= "<td><strong>Resultado</strong></td>";
			$e .= "<td>". $entrevista['resultado_estrato_social'] ."</td>";
			$e .= "</tr>";
			$e .= "</table>";
			// estrato social

			$e .= "<br />";

			// dq
			$e .= "<table width='100%' align='center'>";
			$e .= "<tr bgcolor='#f0f0f0'>";
			$e .= "<td align='center' colspan='4'><strong>Dependência Química</strong></td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td><strong>Drogas utilizadas</strong></td>";
			$e .= "<td>". $drogas_utilizadas_vida ."</td>";
			$e .= "<td><strong>Droga de abuso</strong></td>";
			$e .= "<td>". $droga_de_abuso ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td><strong>Tempo de uso tabaco</strong></td>";
			$e .= "<td>". $tempo_uso_tabaco ."</td>";
			$e .= "<td><strong>Tempo de uso álcool</strong></td>";
			$e .= "<td>". $tempo_uso_alcool ."</td>";
			$e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td><strong>Tempo de uso outras drogas</strong></td>";
            $e .= "<td>". $tempo_uso_outras_drogas ."</td>";
            $e .= "<td><strong>Tempo de uso droga de abuso</strong></td>";
            $e .= "<td>". $tempo_uso_droga_abuso ."</td>";
            $e .= "</tr>";      

            $e .= "<tr>";
            $e .= "<td><strong>Há (ou já houve) dependentes químicos na familia?</strong></td>";
            $e .= "<td>". $dependentes_quimicos_na_familia ."</td>";
            $e .= "<td><strong>Quem?</strong></td>";
            $e .= "<td>". $dq['dependentes_quimicos_na_familia_quem'] ."</td>";
            $e .= "</tr>";  

            $e .= "<tr>";
            $e .= "<td><strong>Já teve internações anteriores?</strong></td>";
            if($internacoes_anteriores == 1){
                $e .= "<td>". $internacoes_anteriores ." (". $dq['quantas_vezes_internacoes_anteriores'] ."x)</td>";
            } else {
                $e .= "<td>". $internacoes_anteriores ."</td>";
            }
            $e .= "<td><strong>Onde, quando e quanto tempo?</strong></td>";
            $e .= "<td>". $dq['onde_quando_quanto_tempo_internacoes'] ."</td>";
            $e .= "</tr>";    

            $e .= "<tr>";
            $e .= "<td><strong>Já foi detido?</strong></td>";
            if($ja_foi_detido == 1){
                $e .= "<td>". $ja_foi_detido ." (". $dq['quantas_vezes_detido'] ."x)</td>";
            } else {
                $e .= "<td>". $ja_foi_detido ."</td>";
            }
            $e .= "<td><strong>Por que?</strong></td>";
            $e .= "<td>". $dq['porque_detido'] ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td><strong>Já foi preso?</strong></td>";
            if($ja_foi_preso == 1){
                $e .= "<td>". $ja_foi_preso ." (". $dq['quantas_vezes_preso'] ."x)</td>";
            } else {
                $e .= "<td>". $ja_foi_preso ."</td>";
            }
            $e .= "<td><strong>Por que?</strong></td>";
            $e .= "<td>". $dq['porque_preso'] ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td><strong>Processos em andamento?</strong></td>";
            $e .= "<td>". $processos_em_andamento ."</td>";
            $e .= "<td><strong>Por que?</strong></td>";
            $e .= "<td>". $dq['processo_em_andamento_pq'] ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td><strong>Processos anteriores?</strong></td>";
            $e .= "<td>". $processos_anteriores ."</td>";
            $e .= "<td><strong>Por que?</strong></td>";
            $e .= "<td>". $dq['processos_anteriores_pq'] ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td><strong>Já se encontrou em situação de rua?</strong></td>";
            $e .= "<td>". $morou_na_rua ."</td>";
            $e .= "<td><strong>Por quanto tempo?</strong></td>";
            $e .= "<td>". $tempo_morou_rua ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td><strong>Já teve problemas laborais por causa da sua depêndencia?</strong></td>";
            $e .= "<td>". $problemas_laborais ."</td>";
            $e .= "<td><strong>Quantas vezes?</strong></td>";
            $e .= "<td>". $problemas_laborais_quantas_vx ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td><strong>No momento está desempregado por causa da sua dependencia?</strong></td>";
            $e .= "<td>". $desemprego_vx_drogas ."</td>";
            $e .= "<td><strong>Há quanto tempo?</strong></td>";
            $e .= "<td>". $desemprego_vx_drogas_qto ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td><strong>Já teve problemas familiares por causa da sua dependência?</strong></td>";
            $e .= "<td>". $familiares_vx_drogas ."</td>";
            $e .= "<td><strong>Quantas vezes?</strong></td>";
            $e .= "<td>". $familiares_vx_drogas_qto ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td><strong>Já teve problemas de saúde por causa da sua dependência?</strong></td>";
            $e .= "<td>". $saude_vx_drogas ."</td>";
            $e .= "<td><strong>Quantas vezes?</strong></td>";
            $e .= "<td>". $saude_vx_drogas_qto ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td><strong>Já teve internações hospitalares por causa da sua dependência?</strong></td>";
            $e .= "<td>". $internacoes_vx_drogas ."</td>";
            $e .= "<td><strong>Quantas vezes?</strong></td>";
            $e .= "<td>". $internacoes_vx_drogas_qto ."</td>";
            $e .= "</tr>";
			$e .= "</table>";
			// dq

			$e .= "<br />";

			// saúde física
			$e .= "<table width='100%' align='center'>";
			$e .= "<tr bgcolor='#f0f0f0'>";
			$e .= "<td align='center' colspan='4'><strong>Saúde Física</strong></td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td width='50%'><strong>Uso atual de medicação geral</strong></td>";
			$e .= "<td colspan='3'>". $uso_atual_medicacao_geral ."</td>";
			$e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td width='50%'><strong>Já teve ou tem</strong></td>";
            $e .= "<td colspan='3'>". $ja_teve_ou_tem ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td width='50%'><strong>Observações</strong></td>";
            $e .= "<td colspan='3'>". $saude['observacoes'] ."</td>";
            $e .= "</tr>";
			$e .= "</table>";
			// saúde física

			$e .= "<br />";

			// saúde mental
			$e .= "<table width='100%' align='center'>";
			$e .= "<tr bgcolor='#f0f0f0'>";
			$e .= "<td align='center' colspan='4'><strong>Saúde Mental</strong></td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td width='50%'><strong>Uso atual de medicação psicoativa</strong></td>";
			$e .= "<td colspan='3'>". $uso_atual_medicacao_psicoativa ."</td>";
			$e .= "</tr>";

			$e .= "<tr>";
			$e .= "<td width='50%'><strong>Já teve ou tem</strong></td>";
			$e .= "<td colspan='3'>". $ja_teve_ou_tem_2 ."</td>";
			$e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td width='50%'><strong>Observações</strong></td>";
            $e .= "<td colspan='3'>". $saude['observacoes2'] ."</td>";
            $e .= "</tr>";
			$e .= "</table>";
			// saúde mental
            
            $e .= "<br />";

            $e .= "<table width='100%' align='center'>";
            $e .= "<tr bgcolor='#f0f0f0'>";
            $e .= "<td align='center' colspan='4'><strong>Saúde - Outras Informações</strong></td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td><strong>Você tem alguma incapacidade fisica?</strong></td>";
            $e .= "<td>". $incapacidade_fisica ."</td>";
            $e .= "<td><strong>Qual?</strong></td>";
            $e .= "<td>". $saude['incapacidade_fisica_qual'] ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td><strong>Você já esteve hospitalizado por outros problemas de saúde?</strong></td>";
            $e .= "<td>". $hospitalizado ."</td>";
            $e .= "<td><strong>Quantas vezes?</strong></td>";
            $e .= "<td>". $hospitalizado_qtas_vx ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td><strong>Por que esteve hospitalizado?</strong></td>";
            $e .= "<td>". $saude['hospitalizado_pq'] ."</td>";
            $e .= "<td><strong>Tempo total de hospitalização?</strong></td>";
            $e .= "<td>". $tempo_hospitalizacao ."</td>";
            $e .= "</tr>";

            $e .= "<tr>";
            $e .= "<td><strong>No momento você sente algum desconforto físico?</strong></td>";
            $e .= "<td>". $desconforto_fisico ."</td>";
            $e .= "<td><strong>Qual?</strong></td>";
            $e .= "<td>". $saude['qual_desconforto'] ."</td>";
            $e .= "</tr>";
            $e .= "</table>";

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
            "FICHA DE ENTREVISTA - " . strtoupper($acolhido["nome"]) . ".pdf", /* Nome do arquivo de saída */
            array(
                "Attachment" => false /* Para download, altere para true */
            )
        );
    }
?>