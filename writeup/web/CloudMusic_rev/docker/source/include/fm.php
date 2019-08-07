<script>nav_active('fm');nav_user('<?php echo @$_SESSION['user']; ?>');</script>
        <div>
<style>
th, td{
    text-align: center;
}
</style>
<div class="container" style="margin-top:30px">
    <h3 style="margin-bottom:15px;text-align:left;font-size:28px">Private FM</h3>
    <div style="margin-top:30px;text-align:left;"><img src="/media/cover/fm.jpg" width="200" height="200"></div>
    <div style="margin-top:30px;margin-bottom:30px;text-align:left;font-size:18px">【习近平新时代中国特色社会主义思想学习纲要】有声FM新上线！持续更新中......</div>
    <table class="table table-striped" style="font-size:16px">
        <tr>
            <th>Chapter</th>
            <th>Duration</th>
            <th></th>
        </tr>
        <tr>
            <td>【开篇·上】习近平新时代中国特色社会主义思想是党和国家必须长期坚持的指导思想</td>
            <td>00:15:35</td>
            <td><button type="button" class="btn btn-default" onclick="play(0);">Listen</button></td>
        </tr>
        <tr>
            <td>【开篇·下】习近平新时代中国特色社会主义思想是党和国家必须长期坚持的指导思想</td>
            <td>00:10:24</td>
            <td><button type="button" class="btn btn-default" onclick="play(1);">Listen</button></td>
        </tr>
        <tr>
            <td>【第一章】中国特色社会主义进入新时代，新时代标示我国发展新的历史方位</td>
            <td>00:06:46</td>
            <td><button type="button" class="btn btn-default" onclick="play(2);">Listen</button></td>
        </tr>
        <tr>
            <td>【第一章】中国特色社会主义进入新时代，新时代是中国特色社会主义新时代</td>
            <td>00:04:43</td>
            <td><button type="button" class="btn btn-default" onclick="play(3);">Listen</button></td>
        </tr>
        <tr>
            <td>【第一章】我国社会主要矛盾变化是关系全局的历史性变化</td>
            <td>00:06:40</td>
            <td><button type="button" class="btn btn-default" onclick="play(4);">Listen</button></td>
        </tr>
    </table>
</div>
<script>
var playlist=[
    {name:'【开篇·上】习近平新时代中国特色社会主义思想是党和国家必须长期坚持的指导思想',artist:'有声FM',url:'/media/fm/00-1.mp3',cover:'/media/cover/fm.jpg'},
    {name:'【开篇·下】习近平新时代中国特色社会主义思想是党和国家必须长期坚持的指导思想',artist:'有声FM',url:'/media/fm/00-2.mp3',cover:'/media/cover/fm.jpg'},
    {name:'【第一章】中国特色社会主义进入新时代，新时代标示我国发展新的历史方位',artist:'有声FM',url:'/media/fm/01-1.mp3',cover:'/media/cover/fm.jpg'},
    {name:'【第一章】中国特色社会主义进入新时代，新时代是中国特色社会主义新时代',artist:'有声FM',url:'/media/fm/01-2.mp3',cover:'/media/cover/fm.jpg'},
    {name:'【第一章】我国社会主要矛盾变化是关系全局的历史性变化',artist:'有声FM',url:'/media/fm/01-3.mp3',cover:'/media/cover/fm.jpg'}
];
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
</script>
            <p style="visibility: hidden">
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Animi aspernatur beatae commodi dolorem in praesentium quia quis sit ullam. Aut facere nihil non soluta temporibus. Modi molestias suscipit voluptate. A?
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aliquid beatae consequatur deserunt earum eligendi ex, illum iure nostrum nulla obcaecati pariatur placeat quae reiciendis repellat similique tenetur totam vel voluptatum?
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aut autem consectetur cum ex expedita id incidunt inventore ipsa laudantium maiores nihil quia quo quod rem, reprehenderit repudiandae sunt unde voluptatibus?
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium ad adipisci animi, commodi cumque doloribus ducimus eaque eveniet illo iste, maxime, molestiae molestias neque nostrum odio officiis reiciendis rem voluptates?
            </p>
        </div>