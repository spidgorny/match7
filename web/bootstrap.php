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
	$vars = count($vars) === 1 ? current($vars) : $vars;
	$json = json_encode($vars, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
	error_log($json);
}

$client = new Predis\Client([
	'scheme' => 'tcp',
	'host' => $_ENV['redis_server'],
	'port' => 6379,
]);
