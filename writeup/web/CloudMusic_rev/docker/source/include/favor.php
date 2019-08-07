<?php
if (!isset($_SESSION['user'])||strlen($_SESSION['user'])<=0){
    ob_end_clean();
    header('Location: /hotload.php?page=login&err=1');
    die();
}
?>
<script>nav_active('favor');nav_user('<?php echo @$_SESSION['user']; ?>');</script>
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
    <h3 style="margin-bottom:15px;">My Favor</h3>
    <div id="favor" style="margin-top:20px;margin-left:60px;"></div>
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
    {name:'光るなら',artist:'Goose house',url:'/media/music/11.mp3',cover:'/media/cover/11.jpg'},
    {name:'トリカゴ',artist:'XX:me',url:'/media/music/12.mp3',cover:'/media/cover/12.jpg'},
    {name:'前前前世',artist:'RADWIMPS',url:'/media/music/13.mp3',cover:'/media/cover/13.jpg'},
    {name:'Best of 2017 Medley',artist:'Anthem Lights',url:'/media/music/14.mp3',cover:'/media/cover/14.jpg'},
    {name:'Home (Blaze U Remix)',artist:'ThimLife,Blaze U',url:'/media/music/15.mp3',cover:'/media/cover/15.jpg'},
    {name:'Dependent',artist:'еяхат музыка',url:'/media/music/16.mp3',cover:'/media/cover/16.jpg'},
    {name:'GQ',artist:'Lola Coca',url:'/media/music/17.mp3',cover:'/media/cover/17.jpg'},
    {name:'No Mean, Into Cold Love',artist:'张晋瑞',url:'/media/music/18.mp3',cover:'/media/cover/18.jpg'}
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
    $('#favor').append('    <div class="m_song"><a onclick="play('+i+');"><div><img class="m_cover" src="'+playlist[i].cover+'"></div><div class="m_name">'+playlist[i].artist+' - '+playlist[i].name+'</div></a></div>');
}
</script>
            <p style="visibility: hidden">
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Animi aspernatur beatae commodi dolorem in praesentium quia quis sit ullam. Aut facere nihil non soluta temporibus. Modi molestias suscipit voluptate. A?
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aliquid beatae consequatur deserunt earum eligendi ex, illum iure nostrum nulla obcaecati pariatur placeat quae reiciendis repellat similique tenetur totam vel voluptatum?
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut autem consectetur cum ex expedita id incidunt inventore ipsa laudantium maiores nihil quia quo quod rem, reprehenderit repudiandae sunt unde voluptatibus?
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium ad adipisci animi, commodi cumque doloribus ducimus eaque eveniet illo iste, maxime, molestiae molestias neque nostrum odio officiis reiciendis rem voluptates?
            </p>
        </div>