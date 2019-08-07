<?php
if (isset($_SESSION['user'])&&strlen($_SESSION['user'])>0){
    ob_end_clean();
    header('Location: /hotload.php?page=disk');
    die();
}

include 'NoSQLite/NoSQLite.php';
include 'NoSQLite/Store.php';

@$username=$_POST['username'];
@$password1=$_POST['password1'];
@$password2=$_POST['password2'];
@$code=$_POST['code'];

function set_code(){
    $_SESSION['code']=rand_str(8);
    $_SESSION['calc']=substr(md5(rand_str(8).$_SESSION['code']),0,6);
}

if (!isset($_SESSION['code'])||strlen($_SESSION['code'])<=0) set_code();

function reg($globals,$un,$pw){
    $nsql=new NoSQLite\NoSQLite($globals['dbfile']);
    $users=$nsql->getStore('users');

    $res=$users->get($un);
    if ($res===null||strlen((string)$res)<=0){
        $users->set($un,md5($globals['salt'].$pw));
        return 1;
    }else{
        return 0;
    }
    return 0;
}

if (isset($username)&&isset($password1)&&isset($password2)&&isset($code)){
    $username=trim((string) $username);
    $password1=(string) $password1;
    $password2=(string) $password2;
    $code=(string) $code;
    if (strlen($username)<4||strlen($password1)<8||strlen($password2)<8||strlen($code)<1||strlen($username)>16||strlen($password1)>32||strlen($code)>32||$username=='admin'){
        set_code();
        ob_end_clean();
        die(json_encode(array('status'=>0,'info'=>'Format or length check failed.','code'=>$_SESSION['code'],'calc'=>$_SESSION['calc'])));
    }elseif ($password1!==$password2) {
        set_code();
        ob_end_clean();
        die(json_encode(array('status'=>0,'info'=>'Not the same password.','code'=>$_SESSION['code'],'calc'=>$_SESSION['calc'])));
    }elseif (substr(md5($code.$_SESSION['code']),0,6)!=$_SESSION['calc']) {
        set_code();
        ob_end_clean();
        die(json_encode(array('status'=>0,'info'=>'Verify code err.','code'=>$_SESSION['code'],'calc'=>$_SESSION['calc'])));
    }else{
        if (reg($_GLOBALS,$username,$password1)===1){
            set_code();
            ob_end_clean();
            die(json_encode(array('status'=>1,'info'=>'Reg succ.')));
        }else{
            set_code();
            ob_end_clean();
            die(json_encode(array('status'=>0,'info'=>'User is existed.','code'=>$_SESSION['code'],'calc'=>$_SESSION['calc'])));
        }
    }
}

set_code();
?>
<script>nav_active('reg');nav_user('<?php echo @$_SESSION['user']; ?>');</script>
        <div>
<div class="container" style="margin-top:30px">
    <h3 style="margin-bottom:15px;">Register</h3>
<div id="info" class="alert" role="alert" style="display: none;"></div>
    <div class='form-container'>
        <div class='form-group'>
            <input type="text" class="form-control" placeholder="username" id="username">
        </div>
        <div class='form-group'>
            <input type="password" class="form-control" placeholder="password" id="password1">
        </div>
        <div class='form-group'>
            <input type="password" class="form-control" placeholder="repeat password" id="password2">
        </div>
        <div class='form-group'>
            <b style="font-size:16px;">md5(code+"<span id="show_code"><?php echo $_SESSION['code']; ?></span>")[:6]=="<span id="show_calc"><?php echo $_SESSION['calc']; ?></span>"</b>
            <input type="text" class="form-control" placeholder="verify code" id="code">
        </div>
        <div class="form-group">
            <button class="btn btn-primary btn-block" onclick="submit()">Reg</button>
        </div>
    </div>
</div>
<script>
function submit(){
    $('#info').hide();
    $.ajax({
        type: 'POST',
        url: "/hotload.php?page=reg",
        data: {username: $('#username').val(), password1: $('#password1').val(), password2: $('#password2').val(), code: $('#code').val()},
        dataType: "json",
        success: function(data) {
            if (data.status==1){
                $('#info').removeClass('alert-danger');
                $('#info').addClass('alert-success');
                setTimeout("loadHash('login');';",1000);
            }else{
                $('#info').removeClass('alert-success');
                $('#info').addClass('alert-danger');
                $('#show_code').html(data.code);
                $('#show_calc').html(data.calc);
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