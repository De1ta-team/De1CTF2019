<?php
ini_set('display_errors','Off');
error_reporting(0);
header('Content-Type: application/octet-stream');
$filepath=base64_decode($_SERVER['QUERY_STRING']);
if (strpos($filepath,".php")!==FALSE) {
    ob_end_clean();
    die("urldecode path:".urldecode($filepath)."\n.php is not allowed.");
}
$filepath=urldecode($filepath);
if (strlen($filepath)<=0) exit();
$file=fopen($filepath,"rb");
if ($file==FALSE) exit();
ob_clean();
while(!feof($file))
{
    print(fread($file,1024*8));
    ob_flush();
    flush();
}