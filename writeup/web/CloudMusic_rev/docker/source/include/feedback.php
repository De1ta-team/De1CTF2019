<?php
@$feed_name=$_POST['feed_name'];
@$feed_phone=$_POST['feed_phone'];
@$feed_content=$_POST['feed_content'];

if (isset($feed_name)&&isset($feed_phone)&&isset($feed_content)){
    $feed_name=(string) $feed_name;
    $feed_phone=(string) $feed_phone;
    $feed_content=(string) $feed_content;
    if (strlen($feed_name)<=0||strlen($feed_phone)<=0||strlen($feed_content)<=0){
        ob_end_clean();
        die(json_encode(array('status'=>0,'info'=>'Format or length check failed.')));
    }else{
        ob_end_clean();
        sleep(1);
        die(json_encode(array('status'=>1,'info'=>'Hi '.$feed_name.'. Thanks for your advice. Our admin will check it soon.')));
    }
}
?>
<script>nav_active('feedback');nav_user('<?php echo @$_SESSION['user']; ?>');</script>
        <div>
<div class="container" style="margin-top:30px">
    <h3 style="margin-bottom:15px;">Feedback</h3>
    <div class='form-container' style="text-align: right;font-size:16px;">
        <div class='form-group' style="height:40px;margin-top:20px">
            <label for="feed_name" class="col-sm-1 control-label">Name：</label>
            <div class="col-sm-11">
                <input type="text" class="form-control" placeholder="Name" id="feed_name">
            </div>
        </div>
        <div class='form-group' style="height:40px;margin-top:20px">
            <label for="feed_phone" class="col-sm-1 control-label">Telephone：</label>
            <div class="col-sm-11">
                <input type="text" class="form-control" placeholder="Telephone" id="feed_phone">
            </div>
        </div>
        <div class='form-group' style="height:160px;margin-top:20px">
            <label for="feed_content" class="col-sm-1 control-label">Content：</label>
            <div class="col-sm-11">
                <textarea class="form-control" rows="7" id="feed_content" style="margin-left:10px"></textarea>
            </div>
        </div>
        <div class="form-group" style="text-align: left;">
            <div class="col-sm-offset-2 col-sm-10">
                <button id="submit_button" type="submit" class="btn btn-default" style="width:160px;margin-left:-80px" onclick="submit()">Submit</button>
            </div>
        </div>  
    </div>
    <div id="info" class="alert" role="alert" style="display: none;margin-top: 80px"></div>
</div>
<script>
function submit(){
    $('#info').hide();
    $('#submit_button').attr("disabled","disabled");
    $.ajax({
        type: 'POST',
        url: "/hotload.php?page=feedback",
        data: {feed_name: $('#feed_name').val(), feed_phone: $('#feed_phone').val(), feed_content: $('#feed_content').val()},
        dataType: "json",
        success: function(data) {
            if (data.status==1){
                $('#info').removeClass('alert-danger');
                $('#info').addClass('alert-success');
            }else{
                $('#info').removeClass('alert-success');
                $('#info').addClass('alert-danger');
            }
            $('#info').html(data.info);
            $('#info').show();
            $('#submit_button').removeAttr("disabled");
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