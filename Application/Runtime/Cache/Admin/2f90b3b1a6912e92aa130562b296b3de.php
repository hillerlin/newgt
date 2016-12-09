<?php if (!defined('THINK_PATH')) exit();?><div class="bjui-pageHeader">
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
            <td align="center"><?php echo auditInit($v['pro_state']);?></td>
            <td align="center"> <a href="<?php echo U('Project/MyAuditProject', array('wf_id'=>$v['wf_id'],'pl_id'=>$v['pl_id'],'xml_id'=>$v['pro_xml_id'],'pj_id'=>$v['pj_id'],'pro_level'=>$v['pro_level'],'pro_times'=>$v['pro_times']))?>" class="btn btn-green"
                                   data-toggle="dialog" data-mask="true" data-height="1000" data-width="800" data-id="modify">点击审核</a></td>
        </tr>
        <?php endforeach;?>
        </tbody>
    </table>

</div>