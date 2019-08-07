hostPrefix = "/hotload.php?page=";
$(function(){
    loadInit();
    var isClickLink = false;
    $("a").click(function(){
        isClickLink = true;
        var nowHref=$(this).attr('href');
        if (nowHref==undefined) return;
        if(nowHref[0]=='#'){
            var addr= hostPrefix + (nowHref.split("#"))[1];
            loadSection(addr);
        }
    });
    window.onhashchange=function(){
        if(isClickLink){
            isClickLink = false;
        }else{
            loadInit();
        }
    }
});
function loadInit(){
    if(location.hash.split("#")[1]!=undefined){
        var loadaddr=hostPrefix + location.hash.split("#")[1];
        loadSection(loadaddr);
    }else{
        var loadaddr=hostPrefix;
        loadSection(loadaddr);
    }
}
function loadHash(hash){
    loadSection(hostPrefix+hash);
}
function loadSection(addr){
    $.get(addr, {}, function(data){
        $("#content").html(data);
    });
}
function nav_active(name){
    $('#L1').find('a').each(function(){
        $(this).removeClass('a1_active');
    });
    if (name!=undefined){
        if(name!="") $('#a1_'+name).addClass('a1_active');
    }
}
function nav_user(name){
    if (name=="") {
        $('#s1_1').hide();
        $('#s1_2').hide();
        $('#s2_1').show();
        $('#s2_2').show();
        $('#a1_info').html('Username');
    }else{
        $('#a1_info').html(name);
        $('#s2_1').hide();
        $('#s2_2').hide();
        $('#s1_1').show();
        $('#s1_2').show();
    }
}