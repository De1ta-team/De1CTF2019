<?php
include_once 'include/config.php';

@$page=$_GET['page'];
if (isset($page)) $page=(string) $page;
if (!isset($page)||strlen($page)<=0) $page='index';
$whitelist=array('index','fm','mv','friend','disk','upload','share','favor','login','reg','feedback','firmware','search','logout','info');
if (!in_array($page,$whitelist,true)) $page='404';

include "include/$page.php";