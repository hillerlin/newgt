<?php if (!defined('THINK_PATH')) exit();?><script type="text/javascript">

</script>
<div class="bjui-pageHeader" style="background:#FFF;">
    <div style="padding: 0 15px;">
        <div style="float:left; width:500px;">
            <h4 style="margin-bottom:20px;">
            网站公告　<small></small>
        </h4>
            <div class="alert alert-info" role="alert" style="margin:0 0 5px; padding:10px;">
                <?php foreach($announcement_list as $list):?>
                <h5  style="font-size: 18px"><a href="<?php echo U('Announcement/detail', array('id' => $list['id']))?>" data-toggle="dialog" data-height="900" data-width="1200"  style="font-size: 18px"><?php echo $list['title']?></a><span class="pull-right" style="line-height:auto"><?php echo date('Y-m-d H:i:s', $list['addtime'])?></span></h5>
                <?php endforeach;?>
            </div>
        </div>
    </div>
</div>
<div class="bjui-pageContent">
    <div style="margin-top:5px; margin-right:50%; overflow:hidden;">
        <div class="row" style="padding: 0 8px;">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">待办事项：<span class="pull-right">共<?php echo $backlog['total']?>项未办</span></h3></div>
                    <div class="panel-body bjui-doc" style="padding:0; height: 100px">
                        <ul>
                            <?php foreach($backlog['list'] as $list) {?>
                            <li><a href="<?php echo U($list['controller'] . '/' .$list['action'])?>" data-toggle="navtab" data-id="<?php echo strtolower($list['controller']) .'-'. $list['action']?>" data-title="<?php echo $list['title']?>" style="font-size: 14px"><?php echo $list['description']?></a>
                                <span style="position:absolute;right:0;width:25%;line-height:auto"><?php echo date('Y-m-d H:i:s', $list['addtime'])?></span></li>
                            <?php }?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php if($admin['dp_id'] == 1) {?>
<!--            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">知识中心：<span class="pull-right">最新研报<?php echo $research_list['total']?><a href="<?php echo U('ResearchReport/more')?>" data-toggle="navtab" data-width="620" data-id="message-more" data-title='知识中心'>更多</a></span></h3></div>
                    <div class="panel-body bjui-doc" style="padding:0; height: 100px">
                        <ul>
                            <?php foreach($research_list['list'] as $list) {?>
                            <li><a href="<?php echo $list['path']?>" target="_blank"><?php echo getFilename($list['filename'])?></a>
                                <span style="position:absolute;right:0;width:25%;line-height:auto"><?php echo date('Y-m-d H:i:s', $list['add_time'])?></span></li>
                            <?php }?>
                        </ul>
                    </div>
                </div>
            </div>-->
            <?php }?>
        </div>
    </div>
    <div style="position:absolute;top:15px;right:0;width:50%;">
        <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">消息提醒：<span class="pull-right">共<?php echo $unReadNums?>项未读<a href="<?php echo U('Message/more')?>" data-toggle="dialog" data-width="620" data-id="message-more">更多</a></span></h3></div>
                    <div class="panel-body bjui-doc" style="padding:0; height: 100px">
                        <ul>
                            
                            <!--<li><a href="<?php echo U('tips/read')?>" data-toggle="doajax" data-id="<?php echo strtolower($list['controller']) .'-'. $list['action']?>" data-title="<?php echo $list['title']?>" style="font-size: 14px">aaaa</a>-->
                            <?php foreach($messages['list'] as $list) {?>
                            <li <?php echo $list['status'] == 0 ? '' : 'class="read"'?>>
                                <?php if($list['status'] == 0) {?>
                                <a id="read" href="<?php echo U('Message/read')?>" data-data='{"id":"<?php echo $list['id']?>"}' data-toggle="doajax" data-callback="read" data-id="message-read" data-title="<?php echo $list['title']?>" style="font-size: 14px"><?php echo html_entity_decode($list['description'])?></a>
                                <?php }else{ ?>
                                <?php echo html_entity_decode($list['description'])?>
                                <?php }?>
                                <span style="position:absolute;right:0;width:25%;line-height:auto"><?php echo date('Y-m-d H:i:s', $list['addtime'])?></span></li>
                            <?php }?>
                        </ul>
                    </div>
                </div>
            </div>
        <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">日历</h3></div>
                    
                    <div class="panel-body" style="padding:0; height: 500px">
                        <!--<a href="<?php echo U('Help/cal')?>" data-toggle="dialog" data-width="620" data-id="message-more">日历</a>-->
                        <div data-toggle="fullCalendar" data-width="620" data-id="message-more"></div>
                        <!--<div id='calendar'></div>-->
                    </div>
                </div>
            </div><!--
        <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">BJUI/GIT</h3></div>
                    
                    <div class="panel-body">
                        
                    </div>
                </div>
            </div>-->
    </div>
</div>
<script>
// $("li.read").each(function(i, domEle){
//     debugger;
//     var a,b,c;
//     a = $(domEle).html();
//     b = $(domEle).val();
//     c = $(domEle).text();
////     a = delHtmlTag(a);
// });
 $("li.read").text(function(n){
     $(this).html(RemoveAH($(this).html()) );
    });
// function delHtmlTag(str)
//{
//      return str.replace(/<[^>]+>/g,"");//去掉所有的html标记
//}
function RemoveAH ( strText )
{

    var regEx = /<code[^>]*>[^>]*<[^>]code>/g;
    var regEx = /(<code[^>]*>)|(<[^>]code>)/g;

    return strText.replace(regEx, "");

}
var options = {
    callback: 'cancel'
}

function read(aa) {
    console.log(aa);
    $(this).navtab('reloadForm', true);
    checkAlert();
}
//$("#read").bjuiajax('doAjax', options);
//$('#calendar').fullCalendar('option', 'height', 700);

</script>