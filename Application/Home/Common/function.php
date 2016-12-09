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
function upload_file($savepath, $field) {
    $upload = new \Think\Upload(C('UPLOAD_CONFIG'));
    $upload->savePath = $savepath;
    $upload->subName = $group_id;
    $upload_info = $upload->upload();
    if (!$upload_info) {
        return $upload->getError();
    } else {
        $file_path = '/Uploads' . ltrim($upload_info[$field]['savepath'], '.') . $upload_info[$field]['savename'];
        $upload_info[$field]['file_path'] = $file_path;
        return $upload_info[$field];
    }
}
