<?php

require_once __DIR__ . '/src/Maitred.php';
$config = require_once __DIR__ . '/config.php';

$fileDirectory = __DIR__ . '/files';
$maitred = new Maitred($config, $fileDirectory, $_SERVER);
$maitred->serve();