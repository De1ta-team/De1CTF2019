<?php
error_reporting(0);
session_start();

include 'secret.php';

$conn = new mysqli($db_host, $db_un, $db_pw, $db_name);

if ($conn->connect_error) {
    die("Connection Error");
} 

$sql = "SELECT password FROM users WHERE username='".$username."'";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row["password"] === $password){
            if ($username==='admin') {
                $res="login success.";
                $_SESSION['login']='1';
                @mkdir($sandbox.'missiles/'.md5($_SERVER['REMOTE_ADDR']).'/');
            }else{
                $res="only admin permitted.";
            }
        }else{
            $res="login fail, password incorrect.";
        }
        break;
    }
} else {
    $res="login fail, user not found.";
}
$conn->close();
?>