<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: text/plain; charset=utf-8');
echo "Hello from test.php\n";
echo "PHP ".PHP_VERSION."\n";
echo "File: ".__FILE__."\n";
echo "Document root: ".$_SERVER['DOCUMENT_ROOT']."\n";
