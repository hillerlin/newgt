<?php if (!defined('THINK_PATH')) exit();?><style>
    .table th {
    text-align: center;
}

.content {
    margin-top: 20px;
}

fieldset span {
    display: inline-block;
    margin: 10px;
}
</style>
<div class="bjui-pageHeader">
    <button class="btn btn-red" style="margin-left: 20px;" onclick="del(this)">删除选择项</button>
    <button class="btn btn-green" style="margin-left: 20px;" onclick="addaction(this)">添加模块</button>
    <input type="text" name="actionname" id="actionname" placeholder="模块名 ，中文">
    <button class="btn btn-green" style="margin-left: 20px;" onclick="addmethod(this)">添加方法</button>
    <input type="text" name="actionname" id="methodname" placeholder="方法描述，中文">
    <input type="text" name="actionpath" id="methodpath" placeholder="路径 ， 英文">
<!--
    <input type="radio" name="whereis" value="suf"/> 后-->
    <?php $i=1; foreach(C('authpage') as $k => $v): ?>
    <input type="radio" name="whereis" value="<?php echo ($k); ?>"  <?php if(($i) == "1"): ?>checked<?php endif; ?>/> <?php echo ($v); ?>
    <?php $i++; endforeach; ?>
</div>
<div class="bjui-pageContent tableContent">
    <table class="table table-bordered table-hover table-striped table-top" data-selected-multi="true">
        <tr>
            <th>角色:</th>
            <td>
                <input type="text" name="custom.roleName" id="roleName" size="30" readonly >
            </td>
        </tr>
        <tr>
            <th>姓名:</th>
            <td>
                <input type="text" name="custom.profession" id="j_custom_profession" value="" readonly size="30" data-toggle="lookup" data-url="<?php echo U('/Admin/Role/listName');?>" data-group="custom" data-width="600"
                       data-height="300">
            </td>
        </tr>
    </table>
    <form action="/admin/Role/del" id="del_data" data-toggle="validate" data-alertmsg="false">
        <input type="hidden" name="custom.adminId" id="adminid">
        <div class="content" id="content">
        </div>
    </form>
</div>
<script type="text/javascript">
    $(function () {
        $('fieldset span a').click(function () {
            return false;
        });
        $('#j_custom_profession').on('afterchange.bjui.lookup', function(e, data) {
            $.ajax({
                url:'/admin/Role/showDetails',
                type:'post',
                dataType:'JSON',
                data:{'realname':data.value},
                success:function(element){
                    $('.content').html(element.html);
                }
            });
        })
    });
    function checkstatus(e){
        var parentRadio=$(e).closest('fieldset');
        parentRadio.children('input[type=radio]').prop('checked','checked');
        parentRadio.siblings('fieldset').find('input:checked').removeProp('checked');
    }

    //添加控制器
    function addaction(e) {
        var actionname = $('#actionname').val();
        //用户id
        var adminid = $('#adminid').val();
        if (actionname == '') {
            $(this).alertmsg('error', '请填写控制器名称和描述信息');
            return false;
        }
        if (adminid == '') {
            $(this).alertmsg('error', '请填写用户姓名与角色');
            return false;
        }
        $.ajax({
            url: '/admin/Role/savePage',
            type: 'POST',
            dataType: 'JSON',
            data: {'actionname': actionname,  'adminid': adminid, 'method': 'saveAction'},
            success: function (data) {
                if (data.status == 1) {
                    //需要添加新的栏位
                    var html = '<fieldset>';
                    html += '<input type="radio" name="action" value="'+actionname+'">';
                    html += '<legend>' + actionname + ' </legend> <br/>';
                    html += '</fieldset>';
                    $('#content').append(html);
                    $(this).alertmsg('ok', '控制器新增成功');
                } else if (data.status == 2) {
                    $(this).alertmsg('error', data.msg);
                }
            },
            error: function () {
                $(this).alertmsg('error', '服务器正忙');
            }
        });
    }
    //添加方法
    function addmethod(e) {
        var methodname = $('#methodname').val();
        var methodpath = $('#methodpath').val();
        var adminid = $('#adminid').val();
        var fix = $('input[name=whereis]:checked').val();
        var actionname = $('input[name=action]:checked');
        if (methodname == '' || methodpath == '') {
            $(this).alertmsg('error', '请填写方法名称和描述');
            return false;
        }
        if (actionname.val() == undefined) {
            $(this).alertmsg('error', '请选择至少一个控制器');
            return false;
        }

        $.ajax({
            url: '/admin/Role/savePage',
            type: 'POST',
            dataType: 'JSON',
            data: {'methodname': methodname, 'methodpath': methodpath, 'adminid': adminid, 'method': 'saveMethod', 'fix': fix, 'actionname': actionname.val()},
            success: function (data) {
                if (data.status == 1) {
                    //需要添加新的栏位

                    var html = '<span>' +
                            '<input type="checkbox" value="' + methodpath + '" name="'+fix+'_method[]" onclick="checkstatus(this)">' +
                            '<a href="' + methodpath + '">' + methodname + '</a>' +
                            '</span>';
                    actionname.siblings('.' + fix).append(html);
                    $(this).alertmsg('ok','新增操作成功');
                } else if (data.status == 2) {
                    $(this).alertmsg('error', data.msg);
                } else {
                    $(this).alertmsg('error', data.msg);
                }
            },
            error: function () {
                $(this).alertmsg('error', '服务器正忙');
            }
        });
    }
    function del(e) {
        $(this).alertmsg('confirm', '确认内容的提示信息！', {
            okCall: function () {
               $('#del_data').submit();
            }
        });
    }
</script>