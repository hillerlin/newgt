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
function xmlIdToInfo($id,$type=0)
{
    $list=logic('xml')->index();
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
                         return  xmlIdToInfo($vv['value']);
                    }
                }
            }else
            {
                return $v;
            }
        }

    }
}
//根据role_name返回role_id
function roleNameToid($name)
{
    $list=D('Role')->where(array('role_name'=>array('like',"%$name%")))->find();
    return $list['role_id'];

}





















