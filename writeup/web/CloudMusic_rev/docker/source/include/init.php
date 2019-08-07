<?php
@mkdir(__DIR__.'/../config/');

function rand_str($length = 16) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function get_filename($ext){
    $files=scandir(__DIR__.'/../config/');
    foreach ($files as $file) {
        if ($file != "." && $file != "..") {
            if (substr($file,-strlen($ext))===$ext){
                return $file;
            }
        }
    }
    return '';
}

function init_config($ext){
    $file=get_filename($ext);
    if ($file==''){
        $file=rand_str(8).$ext;
        file_put_contents(__DIR__.'/../config/'.$file, '');
        if (!file_exists(__DIR__.'/../config/'.$file)){
            $file=='';
        }
    }
    return $file;
}

function read_config($file){
    return file_get_contents(__DIR__.'/../config/'.$file);
}

function write_config($file,$str = '',$length = 16){
    $content=file_get_contents(__DIR__.'/../config/'.$file);
    if ($content==''){
        if ($str=='') $str=rand_str($length);
        file_put_contents(__DIR__.'/../config/'.$file, $str);
    }
    return file_get_contents(__DIR__.'/../config/'.$file);
}
?>