<?php
ini_set('display_errors','Off');
error_reporting(0);

date_default_timezone_set("Asia/Shanghai");

ini_set('session.gc_maxlifetime',"3600");
ini_set("session.cookie_lifetime","3600");
session_start();

include 'init.php';

$_GLOBALS['dbfile']=init_config('.sqlite');
$_GLOBALS['salt']=write_config(init_config('.salt'));
$_GLOBALS['admin_password']=write_config(init_config('.passwd'));
if (strlen($_GLOBALS['dbfile'])<=0||strlen($_GLOBALS['salt'])<=0||strlen($_GLOBALS['admin_password'])<=0){
    ob_end_clean();
    die('Permission denied!');
}
$_GLOBALS['dbfile']=__DIR__.'/../config/'.$_GLOBALS['dbfile'];
?>