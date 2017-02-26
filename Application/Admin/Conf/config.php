<?php
return array(
    //'配置项'=>'配置值'
    'default_m_layer' => 'Model',
    'Page_Title' => '国投保理信息系统',
    'UPLOAD_CONFIG' => array(
        'maxSize' => 83886080,
        'saveName' => array('uniqid', ''),
        'exts' => array('jpg', 'gif', 'png', 'jpeg', 'pdf', 'doc', 'docx', 'ppt', 'pptx', 'xlsx', 'xls', 'rar', 'tif','txt'),
        'autoSub' => true,
        'callback' => 'my_file_exists'
    ),

    'DATA_CACHE_PREFIX' => '',//缓存前缀
    'DATA_CACHE_TYPE'=>'Redis',//默认动态缓存为Redis
    'REDIS_RW_SEPARATE' => true, //Redis读写分离 true 开启
    'REDIS_HOST'=>'127.0.0.1', //线上地址：10.46.69.13//redis服务器ip，多台用逗号隔开；读写分离开启时，第一台负责写，其它[随机]负责读；
    'REDIS_PORT'=>'6379',//端口号
    'REDIS_TIMEOUT'=>'0',//超时时间
    'REDIS_PERSISTENT'=>true,//是否长连接 false=短连接
    'REDIS_AUTH'=>'',//AUTH认证密码

    'page_sizes' => array(30, 60, 90, 150), //页面分页选项
    'page_default_size' => 30,  //默认分页数
    'DM_COLLECT' => array(
        'url' => 'http://res.atrmoney.com//api/borrow.php',
        'oid' => 25,
        'fp_id' => 11,
        'mid' => 1
    ),
    'LOAD_EXT_CONFIG' => array('Pro'=>'process','Ind'=>'industries','forms'=>'forms'),
    'assure_type' => array(
        1 => '个人担保',
        2 => '企业担保',
        3 => '应收账款质押',
        4 => '房屋抵押',
        5 => '机械设备抵押',
        6 => '股权质押',
        7 => '货权凭证',
        8 => '其它',
    ),
    //融资方
    'trade_type'=>array(
        1=>'个人',
        2=>'企业',
        3=>'金融资产交易所',
        4=>'保理机构',
        5=>'其它'
    ),
    'contract_pay_type' => array(
        1 => '网银(E-GOLD)',
        2 => '银行汇款(Bank Transfers)',
        3 => '现金(cash)',
        4 => '商票',
        5 => '其它(others)',
    ),
    'contract_debt_type' => array(
        1 => '债权转让通知书（三方确认）',
        2 => '债权转让通知书（EMS送达）',
        3 => '商票背书',
        4 => '其他:'
    ),
    'REVIEW_FILE_AUTHO' => array('1'),
    'MAIL_ADDRESS' => 'yefan@gtfactoring.com', // 邮箱地址
    'MAIL_SMTP' => 'smtp.exmail.qq.com', // 邮箱SMTP服务器
    'MAIL_LOGINNAME' => 'yefan@gtfactoring.com', // 邮箱登录帐号
    'MAIL_PASSWORD' => 'a154289920A', // 邮箱密码
    'MAIL_CHARSET' => 'UTF-8',//编码
    'MAIL_AUTH' => true,//邮箱认证
    'MAIL_HTML' => true,//true HTML格式 false TXT格式
    'checkuser' => array(
        1 => array('21'),
        2 => array('9999'),
        3 => array('24'),
        4 => array('17'),
        5 => array('20'),
        6 => array('19'),
        7 => array('22'),
        8 => array('13'),
    ),
    'roles' => array(
        1 => '请款申请人',
        21 => '法务负责人',
        9999 => '货后风险控制人', //这个角色是由风控总监指定的，可变动，由project的after_loan_admin确定
        24 => '货中审核人',
        17 => '风控总监',
        18 => '风控专员',
        20 => '副总裁',
        19 => '总裁',
        22 => '出纳',
        13 => '财务部负责人',

    ),
    //页面权限状态设置
    'authpage' => array(
        'pre' => '全部',
        'suf' => '审核后',
        'com' => '驳回',
    ),
    //消息推送权限设置
     'messAuth'=>array(
        1=>array(
            //状态描述
           'depict'=>'项目管理流程',
            //对应的proLevel子状态集合
           'sub'=>array('-1','0','0_1','0_2','0_3','4','4_1','4_2','4_3','4_4','4_5','5','5_1','5_2','5_3',
               '6','6_1','6_2','6_3','6_4','6_5',
               '7','7_1','7_2','7_3','7_4','7_5','7_6','8','8_1','8_2','8_3','8_4','8_5','8_6','9','9_1','9_2','9_3','9_4','9_5','9_6'
           ),
        ),
         2=>array(
             'depict'=>'签约流程',
             'sub'=>array('-2','10','10_1','10_2','10_3','10_4','10_5','10_6','11','11_1','11_2','11_3','11_4','11_5','11_6','11_7','12','12_1','12_2',
                 '12_3','12_4','13','13_1','13_2','13_3','13_4','13_5')
         ),
         3=>array(
             'depict'=>'放款流程',
             'sub'=>array('-3','14','14_1','14_2','14_3','15','15_1','15_2','15_3','15_4','15_5','15_6','15_7','15_8','15_9','15_10','15_11','17','17_1','17_2','17_3','17_4','17_5','17_6','17_7','17_8','17_9','17_10','17_11','16','16_1','16_2','16_3','16_4','16_5','18','18_1','18_2','18_3','18_4','18_5','18_6','18_7','18_8','18_9')

         ),
         4=>array(
             'depict'=>'项目完结流程',
             'sub'=>array()
         )
     ),
    //创建项目后立即创建文件夹的名称
    'upLoadFolder'=>array(
        1=>array('name'=>'项管部','secret'=>'6',
            'sub'=>array(
                1=>array('name'=>'反馈','secret'=>'1'),//'1'是默认公开文件夹
                2=>array('name'=>'投票','secret'=>'4'),  //'4'是对应配置文件fileLevel的下标值
            )),
        2=>array('name'=>'风控部','secret'=>'6'),
        3=>array('name'=>'财务','secret'=>'6'),
        4=>array('name'=>'合同','secret'=>'6','sub'=>array(
            1=>array('name'=>'合同初稿','secret'=>'1'),//'1'是默认公开文件夹
            2=>array('name'=>'合同终稿','secret'=>'1'),//'1'是默认公开文件夹
        )),
        5=>array('name'=>'项目资料','secret'=>'6'),
    ),
    //文件角色等级划分，1为普通的文件，所有人都可以查看，不需要做特殊处理
    'fileLevel'=>array(
        //项目部的机密文件夹，
        2=>array(
            'name'=>'项管部',
            'role_id'=>'2,14,16'
        ),
        3=>array(
            'name'=>'财务部',
            'role_id'=>'7,13,22'
        ),
        4=>array(
            'name'=>'项管总监',
            'role_id'=>'14'),
        5=>array(
            'name'=>'同业部总监',
            'role_id'=>'33'),
        6=>array(
            'name'=>'非同业部人员',
            'role_id'=>'1,2,7,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,32',
        )

    ),
    //上传文件夹改变状态的子流程下标
    'changeUplodState'=>array(
        1=>'4_3',
        2=>'5_2',
        3=>'6_2',
        4=>'8_2',
        5=>'9_2',
        6=>'14_2',
        7=>'15_10',
        8=>'10_3',
    ),
    
    //下载文件夹改变状态的子流程下标
    'changeDodownState'=>array(
        1=>'5_2',
    ),
    //默认子流程归档
    'proLevelClass'=>array(
        '立项子流程'=>array('0','0_1','0_2','0_3'),
        '通知股权和风控知情'=>array('4','4_1','4_2','4_3','4_4'),
        '风控项目审核流程'=>array('5','5_1','5_2','5_3'),
        '召开立项会'=>array('6','6_1','6_2','6_3'),
        '风控报告编写'=>array('7','7_1','7_2','7_3','7_4'),
        '风控会召开流程'=>array('8','8_1','8_2','8_3'),
        '投委会召开流程'=>array('9','9_1','9_2','9_3','9_4'),
        '风控意见出具流程'=>array('10','10_1','10_2','10_3','10_4','10_5','10_6'),
        '合同预签流程'=>array('11','11_1','11_2','11_3','11_4','11_5','11_6'),
        '合同审核流程'=>array('12','12_1','12_2','12_3'),
        '线下签约流程'=>array('13','13_1','13_2','13_3','13_4'),
        '商票上传流程'=>array('14','14_1','14_2','14_3'),
        '放款审核流程'=>array('15','15_1','15_2','15_3','15_4','15_5','15_6','15_7','15_8','15_9','15_10','15_11'),
        '日常利息归还'=>array('16','16_1','16_2','16_3','16_4','16_5'),
        '商票退票流程'=>array('17','17_1','17_2','17_3','17_4','17_5','17_6','17_7','17_8','17_9','17_10','17_11'),
        '换质退款审批'=>array('20','20_1','20_2','20_3','20_4','20_5','20_6','20_7','20_8','20_9','20_10','20_11'),
        '换质退款,退票审批'=>array('21','21_1','21_2','21_3','21_4','21_5','21_6','21_7','21_8','21_9','21_10','21_11'),
        '完结退款审批'=>array('22','22_1','22_2','22_3','22_4','22_5','22_6','22_7'),
        '正常完结退票审批'=>array('23','23_1','23_2','23_3','23_4','23_5','23_6'),
        '非正常完结退票审批'=>array('24','24_1','24_2','24_3','24_4','24_5','24_6','24_7'),
        'OA请款流程'=>array('18','18_1','18_2','18_3','18_4','18_5','18_6','18_7','18_8','18_9'),
        '申请资料下载'=>array('25','25_1','25_2'),
    ),
    'proLevel' => array(
        //立项总流程
        '0' => '项目上报及初审',
        '0_1' => '项目分配',//项管总监  存redis用了role
        '0_2' =>  '初审反馈',
        '0_3' =>  '初审通过',
        //新的子流程1-通知股权和风控知情
        '4'=> '会议报告编写通知',
        '4_1'=> '人员知情',
        '4_2'=>'人员分配',
        '4_3'=>'上传报告',
        '4_4'=>'会议报告准备完成',
        //新的子流程2-风控项目审核流程
        '5'=>'项目审核通知',
        '5_1'=>'风控流程审核',
        '5_2'=>'人员下载知情',
        '5_3'=>'项目审核完成',
        //新的子流程3-召开立项会
        '6'=>'召开立项会',
        '6_1'=>'会议确认',
        '6_2'=>'会议通知',//这里的项管专员不会知情
        '6_3'=>'统计结果',
        '6_4'=>'立项会投票发布',
        //新的子流程7-风控报告编写
        '7'=>'编写风控报告',
        '7_1'=>'报告编写',
        '7_2'=>'报告知情',
        '7_3'=>'报告归档',
       //新的子流程8-风控会召开流程
        '8'=>'召开风控会',
        '8_1'=>'会议确认',
        '8_2'=>'会议通知', //这里的项管专员不会知情
        '8_3'=>'统计结果',
        '8_4'=>'风控会投票发布',
        //新的子流程9-投委会召开流程
        '9'=>'召开投委会',
        '9_1'=>'会议确认',
        '9_2'=>'会议通知',
        '9_3'=>'统计结果',
        '9_4'=>'投委会投票发布',
        //签约流程-子流程风控意见出具流程
        '10'=>'出具风控审核意见',
        '10_1'=>'风控审核流程知情', //后面要加上，没有改变
        '10_2'=>'风控审核流程分配人员',//后面要加上，没有改变
        '10_3'=>'提交方案',
        '10_4'=>'审核方案',
        '10_5'=>'风控审核意见法务知情',
        '10_6'=>'归档',
      //合同预签流程
        '11'=>'合同预签申请',
        '11_1'=>'预签知情',
        '11_2'=>'预签审批',
        '11_3'=>'预签审批',
        '11_4'=>'预签审批',
        '11_5'=>'法务知情',
        '11_6'=>'合同预签申请表归档',
        //合同审核流程
        '12'=>'审核合同',
        '12_1'=>'合同审核',
        '12_2'=>'合同知情',
        '12_3'=>'合同审核完成',
        //线下签约流程
        '13'=>'线下签约',
        '13_1'=>'签约知情',
        '13_2'=>'上传合同',
        '13_3'=>'签约知情',
        '13_4'=>'线下签约完成',
        //放款流程-商票上传流程
        '14'=>'商票上传流程',
        '14_1'=>'请求知情',
        '14_2'=>'商票上传',
        '14_3'=>'商票上传完成',
        //放款审核流程
        '15'=>'请款审批',
        '15_1'=>'请款知情',
        '15_2'=>'放款法务审核',
        '15_3'=>'贷中审核',
        '15_4'=>'主审审核',
        '15_5'=>'请款知情',
        '15_6'=>'请款审批',
        '15_7'=>'请款审批',
        '15_8'=>'请款审批',
        '15_9'=>'财务审批',
        '15_10'=>'上传凭证',
        '15_11'=>'放款完成',
        //日常利息归还
        '16'=>'新建日常利息',
        '16_1'=>'日常利息财务知情',
        '16_2'=>'日常利息出纳上传资料',
        '16_3'=>'按流水挑拣项目',
        '16_4'=>'日常利息知情',
        '16_5'=>'结束',
        //商票退票流程
        '17'=>'换质退票审批',
        '17_1'=>'退票知情',
        '17_2'=>'法务审核',
        '17_3'=>'贷中审核',
        '17_4'=>'主审审核',
        '17_5'=>'退票知情',
        '17_6'=>'退票审批',
        '17_7'=>'退票审批',
        '17_8'=>'退票审批',
        '17_9'=>'财务审批',
        '17_10'=>'上传凭证',
        '17_11'=>'退票完成',
        //OA审批流程
        '18'=>'新建请款书',
        '18_1'=>'请款审核-项管总监',
        '18_2'=>'请款审核-财务A轮',
        '18_3'=>'请款审核-财务B轮',
        '18_4'=>'请款审核-总经理审核',
        '18_5'=>'请款审核-副总裁',
        '18_6'=>'请款审核-总裁',
        '18_7'=>'请款审核-财务C轮',
        '18_8'=>'请款审核-财务D轮',
        '18_9'=>'结束',
        //换质退款审批
        '20'=>'换质退款审批',
        '20_1'=>'退款知情',
        '20_2'=>'法务审核',
        '20_3'=>'贷中审核',
        '20_4'=>'主审审核',
        '20_5'=>'退款知情',
        '20_6'=>'退款审批',
        '20_7'=>'退款审批',//孙总
        '20_8'=>'退款审批',//佟
        '20_9'=>'财务审批',
        '20_10'=>'上传凭证',
        '20_11'=>'结束',
        //换质退款、退票流程
        '21'=>'换质退款、退票审批',
        '21_1'=>'换质知情',
        '21_2'=>'法务审核',
        '21_3'=>'贷中审核',
        '21_4'=>'主审审核',
        '21_5'=>'换质知情',
        '21_6'=>'换质审批',
        '21_7'=>'换质审批',//孙总
        '21_8'=>'换质审批',//佟
        '21_9'=>'财务审批',
        '21_10'=>'上传凭证',
        '21_11'=>'结束',
        //完结退款审批
        '22'=>'完结退款审批',
        '22_1'=>'退款审批',
        '22_2'=>'财务复核',
        '22_3'=>'退款审批',
        '22_4'=>'退款审批',
        '22_5'=>'财务审批',
        '22_6'=>'上传凭证',
        '22_7'=>'结束',
        //正常完结退票审批
        '23'=>'正常完结退票审批',
        '23_1'=>'退票审批',
        '23_2'=>'退票审批',
        '23_3'=>'退票审批',
        '23_4'=>'财务审批',
        '23_5'=>'上传凭证',
        '23_6'=>'结束',
        //非正常完结退票审批
        '24'=>'非正常完结退票审批',
        '24_1'=>'退票审批',
        '24_2'=>'退票审批',
        '24_3'=>'退票审批',
        '24_4'=>'退票审批',
        '24_5'=>'财务审批',
        '24_6'=>'上传凭证',
        '24_7'=>'结束',
        //同业部总监跑流程专用
        '19'=>'新建资料下载',
        '19_1'=>'资料下载知情',
        '19_2'=>'结束',
        //同业部经理跑流程专用
        '25'=>'新建资料下载',
        '25_1'=>'资料下载审核',
        '25_2'=>'资料下载知情',
        '25_3'=>'结束',
    ),

    //页面权限，方法池
    'pageAuthFun'=>array(
        1=>'/Admin/Project/edit',
        2=>'/Admin/Project/file',
        3=>'/Admin/Project/exchange',
        4=>'/Admin/Project/editSubProcess',
        5=>'/Admin/Project/remark',
        6=>'/Admin/Project/ProjectMeetingCheckFile',
        7=>'/Admin/Project/auditEdit'
    ),
    //页面权限按钮池，键是页面中每个按钮操作的名称，值对应页面权限，方法池中的链接
    'pageAuth'=>array(
        '提交'=>1,
        '编辑'=>1,
        '资料包'=>2,
        '分配人员'=>3,
        '交接'=>3,
        '审核风控流程'=>4,
        '投票审核'=>4,
        '审核'=>4,
        '备注'=>5,
        '查看流程'=>6,
        '初审反馈'=>7,
    ),
    'process'=>'22222',
    //[16]
);
