<?php
ini_set('display_errors',0);
error_reporting(0);
session_set_cookie_params(1800);
session_start();

header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');

include 'totp.php';
@$act=$_GET['a'];
@$totp=$_GET['totp'];
$sandbox='/sandbox/';
$otop_key='GAXG24JTMZXGKZBU';

if (isset($act)) {
    $act=(string)$act;
}else{
    $act='';
}

if (isset($totp)) {
    $totp=(string)$totp;
}else{
    $totp='';
}

if (!Google2FA::verify_key($otop_key, $totp)){
    $res=array("code"=>404,"message"=>"totp err, server timestamp:".microtime(true));
    die(json_encode($res));
}

switch($act){
    case 'ls':
        $handler = opendir($sandbox);
        if ($handler){
            while (($filename = readdir($handler)) !== false) {
                if ($filename != "." && $filename != "..") {
                    $files[] = $filename;
                }
            }
            closedir($handler);
            $res=array("code"=>0,"data"=>$files);
        }else{
            $res=array("code"=>404,"message"=>"Access Denied");
        }
        break;
    case 'cat':
        $file_name = $_POST['filename'];
        $file_name = (string) $file_name;
        $tmp_name = str_replace('\\','',$file_name);
        $tmp_name = str_replace('/','',$tmp_name);
        if ($_POST['filename']==='/flag'){
            $res=array("code"=>404,"message"=>"<img src='img/flag.jpg' width='33%' height='33%'>");
            break;
        }else if (stripos($tmp_name,'..')===FALSE&&stripos($tmp_name,'secret')===FALSE){
            if (file_exists($sandbox.$file_name)){
                $data=htmlspecialchars(file_get_contents($sandbox.$tmp_name));
                if ($data){
                    $res=array("code"=>0,"data"=>$data);
                }else{
                    $res=array("code"=>404,"message"=>"cat: ".$file_name.": No such file or directory");
                }
                break;
            }
        }
        $res=array("code"=>404,"message"=>"cat: ".$file_name.": No such file or directory");
        break;
    case 'cd':
        $res=array("code"=>404,"message"=>"something broken~");
        break;
    case 'list':
        $res=array("code"=>404,"data"=>NULL);
        break;
    case 'launch':
        if (isset($_SESSION['login'])){
            include($sandbox.'modules/launch.php');
            $res=array("code"=>0,"message"=>$res);
        }else{
            $res=array("code"=>404,"message"=>'login first.');
        }
        break;
    case 'destruct':
        if (isset($_SESSION['login'])){
            include($sandbox.'modules/destruct.php');
            $res=array("code"=>0,"message"=>"missiles destructed.");
        }else{
            $res=array("code"=>404,"message"=>'login first.');
        }
        break;
    case 'logout':
        session_destroy();
        $res=array("code"=>0,"message"=>"logout.");
        break;
    case 'flag':
        $res=array("code"=>0,"message"=>"<img src='img/flag.jpg' width='33%' height='33%'>");
        break;
    case 'getflag':
        $res=array("code"=>0,"message"=>"flag{congratulations!}");
        break;
    case 'sh0w_hiiintttt_23333':
        $res=array("code"=>0,"message"=>"we add an evil monster named 'eval' when launching missiles.");
        break;
    case 'usage':
        $res=array("code"=>0,"message"=>"use `cat usage.md` instead.");
        break;
    case 'uname':
        $res=array("code"=>0,"message"=>"Darwin<br>");
        break;
    case 'uname -a':
        $res=array("code"=>0,"message"=>"Darwin de1ta-mbp 17.7.0 Darwin Kernel Version 17.7.0; root:xnu-4570.71.22~1/RELEASE_X86_64 x86_64<br>");
        break;
    case 'uname -r':
        $res=array("code"=>0,"message"=>"17.7.0<br>");
        break;
    case 'hostname':
        $res=array("code"=>0,"message"=>"de1ta-mbp<br>");
        break;
    default:
        $coms=explode(' ', $act);
        switch ($coms[0]) {
            case 'login':
                $username=$coms[1];
                $password=$coms[2];
                if (strlen($username)>100 || strlen($password)>100){
                    $res=array("code"=>404,"message"=>'username or password too long<br>');
                }
                if (strlen($username)>0 && strlen($password)>0){
                    include($sandbox.'modules/login.php');
                    $res=array("code"=>0,"message"=>$res);
                }else{
                    $res=array("code"=>404,"message"=>'usage: login [username] [password]<br>');
                }
                break;
            case 'targeting':
                if (isset($_SESSION['login'])){
                    $code=$coms[1];
                    $position=$coms[2];
                    if (strlen($coms[1])>0 || strlen($coms[2])>0){
                        include($sandbox.'modules/targeting.php');
                        $res=array("code"=>0,"message"=>$res);
                    }else{
                        $res=array("code"=>404,"message"=>'usage: targeting [code] [position]<br>');
                    }
                }else{
                    $res=array("code"=>404,"message"=>'login first.');
                }
                break;
            case 'echo':
                $str=$coms[1];
                $res=array("code"=>0,"message"=>$str."<br>");
                break;
            case 'export':
                $res=array("code"=>0,"message"=>$act."<br>");
                break;
            case 'ping':
            case 'curl':
            case 'wget':
                $res=array("code"=>0,"message"=>$coms[0].": Network is unreachable.<br>");
                break;
            case 'ip':
            case 'ifconfig':
                $res=array("code"=>0,"message"=>$coms[0].": No network adapters.<br>");
                break;
            case 'python':
            case 'python2':
            case 'python3':
            case 'pip':
            case 'pip2':
            case 'pip3':
            case 'php':
            case 'php5':
            case 'php7':
            case 'nodejs':
            case 'node':
            case 'npm':
            case 'perl':
            case 'gcc':
            case 'g++':
            case 'bash':
            case 'sh':
            case 'go':
            case 'gem':
            case 'ruby':
            case 'java':
            case 'javac':
            case '':
                $res=array("code"=>0,"message"=>$coms[0].": DISABLED BY DE1TA TEAM.<br>");
                break;
            default:
                break;
        }
        if (isset($res)) break;
        $res=array("code"=>404,"message"=>"zsh: command not found: ".$act."<br>");
        break;
}
die(json_encode($res));
?>