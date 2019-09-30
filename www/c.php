<?php
include_once 'Fitbit.php';
include_once 'config.php';
$m = new PHPFitBit(Config::$client_id, Config::$client_secret, Config::$redirect_url);

if (isset($_GET['code'])) {
	$request_token = $_GET['code'];
	$tokens = $m->auth($request_token);
	file_put_contents("token.json", json_encode($tokens));
}

?>
