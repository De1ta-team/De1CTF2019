<?php
error_reporting(0);

function checkCode($code){
    $table='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    if (strlen($code)>2){
        return 'code too long.';
    }
    for ($i=0; $i<strlen($code); $i++) {
        if (strpos($table,$code[$i])===FALSE){
            return 'bad code.';
        }
    }
    if (file_exists($sandbox.'missiles/'.md5($_SERVER['REMOTE_ADDR']).'/'.$code)){
        return 'target existed.';
    }
    return NULL;
}

function checkPosition($position){
    $table='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789})$({_+-,.';
    if (strlen($position)>12){
        return 'position too long.';
    }
    for ($i=0; $i<strlen($position); $i++) {
        if (strpos($table,$position[$i])===FALSE){
            return 'bad position.';
        }
    }
    return NULL;
}

$res1=checkCode($code);
$res2=checkPosition($position);

if ($res1!==NULL){
    $res=$res1;
}else if ($res2!==NULL){
    $res=$res2;
}else{
    @mkdir($sandbox.'missiles/'.md5($_SERVER['REMOTE_ADDR']).'/');
    if (file_exists($sandbox.'missiles/'.md5($_SERVER['REMOTE_ADDR']).'/'.$code)){
        $res='target existed.';
    }else{
        file_put_contents($sandbox.'missiles/'.md5($_SERVER['REMOTE_ADDR']).'/'.$code, $position);
        $res='target marked.';
    }
}
?>