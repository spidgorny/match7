<?php

require_once __DIR__ . '/bootstrap.php';

$arg = $_SERVER['argv'][1];
echo $arg, PHP_EOL;

$fw = new FileWalker(null);
$outFile = $fw->denoise($arg);
echo $outFile, PHP_EOL;
