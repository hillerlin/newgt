<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/3
 * Time: 14:05
 */
/**
 * 获取token
 *
 * @param unknown $uid
 * @return Ambigous <string, string, mixed>
 */
function setToken($uid)
{
    $teken_key = md5(rand(1000, 10000));
    $token = _encrypt($arr = array(
        $uid,
        $teken_key
    ));
    // S(array('type'=>'memcache','expire'=>60));
   // S('TOKEN_' . $uid, $token, 24 * 3600);
    S()->set('TOKEN_'.$uid,$token);
    S()->setTimeout('TOKEN_'.$uid,24 * 3600);
    return $token;
}

//获取token
function getToken($uid)
{
    // S(array('type'=>'memcache','expire'=>60));
   // $result = S('TOKEN_' . $uid);
    $result=S()->get('TOKEN_' . $uid);
    return $result;
}

//检验token
function checkToken($token)
{
    //S(array('type'=>'memcache','expire'=>60));
    $detoken = _decrypt($token);
    $checkToken = S()->get("TOKEN_" . $detoken[0]);
    if (!$checkToken) {
        $json['retCode'] = 1000;
        $json['retMessage'] = "登录超时";
        exit(json_encode($json));
    }
   // S("TOKEN_" . $detoken[0], $checkToken, 24 * 3600);
    S()->set('TOKEN_'.$detoken[0],$token);
    S()->setTimeout('TOKEN_'.$detoken[0],24 * 3600);
    return $detoken[0];
}

/**
 * 解密函数
 *
 * @param string $txt
 *            需要解密的字符串
 * @param string $key
 *            密匙
 * @return string 字符串类型的返回结果
 */
function _decrypt($txt, $key = '59e2b673ad709', $ttl = 0)
{
    if (empty($txt))
        return $txt;
    if (empty($key))
        $key = md5($key);

    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
    $ikey = "-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
    $knum = 0;
    $i = 0;
    $tlen = @strlen($txt);
    while (isset($key{$i}))
        $knum += ord($key{$i++});
    $ch1 = @$txt{$knum % $tlen};
    $nh1 = strpos($chars, $ch1);
    $txt = @substr_replace($txt, '', $knum % $tlen--, 1);
    $ch2 = @$txt{$nh1 % $tlen};
    $nh2 = @strpos($chars, $ch2);
    $txt = @substr_replace($txt, '', $nh1 % $tlen--, 1);
    $ch3 = @$txt{$nh2 % $tlen};
    $nh3 = @strpos($chars, $ch3);
    $txt = @substr_replace($txt, '', $nh2 % $tlen--, 1);
    $nhnum = $nh1 + $nh2 + $nh3;
    $mdKey = substr(md5(md5(md5($key . $ch1) . $ch2 . $ikey) . $ch3), $nhnum % 8, $knum % 8 + 16);
    $tmp = '';
    $j = 0;
    $k = 0;
    $tlen = @strlen($txt);
    $klen = @strlen($mdKey);
    for ($i = 0; $i < $tlen; $i++) {
        $k = $k == $klen ? 0 : $k;
        $j = strpos($chars, $txt{$i}) - $nhnum - ord($mdKey{$k++});
        while ($j < 0)
            $j += 64;
        $tmp .= $chars{$j};
    }
    $tmp = str_replace(array(
        '-',
        '_',
        '.'
    ), array(
        '+',
        '/',
        '='
    ), $tmp);
    $tmp = trim(base64_decode($tmp));

    if (preg_match("/\d{10}_/s", substr($tmp, 0, 11))) {
        if ($ttl > 0 && (time() - substr($tmp, 0, 11) > $ttl)) {
            $tmp = null;
        } else {
            $tmp = substr($tmp, 11);
        }
    }
    if (strpos($tmp, ",")) {
        $tmp = explode(",", $tmp);
    }
    return $tmp;
}

function _encrypt($txt, $key = '59e2b673ad709')
{
    if (empty($txt))
        return $txt;
    if (empty($key))
        $key = md5($key);
    if (is_array($txt)) {
        $txt = implode(",", $txt);
    }
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
    $ikey = "-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
    $nh1 = rand(0, 64);
    $nh2 = rand(0, 64);
    $nh3 = rand(0, 64);
    $ch1 = $chars{$nh1};
    $ch2 = $chars{$nh2};
    $ch3 = $chars{$nh3};
    $nhnum = $nh1 + $nh2 + $nh3;
    $knum = 0;
    $i = 0;
    while (isset($key{$i}))
        $knum += ord($key{$i++});
    $mdKey = substr(md5(md5(md5($key . $ch1) . $ch2 . $ikey) . $ch3), $nhnum % 8, $knum % 8 + 16);
    $txt = base64_encode(time() . '_' . $txt);
    $txt = str_replace(array(
        '+',
        '/',
        '='
    ), array(
        '-',
        '_',
        '.'
    ), $txt);
    $tmp = '';
    $j = 0;
    $k = 0;
    $tlen = strlen($txt);
    $klen = strlen($mdKey);
    for ($i = 0; $i < $tlen; $i++) {
        $k = $k == $klen ? 0 : $k;
        $j = ($nhnum + strpos($chars, $txt{$i}) + ord($mdKey{$k++})) % 64;
        $tmp .= $chars{$j};
    }
    $tmplen = strlen($tmp);
    $tmp = substr_replace($tmp, $ch3, $nh2 % ++$tmplen, 0);
    $tmp = substr_replace($tmp, $ch2, $nh1 % ++$tmplen, 0);
    $tmp = substr_replace($tmp, $ch1, $knum % ++$tmplen, 0);
    return $tmp;
}