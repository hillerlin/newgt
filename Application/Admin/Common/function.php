<?php

function check_verify($code, $id = '') {
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

/**
 * 取验证码hash值
 *
 * @param
 * @return string 字符串类型的返回结果
 */
function getHash() {
    return substr(md5(__SELF__), 0, 8);
}

/* 生成哈希链接 */

function U_hash($link) {
    return U($link, array('nchash' => getHash()));
}

function random($length, $numeric = 0) {
    $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
    $hash = '';
    $max = strlen($seed) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $seed{mt_rand(0, $max)};
    }
    return $hash;
}

function makeSeccode($nchash) {
    $seccode = random(6, 1);
    $seccodeunits = '';

    $s = sprintf('%04s', base_convert($seccode, 10, 23));
    $seccodeunits = 'ABCEFGHJKMPRTVXY2346789';
    if ($seccodeunits) {
        $seccode = '';
        for ($i = 0; $i < 4; $i++) {
            $unit = ord($s{$i});
            $seccode .= ($unit >= 0x30 && $unit <= 0x39) ? $seccodeunits[$unit - 0x30] : $seccodeunits[$unit - 0x57];
        }
    }
    session('seccode', $nchash);
    //cookie('seccode'.$nchash, encrypt(strtoupper($seccode)."\t".(time())."\t".$nchash,MD5_KEY),3600);
    return $seccode;
}

//上传文件
function upload_file($savepath, $field, $short_name = '') {
    $upload = new \Think\Upload(C('UPLOAD_CONFIG'));
    $upload->savePath = $savepath;
    $upload->subName = $field;
    $upload->saveName = array('uniqid', $short_name);
    $upload_info = $upload->upload();
    if (!$upload_info) {
        return $upload->getError();
    } else {
        $file_path = '/Uploads' . ltrim($upload_info[$field]['savepath'], '.') . $upload_info[$field]['savename'];
        $upload_info[$field]['file_path'] = $file_path;
        return $upload_info[$field];
    }
}

//上传文件夹
function upload_document($savepath, $field, $short_name = '') {
    $upload = new \Think\Upload(C('UPLOAD_CONFIG'));
    $upload->savePath = $savepath;
    $upload->subName = $field;
    $upload->saveName = array(array('\Admin\Lib\FileTree', 'generateFileName'), '__FILE__');
    $upload->isDocument = true;
    $upload_info = $upload->upload();
    if (!$upload_info) {
        return $upload->getError();
    } else {
//        $file_path = '/Uploads' . ltrim($upload_info[$field]['savepath'], '.') . $upload_info[$field]['savename'];
//        $upload_info[$field]['file_path'] = $file_path;
        return $upload_info;
    }
}

//创建文件名称
function create_name($filename) {
    $paths = explode("###", rtrim($_POST['paths'], "###"));
    foreach ($paths as $key => $val) {
        $filenames[$key] = basename($val);
    }
    //查出文档对应的id
    $filekey = array_search($filename, $filenames);
    $path = $paths[$filekey];
    $pathArr = explode('/', dirname($path));
    //拿到文件夹名称
    $document_name = array_pop($pathArr);
    var_dump($document_name);
    $file = D('ProjectFile')->select();
    $file_ids = array_switch_key($file, 'file_id');
    foreach ($file_ids as $key => $value) {
        $file_names[$key] = $value['file_name'];
    }
//    var_dump($file_names);
//    var_dump(array_search($document_name, $file_names));
    $pre_file_name = '';
    if ($file_id = array_search($document_name, $file_names)) {
        $pre_file_name = $file_id . '-' . $file_ids[$file_id]['short_name'] . '-';
        $pre_file_name = uniqid($pre_file_name);
    }

    return $pre_file_name;
}

//检查文件sha1值是否存在
function my_file_exists($data) {
    $pro_id = session('pro_id');
    if (D('ProjectAttachment')->sha1Exists($pro_id, $data['sha1'])) {
        return true;
    }
    return false;
}

function debt_tr_class($status, $endtime) {
    $class = '';
    if ($status == 0) {
        $class = 'active';
    } else {
        $now = time();
        $diff = $endtime - $now;
        if ($diff > 0) {
            $days = $diff / 86400;
            if ($days <= 30) {
                $class = 'danger';
            } elseif ($days <= 60) {
                $class = 'warning';
            } elseif ($days <= 90) {
                $class = 'info';
            }
        } else {
            $class = 'active';
        }
    }
    return $class;
}

//判断是否为超级管理员
function isSupper() {
    $admin = session('admin');
    if ($admin['is_supper']) {
        return true;
    }
    return false;
}

//判断是否是总监级别
function isBoss() {
    $admin = session('admin');
    if ($admin['position_id'] == 1) {
        return true;
    }
    return false;
}

//判断是否为项管部总监
function isPmdBoss() {
    $admin = session('admin');
    if ($admin['position_id'] == 1 && $admin['dp_id'] == 1) {
        return true;
    }
    return false;
}

/**
 * 返回项目债权状态
 * @param string $status 状态值
 * @return string
 */
function debtStatusStr($status) {
    return Admin\Model\ProjectDebtDetailModel::debtStatusStr($status);
}

//是否商票背书
function debtTypeStr($type) {
    return $type == 0 ? '否' : '是';
}

/**
 * 
 * @param type $tmpId
 * @param type $admin_id
 * @param type $is_role
 * @return boolaen
 */
function push($tmpId, $admin_id, $is_role) {
    $msgTmp = new Admin\Lib\MsgTmp($tmpId);
    $pushMsg = new \Admin\Lib\PushMsg($msgTmp);
    return $pushMsg->push($admin_id, $is_role);
}

/**
 * 记录操作日志
 * @param type $action
 * @param type $author
 * @param string $message
 */
function adminLog($action, $admiId, $author, $message) {
    $message = $action . ';' . $admiId . ';' . $author . ';' . $message;
    $destination = C('LOG_PATH') . '/Operation/' . date('y_m_d') . '.log';
    \Think\Log::write($message, 'INFO ', 'File', $destination);
}

//返回前端要求的格式的扩展名
function getFormerExts() {
    $upload_config = C('UPLOAD_CONFIG');
    $exts = $upload_config['exts'];
    foreach ($exts as & $ext) {
        $ext = '*.' . $ext;
    }
    return implode(';', $exts);
}

//去掉文件名后缀
function getFilename($filename) {
    $arr = explode('.', $filename);
    return $arr[0];
}
//计算二维数组中指定列的和
function getcolvars($arr,$colname){
    if(empty($arr))return 0;
    if(count($arr,1)==count($arr)){
        return 0;
    }
    $colvalues=array_column($arr,$colname);
    $result=0;
    foreach (array_count_values($colvalues ) as $k => $v) {
        $result+=$k*$v;
    }
    return $result;
}
//查找部门名字
function getbname($arr,$bid){
    if(empty($arr)) return '';
    foreach ($arr as  $v) {
        if($v['branch_id']==$bid){
            return $v['branch_name'];
        }
    }
    return '';
}
//记录日志
function put($content)
{
    $content .= "\r\n";
    $path = "./log.txt";
    if (file_exists($path)) {
        file_put_contents("./log.txt", $content, FILE_APPEND);

    } else {
        $fopen = fopen($path, 'wb');//新建文件命令
        fputs($fopen, $content);//向文件中写入内容;
        fclose($fopen);
    }

}

//根据roleId处理sql的搜索
function logicRoleId($roleId)
{
    switch ($roleId)
    {
        case 32:
            return true;
            break;
        default:
            return false;
            break;
    }

}

//跨模块访问
function logic($name)
{
    return $name ? D(ucfirst($name),'Logic'):null;
}
//返回审核对应的信息
function auditInit($auditType)
{
    switch ($auditType){
        case 0:
            return '待审核';
        break;
        case 1:
            return '审核中';
        break;
        case 2:
            return '已审核';
        break;
        case 3:
            return '驳回';
        break;
    }
}
//根据前一个指派xmlId返回下一个执行人的信息
//$type是判断是水平审批还是垂直审批 0是垂直审批 1是水平审批
function xmlIdToInfo($id,$file,$type=0)
{
    $xmlObj=logic('xml');
    $xmlObj->file=$file;
    $list = $xmlObj->index();
    foreach ($list as $k=>$v)
    {
        if($k==$id)
        {
            if($v['value'])
            {
                foreach($v['value'] as $kk=>$vv)
                {
                    if($vv['tag']=='BPMN2:OUTGOING')//指出的线
                    {
                        // $id=$vv['value'];
                         return  xmlIdToInfo($vv['value'],$file);
                    }
                }
            }else
            {
                return $v;
            }
        }

    }
}
//根据xml的name返回下一级xml的id和name
function xmlNameToIdAndName($name,$file)
{
    $xmlObj=logic('xml');
    $xmlObj->file=$file;
    $xmlInfo = $xmlObj->index();
    foreach ($xmlInfo as $k=>$v)
    {
        //$xmlName=explode('_',$v['name'])[0];
        if($v['name']==$name) //新建的config的第一项必须跟xml的第一步相等
        {
            return xmlIdToInfo($k,$file);
        }
    }
}
//根据xml的name返回当前的信息
function xmlNameToLoacalInfo($name,$file)
{
    $xmlObj=logic('xml');
    $xmlObj->file=$file;
    $xmlInfo = $xmlObj->index();
    foreach ($xmlInfo as $k=>$v)
    {
        //$xmlName=explode('_',$v['name'])[0];
        if($v['name']==$name) //新建的config的第一项必须跟xml的第一步相等
        {
            return $v;
        }
    }
}
//根据role_name返回role_id
function roleNameToid($name)
{
    $list=D('Role')->where(array('role_name'=>array('like',"%$name%")))->find();
    return $list['role_id'];
}
//根据adminId返回adminName
function adminNameToId($adminId)
{
    $adminInfo=D('Admin')->where("`admin_id`=%d",array($adminId))->field('real_name')->find();
    return $adminInfo['real_name'];
}


//根据项目的状态返回相应的操作事件
function projectToAction($authType,$pageAuth,$middleType='pre')
{
     $attrInde=C('proLevel')[$authType];
     return $pageAuth[$attrInde][$middleType];

}

/******
 * @param $proLevel 审批级别
 * @param $sender   送审人全称
 * @param $receive  审批人 25|role or admin  role是roleId  admin是adminId
 * @param $time     时间
 * @param $proId    项目Id
 * @param $proNmae    项目名称
 * @param $specialType   特殊类型   比如项管专员发送知情通知
 */

function redisCollect($proLevel,$sender,$receive='',$time,$proId,$specialMessage=null,$specialType=null)
{
    $type=auditMainProcessType($proLevel);
    $adminAttr=D('Admin')->where("`admin_id`=%d",array($sender))->field('admin_id,real_name')->find();
    $sender=$adminAttr['real_name'];//送审人的姓名
    $receiveAttr=explode('|',$receive);
    //查出是受审人是部门形式还是管理员形式
    if($receiveAttr[1]=='role')
    {
        $receiveObj=D('Role')->where("`role_id`=%d",array($receiveAttr[0]))->field('role_name')->find();
        $receive=$receiveObj['role_name'];
        $authorType='role';
    }else
    {
        $receiveObj=D('Admin')->where("`admin_id`=%d",array($receiveAttr[0]))->field('real_name')->find();
        $receive=$receiveObj['real_name'];
        $authorType='admin';
    }
    //查出项目名字
        $proName=projectNameFromId($proId);
        $contents='';
        $redisKey='Type:'.$type.':Time:'.date('Ymd',$time);
    //用集合记录录入的时间
    if(!S()->sIsMember('sType:'.$type,date('Ymd',time())))
    {
        S()->sAdd('sType:'.$type,date('Ymd',time()));
    }
    switch ($proLevel)
    {
        case '0':
            //项目经理立项
            $contents='项目经理<code>'.$sender.'</code>新建项目<code>'.$proName.'</code>';
            break;
        case '0_1':
            //项目总监分配人手
            $contents='项管总监<code>'.$sender.'</code>将项目<code>'.$proName.'</code>分配给：<code>'.$receive.'</code>';
            break;
        case '0_2':
            //项管专员归档
            $contents='项管专员<code>'.$sender.'</code>将项目<code>'.$proName.'</code>归档';
            break;
        case '0_3':
            //项管专员归档
            $contents='项管专员<code>'.$sender.'</code>将项目<code>'.$proName.'</code><code>归档完成，并结束立项</code>';
            break;
        case '4':
            $contents='项管专员<code>'.$sender.'</code>发起项目<code>'.$proName.'</code>通知知情事宜';
            break;
        case '4_1':
            $contents='项管总监<code>'.$sender.'</code>发起项目<code>'.$proName.'</code>知情给：<code>'.$receive.'</code>';
            break;
        case '5':
            $contents='项管专员<code>'.$sender.'</code>新建项目<code>'.$proName.'</code>风控审核子流程';
            break;
        case '5_1':
            $contents='项管总监<code>'.$sender.'</code>发起项目<code>'.$proName.'</code>风控审核通知给：<code>'.$receive.'</code>';
            break;
        case '6':
            $contents='项管专员<code>'.$sender.'</code>新建项目<code>'.$proName.'</code>立项会';
            break;
        case '6_1':
            $contents='项管总监<code>'.$sender.'</code>将项目<code>'.$proName.'</code>召开立项会事宜通知：<code>'.$receive.'</code>';
            break;
        case '7':
            $contents='风控总监<code>'.$sender.'</code>新建项目<code>'.$proName.'</code>风控报告';
            break;
        case '7_2':
            $contents='项管专员<code>'.$sender.'</code>已完成项目<code>'.$proName.'</code>风控报告的审核';
            break;
        case '8':
            $contents='项管专员<code>'.$sender.'</code>新建项目<code>'.$proName.'</code>风控会';
            break;
        case '8_1':
            $contents='项管总监<code>'.$sender.'</code>将项目<code>'.$proName.'</code>风控会事宜通知：<code>'.$receive.'</code>';
            break;
        case '9':
            $contents='项管专员<code>'.$sender.'</code>新建项目<code>'.$proName.'</code>投委会';
            break;
        case '9_1':
            $contents='项管总监<code>'.$sender.'</code>将项目<code>'.$proName.'</code>投委会事宜通知：<code>'.$receive.'</code>';
            break;
        case -1:
            $contents=$specialMessage;
            break;
    }
    $redisValue=array($time=>json_encode(array('contents'=>$contents,'time'=>$time,'proId'=>$proId,'authorType'=>$authorType)));
    return S()->hMset($redisKey,$redisValue);
}
//整合redis的消息发送
function redisTotalPost($proLevel,$sender,$receive,$time,$proId,$plId,$specialMessage=null,$specialType=null)
{
   return redisPostAudit($proLevel,$sender,$receive,$time,$proId,$plId,$specialMessage,$specialType) && redisCollect($proLevel,$sender,$receive,$time,$proId,$specialMessage,$specialType);

}
//待我审核事项
function redisPostAudit($proLevel,$sender,$receive='',$time,$proId,$plId,$specialMessage=null,$specialType=null)
{
    //查出项目名字
    $proName=projectNameFromId($proId);
    $adminAttr=D('Admin')->where("`admin_id`=%d",array($sender))->field('admin_id,real_name')->find();
    $sender=$adminAttr['real_name'];//送审人的姓名
    $receiveAttr=explode('|',$receive);
    //查出是受审人是部门形式还是管理员形式
    if($receiveAttr[1]=='role')
    {
        //$receiveObj=D('Role')->where("`role_id`=%d",array($receiveAttr[0]))->field('role_name')->find();
       // $receive=$receiveObj['role_name'];
        $redisKey='role:'.$receiveAttr[0];
        $authorType='role';
    }else
    {
        //$receiveObj=D('Admin')->where("`admin_id`=%d",array($receiveAttr[0]))->field('real_name')->find();
        //$receive=$receiveObj['real_name'];
        $redisKey='admin:'.$receiveAttr[0];
        $authorType='admin';
    }
    switch ($proLevel)
    {
        case '0':
            //项目经理立项
            $contents='项目经理:<code>'.$sender.'</code>新建项目<code>'.$proName.'</code>';
            break;
        case '0_1':
            //项目总监分配人手
            $contents='项管总监<code>'.$sender.'</code>将项目<code>'.$proName.'</code>分配给我！';
            break;
        case '0_2':
            //项管专员归档
            $contents='项管专员<code>'.$sender.'</code>将项目<code>'.$proName.'</code>归档';
            break;
        case '0_3':
            //项管专员归档
            $contents='项管专员<code>'.$sender.'</code>将项目<code>'.$proName.'</code><code>归档完成，并结束立项</code>';
            break;
        case '4':
            $contents='项管专员<code>'.$sender.'</code>发起项目<code>'.$proName.'</code>通知知情事宜';
            break;
        case '4_1':
            $contents='项管总监<code>'.$sender.'</code>发起项目<code>'.$proName.'</code>知情给我';
            break;
        case '5':
            $contents='项管专员<code>'.$sender.'</code>新建项目<code>'.$proName.'</code>风控审核子流程';
            break;
        case '5_1':
            $contents='项管总监<code>'.$sender.'</code>发起项目<code>'.$proName.'</code>风控审核通知给我';
            break;
        case '6':
            $contents='项管专员<code>'.$sender.'</code>新建项目<code>'.$proName.'</code>立项会';
            break;
        case '6_1':
            $contents='项管总监<code>'.$sender.'</code>将项目<code>'.$proName.'</code>召开立项会事宜通知我';
            break;
        case '7':
            $contents='风控总监<code>'.$sender.'</code>新建项目<code>'.$proName.'</code>风控报告';
            break;
        case '7_2':
            $contents='项管专员<code>'.$sender.'</code>已完成项目<code>'.$proName.'</code>风控报告的审核';
            break;
        case '8':
            $contents='项管专员<code>'.$sender.'</code>新建项目<code>'.$proName.'</code>风控会';
            break;
        case '8_1':
            $contents='项管总监<code>'.$sender.'</code>将项目<code>'.$proName.'</code>风控会事宜通知我';
            break;
        case '9':
            $contents='项管专员<code>'.$sender.'</code>新建项目<code>'.$proName.'</code>投委会';
            break;
        case '9_1':
            $contents='项管总监<code>'.$sender.'</code>将项目<code>'.$proName.'</code>投委会事宜通知我';
            break;
        case -1:
            $contents=$specialMessage;
            break;
    }
    $redisValue=array($plId=>json_encode(array('contents'=>$contents,'time'=>$time,'proId'=>$proId,'plId'=>$plId,'authorType'=>$authorType)));
    return S()->hMset($redisKey,$redisValue);

}
//根据项目id查出项目名字
function projectNameFromId($proId)
{
    $projectObj=D('Project')->where("`pro_id`=%d",array($proId))->field('pro_title')->find();
    return $projectObj['pro_title'];
}
//返回子流程所属大流程所属大类
function auditMainProcessType($proLevel)
{
    foreach (C('messAuth') as $k=>$v)
    {
        if(in_array($proLevel,$v['sub']))
        {
            return $k;
        }
    }
}
//返回驳回重新提交模块
/*****
 * @param $wfId
 * @param $proIid
 * @param $proRebutterLevel
 * @param $proTimes
 * @param $admin
 * @param $proRebutter
 * @param $xmlId
 * @param $plId
 * @param $type list 是数组模式回传  one 是bool类型回传
 * @return array
 */
function postRebutter($wfId,$proIid,$proRebutterLevel,$proTimes,$admin,$proRebutter,$xmlId,$plId,$type='list')
{
    //审批流入库处理
    $pjWorkFlow = D('PjWorkflow')->where("`wf_id`=%d",array($wfId))->data(array('pj_id' => $proIid, 'pj_state' => '待审核', 'pro_level_now' => $proRebutterLevel, 'pro_times_now' => $proTimes))->save();
    $sendProcess = D('SendProcess')->data(array('wf_id' => $wfId,'sp_message'=>'已提交', 'sp_author' => $admin['admin_id'], 'sp_addtime' => time(), 'sp_role_id' => $admin['role_id']))->add();
    $workFlowLog = D('WorkflowLog')->data(array(
        'sp_id' => $sendProcess, 'pj_id' => $proIid, 'pro_level' => $proRebutterLevel, 'pro_times' => $proTimes, 'pro_state' => 0, 'pro_addtime' => time(),'pro_author'=>$proRebutter,
        'wf_id' => $wfId, 'pro_xml_id' => $xmlId
    ))->add();
    $oldworkFlowLog=D('WorkflowLog')->where("`pl_id`=%d",array($plId))->data(array('pro_state'=>2))->save();
    $contents=$admin['role_name'].'<code>'.$admin['real_name'].'</code>重新提交<code>被驳回</code>项目<code>'.projectNameFromId($proIid).'</code>';
    $redisPost = redisTotalPost(-1, $admin['admin_id'], $proRebutter . '|admin', time(), $proIid, $workFlowLog,$contents,-1);
    if($type=='list')
    {

        return array($pjWorkFlow,$sendProcess,$workFlowLog,$redisPost && $oldworkFlowLog);

    }elseif($type=='one')
    {
        return $pjWorkFlow && $sendProcess && $workFlowLog && $oldworkFlowLog && $redisPost;
    }
}
//返回执行完下一步的流程模块
/******
 * @param $wfId
 * @param $proLevel
 * @param $proTimes
 * @param $admin
 * @param $proIid
 * @param $proRoleId  有roleId  就传 实参
 * @param $proAdminId 有adminId 就传 实参
 * @param $xmlId
 * @param $plId
 * @param $type list 是数组模式回传  one 是bool类型回传
 * @return bool
 */
function postNextProcess($wfId,$proLevel,$proTimes,$admin,$proIid,$proRoleId=0,$proAdminId=0,$xmlId,$plId,$type='list',$specialMessage=null,$specialType=null)
{
    $explodeLevel=explode('_',$proLevel);//拼接审批轮次
    if(!$explodeLevel[1])
    {
        $newLevel=$explodeLevel[0].'_1';
    }
    else
    {
        $modify=$explodeLevel[1]+1;
        $newLevel=$explodeLevel[0].'_'.$modify;
    }
    $pjWorkFlow = D('PjWorkflow')->where("`wf_id`=%d",array($wfId))->data(array( 'pj_state' => '待审核', 'pro_level_now' => $newLevel, 'pro_times_now' => $proTimes))->save();
    $sendProcess = D('SendProcess')->data(array('wf_id' => $wfId,'sp_message'=>'已提交', 'sp_author' => $admin['admin_id'], 'sp_addtime' => time(), 'sp_role_id' => $admin['role_id']))->add();
    $workFlowLog = D('WorkflowLog')->data(array(
        'sp_id' => $sendProcess, 'pj_id' => $proIid, 'pro_level' => $newLevel, 'pro_times' => $proTimes, 'pro_state' => 0, 'pro_addtime' => time(),'pro_role'=>$proRoleId,'pro_author'=>$proAdminId,
        'wf_id' => $wfId, 'pro_xml_id' => $xmlId
    ))->add();
    $oldworkFlowLog=D('WorkflowLog')->where("`pl_id`=%d",array($plId))->data(array('pro_state'=>2))->save();
    $adminValue=($proRoleId>0)?$proRoleId:$proAdminId;
    $adminType=($proRoleId>0)?'|role':'|admin';
    $noticeType=isset($specialType)?$specialType:$proLevel;
    $redisPost = redisTotalPost($noticeType, $admin['admin_id'], $adminValue . $adminType, time(), $proIid, $workFlowLog,$specialMessage,$specialType) && $oldworkFlowLog===false?false:true;

    if($type=='list')
    {
        return array($pjWorkFlow,$sendProcess,$workFlowLog,$redisPost && $oldworkFlowLog);

    }elseif($type=='one')
    {
        return $pjWorkFlow===false?false:true && $sendProcess===false?false:true && $workFlowLog===false?false:true && $oldworkFlowLog===false?false:true && $redisPost;
    }
}

/******
 * 新增子流程
 * @param $result 项目id
 * @param $pro_level
 * @param $admin
 * @return array
 */
function addSubProcess($result,$pro_level,$admin,$xmlfile)
{
    $xmlId=xmlNameToIdAndName(C('proLevel')[$pro_level],$xmlfile)['TARGETREF'];
    //审批流入库处理
    $pjWorkFlow = D('PjWorkflow')->data(array('pj_id' => $result, 'pj_state' => '待审核', 'pro_level_now' => $pro_level, 'pro_times_now' => '1'))->add();
    $sendProcess = D('SendProcess')->data(array('wf_id' => $pjWorkFlow,'sp_message'=>'已提交', 'sp_author' => $admin['admin_id'], 'sp_addtime' => time(), 'sp_role_id' => $admin['role_id']))->add();
    $workFlowLog = D('WorkflowLog')->data(array(
        'sp_id' => $sendProcess, 'pj_id' => $result, 'pro_level' => $pro_level, 'pro_times' => 1, 'pro_state' => 0, 'pro_addtime' => time(),'pro_author'=>$admin['admin_id'],
        'wf_id' => $pjWorkFlow, 'pro_role' => $admin['role_id'], 'pro_xml_id' =>$xmlId
    ))->add();
    $redisPost = redisTotalPost($pro_level, $admin['admin_id'], $admin['admin_id'] . '|admin', time(), $result, $workFlowLog);
    return $pjWorkFlow && $sendProcess && $workFlowLog && $redisPost;
}
//往project库里添加子流程的审核人
/**** $auditor_id $auditor_name为null时就只存pro_subprocess_desc
 * @param $pjId
 * @param $auditor_id
 * @param $auditor_name
 * @param $pro_level
 * @param $pro_subprocess_desc 备注
 */
function addSubProcessAuditor($pjId,$auditor_id,$auditor_name,$pro_level,$pro_subprocess_desc)
{
    $projectModel = D('Project');
    //因为是新建子流程  还有个提交的流程  所以不能做拼接
    //$explodeLevel=explode('_',$pro_level);//拼接审批轮次
/*    if(!$explodeLevel[1])
    {
        $newLevel=$explodeLevel[0].'_1';
    }
    else
    {
        $modify=$explodeLevel[1]+1;
        $newLevel=$explodeLevel[0].'_'.$modify;
    }*/
    //往project表添加状态
    $finishStatusJson = $projectModel->where("`pro_id`=%d", array($pjId))->field('finish_status')->find();
    //将adminid和adminname转数组
    $auditor_id = explode(',', $auditor_id);
    $auditor_name = explode(',', $auditor_name);
    $finish_status = json_decode($finishStatusJson['finish_status'],true);
    //$proKeys = array_keys($finish_status);
    foreach ($auditor_id as $k => $v) {
        $finish_status[$pro_level][$k]['adminId'] = $v;
        $finish_status[$pro_level][$k]['adminName'] = $auditor_name[$k];

    }
    $finish_enjson = json_encode($finish_status);
    $proSubprocessDesc='pro_subprocess'.explode('_',$pro_level)[0].'_desc'; //如果4_1 4_2 4_3 还原成4
    if(!$auditor_id || !$auditor_name)
    {
        $oldProject = $projectModel->where("`pro_id`=%d", array($pjId))->data(array($proSubprocessDesc => $pro_subprocess_desc))->save();
    }
    else
    {
        $oldProject = $projectModel->where("`pro_id`=%d", array($pjId))->data(array($proSubprocessDesc => $pro_subprocess_desc, 'finish_status' => $finish_enjson))->save();

    }
    return $oldProject===false?false:true;
}
//新审批轮次加1
function addNewLevel($proLevel)
{
    $explodeLevel=explode('_',$proLevel);//拼接审批轮次
        if(!$explodeLevel[1])
        {
            $newLevel=$explodeLevel[0].'_1';
        }
        else
        {
            $modify=$explodeLevel[1]+1;
            $newLevel=$explodeLevel[0].'_'.$modify;
        }
    return $newLevel;

}
//找项目提交人的id
function ProjectSubmitter($spId)
{
     $submitterInfo= D('SendProcess')->where("`sp_id`=%d",array($spId))->field('sp_author')->find();
     return $submitterInfo['sp_author'];

}

/**
 * @param $time 时间
 * @param $type 消息类型
 * @param $proId 项目id
 * @return mixed
 */
function checkMessage($time,$type,$proId){
    $admin_id=session('admin')['admin_id'];
    $data=json_decode(S()->hGet('Type:'.$type.':Time:'.date('Ymd',$time),$time),true);
    if(empty($data['adminIds'])){
        //adminIds为空，则表示没有人查看这条消息
        $data['adminIds']=$admin_id;
    }else{
        //不为空，则将看过这条消息的id值和此人的id进行比较，不在里面就追加上，在里面就不操作
        $tmp=explode(',',$data['adminIds']);
        if(!in_array($admin_id,$tmp)){
            array_push($tmp,$admin_id);
        }
        $tmp=implode(',',$tmp);
        $data['adminIds']=$tmp;
    }
    //拼接好的数据加入进来,存入到redis中去,返回操作是否正确的结果
    return  S()->hSet('Type:'.$type.':Time:'.date('Ymd',$time),$time,json_encode($data));
}

//新建立项时创建资料包对应的文件夹
function createFolder($proId)
{
    $returns=true;
    foreach (C('upLoadFolder') as $k=>$v)
    {
        $return=D('ProjectFile')->data(array('pro_id'=>$proId,'pid'=>0,'file_name'=>$v,))->add();
        $returns=$return && $returns;
    }
    return $return;
}
//上传文件夹或者下载文件夹改变状态
/*****
 * @param $wfId
 * @param $proLevel
 * @param $proTimes
 * @param $admin
 * @param $proIid
 * @param int $proRoleId
 * @param int $proAdminId
 * @param $plId
 * @param int $isUpload //0是下载 1是上传 2是查看
 * @param $isUploadEnd //下载或者上传是否就结束项目 0是结束 1是继续流程
 * @param null $specialMessage
 * @param null $specialType
 */
function uploadUpdataWorkFlowState($wfId,$proLevel,$proTimes,$admin,$proIid,$plId,$isUpload=0,$isUploadEnd=0,$specialMessage=null,$specialType=null)
{
    $explodeLevel=explode('_',$proLevel);//拼接审批轮次
    if(!$explodeLevel[1])
    {
        $newLevel=$explodeLevel[0].'_1';
    }
    else
    {
        $modify=$explodeLevel[1]+1;
        $newLevel=$explodeLevel[0].'_'.$modify;
    }
    //更新原数据状态
    $wfModel=D('WorkflowLog');
    $pjModel=D('PjWorkflow');
    $result=true;
    if($isUploadEnd==0 && $isUpload==0)//下载或者上传就结束
    {
        $updataOldPj=$pjModel->data(array('pro_level_now'=>$newLevel))->where("`wf_id`=%d",array($wfId))->save();
        $updataOldWf=$wfModel->data(array('pro_state'=>'2','pro_last_edit_time'=>time()))->where("`pl_id`=%d",array($plId))->save();
        $workFlowLog = D('WorkflowLog')->data(array(
            'sp_id' => '', 'pj_id' => $proIid, 'pro_level' => $newLevel, 'pro_times' => $proTimes, 'pro_state' => 2, 'pro_addtime' => time(),'pro_role'=>'','pro_author'=>'',
            'wf_id' => $wfId, 'pro_xml_id' => ''
        ))->add();
        $result=$result && $updataOldPj && $workFlowLog && $updataOldWf;
    }elseif ($isUploadEnd==1 && $isUpload==0)
    {
        $updataOldWf=$wfModel->data(array('pro_state'=>'2','pro_last_edit_time'=>time()))->where("`pl_id`=%d",array($plId))->save();
        $result=$result && $updataOldWf;
    }
    elseif ($isUploadEnd==0 && $isUpload==1)//上传完成就结束
    {
        $oldPjInfo=$wfModel->where("`pj_id`=%d and `pro_level`='%s'",array($proIid,$newLevel))->find();
        if($oldPjInfo)
        //如果已经更新过就不需要新添加，而是改变自身的状态就好
        {
            $updataOldWf=$wfModel->data(array('pro_state'=>'2','pro_last_edit_time'=>time()))->where("`pl_id`=%d",array($plId))->save();
            $result=$result && $updataOldWf;
        }else
        {
            $updataOldPj=$pjModel->data(array('pro_level_now'=>$newLevel))->where("`wf_id`=%d",array($wfId))->save();
            $updataOldWf=$wfModel->data(array('pro_state'=>'2','pro_last_edit_time'=>time()))->where("`pl_id`=%d",array($plId))->save();
            $workFlowLog = D('WorkflowLog')->data(array(
                'sp_id' => '', 'pj_id' => $proIid, 'pro_level' => $newLevel, 'pro_times' => $proTimes, 'pro_state' => 2, 'pro_addtime' => time(),'pro_role'=>'','pro_author'=>'',
                'wf_id' => $wfId, 'pro_xml_id' => ''
            ))->add();
            $result=$result && $updataOldPj && $workFlowLog && $updataOldWf;
        }

    }elseif($isUpload==2)
    {
        $updataOldPj=$wfModel->data(array('pro_state'=>2))->where("`pl_id`=%d",array($plId))->save();
        $result=$result && $updataOldPj;
    }


    $noticeType=isset($specialType)?$specialType:$proLevel;
    $isUpload==0?$action='下载':($isUpload==2?$action='已查看':$action='上传');
    $specialMessage=$contents= $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>'.$action.'项目<code>' . projectNameFromId($proIid) . '</code>资料';
    $redisPost = redisCollect($noticeType,$admin['admin_id'],$receive='',time(),$proIid,$specialMessage,$specialType) && $result;
    return $redisPost;

}



















