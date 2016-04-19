<?php

require('phar://myapp.phar/Convertor.php');
require('phar://myapp.phar/PostmanCollection.php');

if (strnatcmp(phpversion(),'5.5.0') < 0) {
    die("Upgrade your PHP installation. >= 5.5 supported");
}

if (!isset($argv[1])) {
    die('Usage: ' . "$argv[0] /path/to/dop/file [collectionName]" . PHP_EOL);
}
$file = realpath($argv[1]);
if (!$file || !is_readable($file)) {
    die('File ' . "'$argv[1]' is not found or unreadable" . PHP_EOL);
}
$collectionName = null;
if (isset($argv[2])) {
    $collectionName = $argv[2];
}
$data = file_get_contents($file);
$doHttpCollections = json_decode($data, true);
# do magic:
Convertor::factory()->extract($doHttpCollections, $collectionName);