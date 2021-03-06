<?php if (!defined('THINK_PATH')) exit();?><div class="bjui-pageHeader">
    <form id="pagerForm" data-toggle="ajaxsearch" action="<?php echo U('Project/auditList')?>" method="post">
        <input type="hidden" name="pageSize" value="${model.pageSize}">
        <input type="hidden" name="pageCurrent" value="${model.pageCurrent}">
        <input type="hidden" name="orderField" value="${param.orderField}">
        <input type="hidden" name="orderDirection" value="${param.orderDirection}">
        <div class="bjui-searchBar">
            <label>提交状态:</label>
            <select name="submit_type" data-toggle="selectpicker">
                <option value="">全部</option>
                <option value="0">草稿</option>
                <option value="1">已提交</option>
            </select>&nbsp;
            <label>项目标题：</label><input type="text" id="pro_no" value="" name="pro_title" class="form-control" size="10">&nbsp;
            <!--<button type="button" class="showMoreSearch" data-toggle="moresearch" data-name="custom"><i class="fa fa-angle-double-down"></i></button>-->
            <button type="submit" class="btn-default" data-icon="search">查询</button>&nbsp;
            <a class="btn btn-orange" href="javascript:;" data-toggle="reloadsearch" data-clear-query="true" data-icon="undo">清空查询</a>
        </div>
    </form>
</div>
<div class="bjui-pageContent tableContent">
    <table class="table table-bordered table-hover table-striped table-top" data-selected-multi="true">
        <thead>
        <tr>
            <th data-order-field="pro_no" align="center" width="300">项目编号</th>
            <th data-order-field="pro_title" align="center" width="300">项目标题</th>
            <th data-order-direction="asc" data-order-field="addtime" align="center" width="300">添加时间</th>
            <th data-order-field="company" align="center" width="300">公司</th>
            <th data-order-field="gt_uid" align="center" width="150">项管部跟进人</th>
            <th data-order-field="pro_status"  align="center" width="100">立项状态</th>
            <!--<th data-order-field="file"  align="center" width="100"></th>-->
            <th width="300" align="center">操作</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($list as $v){?>
        <tr data-id="<?php echo $v['pro_id']?>">
            <td align="center"><?php echo $v['pro_no']?></td>
            <td align="center"><?php echo $v['pro_title']?></td>
            <td align="center"><?php echo date('Y-m-d H:i:s', $v['addtime'])?></td>
            <td align="center"><?php echo $v['company_name']?></td>
            <td align="center"><?php echo empty($v['pmd_name']) ? '<code>未分配</code>' : $v['pmd_name']?>&nbsp;</td>
            <td align="center"><?php echo C('proLevel')[$v['pro_level']]?></td>
            <!--<td align="center"><?php echo $v['submit_status']==1?'已提交':'草稿'?></td>-->
            <td align="center">
                <?php if($v['submit_status'] == 0){?>
                <a href="<?php echo U('Project/submit', array('pro_id'=>$v['pro_id']))?>" class="btn btn-green" data-toggle="navtab" data-id="project-submit" data-reload-warn="本页已有打开的内容，确定将刷新本页内容，是否继续？" data-title="项目审核">提交</a>
                <a href="<?php echo U('Project/del', array('pro_id'=>$v['pro_id']))?>" class="btn btn-red" data-toggle="doajax" data-confirm-msg="确定要删除该行信息吗？">删</a>
                <?php }else{ ?>
                <a href="<?php echo U('Project/auditEdit', array('pro_id'=>$v['pro_id']))?>" class="btn btn-green" data-toggle="dialog" data-mask="true" data-height="900" data-width="800" data-id="project-audit" data-reload-warn="本页已有打开的内容，确定将刷新本页内容，是否继续？" data-title="<?php echo $workflow[$v['step_pid']][$v['pro_step']]['step_desc']?>"><?php echo $workflow[$v['step_pid']][$v['pro_step']]['step_desc']?></a>
                <a href="<?php echo U('Project/file', array('pro_id'=>$v['pro_id']))?>" class="btn btn-green" data-toggle="navtab" data-id="project-file" data-reload-warn="本页已有打开的内容，确定将刷新本页内容，是否继续？" data-title="资料包">资料包</a>
                <a href="<?php echo U('Project/edit', array('pro_id'=>$v['pro_id']))?>" class="btn btn-green" data-toggle="dialog" data-width='600' data-mask='true' data-id="project-edit" data-reload-warn="本页已有打开的内容，确定将刷新本页内容，是否继续？" data-title="编辑-项目">编辑</a>
                <?php if ($is_supper || $is_boss) :?>
                <a href="<?php echo U('Project/exchange', array('pro_id'=>$v['pro_id']))?>" class="btn btn-red" data-toggle="dialog" data-mask="true" data-height="300" data-width="500" data-id="project-exchange"><?php echo empty($v['pmd_name']) ? '分配跟进人' : '交接' ?></a>
                <?php endif; ?>
                <?php }?>
            </td>
        </tr>
        <?php }?>
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
        <span>&nbsp;条，共 <?php echo $total?> 条</span>
    </div>
    <div class="pagination-box" data-toggle="pagination" data-total="<?php echo $total?>" data-page-size="30" data-page-current="1">
    </div>
</div>




<!--
<div class="bjui-pageHeader">
    <label style="margin-top: 10px;margin-bottom: 10px;">我是：<?php echo ($name); ?></label>
    <table class="table table-bordered table-hover table-striped table-top" data-selected-multi="true">
        <thead>
        <tr>
            <th align="center" width="300">我的项目ID</th>
            <th align="center" width="300">项目状态</th>
            <th align="center" width="300">操作</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($list as $v):?>
        <tr>
            <td align="center"><?php echo $v['pj_id'];?></td>
            <td align="center"><?php echo auditInit($v['pro_state']).'-&#45;&#45;&#45;&#45;'.C('proLevel');?></td>
            <td align="center"> <a href="<?php echo U('Project/MyAuditProject', array('wf_id'=>$v['wf_id'],'pl_id'=>$v['pl_id'],'xml_id'=>$v['pro_xml_id'],'pj_id'=>$v['pj_id'],'pro_level'=>$v['pro_level'],'pro_times'=>$v['pro_times']))?>" class="btn btn-green"
                                   data-toggle="dialog" data-mask="true" data-height="1000" data-width="800" data-id="modify">点击审核</a></td>
        </tr>
        <?php endforeach;?>
        </tbody>
    </table>

</div>-->