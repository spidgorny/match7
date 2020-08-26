<?php

require_once __DIR__ . '/vendor/autoload.php';

//Predis\Autoloader::register();
\Dotenv\Dotenv::createImmutable(__DIR__)->load();

$client = new Predis\Client([
	'scheme' => 'tcp',
	'host' => $_ENV['redis_server'],
	'port' => 6379,
]);

$walker = new FileWalker($client);
$walker();

