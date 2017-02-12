<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/12 0012
 * Time: 下午 10:03
 */

namespace Admin\Lib;


class AsynReturn
{
    public  function init($url,$postData)
    {
        $this->url=$url;
        $this->postData=$postData;
    }
    public function request_post() {
        $url=$this->url;
        $post_data=$this->postData;
        if (empty($url) || empty($post_data)) {
            return false;
        }

        $o = "";
        foreach ( $post_data as $k => $v )
        {
            $o.= "$k=" . urlencode( $v ). "&" ;
        }
        $post_data = substr($o,0,-1);

        $postUrl = $url;
        $curlPost = $post_data;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        return $data;
    }
}