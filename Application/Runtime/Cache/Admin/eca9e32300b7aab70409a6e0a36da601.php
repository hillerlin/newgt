<?php if (!defined('THINK_PATH')) exit(); if(is_array($list)): foreach($list as $k=>$v): ?><fieldset>
    <input type="radio" name="action" value="<?php echo ($k); ?>">
    <legend>
        <?php echo ($k); ?>
        <input type="checkbox" name="delaction" value="true" onclick="checkstatus(this)">
    </legend>
    <br/>
    <?php unset($v['name']); foreach($v as $subk => $subv): ?>
        <?php echo C('authpage')[$subk]; ?>
        <div class="<?php echo ($subk); ?>">
        <?php if(is_array($subv)): foreach($subv as $prek=>$prev): ?><span><input type="checkbox" value="<?php echo ($prek); ?>" name="<?php echo ($subk); ?>_method[]" onclick="checkstatus(this)"><a href="<?php echo ($prek); ?>" onclick="javascript:return false;"><?php echo ($prev); ?></a></span><?php endforeach; endif; ?>
        </div>
    <?php endforeach; ?>
</fieldset><?php endforeach; endif; ?>