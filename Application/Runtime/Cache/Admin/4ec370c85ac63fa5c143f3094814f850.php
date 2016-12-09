<?php if (!defined('THINK_PATH')) exit();?><div class="bjui-pageHeader">
    <form id="pagerForm" data-toggle="ajaxsearch" action="<?php echo U('Company/lookup');?>" method="post">
        <input type='hidden' name='isSearch' value='1'>
        <div class="bjui-searchBar">
            <label>公司名称：</label><input type="text" value="" name="company_name" size="10" />&nbsp;
            <button type="submit" class="btn-default" data-icon="search">查询</button>&nbsp;
            <a class="btn btn-orange" href="javascript:;" data-toggle="reloadsearch" data-clear-query="true" data-icon="undo">清空查询</a>&nbsp;
        </div>
    </form>
</div>

<div class="bjui-pageContent">
    <table class="table table-hover table-striped" data-width="100%">
        <thead>
            <tr>
                <th data-order-field="name">公司名称</th>
                <th data-order-field="name">联系人</th>
                <th width="74">操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($list as $v) {?>
            <tr>
                <td><?php echo $v['company_name']?></td>
                <td><?php echo $v['company_linker']?></td>
                <td>
                    <a href="javascript:;" data-toggle="lookupback" data-args="{company_id:'<?php echo $v['company_id']?>', company_name:'<?php echo $v['company_name']?>'}" class="btn btn-blue" title="选择本项" data-icon="check">选择</a>
                </td>   
            </tr>
            <?php }?>
        </tbody>
    </table>
</div>
<div class="bjui-pageFooter">
    <ul>
        <li><button type="button" class="btn-close" data-icon="close">关闭</button></li>
    </ul>
</div>