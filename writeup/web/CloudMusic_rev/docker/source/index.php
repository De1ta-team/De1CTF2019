<?php
include_once 'include/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Comical CloudMusic</title>

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/other.css">
    <link rel="stylesheet" href="css/APlayer.min.css">

    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/APlayer.min.js"></script>
    <script src="js/color-thief.js"></script>
</head>
<body>
<header>
    <div class="top">
<?php include 'include/top.php'; ?>
    </div>
</header>
<div id="container">
    <div id="left">
<?php include 'include/left.php'; ?>
    </div>
    <div id="content"></div>
    <div id="player"></div>
</div>
<script>
const ap = new APlayer({
    container: document.getElementById('player'),
    fixed: true
});
const colorThief = new ColorThief();
const setTheme = (index) => {
    if (ap.list.audios.length<=0) return;
    if (ap.list.audios[index].theme==undefined) {
        colorThief.getColorAsync(ap.list.audios[index].cover, function (color) {
            ap.theme(`rgb(${color[0]}, ${color[1]}, ${color[2]})`, index);
        });
    }
};
ap.on('listswitch', (index) => {
    setTheme(index.index);
});
ap.list.add({name:'Friendships',artist:'Pascal Letoublon',url:'/media/music/welcome.mp3',cover:'/media/cover/welcome.png'});
ap.list.switch(0);
</script>
<footer style="text-align:center;">
<?php include 'include/bottom.php'; ?>
</footer>
<script src="js/hotload.js"></script>
</body>
</html>