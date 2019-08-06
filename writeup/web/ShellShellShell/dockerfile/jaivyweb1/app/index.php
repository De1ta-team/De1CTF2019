<?php

require_once 'user.php';
$C = new Customer();
if(isset($_GET['action']))
{
    $action=$_GET['action'];
    $allow=0;
    $white_action = "delete|index|login|logout|phpinfo|profile|publish|register";
    $vpattern = explode("|",$white_action);
    foreach($vpattern as $key=>$value)
    {
        if(preg_match("/$value/i", $action ) &&  (!preg_match("/\//i",$action))   )
        {
            $allow=1;
        }
    }
    if($allow==1)
    {require_once 'views/'.$_GET['action'];}
    else {
        die("Get out hacker!<br>jaivy's laji waf.");
    }
}
else
header('Location: index.php?action=login');





