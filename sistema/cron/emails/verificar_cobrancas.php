<?php
	
	require 'src/PHPMailer.php';
	require 'src/SMTP.php';

	function db(): mysqli
	{
	    static $db;
	    $db OR $db = new mysqli('localhost', 'root', 'bb744e9e47', 'renovacao');
	    return $db;
	}

	$cobrancas = db()->query("SELECT * FROM doacoes_charges WHERE paymentStatus = 'CONFIRMED' AND emailAfterPayment = 0");

	if($cobrancas->num_rows > 0)
	{

		while($x = $cobrancas->fetch_assoc()){
			
			$doador = db()->query("SELECT * FROM doadores WHERE id = {$x['doador']}")->fetch_assoc();

			$variables = array();

			$variables['nome'] = $doador['nome'];

			$template = file_get_contents("https://sistema.renovacaopara.org.br/cron/emails/templates/welcome/index.html");

			foreach($variables as $key => $value)
			{
			    $template = str_replace('{{ '.$key.' }}', $value, $template);
			}

			$mail = new PHPMailer();
			$mail->isSMTP();
			$mail->Host = 'smtp.uni5.net';
			$mail->SMTPAuth = true;
			$mail->SMTPSecure = 'tls';
			$mail->Username = 'adm@novajornada.org.br';
			$mail->Password = 'ctnj@2021';
			$mail->Port = 587;
			$mail->CharSet = 'UTF-8';
			$mail->setFrom('contato@novajornada.org.br', 'Comunidade Terapêutica Nova Jornada');
			$mail->addAddress($doador['email'], $doador['nome']);
			$mail->isHTML(true);
			$mail->Subject = 'Bem-vindo ao Programa Amigo Solidário!';
			$mail->Body = $template;
			if(!$mail->send()) {
			    echo 'Não foi possível enviar a mensagem.<br>';
			    echo 'Erro: ' . $mail->ErrorInfo;
			} else {
				$cobranca = db()->query("UPDATE doacoes_charges SET emailAfterPayment = 1 WHERE id = {$x['id']}");
				if($cobranca){
			    	echo 'ID #'.$x['id']."<br />";
			    } else {
			    	echo 'E-mail enviado mas registro não atualizado no banco de dados';
			    }
			}

		}

	} else {
		echo 'Nenhuma cobrança!';
	}
?>