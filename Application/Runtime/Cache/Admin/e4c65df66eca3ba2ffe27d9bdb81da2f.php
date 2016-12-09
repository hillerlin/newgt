<?php if (!defined('THINK_PATH')) exit();?><script type="text/javascript">
function pic_upload_success(file, data) {
    var json = $.parseJSON(data)
    
    $(this).bjuiajax('ajaxDone', json)
    if (json[BJUI.keys.statusCode] == BJUI.statusCode.ok) {
        $('#j_custom_pic').val(json.filename).trigger('validate')
        $('#j_custom_span_pic').html('<img src="'+ json.filename +'" width="100" />')
    }
}
function do_OK(json, $form) {
    console.log(json)
    console.log($form)
}
//护照有效日期  = 签发日期 + 10年
$('#j_custom_issuedate').on('afterchange.bjui.datepicker', function(e, data) {
    var pattern = 'yyyy-MM-dd'
    var start   = end = data.value
    
    end.setFullYear(start.getFullYear() + 10)
    end.setDate(start.getDate() - 1)
    
    $('#j_custom_indate').val(end.formatDate(pattern))
})
</script>
<div class="bjui-pageContent">
    <form action="<?php echo U('Project/save_project')?>" id="j_custom_form" data-toggle="validate" data-alertmsg="false">
        <table class="table table-condensed table-hover" width="100%">
            <tbody>
                <tr>
                    <td>
                        <label for="j_custom_name" class="control-label x120">项目名称：</label>
                        <input type="text" name="pro_title" id="p_name" value="" data-rule="required" size="15">
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="pro_account" class="control-label x120">融资金额：</label>
                        <input type="text" name="pro_account" id="p_account" value="" data-rule="required;integer[+]; range[0~]" size="15">元
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="company_id" class="control-label x120">公司：</label>
                        <input type="hidden" name="company_id" value='1'>
                        <input type="text" name="company_name" id="p_company" value="" size="15" data-toggle="lookup" data-url="<?php echo U('Company/lookup')?>" data-width="600" data-height="300" data-rule="required" readonly>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="pro_type" class="control-label x120">反向保理：</label>
                        <input type="checkbox" name="pro_type" value="1" id="check" data-toggle="icheck">
                    </td>
                </tr>
                <tr style="display: none" id="reversed">
                    <td>
                        <label for="company_id" class="control-label x120">供应商：</label>
                        <input type="hidden" name="supplier_id" value=''>
                        <input type="text" name="supplier_name" id="p_company" value="" size="30" data-toggle="lookup" data-url="<?php echo U('Company/lookupSupplier')?>" data-width="600" data-height="300" readonly>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="pro_type" class="control-label x120">已有合作项目：</label>
                        <input type="checkbox" name="pro_term" id="check" data-toggle="icheck">
                    </td>
                </tr>
                <tr style="display: none" id="redo">
                    <td>
                        <label for="company_id" class="control-label x120">已有项目名称：</label>
                        <input type="hidden" name="before_pro_id" value='0'>
                        <input type="text" name="befor_pro_title" id="p_company" value="" size="15" data-toggle="lookup" data-url="<?php echo U('Project/lookUp')?>" data-width="600" data-height="300" readonly>
                    </td>
                </tr>
                <?php if($admin['dp_id'] == 1) {?>
                <tr>
                    <td>
                        <label for="shorcut_flow" class="control-label x120">发起快捷流程：</label>
                        <input type="checkbox" name="shorcut_flow" value="1" id="check" data-toggle="icheck">
                    </td>
                </tr>
                <?php }?>
                <tr>
                    <td>
                        <label for="pro_desc" class="control-label x120">项目简介：</label>
                        <textarea name="pro_desc" id="j_custom_note_1" data-toggle="autoheight" cols="40" rows="2"></textarea>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>
<div class="bjui-pageFooter">
    <ul>
        <li><button type="button" class="btn-close" data-icon="close">取消</button></li>
        <li><button type="submit" class="btn-default" data-icon="save">保存</button></li>
    </ul>
</div>
<script>
    $('input[name="pro_type"]').on('ifChecked', function (event) {
        $('#reversed').show();
        $('input[name="supplier_name"]').attr('data-rule', 'required');
    });
    $('input[name="pro_type"]').on('ifUnchecked', function (event) {
        $('#reversed').hide();
        $('input[name="supplier_name"]').removeAttr('data-rule');
    });
    $('input[name="pro_term"]').on('ifChecked', function (event) {
        $('#redo').show();
        $('input[name="pro_title"]').attr('data-rule', 'required');
    });
    $('input[name="pro_term"]').on('ifUnchecked', function (event) {
        $('#redo').hide();
        $('input[name="pro_title"]').removeAttr('data-rule');
    });
</script>