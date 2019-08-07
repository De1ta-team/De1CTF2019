<?php
error_reporting(0);

$count=0;
$res='Initializing launching system...<br>';
@mkdir($sandbox.'missiles/'.md5($_SERVER['REMOTE_ADDR']).'/');
$files=scandir($sandbox.'missiles/'.md5($_SERVER['REMOTE_ADDR']).'/');
$codes=array();
$positions=array();
$exprs=array();
foreach ($files as $code) {
    if ($code != "." && $code != "..") {
        $position=file_get_contents($sandbox.'missiles/'.md5($_SERVER['REMOTE_ADDR']).'/'.$code);
        array_push($codes, $code);
        array_push($positions, $position);
        array_push($exprs, '$'.$code.' = "'.$position.'";');
        $count++;
    }
}
for ($cnt=0; $cnt<count($exprs); $cnt++){
    $res.='Setting target: $'.$codes[$cnt].' = "'.$positions[$cnt].'";<br>';
    try{
        @eval($exprs[$cnt]);
    }catch(Error $e){
        $count=-1;
        break;
    }
    $res.='Reading target: $'.$codes[$cnt].' = "'.${$codes[$cnt]}.'";<br>';
}
if ($count>0){
    $res.="3..2..1..Fire!<br>";
    $res.="All {$count} missiles are launched...<br>";
    $res.="Cruising...<br>";
    $res.="Engaging...Bull's-eye!<br>";
    $res.="All targets are eliminated.<br>";
}else if ($count==0){
    $res.="No targets were selected.<br>";
    $res.="Aborted.<br>";
}else{
    $res.="Error when setting target.<br>";
    $res.="Aborted.<br>";
}
?>