<?php

function check_verify($code, $id = '')
{
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

/**
 * 取验证码hash值
 *
 * @param
 * @return string 字符串类型的返回结果
 */
function getHash()
{
    return substr(md5(__SELF__), 0, 8);
}

/* 生成哈希链接 */

function U_hash($link)
{
    return U($link, array('nchash' => getHash()));
}

function random($length, $numeric = 0)
{
    $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
    $hash = '';
    $max = strlen($seed) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $seed{mt_rand(0, $max)};
    }
    return $hash;
}

function makeSeccode($nchash)
{
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
function upload_file($savepath, $field, $short_name = '')
{
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
function upload_document($savepath, $field, $short_name = '')
{
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
function create_name($filename)
{
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
function my_file_exists($data)
{
    $pro_id = session('pro_id');
    if (D('ProjectAttachment')->sha1Exists($pro_id, $data['sha1'])) {
        return false;
    }
    return false;//暂时去掉文件重复性
}

function debt_tr_class($status, $endtime)
{
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
function isSupper()
{
    $admin = session('admin');
    if ($admin['is_supper']) {
        return true;
    }
    return false;
}

//判断是否是总监级别
function isBoss()
{
    $admin = session('admin');
    if ($admin['position_id'] == 1) {
        return true;
    }
    return false;
}

//判断是否为项管部总监
function isPmdBoss()
{
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
function debtStatusStr($status)
{
    return Admin\Model\ProjectDebtDetailModel::debtStatusStr($status);
}

//是否商票背书
function debtTypeStr($type)
{
    return $type == 0 ? '否' : '是';
}

/**
 *
 * @param type $tmpId
 * @param type $admin_id
 * @param type $is_role
 * @return boolaen
 */
function push($tmpId, $admin_id, $is_role)
{
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
function adminLog($action, $admiId, $author, $message)
{
    $message = $action . ';' . $admiId . ';' . $author . ';' . $message;
    $destination = C('LOG_PATH') . '/Operation/' . date('y_m_d') . '.log';
    \Think\Log::write($message, 'INFO ', 'File', $destination);
}

//返回前端要求的格式的扩展名
function getFormerExts()
{
    $upload_config = C('UPLOAD_CONFIG');
    $exts = $upload_config['exts'];
    foreach ($exts as & $ext) {
        $ext = '*.' . $ext;
    }
    return implode(';', $exts);
}

//去掉文件名后缀
function getFilename($filename)
{
    $arr = explode('.', $filename);
    return $arr[0];
}

//计算二维数组中指定列的和
function getcolvars($arr, $colname)
{
    if (empty($arr)) return 0;
    if (count($arr, 1) == count($arr)) {
        return 0;
    }
    $colvalues = array_column($arr, $colname);
    $result = 0;
    foreach (array_count_values($colvalues) as $k => $v) {
        $result += $k * $v;
    }
    return $result;
}

//查找部门名字
function getbname($arr, $bid)
{
    if (empty($arr)) return '';
    foreach ($arr as $v) {
        if ($v['branch_id'] == $bid) {
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
    switch ($roleId) {
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
    return $name ? D(ucfirst($name), 'Logic') : null;
}

//返回审核对应的信息
function auditInit($auditType)
{
    switch ($auditType) {
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
function xmlIdToInfo($id, $file, $type = 0)
{
    $xmlObj = logic('xml');
    $xmlObj->file = $file;
    $list = $xmlObj->index();
    foreach ($list as $k => $v) {
        if ($k == $id) {
            if ($v['value']) {
                foreach ($v['value'] as $kk => $vv) {
                    if ($vv['tag'] == 'BPMN2:OUTGOING')//指出的线
                    {
                        // $id=$vv['value'];
                        return xmlIdToInfo($vv['value'], $file);
                    }
                }
            } else {
                return $v;
            }
        }

    }
}

//根据xml的name返回下一级xml的id和name
function xmlNameToIdAndName($name, $file)
{
    $xmlObj = logic('xml');
    $xmlObj->file = $file;
    $xmlInfo = $xmlObj->index();
    foreach ($xmlInfo as $k => $v) {
        //$xmlName=explode('_',$v['name'])[0];
        if ($v['name'] == $name) //新建的config的第一项必须跟xml的第一步相等
        {
            return xmlIdToInfo($k, $file);
        }
    }
}

//根据xml的name返回当前的信息
function xmlNameToLoacalInfo($name, $file)
{
    $xmlObj = logic('xml');
    $xmlObj->file = $file;
    $xmlInfo = $xmlObj->index();
    foreach ($xmlInfo as $k => $v) {
        //$xmlName=explode('_',$v['name'])[0];
        if ($v['name'] == $name) //新建的config的第一项必须跟xml的第一步相等
        {
            return $v;
        }
    }
}

//根据role_name返回role_id
function roleNameToid($name)
{
    $list = D('Role')->where(array('role_name' => array('like', "%$name%")))->find();
    return $list['role_id'];
}

//根据adminId返回adminName
function adminNameToId($adminId)
{
    $adminInfo = D('Admin')->where("`admin_id`=%d", array($adminId))->field('real_name')->find();
    return $adminInfo['real_name'];
}


//根据项目的状态返回相应的操作事件
function projectToAction($authType, $pageAuth, $middleType = 'pre')
{
    $attrInde = C('proLevel')[$authType];
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

function redisCollect($proLevel, $sender, $receive = '', $time, $proId, $specialMessage = null, $specialType = null)
{
    $admin=session('admin');
    $type = auditMainProcessType($proLevel);
    $adminAttr = D('Admin')->where("`admin_id`=%d", array($sender))->field('admin_id,real_name')->find();
    $sender = $adminAttr['real_name'];//送审人的姓名
    $receiveAttr = explode('|', $receive);
    //查出是受审人是部门形式还是管理员形式
    if ($receiveAttr[1] == 'role') {
        $receiveObj = D('Role')->where("`role_id`=%d", array($receiveAttr[0]))->field('role_name')->find();
        $receive = $receiveObj['role_name'];
        $authorType = 'role';
    } else {
        $receiveObj = D('Admin')->where("`admin_id`=%d", array($receiveAttr[0]))->field('real_name')->find();
        $receive = $receiveObj['real_name'];
        $authorType = 'admin';
    }
    //查出项目名字
    $proName = projectNameFromId($proId);
    $contents = '';
    $redisKey = 'Type:' . $type . ':Time:' . date('Ymd', $time);
    //用集合记录录入的时间
    if (!S()->sIsMember('sType:' . $type, date('Ymd', time()))) {
        S()->sAdd('sType:' . $type, date('Ymd', time()));
    }
    switch ($proLevel) {
        case '0':
            //项目经理立项
            $contents = $admin['role_name'].'<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>';
            break;
        case '0_1':
            //项目总监分配人手
            $contents = '项管总监<code>' . $sender . '</code>将项目<code>' . $proName . '</code>分配给：<code>' . $receive . '</code>';
            break;
        case '0_2':
            //项管专员归档
            $contents = '项管专员<code>' . $sender . '</code>将项目<code>' . $proName . '</code>初审反馈';
            break;
        case '0_3':
            //项管专员归档
            $contents = '项管专员<code>' . $sender . '</code>将项目<code>' . $proName . '</code><code>初审通过</code>';
            break;
        case '4':
            $contents = '项管专员<code>' . $sender . '</code>发起项目<code>' . $proName . '</code>会议报告编写通知';
            break;
        case '4_1':
            $contents = '项管总监<code>' . $sender . '</code>发起项目<code>' . $proName . '</code>知情给：<code>' . $receive . '</code>';
            break;
        case '5':
            $contents = '项管专员<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>风控审核子流程';
            break;
        case '5_1':
            $contents = '项管总监<code>' . $sender . '</code>发起项目<code>' . $proName . '</code>风控审核通知给：<code>' . $receive . '</code>';
            break;
        case '6':
            $contents = '项管专员<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>立项会';
            break;
        case '6_1':
            $contents = '项管总监<code>' . $sender . '</code>将项目<code>' . $proName . '</code>召开立项会事宜通知：<code>' . $receive . '</code>';
            break;
        case '7':
            $contents = '风控总监<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>风控报告';
            break;
        case '7_2':
            $contents = '项管专员<code>' . $sender . '</code>已完成项目<code>' . $proName . '</code>风控报告的审核';
            break;
        case '8':
            $contents = '项管专员<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>风控会';
            break;
        case '8_1':
            $contents = '项管总监<code>' . $sender . '</code>将项目<code>' . $proName . '</code>风控会事宜通知：<code>' . $receive . '</code>';
            break;
        case '9':
            $contents = '项管专员<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>投委会';
            break;
        case '9_1':
            $contents = '项管总监<code>' . $sender . '</code>将项目<code>' . $proName . '</code>投委会事宜通知：<code>' . $receive . '</code>';
            break;
        case '10':
            $contents = '项管专员<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>出具风控审核意见';
            break;
        case '11':
            $contents = '项管专员<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>合同预签申请';
            break;
        case '13':
            $contents = '法务<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>线下签约';
            break;
        case '14':
            $contents = '项管专员<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>商票上传流程';
            break;
        case '15':
            $contents = '项管专员<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>请款审批';
            break;
        case '17':
            $contents = '项管专员<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>换质退票审批';
            break;
        case '18':
            $contents = '项管专员<code>' . $sender . '</code>新建大麦<code>' . $proName . '</code>放款流程';
            break;
        case '25':
            $contents = '同业部经理<code>' . $sender . '</code>申请项目<code>' . $proName . '</code>资料下载';
            break;
        case '-1':
        case '-2':
        case '-3':
        default:
            $contents = $specialMessage;
            break;
    }
    $redisValue = array($time => json_encode(array('contents' => $contents, 'time' => $time, 'proId' => $proId, 'authorType' => $authorType)));
    return S()->hMset($redisKey, $redisValue);
}

//整合redis的消息发送
function redisTotalPost($proLevel, $sender, $receive, $time, $proId, $plId, $specialMessage = null, $specialType = null)
{
    return redisPostAudit($proLevel, $sender, $receive, $time, $proId, $plId, $specialMessage, $specialType) && redisCollect($proLevel, $sender, $receive, $time, $proId, $specialMessage, $specialType);

}

//待我审核事项
function redisPostAudit($proLevel, $sender, $receive = '', $time, $proId, $plId, $specialMessage = null, $specialType = null)
{
    //查出项目名字
    $proName = projectNameFromId($proId);
    $adminAttr = D('Admin')->where("`admin_id`=%d", array($sender))->field('admin_id,real_name')->find();
    $sender = $adminAttr['real_name'];//送审人的姓名
    $receiveAttr = explode('|', $receive);
    //查出是受审人是部门形式还是管理员形式
    if ($receiveAttr[1] == 'role') {
        //$receiveObj=D('Role')->where("`role_id`=%d",array($receiveAttr[0]))->field('role_name')->find();
        // $receive=$receiveObj['role_name'];
        $redisKey = 'role:' . $receiveAttr[0];
        $authorType = 'role';
    } else {
        //$receiveObj=D('Admin')->where("`admin_id`=%d",array($receiveAttr[0]))->field('real_name')->find();
        //$receive=$receiveObj['real_name'];
        $redisKey = 'admin:' . $receiveAttr[0];
        $authorType = 'admin';
    }
    switch ($proLevel) {
        case '0':
            //项目经理立项
            $contents = '项目经理:<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>';
            break;
        case '0_1':
            //项目总监分配人手
            $contents = '项管总监<code>' . $sender . '</code>将项目<code>' . $proName . '</code>分配给我！';
            break;
        case '0_2':
            //项管专员归档
            $contents = '项管专员<code>' . $sender . '</code>将项目<code>' . $proName . '</code>初审反馈';
            break;
        case '0_3':
            //项管专员归档
            $contents = '项管专员<code>' . $sender . '</code>将项目<code>' . $proName . '</code><code>初审通过</code>';
            break;
        case '4':
            $contents = '项管专员<code>' . $sender . '</code>发起项目<code>' . $proName . '</code>会议报告编写通知';
            break;
        case '4_1':
            $contents = '项管总监<code>' . $sender . '</code>发起项目<code>' . $proName . '</code>知情给我';
            break;
        case '5':
            $contents = '项管专员<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>项目审核通知';
            break;
        case '5_1':
            $contents = '项管总监<code>' . $sender . '</code>发起项目<code>' . $proName . '</code>风控流程审核';
            break;
        case '6':
            $contents = '项管专员<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>立项会';
            break;
        case '6_1':
            $contents = '项管总监<code>' . $sender . '</code>将项目<code>' . $proName . '</code>召开立项会事宜通知我';
            break;
        case '7':
            $contents = '风控总监<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>编写风控报告';
            break;
        case '7_2':
            $contents = '项管专员<code>' . $sender . '</code>已完成项目<code>' . $proName . '</code>报告知情';
            break;
        case '8':
            $contents = '项管专员<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>风控会';
            break;
        case '8_1':
            $contents = '项管总监<code>' . $sender . '</code>将项目<code>' . $proName . '</code>风控会事宜通知我';
            break;
        case '9':
            $contents = '项管专员<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>投委会';
            break;
        case '9_1':
            $contents = '项管总监<code>' . $sender . '</code>将项目<code>' . $proName . '</code>投委会事宜通知我';
            break;
        case '10':
            $contents = '项管专员<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>出具风控审核意见';
            break;
        case '11':
            $contents = '项管专员<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>合同预签申请';
            break;
        case '13':
            $contents = '法务<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>线下签约流程';
            break;
        case '14':
            $contents = '项管专员<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>商票上传流程';
            break;
        case '15':
            $contents = '项管专员<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>请款审批';
            break;
        case '17':
            $contents = '项管专员<code>' . $sender . '</code>新建项目<code>' . $proName . '</code>换质退票审批';
            break;
        case '18':
            $contents = '项管专员<code>' . $sender . '</code>新建大麦<code>' . $proName . '</code>新建请款书';
            break;
        case '25':
            $contents = '同业部经理<code>' . $sender . '</code>申请项目<code>' . $proName . '</code>资料下载';
            break;

        case '-1':
        case '-2':
        case '-3':
        default:
            $contents = $specialMessage;
            break;
    }
    $redisValue = array($plId => json_encode(array('contents' => $contents, 'time' => $time, 'proId' => $proId, 'plId' => $plId, 'authorType' => $authorType)));
    return S()->hMset($redisKey, $redisValue);

}

//根据项目id查出项目名字
function projectNameFromId($proId)
{
    $projectObj = D('Project')->where("`pro_id`=%d", array($proId))->field('pro_title')->find();
    return $projectObj['pro_title'];
}

//返回子流程所属大流程所属大类
function auditMainProcessType($proLevel)
{
    foreach (C('messAuth') as $k => $v) {
        if (in_array($proLevel, $v['sub'])) {
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
function postRebutter($wfId, $proIid, $proRebutterLevel, $proTimes, $admin, $proRebutter, $xmlId, $plId, $type = 'list')
{
    //审批流入库处理
    $proRebutterPlid = I('get.proRebutterPlid') ? I('get.proRebutterPlid') : I('post.proRebutterPlid');//被驳回的plid是多少
    $pjWorkFlow = D('PjWorkflow')->where("`wf_id`=%d", array($wfId))->data(array('pj_id' => $proIid, 'pj_state' => '待审核', 'pro_level_now' => $proRebutterLevel, 'pro_times_now' => $proTimes))->save();
    $sendProcess = D('SendProcess')->data(array('wf_id' => $wfId, 'sp_message' => '已提交', 'sp_author' => $admin['admin_id'], 'sp_addtime' => time(), 'sp_role_id' => $admin['role_id']))->add();
    $workFlowLog = D('WorkflowLog')->data(array(
        'sp_id' => $sendProcess, 'pj_id' => $proIid, 'pro_level' => $proRebutterLevel, 'pro_times' => $proTimes, 'pro_state' => 0, 'pro_addtime' => time(), 'pro_author' => $proRebutter,
        'wf_id' => $wfId, 'pro_xml_id' => $xmlId
    ))->add();
    $oldworkFlowLog = D('WorkflowLog')->where("`pl_id`=%d", array($plId))->data(array('pro_state' => 2))->save();
    $explodeLevel = explode('_', $proRebutterLevel);//拼接审批轮次
    if($explodeLevel[0]=='0')
    {
        $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>重新提交<code>被反馈的</code>项目<code>' . projectNameFromId($proIid) . '</code>';
    }else
    {
        $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>重新提交<code>被驳回</code>项目<code>' . projectNameFromId($proIid) . '</code>';
    }

    $deleRedis = delredis($proRebutterPlid, $admin['admin_id']);
    $noticeType='-'.auditMainProcessType($proRebutterLevel);
    //$redisPost = redisTotalPost($proRebutterLevel, $admin['admin_id'], $proRebutter . '|admin', time(), $proIid, $workFlowLog, $contents, -1) && $deleRedis;
    $redisPost = redisTotalPost($noticeType, $admin['admin_id'], $proRebutter . '|admin', time(), $proIid, $workFlowLog, $contents, $noticeType) && $deleRedis;
    if ($type == 'list') {

        return array($pjWorkFlow, $sendProcess, $workFlowLog, $redisPost && $oldworkFlowLog);

    } elseif ($type == 'one') {
        return $pjWorkFlow && $sendProcess && $workFlowLog && $oldworkFlowLog && $redisPost;
    }
}

//驳回的模块
function reButter($plId, $wfId, $proIid, $proLevel, $contents, $proRebutterLevel, $reButter, $proTimes, $admin, $xmlId)
{
    $WflMode = D('WorkflowLog');
    //改变审批人的状态为2-已审核状态
    $updateState = $WflMode->where("`pl_id`=%d", array($plId))->data(array('pro_state' => '2'))->save();
    $sendProcess = D('SendProcess')->data(array('wf_id' => $wfId, 'sp_message' => '已提交', 'sp_author' => $admin['admin_id'], 'sp_addtime' => time(), 'sp_role_id' => $admin['role_id']))->add();
    //改变pj_workflow 表 因为是驳回，所以pro_time_now + 1
    $updatePj = D('PjWorkflow')->where("`wf_id`=%d", array($wfId))->data(array('pro_level_now' => $proRebutterLevel, 'pro_times_now' => intval($proTimes) + 1))->save();
    //新建worklowLog表中驳回的人的相关信息
    $WflMode->data(array(
        'sp_id' => $sendProcess, 'pj_id' => $proIid, 'pro_author' => $reButter, 'pro_level' => $proRebutterLevel, 'pro_times' => intval($proTimes) + 1, 'pro_state' => 3, 'pro_addtime' => time(),
        'wf_id' => $wfId, 'pro_role' => '0', 'pro_xml_id' => $xmlId, 'pro_rebutter' => $admin['admin_id'], 'pro_rebutter_level' => $proLevel, 'pro_rebutter_plid' => $plId
    ))->add();
    //redis推送消息
    //$contents = '项管专员<code>' . $admin['real_name'] . '</code>将项目<code>' . projectNameFromId($proIid) . '</code>立项事宜驳回给<code>' . adminNameToId($reButter) . '</code>';
    $contents = $contents;
    $deleRedis = delredis($plId); //删除对应的redis记录
    $pro_subprocess_desc = $_GET['pro_subprocess_desc'];//子流程备注
    $updataProject = addSubProcessAuditor($proIid, '', '', $proLevel, $pro_subprocess_desc);;//将编辑的数据先入project库 $proLevel+1 因为中间环节有个提交
    $noticeType='-'.auditMainProcessType($proLevel);
    $redisPost = redisTotalPost($noticeType, $admin['admin_id'], $reButter . '|admin', time(), $proIid, $plId, $contents, $noticeType);
    return $updateState && $updatePj && $sendProcess && $WflMode && $redisPost && $deleRedis && $updataProject;
}

//返回执行完下一步的流程模块
/******
 * @param $wfId gt_pj_workflow id
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
function postNextProcess($wfId, $proLevel, $proTimes, $admin, $proIid, $proRoleId = 0, $proAdminId = 0, $xmlId, $plId, $type = 'list', $specialMessage = null, $specialType = null, $time = null)
{
    //url='http://j.mp/2ks0GkT';//大麦的审核系统，附带大麦logo

    //手机短信通知
    $proModel = D('Project');
    $adminModel = D('Admin');
    $mobileList = $adminModel->getMobile($proAdminId);
    $projectInfo = $proModel->returnProjectInfo($proIid);
    if ($projectInfo['binding_oa']) {
        //如果轮到孙总审批，需同时通知彭诗慧
        if ($proLevel == '18_4') {
            sendMsg('13794476673', '彭诗慧', $projectInfo['pro_title'], $projectInfo['pro_account'], 'http://t.cn/RJYA71M', 'dm');
        }
        $send = sendMsg($mobileList['mobile'], $mobileList['realName'], $projectInfo['pro_title'], $projectInfo['pro_account'], 'http://t.cn/RJYA71M', 'dm');
    }
    $explodeLevel = explode('_', $proLevel);//拼接审批轮次
    if (!$explodeLevel[1]) {
        $newLevel = $explodeLevel[0] . '_1';
    } else {
        $modify = $explodeLevel[1] + 1;
        $newLevel = $explodeLevel[0] . '_' . $modify;
    } //记录此项目当前的子流程信息
    $pjWorkFlow = D('PjWorkflow')->where("`wf_id`=%d", array($wfId))->data(array('pj_state' => '待审核', 'pro_level_now' => $newLevel, 'pro_times_now' => $proTimes))->save();
    //将创建当前子流程的人的信息记录到SendProcess中去
    $sendProcess = D('SendProcess')->data(array('wf_id' => $wfId, 'sp_message' => '已提交', 'sp_author' => $admin['admin_id'], 'sp_addtime' => time(), 'sp_role_id' => $admin['role_id']))->add();
    //将当前子流程执行的信息记录到流程执行日志表workflow_log中
    $workFlowLog = D('WorkflowLog')->data(array(
        'sp_id' => $sendProcess, 'pj_id' => $proIid, 'pro_level' => $newLevel, 'pro_times' => $proTimes, 'pro_state' => 0, 'pro_addtime' => time(), 'pro_role' => $proRoleId, 'pro_author' => $proAdminId,
        'wf_id' => $wfId, 'pro_xml_id' => $xmlId
    ))->add();
    $oldworkFlowLog = D('WorkflowLog')->where("`pl_id`=%d", array($plId))->data(array('pro_state' => 2, 'pro_addtime' => time()))->save();//eg ,  此时是10_2子流程，则将10_1的子流程审核状态改变为2 表示已审核状态

    if ($oldworkFlowLog) {
        delredis($plId); //删除对应的redis记录
    }


    $adminValue = ($proRoleId > 0) ? $proRoleId : $proAdminId;
    $adminType = ($proRoleId > 0) ? '|role' : '|admin';
    $noticeType = isset($specialType) ? $specialType : $proLevel;
    isset($time) ? $time = $time : $time = time();
    $redisPost = redisTotalPost($noticeType, $admin['admin_id'], $adminValue . $adminType, $time, $proIid, $workFlowLog, $specialMessage, $specialType) && $oldworkFlowLog === false ? false : true;

    if ($type == 'list') {
        return array($pjWorkFlow, $sendProcess, $workFlowLog, $redisPost && $oldworkFlowLog);

    } elseif ($type == 'one') {
        return $pjWorkFlow === false ? false : true && $sendProcess === false ? false : true && $workFlowLog === false ? false : true && $oldworkFlowLog === false ? false : true && $redisPost;
    }
}

/******
 * 新增子流程
 * @param $result 项目id
 * @param $pro_level
 * @param $admin
 * @return array
 */
function addSubProcess($result, $pro_level, $admin, $xmlfile)
{
    $xmlId = xmlNameToIdAndName(C('proLevel')[$pro_level], $xmlfile)['TARGETREF'];
    //审批流入库处理
    $pjWorkFlow = D('PjWorkflow')->data(array('pj_id' => $result, 'pj_state' => '待审核', 'pro_level_now' => $pro_level, 'pro_times_now' => '1'))->add();
    $sendProcess = D('SendProcess')->data(array('wf_id' => $pjWorkFlow, 'sp_message' => '已提交', 'sp_author' => $admin['admin_id'], 'sp_addtime' => time(), 'sp_role_id' => $admin['role_id']))->add();
    $workFlowLog = D('WorkflowLog')->data(array(
        'sp_id' => $sendProcess, 'pj_id' => $result, 'pro_level' => $pro_level, 'pro_times' => 1, 'pro_state' => 0, 'pro_addtime' => time(), 'pro_author' => $admin['admin_id'],
        'wf_id' => $pjWorkFlow, 'pro_role' => $admin['role_id'], 'pro_xml_id' => $xmlId
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
function addSubProcessAuditor($pjId, $auditor_id, $auditor_name, $pro_level, $pro_subprocess_desc)
{
    $projectModel = D('Project');
    $admin = session('admin');
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
    $proSubprocessDesc = 'pro_subprocess' . explode('_', $pro_level)[0] . '_desc'; //如果4_1 4_2 4_3 还原成4
    //往project表添加状态
    $finishStatusJson = $projectModel->where("`pro_id`=%d", array($pjId))->field("finish_status,$proSubprocessDesc")->find();
    //将adminid和adminname转数组
    $auditor_id = explode(',', $auditor_id);
    $auditor_name = explode(',', $auditor_name);
    $finish_status = json_decode($finishStatusJson['finish_status'], true);
    //$proKeys = array_keys($finish_status);
    foreach ($auditor_id as $k => $v) {
        $finish_status[$pro_level]['auditor'][$k]['adminId'] = $v;
        $finish_status[$pro_level]['auditor'][$k]['adminName'] = $auditor_name[$k];
    }
    //新增的商票--背书等信息和日常利息--银行等信息
    if ($pro_level == '14') {
        $finish_status[$pro_level]['electronicInfo']['handling_charge_bank_name'] = I('get.handling_charge_bank_name');
        $finish_status[$pro_level]['electronicInfo']['handling_charge_account_name'] = I('get.handling_charge_account_name');
        $finish_status[$pro_level]['electronicInfo']['handling_charge_bank_no'] = I('get.handling_charge_bank_no');
        $finish_status[$pro_level]['electronicInfo']['electronicBillMoney'] = I('get.electronicBillMoney');
        $finish_status[$pro_level]['electronicInfo']['electronicBillName'] = I('get.electronicBillName');
    }
    if ($pro_level == '16') {
        $finish_status[$pro_level]['financeFlow']['handling_charge_bank_name'] = I('get.handling_charge_bank_name');
        $finish_status[$pro_level]['financeFlow']['handling_charge_account_name'] = I('get.handling_charge_account_name');
        $finish_status[$pro_level]['financeFlow']['handling_charge_bank_no'] = I('get.handling_charge_bank_no');
        $finish_status[$pro_level]['financeFlow']['electronicBillMoney'] = I('get.electronicBillMoney');
        $finish_status[$pro_level]['financeFlow']['electronicBillName'] = I('get.electronicBillName');
    }


    $finish_enjson = json_encode($finish_status);
    $old_subprocess_desc = $finishStatusJson[$proSubprocessDesc];//子流程老数据备注
    $pro_subprocess_desc_new = '';
    //新备注补丁，可以修改自己名下的备注
    empty(I('get.desc')) ? $desc = I('post.desc') : $desc = I('get.desc');
    if ($old_subprocess_desc) {
        foreach (array_filter(explode('<br/>', $old_subprocess_desc)) as $dkey => $dvalue) {
            if (explode('::', $desc[$dkey])[0] == $admin['real_name']) {
                $pro_subprocess_desc_new .= $desc[$dkey] . '<br/>';
            } else {
                $pro_subprocess_desc_new .= $dvalue . '<br/>';
            }
        }
    }

    if ($pro_subprocess_desc) {
        $pro_subprocess_desc = $admin['real_name'] . '::' . $pro_subprocess_desc . '<br/>' . $pro_subprocess_desc_new;
    } else {
        $pro_subprocess_desc = $pro_subprocess_desc_new;
    }
    //$pro_subprocess_desc=$admin['real_name'].'::'.$pro_subprocess_desc=!empty($pro_subprocess_desc)?$pro_subprocess_desc.'<br/>'.$old_subprocess_desc:'无意见！'.'<br/>'.$old_subprocess_desc;
    if (!$auditor_id || !$auditor_name) {
        $oldProject = $projectModel->where("`pro_id`=%d", array($pjId))->data(array($proSubprocessDesc => $pro_subprocess_desc))->save();
    } else {
        $oldProject = $projectModel->where("`pro_id`=%d", array($pjId))->data(array($proSubprocessDesc => $pro_subprocess_desc, 'finish_status' => $finish_enjson))->save();

    }
    return $oldProject === false ? false : true;
}

//新审批轮次加1
function addNewLevel($proLevel)
{
    $explodeLevel = explode('_', $proLevel);//拼接审批轮次
    if (!$explodeLevel[1]) {
        $newLevel = $explodeLevel[0] . '_1';
    } else {
        $modify = $explodeLevel[1] + 1;
        $newLevel = $explodeLevel[0] . '_' . $modify;
    }
    return $newLevel;

}

//找项目提交人的id
function ProjectSubmitter($spId)
{
    $submitterInfo = D('SendProcess')->where("`sp_id`=%d", array($spId))->field('sp_author')->find();
    return $submitterInfo['sp_author'];

}

/**
 * @param $time 时间
 * @param $type 消息类型
 * @param $proId 项目id
 * @return mixed
 */
function checkMessage($time, $type, $proId)
{
    $admin_id = session('admin')['admin_id'];
    $data = json_decode(S()->hGet('Type:' . $type . ':Time:' . date('Ymd', $time), $time), true);
    if (empty($data['adminIds'])) {
        //adminIds为空，则表示没有人查看这条消息
        $data['adminIds'] = $admin_id;
    } else {
        //不为空，则将看过这条消息的id值和此人的id进行比较，不在里面就追加上，在里面就不操作
        $tmp = explode(',', $data['adminIds']);
        if (!in_array($admin_id, $tmp)) {
            array_push($tmp, $admin_id);
        }
        $tmp = implode(',', $tmp);
        $data['adminIds'] = $tmp;
    }
    //拼接好的数据加入进来,存入到redis中去,返回操作是否正确的结果
    return S()->hSet('Type:' . $type . ':Time:' . date('Ymd', $time), $time, json_encode($data));
}

//新建立项时创建资料包对应的文件夹
function createFolder($proId, $folderList = null, $pid = 0)
{
    $returns = true;
    isset($folderList) ? $folderList = $folderList : $folderList = C('upLoadFolder');
    foreach ($folderList as $k => $v) {
        $return = D('ProjectFile')->data(array('pro_id' => $proId, 'pid' => $pid, 'file_name' => $v['name'], 'secret' => $v['secret']))->add();
        if (array_key_exists('sub', $v)) {
            createFolder($proId, $v['sub'], $return);
        }
        $returns = $return && $returns;
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
 * @param $plId
 * @param int $isUpload //0是下载 1是上传 2是查看
 * @param $isUploadEnd //下载或者上传是否就结束项目 0是结束 1是继续流程
 * @param null $specialMessage
 * @param null $specialType
 */
function uploadUpdataWorkFlowState($wfId, $proLevel, $proTimes, $admin, $proIid, $plId, $isUpload = 0, $isUploadEnd = 0, $specialMessage = null, $specialType = null)
{
    $explodeLevel = explode('_', $proLevel);//拼接审批轮次
    if (!$explodeLevel[1]) {
        $newLevel = $explodeLevel[0] . '_1';
    } else {
        $modify = $explodeLevel[1] + 1;
        $newLevel = $explodeLevel[0] . '_' . $modify;
    }
    //更新原数据状态
    $wfModel = D('WorkflowLog');
    $pjModel = D('PjWorkflow');
    $result = true;
    if ($isUploadEnd == 0 && $isUpload == 0)//上传就结束
    {
        $oldPjInfo = $wfModel->where("`pj_id`=%d and `pro_level`='%s'", array($proIid, $newLevel))->find();
        if ($oldPjInfo) {
            $updataOldWf = $wfModel->data(array('pro_state' => '2', 'pro_last_edit_time' => time()))->where("`pl_id`=%d", array($plId))->save();
            $result = $result && $updataOldWf;
        } else {
            $updataOldPj = $pjModel->data(array('pro_level_now' => $newLevel))->where("`wf_id`=%d", array($wfId))->save();
            $updataOldWf = $wfModel->data(array('pro_state' => '2', 'pro_last_edit_time' => time()))->where("`pl_id`=%d", array($plId))->save();
            $workFlowLog = $wfModel->data(array(
                'sp_id' => '', 'pj_id' => $proIid, 'pro_level' => $newLevel, 'pro_times' => $proTimes, 'pro_state' => 2, 'pro_addtime' => time(), 'pro_role' => '', 'pro_author' => '',
                'wf_id' => $wfId, 'pro_xml_id' => ''
            ))->add();
            $result = $result && $updataOldPj && $workFlowLog && $updataOldWf;

        }

    } elseif ($isUploadEnd == 1 && $isUpload == 0) {
        $updataOldWf = $wfModel->data(array('pro_state' => '2', 'pro_last_edit_time' => time()))->where("`pl_id`=%d", array($plId))->save();
        $result = $result && $updataOldWf;
    } elseif ($isUploadEnd == 0 && $isUpload == 1)//上传完成还继续
    {
        $oldPjInfo = $wfModel->where("`pj_id`=%d and `pro_level`='%s'", array($proIid, $newLevel))->find();
        if ($oldPjInfo) //如果已经更新过就不需要新添加，而是改变自身的状态就好
        {
            $updataOldWf = $wfModel->data(array('pro_state' => '2', 'pro_last_edit_time' => time()))->where("`pl_id`=%d", array($plId))->save();
            $result = $result && $updataOldWf;
        } else {
            $updataOldPj = $pjModel->data(array('pro_level_now' => $newLevel))->where("`wf_id`=%d", array($wfId))->save();
            $updataOldWf = $wfModel->data(array('pro_state' => '2', 'pro_last_edit_time' => time()))->where("`pl_id`=%d", array($plId))->save();
            //更新项目流程状态，
            $workFlowLog = D('WorkflowLog')->data(array(
                'sp_id' => '', 'pj_id' => $proIid, 'pro_level' => $newLevel, 'pro_times' => $proTimes, 'pro_state' => 2, 'pro_addtime' => time(), 'pro_role' => '', 'pro_author' => '',
                'wf_id' => $wfId, 'pro_xml_id' => ''
            ))->add();
            $result = $result && $updataOldPj && $workFlowLog && $updataOldWf;
        }

    } elseif ($isUpload == 2) {
        $updataOldPj = $wfModel->data(array('pro_state' => 2))->where("`pl_id`=%d", array($plId))->save();
        $result = $result && $updataOldPj;
        if ($updataOldPj) delredis($plId);
    }
    //如果更新的workFlowLog表正确执行，则删除对应plId的redis记录
    if ($updataOldWf) delredis($plId);
    $noticeType = isset($specialType) ? $specialType : $proLevel;
    $isUpload == 0 ? $action = '下载' : ($isUpload == 2 ? $action = '已查看' : $action = '上传');
    $specialMessage = $contents = $admin['role_name'] . '<code>' . $admin['real_name'] . '</code>' . $action . '项目<code>' . projectNameFromId($proIid) . '</code>资料';
    $redisPost = redisCollect($noticeType, $admin['admin_id'], $receive = '', time(), $proIid, $specialMessage, $specialType) && $result;
    return $redisPost;

}

//拿出project表中finish_status字段里面选中的人
function getFinishStatus($proLevel, $proId)
{
    $projectInfo = D('Project')->where("`pro_id`=%d", array($proId))->find();
    foreach (json_decode($projectInfo['finish_status'], true)[$proLevel]['auditor'] as $k => $v) {

        $data['auditorId'][] = $v['adminId'];
        $data['auditorName'][] = $v['adminName'];

    }
    return $data['auditorId'];

}

/**
 * 删除redis中对应的代办信息记录
 * @param $plid  workflow_log  的 id号
 */
function delredis($plid, $author = null)
{
    //查找出对应的workflog的id号的记录
    $pro_author = D('WorkflowLog')->field('pro_author,pro_role')->where('pl_id =' . $plid)->find();
    //如果pro_author的优先级大于pro_role,所以，如果pro_author存在，则表示采用的是admin方式存储于redis中的，否则是role方式
    $authorType = $pro_author['pro_author'] ? 'admin' : 'role';
    //redis中对应的键
    if ($author) {
        $authorId = $author;
    } else {
        $authorId = $pro_author['pro_author'] ? $pro_author['pro_author'] : $pro_author['pro_role'];
    }

    return S()->hDel($authorType . ':' . $authorId, $plid);
}

/**
 * @param  $folder  文件夹的id号
 * @return 返回$folder所标记的祖先文件夹集合
 */
function pidfile($folder)
{
    static $countFolder = [];
    $tmp = M('ProjectFile')->getFieldByFileId($folder, 'pid');
    if ($tmp == 0) {
        return $countFolder;
    } else {
        array_push($countFolder, $tmp);
        pidfile($tmp);
    }
}

/**
 * 一次性更新多条记录
 * @param $table    操作的标
 * @param $index    需要更新的字段下标
 * @param $addData  新增的数据
 * @param $data     旧数据
 * @param $referer  参考用于更新数据的字段
 * @param $where    更新的条件
 * @return          返回被更新的记录条数
 */
function saveAll($table, $index, $addData, $data, $referer, $where)
{
    //saveAll('ProjectAttachment','allow_adminid',$personId,$oldFiles,'id',array('id'=>array('in',$fileId)));
    //将需要插入的新人添加到原先已经存在的人的id集合中即，插入到allow_adminid中
    foreach ($data as $k => $v) {
        //将旧数据，和要插入的数据都转换为数组，然后合并，组合成新的要插入的数据
        $tmp=array_merge(explode(',',$v[$index]),explode(',',$addData));
       // $tmp = explode(',', $addData);
        //合并数组的时候保证数组唯一，并且去除空值
        $tmp = array_filter(array_unique($tmp));
        $data[$k]['allow_adminid'] = implode(',', $tmp);
    }

    $whencase = '';
    //拼接whencase 字段
    foreach ($data as $k => $v) {
        $whencase .= ' when ' . $v[$referer] . ' then \'' . $v[$index] . '\'';
    }
    $whencase .= ' END';
    $sql = "update %TABLE% SET $index = case $referer $whencase  %WHERE%";
    //执行更新操作,execute第二个参数用于标记需要解析sql中的%TABLE%和%WHERE%的
    return M($table)->where($where)->execute($sql, true);
}


//查询动态审核人
function subLevelUser($level)
{

    $explodeLevel = explode('_', $level);//拼接审批轮次
    if (!$explodeLevel[1]) {
        $newLevel = $explodeLevel[0] . '_1';
    } else {
        $modify = $explodeLevel[1] + 1;
        $newLevel = $explodeLevel[0] . '_' . $modify;
    }
    $list = D('SublevelCheck')->where("`wf_id`='%s'", array($newLevel))->field('admin_ids')->find();
    return $list['admin_ids'];
}

//提交审核状态接口
function submitStatus($type, $bid)//1是提交审核 2是审核完毕
{
    header("Content-type:text/html:charset=utf-8");
    $url = 'http://ndm.damailicai.com/admin/dmlc/ProjectApi/requestLoan'; // 平台接口地址前缀
    $params['bid'] = $bid;
    $params['loan_status'] = $type;
    $key = md5('xiaopinguo');
    $json = json_encode($params);
    $sign = md5($json . $key);
    $params['sign'] = $sign;
    $asynClass = new \Admin\Lib\AsynReturn;
    $asynClass->init($url, array('data' => json_encode($params)));
    $result = $asynClass->request_post();
    return json_decode($result, true);
}

//手机短信通知
function sendMsg($phone, $sendName, $projectName, $projectMoney, $loginUrl, $type = 'dm')
{
    if ($type == 'dm') {
        $Contents = '尊敬的' . $sendName . ',你有一个待审批的项目(项目名称:' . $projectName . ',总金额:' . $projectMoney . '元),你可以登录大麦审批系统进行操作,网址:' . $loginUrl;
    }
    $params = array(
        'account' => 'sz_dmlc',
        'pswd' => 'Dmlc123456',
        'msg' => $Contents,
        'mobile' => $phone,
        'needstatus' => 'true',
        'product' => '',
        'extno' => ''
    );
    $url = 'http://222.73.117.156/msg/HttpBatchSendSM';
    $asynClass = new \Admin\Lib\AsynReturn;
    $asynClass->init($url, $params);
    $result = $asynClass->request_post();
    $result = preg_split("/[,\r\n]/", $result);
    if (isset($result[1])) {
        return true;
    } else {
        return false;
    }

}

//根据OA的id返回OA对应的值
function returnOaNameAndIdAttr($ids)
{
    $list = D('RequestFound')->returnOaInfoFromProId($ids, 'in');
    $idsAttr = array();
    foreach ($list as $k => $v) {
        $idsAttr[$v['id']] = $v['product_name'];
    }
    return $idsAttr;
}
















