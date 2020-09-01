<?php

require_once __DIR__ . '/bootstrap.php';

$config = Config::getInstance();
$index = Index::getInstance($config);
echo $index->render();
