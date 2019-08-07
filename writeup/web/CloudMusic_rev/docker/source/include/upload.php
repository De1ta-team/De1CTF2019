<?php
if (!isset($_SESSION['user'])||strlen($_SESSION['user'])<=0){
    ob_end_clean();
    header('Location: /hotload.php?page=login&err=1');
    die();
}

include 'NoSQLite/NoSQLite.php';
include 'NoSQLite/Store.php';

function clean_string($str){
    $str=substr($str,0,1024);
    return str_replace("\x00","",$str);
}

if (isset($_FILES["file_data"])){
    if ($_FILES["file_data"]["error"] > 0||$_FILES["file_data"]["size"] > 1024*1024*1){
        ob_end_clean();
        die(json_encode(array('status'=>0,'info'=>'upload err, maximum file size is 1MB.')));
    }else{
        $music_filename=__DIR__."/../uploads/music/".md5($_GLOBALS['salt'].$_SESSION['user']).".mp3";
        if (time()-$_SESSION['timestamp']<3){
            ob_end_clean();
            die(json_encode(array('status'=>0,'info'=>'too fast, try later.')));
        }
        $_SESSION['timestamp']=time();
        move_uploaded_file($_FILES["file_data"]["tmp_name"], $music_filename);
        $handle = fopen($music_filename, "rb");
        if ($handle==FALSE){
            ob_end_clean();
            die(json_encode(array('status'=>0,'info'=>'upload err, unknown fault.')));
        }
        $flags = fread($handle, 3);
        fclose($handle);
        if ($flags!=="ID3"){
            unlink($music_filename);
            ob_end_clean();
            die(json_encode(array('status'=>0,'info'=>'upload err, not a valid MP3 file.')));
        }
        try{
            $parser = FFI::cdef("
                struct Frame{
                    char * data;
                    int size;
                };
                struct Frame * parse(char * password, char * classname, char * filename);
            ", __DIR__ ."/../lib/parser.so");
            $result=$parser->parse($_GLOBALS['admin_password'],"title",$music_filename);
            if ($result->size>0x60) $result->size=0x60;
            $mp3_title=(string) FFI::string($result->data,$result->size);
            if (substr($mp3_title,0,2)=="\xFF\xFE"){
                @$mp3_title_conv=iconv("unicode","utf-8",$mp3_title);
                if ($mp3_title_conv!==FALSE) $mp3_title=$mp3_title_conv;
            }
            $mp3_title=base64_encode(clean_string($mp3_title));
            $result=$parser->parse($_GLOBALS['admin_password'],"artist",$music_filename);
            if ($result->size>0x60) $result->size=0x60;
            $mp3_artist=(string) FFI::string($result->data,$result->size);
            if (substr($mp3_artist,0,2)=="\xFF\xFE"){
                @$mp3_artist_conv=iconv("unicode","utf-8",$mp3_artist);
                if ($mp3_artist_conv!==FALSE) $mp3_artist=$mp3_artist_conv;
            }
            $mp3_artist=base64_encode(clean_string($mp3_artist));
            $result=$parser->parse($_GLOBALS['admin_password'],"album",$music_filename);
            if ($result->size>0x60) $result->size=0x60;
            $mp3_album=(string) FFI::string($result->data,$result->size);
            if (substr($mp3_album,0,2)=="\xFF\xFE"){
                @$mp3_album_conv=iconv("unicode","utf-8",$mp3_album);
                if ($mp3_album_conv!==FALSE) $mp3_album=$mp3_album_conv;
            }
            $mp3_album=base64_encode(clean_string($mp3_album));
            $song=array($mp3_title,$mp3_artist,$mp3_album);
            $nsql=new NoSQLite\NoSQLite($_GLOBALS['dbfile']);
            $music=$nsql->getStore('music');
            $res=$music->get($_SESSION['user']);
            if ($res===null||strlen((string)$res)<=0){
                $res=array();
            }else{
                $res=json_decode($res,TRUE);
            }
            array_push($res,$song);
            $res=json_encode($res);
            $music->set($_SESSION['user'],$res);
            ob_end_clean();
            die(json_encode(array('status'=>1,'info'=>'upload succ.','title'=>$mp3_title,'artist'=>$mp3_artist,'album'=>$mp3_album)));
        }catch(Error $e){
            ob_end_clean();
            die(json_encode(array('status'=>0,'info'=>'upload err, not a valid MP3 file.')));
        }
    }
}else{
    if (isset($_SERVER['CONTENT_TYPE'])){
        if (stripos($_SERVER['CONTENT_TYPE'],'form-data')!=FALSE){
            ob_end_clean();
            die(json_encode(array('status'=>0,'info'=>'upload err, maximum file size is 1MB.')));
        }
    }
}
?>
<script>nav_active('upload');nav_user('<?php echo @$_SESSION['user']; ?>');</script>
    <link rel="stylesheet" href="css/fileinput.min.css">
    <link rel="stylesheet" href="css/fileinput-rtl.min.css">
    <script src="js/fileinput.min.js"></script>
    <script src="js/locales/zh.js"></script>
        <div>
<div class="container" style="margin-top:30px">
    <h3 style="margin-bottom:15px;">Upload Music</h3>
    <input id="upload" type="file" class="file" data-preview-file-type="text" >
    <div style="height:30px"></div>
    <div id="info" class="alert" role="alert" style='display: none;'></div>
    <script>
        $("#upload").fileinput({language: 'zh',uploadUrl: '/hotload.php?page=upload'});
        $("#upload").on("fileuploaded", function (event, data, previewId, index) {
            console.log("123");
            var data = data.response;
            if (data.status==1){
                $('#info').removeClass('alert-danger');
                $('#info').addClass('alert-success');
                try{
                    data.info+="<br>Title："+decodeURIComponent(escape(atob(data.title)));
                    data.info+="<br>Artist："+decodeURIComponent(escape(atob(data.artist)));
                    data.info+="<br>Album："+decodeURIComponent(escape(atob(data.album)));
                }catch(err){
                    data.info+="<br>Title："+atob(data.title);
                    data.info+="<br>Artist："+atob(data.artist);
                    data.info+="<br>Album："+atob(data.album);
                }
            }else{
                $('#info').removeClass('alert-success');
                $('#info').addClass('alert-danger');
            }
            $('#info').html(data.info);
            $('#info').show();
        });
    </script>
</div>
            <p style="visibility: hidden">
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Animi aspernatur beatae commodi dolorem in praesentium quia quis sit ullam. Aut facere nihil non soluta temporibus. Modi molestias suscipit voluptate. A?
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aliquid beatae consequatur deserunt earum eligendi ex, illum iure nostrum nulla obcaecati pariatur placeat quae reiciendis repellat similique tenetur totam vel voluptatum?
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut autem consectetur cum ex expedita id incidunt inventore ipsa laudantium maiores nihil quia quo quod rem, reprehenderit repudiandae sunt unde voluptatibus?
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium ad adipisci animi, commodi cumque doloribus ducimus eaque eveniet illo iste, maxime, molestiae molestias neque nostrum odio officiis reiciendis rem voluptates?
            </p>
        </div>