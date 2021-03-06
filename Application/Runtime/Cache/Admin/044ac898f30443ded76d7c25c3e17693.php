<?php if (!defined('THINK_PATH')) exit();?><div class="bjui-pageHeader">
    <form id="pagerForm" data-toggle="ajaxsearch" action="<?php echo U('Company/lookupSupplier');?>" method="post">
        <input type='hidden' name='isSearch' value='1'>
        <div class="bjui-searchBar">
            <button data-url="<?php echo U('Company/add', array('type'=>1));?>" class="btn btn-blue" data-toggle="dialog" data-height='400' data-icon="plus" data-id="supplier-add">新增供应商</button>
            <label>公司名称：</label><input type="text" value="" name="company_name" size="10" />&nbsp;
            <button type="submit" class="btn-default" data-icon="search">查询</button>&nbsp;
            <a class="btn btn-orange" href="javascript:;" data-toggle="reloadsearch" data-clear-query="true" data-icon="undo">清空查询</a>&nbsp;
            <div class="pull-right">
                <input type="checkbox" name="lookupType" value="1" data-toggle="icheck" data-label="追加" checked>
                <!--<button type="button" class="btn-blue" data-toggle="lookupback" data-lookupid="ids" data-warn="请至少选择一项职业" data-icon="check-square-o">选择选中</button>-->
            </div>
        </div>
    </form>
</div>

<div class="bjui-pageContent">
    <table class="table table-hover table-striped" data-width="100%">
        <thead>
            <tr>
                <th data-order-field="name">公司名称</th>
                <th data-order-field="name">联系人</th>
                <th width="28"><input type="checkbox" class="checkboxCtrl" data-group="ids" data-toggle="icheck"></th>
                <th width="74">操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($list as $v) {?>
            <tr>
                <td><?php echo $v['company_name']?></td>
                <td><?php echo $v['company_linker']?></td>
                <td><input type="checkbox" name="ids" data-toggle="icheck" value="{supplier_id:'<?php echo $v['company_id']?>', supplier_name:'<?php echo $v['company_name']?>'}"></td>
                <td>
                    <a href="javascript:;" data-toggle="lookupback" data-args="{supplier_id:'<?php echo $v['company_id']?>', supplier_name:'<?php echo $v['company_name']?>'}" class="btn btn-blue" title="选择本项" data-icon="check">选择</a>
                </td>   
            </tr>
            <?php }?>
        </tbody>
    </table>
</div>
<div class="bjui-pageFooter">
    <ul>
        <li><button type="button" class="btn-close" data-icon="close">关闭</button></li>
        <li><button type="button" class="btn-default" data-toggle="lookupback" data-lookupid="ids" data-warn="请至少选择一项职业"  data-icon="save">确定</button></li>
    </ul>
</div>