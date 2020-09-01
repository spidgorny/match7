<?php

require_once __DIR__ . '/bootstrap.php';

$walker = new FileWalker($client);
$walker();

