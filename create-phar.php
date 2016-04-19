<?php
/**
 * Created by PhpStorm.
 * User: sergei
 * Date: 19.04.16
 * Time: 9:22
 */


$output = 'dohttp2postman.phar';
if (file_exists($output)) {
    unlink($output);
}
$srcRoot = dirname(__FILE__) . DIRECTORY_SEPARATOR . "src";
$buildRoot = dirname(__FILE__) . DIRECTORY_SEPARATOR . "build";

$phar = new Phar($buildRoot . DIRECTORY_SEPARATOR . $output,
    FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, 'myapp.phar');
// start buffering. Mandatory to modify stub.
$phar->startBuffering();
$phar->buildFromDirectory($srcRoot);
// Get the default stub. You can create your own if you have specific needs
$defaultStub = $phar->createDefaultStub("index.php");
// Create a custom stub to add the shebang
$stub = "#!/usr/bin/env php \n" . $defaultStub;
// Add the stub
$phar->setStub($stub);

$phar->stopBuffering();

chmod($buildRoot . DIRECTORY_SEPARATOR . $output, 0755);
