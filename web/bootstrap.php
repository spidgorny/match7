<?php

require_once __DIR__ . '/vendor/autoload.php';

//Predis\Autoloader::register();
\Dotenv\Dotenv::createImmutable(__DIR__)->load();

session_set_cookie_params([
	'samesite' => 'Lax',
]);

$_COOKIE['debug'] = 1;
$init = new InitNADLIB();
$init->init();

function __($a)
{
	return $a;
}

function llog(...$vars)
{
	$json = json_encode($vars);
	error_log($json);
}

$client = new Predis\Client([
	'scheme' => 'tcp',
	'host' => $_ENV['redis_server'],
	'port' => 6379,
]);
