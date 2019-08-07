<?php
error_reporting(0);

@mkdir($sandbox.'missiles/'.md5($_SERVER['REMOTE_ADDR']).'/');
$handler = opendir($sandbox.'missiles/'.md5($_SERVER['REMOTE_ADDR']).'/');
if ($handler){
    while (($filename = readdir($handler)) !== false) {
        if ($filename != "." && $filename != "..") {
            @unlink($sandbox.'missiles/'.md5($_SERVER['REMOTE_ADDR']).'/'.$filename);
        }
    }
    closedir($handler);
}
?>