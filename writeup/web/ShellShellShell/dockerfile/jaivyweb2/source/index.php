<?php
    $sandbox = '/var/sandbox/' . md5("prefix" . $_SERVER['REMOTE_ADDR']);
    @mkdir($sandbox);
    @chdir($sandbox);

    if($_FILES['file']['name'])
    {
        $filename = !empty($_POST['file']) ? $_POST['file'] : $_FILES['file']['name'];
        if (!is_array($filename)) 
        {
            $filename = explode('.', $filename);
        }
        $ext = end($filename);
        if($ext==$filename[count($filename) - 1])
        {
            die("try again!!!");
        }
        $new_name = (string)rand(100,999).".".$ext;
        move_uploaded_file($_FILES['file']['tmp_name'],$new_name);
        $_ = $_POST['hello'];
        if(@substr(file($_)[0],0,6)==='@<?php')
        {
            if(strpos($_,$new_name)===false)
            {
                include($_);
            }
            else
            {
                echo "you can do it!";
            }
        }
        unlink($new_name);
    }
    else
    {
        highlight_file(__FILE__);
    }