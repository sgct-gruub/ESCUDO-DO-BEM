<?php

/*
$ip = $_SERVER["REMOTE_ADDR"];
if ($ip != '201.86.13.70') {
  echo("EM DESENVOLVIMENTO!");
  exit();
}
*/

// error_reporting(E_ALL); 
// ini_set('display_errors', 1);

require 'config.php';

// routes
require 'routes/web.php';
require 'routes/api.php';

// functions
function moveUploadedFile($directory, $uploadedFile)
{
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}

function calculaData($data)
	{
		$data_atual = new DateTime( date("Y-m-d") );
		$data = new DateTime( $data );

		$intervalo = $data_atual->diff( $data );
		
		if($intervalo->y > 1 && $intervalo->m > 1 && $intervalo->d > 1)
		{
			return $intervalo->y . " anos " . $intervalo->m . " meses " . $intervalo->d . " dias ";
		}
		elseif($intervalo->y > 0 && $intervalo->m > 0 && $intervalo->d > 0)
		{
			return $intervalo->y . " ano " . $intervalo->m . " mes " . $intervalo->d . " dia ";
		}
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
	
function calculaDataMes($data)
	{
		$app = Slim::getInstance();

		$data_atual = new DateTime( date("Y-m-d") );
		$data = new DateTime( $data );

		$intervalo = $data_atual->diff( $data );
		
		if($intervalo->m > 1)
		{
			return $intervalo->m . " meses ";
		}
		elseif($intervalo->m > 0)
		{
			return $intervalo->m . " mes ";
		}

		$app->response();
	}
	
function calculaDataDia($data)
	{
		$data_atual = new DateTime( date("Y-m-d") );
		$data = new DateTime( $data );

		$intervalo = $data_atual->diff( $data );
		
		if($intervalo->d > 1)
		{
			return $intervalo->d . " dias ";
		}
		elseif($intervalo->d > 0)
		{
			return $intervalo->d . " dia ";
		}
	}

// run the application
$app->run();
