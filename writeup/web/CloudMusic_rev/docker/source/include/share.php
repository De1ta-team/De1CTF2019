<?php
if (!isset($_SESSION['user'])||strlen($_SESSION['user'])<=0){
    ob_end_clean();
    header('Location: /hotload.php?page=login&err=1');
    die();
}
?>
<script>nav_active('share');nav_user('<?php echo @$_SESSION['user']; ?>');</script>
        <div>
<style>
.m_song{
    float: left;
    margin-top: 20px;
    margin-left: 30px;
    margin-right: 30px;
    margin-bottom: 20px;
}
.m_cover{
    width: 192px;
    height: 192px;
}
.m_name{
    font-size: 12px;
    margin-top: 10px;
}
</style>
<div class="container" style="margin-top:30px">
    <h3 style="margin-bottom:15px;">My Share</h3>
    <div id="share" style="margin-top:20px;margin-left:60px;"></div>
</div>
<script>
function shuffleArray(array) {
    for (var i = array.length - 1; i > 0; i--) {
        var j = Math.floor(Math.random() * (i + 1));
        var temp = array[i];
        array[i] = array[j];
        array[j] = temp;
    }
    return array;
}

var playlist=[
    {name:'过客',artist:'阿涵',url:'/media/music/01.mp3',cover:'/media/cover/01.jpg'},
    {name:'岁月神偷',artist:'金玟岐',url:'/media/music/02.mp3',cover:'/media/cover/02.jpg'},
    {name:'不只爱情',artist:'区文诗',url:'/media/music/03.mp3',cover:'/media/cover/03.png'},
    {name:'厌弃',artist:'许廷铿',url:'/media/music/04.mp3',cover:'/media/cover/04.png'},
    {name:'侧脸',artist:'于果',url:'/media/music/05.mp3',cover:'/media/cover/05.jpg'},
    {name:'房间',artist:'刘瑞琦',url:'/media/music/06.mp3',cover:'/media/cover/06.jpg'},
    {name:'光年之外',artist:'G.E.M.邓紫棋',url:'/media/music/07.mp3',cover:'/media/cover/07.jpg'},
    {name:'来自天堂的魔鬼',artist:'G.E.M.邓紫棋',url:'/media/music/08.mp3',cover:'/media/cover/08.jpg'},
    {name:'心如止水',artist:'Ice Paper',url:'/media/music/09.mp3',cover:'/media/cover/09.jpg'},
    {name:'上心',artist:'郑欣宜',url:'/media/music/10.mp3',cover:'/media/cover/10.png'},
    {name:'Friendships',artist:'Pascal Letoublon',url:'/media/share.php?'+btoa('music/welcome.mp3'),cover:'/media/share.php?'+btoa('cover/welcome.png')}
];
playlist=shuffleArray(playlist);

function play(id){
    for (var i=0;i<ap.list.audios.length;i++){
        if (ap.list.audios[i].url==playlist[id].url){
            ap.list.switch(i);
            ap.play();
            return;
        }
    }
    ap.list.add([playlist[id]]);
    ap.list.switch(ap.list.audios.length-1);
    ap.play();
}
for (var i=0;i<playlist.length;i++){
    $('#share').append('    <div class="m_song"><a onclick="play('+i+');"><div><img class="m_cover" src="'+playlist[i].cover+'"></div><div class="m_name">'+playlist[i].artist+' - '+playlist[i].name+'</div></a></div>');
}
</script>
            <p style="visibility: hidden">
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Animi aspernatur beatae commodi dolorem in praesentium quia quis sit ullam. Aut facere nihil non soluta temporibus. Modi molestias suscipit voluptate. A?
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aliquid beatae consequatur deserunt earum eligendi ex, illum iure nostrum nulla obcaecati pariatur placeat quae reiciendis repellat similique tenetur totam vel voluptatum?
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut autem consectetur cum ex expedita id incidunt inventore ipsa laudantium maiores nihil quia quo quod rem, reprehenderit repudiandae sunt unde voluptatibus?
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium ad adipisci animi, commodi cumque doloribus ducimus eaque eveniet illo iste, maxime, molestiae molestias neque nostrum odio officiis reiciendis rem voluptates?
            </p>
        </div>