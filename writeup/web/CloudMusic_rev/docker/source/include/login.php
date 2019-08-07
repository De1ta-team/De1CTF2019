<?php
if (isset($_SESSION['user'])&&strlen($_SESSION['user'])>0){
    ob_end_clean();
    header('Location: /hotload.php?page=disk');
    die();
}

include 'NoSQLite/NoSQLite.php';
include 'NoSQLite/Store.php';

@$username=$_POST['username'];
@$password=$_POST['password'];

function login($globals,$un,$pw){
    $nsql=new NoSQLite\NoSQLite($globals['dbfile']);
    $users=$nsql->getStore('users');

    $res=$users->get($un);
    if (isset($res)&&strlen((string)$res)>0&&$res===md5($globals['salt'].$pw)){
        return 1;
    }else{
        return 0;
    }
    return 0;
}

if (isset($username)&&isset($password)){
    $username=trim((string) $username);
    $password=(string) $password;
    if (strlen($username)<4||strlen($password)<8||strlen($username)>16||strlen($password)>32){
        ob_end_clean();
        die(json_encode(array('status'=>0,'info'=>'Format or length check failed.')));
    }else{
        if (login($_GLOBALS,$username,$password)===1){
            $_SESSION['user']=$username;
            $_SESSION['role']='user';
            $_SESSION['timestamp']=time();
            ob_end_clean();
            die(json_encode(array('status'=>1,'info'=>'Login succ.')));
        }else if ($username==='admin'&&$password===$_GLOBALS['admin_password']){
            $_SESSION['user']=$username;
            $_SESSION['role']='admin';
            $_SESSION['timestamp']=time();
            ob_end_clean();
            die(json_encode(array('status'=>1,'info'=>'Login succ.')));
        }else{
            ob_end_clean();
            die(json_encode(array('status'=>0,'info'=>'Username or password is incorrect.')));
        }
    }
}
?>
<script>nav_active('login');nav_user('<?php echo @$_SESSION['user']; ?>');</script>
        <div>
<div class="container" style="margin-top:30px">
    <h3 style="margin-bottom:15px;">Login</h3>
<?php
if(@$_GET['err']==='1') {
    echo "<div id=\"info\" class=\"alert alert-danger\" role=\"alert\">Login first.</div>";
}else{
    echo "<div id=\"info\" class=\"alert\" role=\"alert\" style=\"display: none;\"></div>";
}
?>
    <div class='form-container'>
        <div class='form-group'>
            <input type="text" class="form-control" placeholder="Username" id="username">
        </div>
        <div class='form-group'>
            <input type="password" class="form-control" placeholder="Password" id="password">
        </div>
        <div class="form-group">
            <button class="btn btn-primary btn-block" onclick="submit()">Login</button>
        </div>
    </div>
</div>
<script>
function submit(){
    $('#info').hide();
    $.ajax({
        type: 'POST',
        url: "/hotload.php?page=login",
        data: {username: $('#username').val(), password: $('#password').val()},
        dataType: "json",
        success: function(data) {
            if (data.status==1){
                $('#info').removeClass('alert-danger');
                $('#info').addClass('alert-success');
                setTimeout("loadHash('disk');",1000);
            }else{
                $('#info').removeClass('alert-success');
                $('#info').addClass('alert-danger');
            }
            $('#info').html(data.info);
            $('#info').show();
        }
    });
}
</script>
            <p style="visibility: hidden">
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Animi aspernatur beatae commodi dolorem in praesentium quia quis sit ullam. Aut facere nihil non soluta temporibus. Modi molestias suscipit voluptate. A?
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aliquid beatae consequatur deserunt earum eligendi ex, illum iure nostrum nulla obcaecati pariatur placeat quae reiciendis repellat similique tenetur totam vel voluptatum?
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut autem consectetur cum ex expedita id incidunt inventore ipsa laudantium maiores nihil quia quo quod rem, reprehenderit repudiandae sunt unde voluptatibus?
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium ad adipisci animi, commodi cumque doloribus ducimus eaque eveniet illo iste, maxime, molestiae molestias neque nostrum odio officiis reiciendis rem voluptates?
            </p>
        </div>