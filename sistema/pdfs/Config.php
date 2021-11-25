<?php

function db(): mysqli
{
    static $db;
    $db OR $db = new mysqli('localhost', 'root', 'bb744e9e47', 'escudodobem');
    return $db;
}

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
	for($i=0;$i<count($inteiro);$i++){
		for($ii=strlen($inteiro[$i]);$ii<3;$ii++){
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
	}
}

// print_r (db()->query('SELECT * FROM configuracoes')->fetch_object());