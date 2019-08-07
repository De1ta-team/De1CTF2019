        <span class="sTop s1"><a href="/"><img src="img/logo.png"></a></span>
        <span class="sTop s2"><a onclick="history.go(-1);loadInit();"><img src="img/left.png"></a></span>
        <span class="sTop s2"><a onclick="history.go(+1);loadInit();"><img src="img/right.png"></a></span>
        <span class="sTop s3">
            <input type="text" id="keyword" value=" search music, artist, lyrics，user" onfocus="this.value='' " onblur="this.value=' search music, artist, lyrics，user'" onkeyup="if (event.keyCode==13) {loadHash('search&keyword='+encodeURIComponent($('#keyword').val()));$('#keyword').val('')}">
            <a href="#" onclick="loadHash('search&keyword='+encodeURIComponent($('#keyword').val()));"><img src="img/search.png" alt=""></a>
        </span>