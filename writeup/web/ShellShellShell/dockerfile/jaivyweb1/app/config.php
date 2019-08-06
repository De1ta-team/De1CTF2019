<?php
header("Content-Type:text/html;charset=UTF-8");
date_default_timezone_set("PRC");

session_start();
class Db
{
    private  $servername = "localhost";
    private  $username = "jaivy";
    private  $password = "jaivypassword666";
    private  $dbname = "jaivyctf";
    private  $conn;

    function __construct()
    {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
    }

    function __destruct()
    {
        $this->conn->close();
    }

    private function get_column($columns){

        if(is_array($columns))
            $column = ' `'.implode('`,`',$columns).'` ';
        else
            $column = ' `'.$columns.'` ';

        return $column;
    }

    public function select($columns,$table,$where) {

        $column = $this->get_column($columns);

        $sql = 'select '.$column.' from '.$table.' where '.$where.';';
        $result = $this->conn->query($sql);

        return $result;

    }

    public function insert($columns,$table,$values){

        $column = $this->get_column($columns);
        $value = '('.preg_replace('/`([^`,]+)`/','\'${1}\'',$this->get_column($values)).')';
        $nid =
        $sql = 'insert into '.$table.'('.$column.') values '.$value;
        $result = $this->conn->query($sql);

        return $result;
    }

    public function delete($table,$where){

        $sql =  'delete from '.$table.' where '.$where;
        $result = $this->conn->query($sql);

        return $result;
    }

    public function update_single($table,$where,$column,$value){

        $sql = 'update '.$table.' set `'.$column.'` = \''.$value.'\' where '.$where;
        $result = $this->conn->query($sql);

        return $result;
    }




}

class Mood{

    public $mood, $ip, $date;

    public function __construct($mood, $ip) {
        $this->mood = $mood;
        $this->ip  = $ip;
        $this->date = time();

    }

    public function getcountry()
    {
        $ip = @file_get_contents("http://ip.taobao.com/service/getIpInfo.php?ip=".$this->ip);
        $ip = json_decode($ip,true);
        return $ip['data']['country'];
    }

    public function getsubtime()
    {
        $now_date = time();
        $sub_date = (int)$now_date - (int)$this->date;
        $days = (int)($sub_date/86400);
        $hours = (int)($sub_date%86400/3600);
        $minutes = (int)($sub_date%86400%3600/60);
        $res = ($days>0)?"$days days $hours hours $minutes minutes ago":(($hours>0)?"$hours hours $minutes minutes ago":"$minutes minutes ago");
        return $res;
    }

    
}

function get_ip(){
    return $_SERVER['REMOTE_ADDR'];
}


function addslashes_deep($value)
{
    if (empty($value))
    {
        return $value;
    }
    else
    {
        return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
    }
}
function rand_s($length = 8)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
    $password = '';
    for ( $i = 0; $i < $length; $i++ )
    {
        $password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
    }
    return $password;
}

function addsla_all()
{
    if (!get_magic_quotes_gpc())
    {
        if (!empty($_GET))
        {
            $_GET  = addslashes_deep($_GET);
        }
        if (!empty($_POST))
        {
            $_POST = addslashes_deep($_POST);
        }
        $_COOKIE   = addslashes_deep($_COOKIE);
        $_REQUEST  = addslashes_deep($_REQUEST);
    }
}
addsla_all();


