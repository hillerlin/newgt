<?php
namespace Admin\Controller;

class CheckController extends CommonController
{
      
    public function liumiAction()
    {
        header("Content-type:text/html;charset=utf-8");
/*        $a=(float)5.02;
        var_dump($a);die;*/
        //颜色：白色 黑色 黄色
        //尺寸: 1 2 3
       // $list = array('brandList' => '','areaList' => '','attrList' => '');



        //var_dump(json_encode($list));die;
        //$a[]=array('cartId'=>1903,'num'=>1,'goods_id'=>'355','market_price'=>'1500','goods_price'=>'1000','goods_attr'=>'颜色:白色,版本:官方版,镜头:广角,');
        //$a[]=array('cartId'=>1904,'num'=>1,'goods_id'=>'346','market_price'=>'261.59','goods_price'=>'218','goods_attr'=>'颜色:白色,尺码:150,123:1,456:2,');
        //var_dump(json_encode($a));die;
        //var_dump($a);die;
/*        $a='镜头:鱼眼,版本:官方版,颜色:白色,';
        $a=explode(',',$a);
        $a=array_filter($a);
        foreach($a as $k=>$v)
        {
            $newAttr[]=explode(':',$v)[1];

        }
        var_dump($newAttr);die;*/
        //$newdata['default_chose'] = 1;
       // $num= M('user_address')->where("`user_id`='47'")->save($newdata);
       // var_dump($num);die;
/*        $valList = array('899', '4.5', 'Adroid', '白色');
        var_dump(json_encode($valList));die;*/
       // $list = array('brandList' => '40','areaList' => '4','attrList' => array('682' => array('黑色'),'681' => array('白色')));
       // $list="array('brandList' => '','areaList' => '','attrList' => array('687' => array('官方版')))";
        // $a='[{"cartId":2269,"num":1,"goods_id":"519","market_price":"166.79","goods_price":"139.00","goods_attr":"\u989c\u8272:\u767d\u8272,\u7248\u672c:\u5b98\u65b9\u7248,\u955c\u5934:\u5e7f\u89d2,"},{"cartId":2267,"num":1,"goods_id":"1090","market_price":"147.6","goods_price":"123.00","goods_attr":"\u989c\u8272:\u767d\u8272,\u5c3a\u7801:150,123:1,456:2,"}]';
        //$a='[{"cartId": 2269,"num": 1,"goods_id": "519","market_price": "166.79","goods_price": "139.00", "goods_attr": "\U7f8e\U56fdKirkland\U79d1\U514b\U5170\U539f\U5473\U6838\U6843\U4ec11.36kg"},{"cartId": 2267,"num": 1,"goods_id": "1090","market_price": "147.60","goods_price": "123.00","goods_attr": "\U5fb7\U56fdHiPP\U559c\U5b9d\U5a74\U5e7c\U513f\U5976\U7c89\U76ca\U751f\U83ccpre\U521d\U6bb5600g"}]';
      //  var_dump(json_encode($a));die;
        // $url = 'http://api.zcgmall.com/Home/Index/orderList'; // 平台接口地址前缀
        // $url = 'http://api.zcgmall.com/Home/Index/subOrder'; // 平台接口地址前缀
           //$url = 'http://192.168.8.188:9016/admin/dmlc/ProjectApi/waitLoan'; // 平台接口地址前缀
          //$url = 'http://192.168.8.188:9016/admin/dmlc/ProjectApi/requestLoan'; // 平台接口地址前缀
          //$url = 'http://ndm.atrmoney.com/admin/dmlc/ProjectApi/waitLoan'; // 平台接口地址前缀
          $url = 'http://222.73.117.156/msg/HttpBatchSendSM'; // 平台接口地址前缀
         //$url = 'http://api.zcgmall.com/Home/Index/goodsInfo'; // 平台接口地址前缀
//        $params['cateList'] ='{"brandList":"40","areaList":"4","attrList":{"682":["\u9ed1\u8272"]}}';
//        $params['catId'] =170;
/*        $params['list'] ='[{"cartId":956,"num":3}]';
        $params['type'] ='0';
        $params['notify_id'] ='151855b607077dbb0a604f98c3f0a82kee';
        $params['code'] ='2907';
        $params['user_id']=33;
        $params['order_id']=15;
        $params['orderId']=10;
        $params['goods_id'] = '354';
        $params['goods_number']='1';
        $params['goods_attr']='镜头:鱼眼,版本:官方版,颜色:白色';
        $params['list'] ='[{"cartId":956,"num":3}]';*/
   //  $params['catId']=37;
/*        $params['page']=1;
        $params['pageNum']=10;
        $params['bid']=2579;
        $params['loan_status']=1;
        $key=md5('xiaopinguo');
        $json=json_encode($params);
        $sign=md5($json.$key);
        $params['sign']=$sign;*/
        $params=array(
            'account'=>'sz_dmlc',
            'pswd'=>'Dmlc123456',
            'msg'=>'我是来测试的',
            'mobile'=>'18617018050',
            'needstatus'=>'true',
            'product'=>'',
            'extno'=>''
        );




       // $result = $this->request_post($url,array('data'=>json_encode($params)));
        $result = $this->request_post($url,$params);
        $result=preg_split("/[,\r\n]/",$result);
       // $return_data = json_decode($result, true);
        print_r($result);
    }
    
    public function Api_Request($url, $data, $method = "GET")
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        // 以下两行，忽略 https 证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $method = strtoupper($method);
        if ($method == "POST") {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json"
            ));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }
    
 
     public function request_post($url = '', $post_data = array()) {
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