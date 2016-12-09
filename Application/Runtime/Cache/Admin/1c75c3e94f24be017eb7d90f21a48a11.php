<?php if (!defined('THINK_PATH')) exit();?><style>
    .table td{
        text-align:center;
    }
</style>
<div class="bjui-pageHeader">
    <form id="pagerForm" data-toggle="ajaxsearch" action="/admin/Role/listName" method="post">
        <input type="hidden" name="pageCurrent" value="1">
        <input type="hidden" name="pageSize" value="${model.pageSize}">
        <input type="hidden" name="orderField" value="${param.orderField}">
        <div class="bjui-searchBar">
            <label>名称：</label><input type="text" value="" name="real_name" size="10">&nbsp;
            <button type="submit" class="btn-default" data-icon="search">查询</button>&nbsp;
            <a class="btn btn-orange" href="javascript:;" data-toggle="reloadsearch" data-clear-query="true" data-icon="undo">清空查询</a></li>
            <div class="pull-right">
            </div>
        </div>
    </form>
</div>
<div class="bjui-pageContent tableContent">
    <table data-toggle="tablefixed" data-width="100%">
        <thead>
        <tr>
            <th data-order-field="name">姓名</th>
            <th data-order-field="note">角色</th>
            <th width="74">操作</th>
        </tr>
        </thead>
        <tbody>
        <?php if(is_array($list)): foreach($list as $key=>$v): ?><tr>
            <td><?php echo ($v['real_name']); ?></td>
            <td><?php echo ($v['role_name']); ?></td>
            <td>
                <a href="javascript:;" data-toggle="lookupback" data-args="{adminId:'<?php  echo $v['admin_id'] ;?>', profession: '<?php  echo $v['real_name'] ;?>' , roleName:'<?php  echo $v['role_name'] ;?>' , roleId:'<?php echo $v['role_id'];?>'}" class="btn btn-blue" title="选择本项" data-icon="check">选择</a>
            </td>
        </tr><?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<div class="bjui-pageFooter">
    <div class="pages">
        <span>每页&nbsp;</span>
        <div class="selectPagesize">
            <select data-toggle="selectpicker" data-toggle-change="changepagesize">
                <option value="30">30</option>
                <option value="60">60</option>
                <option value="120">120</option>
                <option value="150">150</option>
            </select>
        </div>
        <span>&nbsp;条，共 <?php echo ($total); ?> 条</span>
    </div>
    <div class="pagination-box" data-toggle="pagination" data-total="<?php echo ($total); ?>" data-page-size="30" data-page-current="1">
    </div>
</div>