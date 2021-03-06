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
</script>
<div class="bjui-pageContent">
    <form action="<?php echo U('Project/save_project')?>" id="j_custom_form" data-toggle="validate" data-alertmsg="false">
        <input type="hidden" name="pro_id" value="<?php echo $pro_id?>">
        <table class="table table-condensed table-hover" width="100%">
            <tbody>
                <tr>
                    <td>
                        <label for="j_custom_name" class="control-label x120">项目名称：</label>
                        <input type="text" name="pro_title" id="p_name" value="<?php echo $pro_title?>" data-rule="required" size="15">
                    </td>
<!--                    <td>
                        <label for="j_custom_sale" class="control-label x120">项目编号：</label>
                        <input type="text" name="pro_no" id="p_name" value="<?php echo $pro_no?>" data-rule="required" size="15">
                    </td>-->
                </tr>
                <tr>
                    <td>
                        <label for="pro_account" class="control-label x120">融资金额：</label>
                        <input type="text" name="pro_account" id="p_account" value="<?php echo $pro_account?>" data-rule="required;money" size="15">
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="company_id" class="control-label x120">公司：</label>
                        <input type="hidden" name="company_id" value='<?php echo $company_id?>'>
                        <input type="text" name="company_name" id="p_company" value="<?php echo $company['company_name']?>" size="15" data-toggle="lookup" data-url="<?php echo U('Company/lookup')?>" data-width="600" data-height="300" data-rule="required" readonly>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="pro_type" class="control-label x120">反向保理：</label>
                        <input type="checkbox" name="pro_type" id="check" data-toggle="icheck" value="1" <?php echo $pro_type == 1 ? 'checked ' : ''?>>
                    </td>
                </tr>
                <tr <?php echo $pro_type == 1 ? '' : 'style="display: none"'?> id="reversed">
                    <td>
                        <label for="company_id" class="control-label x120">供应商：</label>
                        <input type="hidden" name="supplier_id" value='<?php echo $supplier_id?>'>
                        <input type="text" name="supplier_name" id="p_company" value="<?php echo $supplier_name?>" size="15" data-toggle="lookup" data-url="<?php echo U('Company/lookupSupplier')?>" data-width="600" data-height="300" readonly>
                    </td>
                </tr>
                <tr>
                    <td >
                        <label for="pro_desc" class="control-label x120">项目简介：</label>
                        <textarea name="pro_desc" id="j_custom_note_1" data-toggle="autoheight" cols="40" rows="2" ><?php echo $pro_desc?></textarea>
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
//        $('#abc').iCheck('check');
        $('#reversed').show();
        $('input[name="pro_type"]').attr('value','1')//补丁
        $('input[name="supplier_name"]').attr('data-rule', 'required');
    });
    $('input[name="pro_type"]').on('ifUnchecked', function (event) {
        $('#reversed').hide();
        $('input[name="supplier_name"]').removeAttr('data-rule');
    });
</script>