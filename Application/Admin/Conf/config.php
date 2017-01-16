<?php
return array(
    //'配置项'=>'配置值'
    'default_m_layer' => 'Model',
    'Page_Title' => '国投保理信息系统',
    'UPLOAD_CONFIG' => array(
        'maxSize' => 83886080,
        'saveName' => array('uniqid', ''),
        'exts' => array('jpg', 'gif', 'png', 'jpeg', 'pdf', 'doc', 'docx', 'ppt', 'pptx', 'xlsx', 'xls', 'rar', 'tif'),
        'autoSub' => true,
        'callback' => 'my_file_exists'
    ),

    'DATA_CACHE_PREFIX' => '',//缓存前缀
    'DATA_CACHE_TYPE'=>'Redis',//默认动态缓存为Redis
    'REDIS_RW_SEPARATE' => true, //Redis读写分离 true 开启
    'REDIS_HOST'=>'127.0.0.1', //redis服务器ip，多台用逗号隔开；读写分离开启时，第一台负责写，其它[随机]负责读；
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
             'sub'=>array('-3','14','14_1','14_2','14_3','15','15_1','15_2','15_3','15_4','15_5','15_6','15_7','15_8','15_9','15_10','15_11'
             ,'17','17_1','17_2','17_3','17_4','17_5','17_6','17_7','17_8','17_9','17_10','17_11','16','16_1','16_2','16_3','16_4','16_5')
         ),
         4=>array(
             'depict'=>'项目完结流程',
             'sub'=>array()
         )
     ),
    //创建项目后立即创建文件夹的名称
    'upLoadFolder'=>array(
        1=>'立项',
        2=>'股权与风控知情',
        3=>'风控审核',
        4=>'立项会',
        5=>'风控报告',
        6=>'风控会',
        7=>'投委会',
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
        '子流程风控意见出具流程'=>array('10','10_1','10_2','10_3','10_4','10_5','10_6'),
        '合同预签流程'=>array('11','11_1','11_2','11_3','11_4','11_5','11_6'),
        '合同审核流程'=>array('12','12_1','12_2','12_3'),
        '线下签约流程'=>array('13','13_1','13_2','13_3','13_4'),
        '商票上传流程'=>array('14','14_1','14_2','14_3'),
        '放款审核流程'=>array('15','15_1','15_2','15_3','15_4','15_5','15_6','15_7','15_8','15_9','15_10','15_11'),
        '日常利息归还'=>array('16','16_1','16_2','16_3','16_4','16_5'),
        '商票退票流程'=>array('17','17_1','17_2','17_3','17_4','17_5','17_6','17_7','17_8','17_9','17_10','17_11'),
    ),
    'proLevel' => array(
        //立项总流程
        '0' => '项目经理_立项',
        '0_1' => '分配跟进人',//项管总监  存redis用了role
        '0_2' =>  '立项归档',
        '0_3' =>  '立项_结束',
        //新的子流程1-通知股权和风控知情
        '4'=> '新建知情',
        '4_1'=> '知情审核',  //财务总监  存redis用了role
        '4_2'=>'分配知情人员',
        '4_3'=>'上传知情资料',
        '4_4'=>'上传完成_结束',
        //新的子流程2-风控项目审核流程
        '5'=>'新建风控审核',
        '5_1'=>'风控流程审核',
        '5_2'=>'风控部下载资料',
        '5_3'=>'资料下载结束',
        //新的子流程3-召开立项会
        '6'=>'新建立项会',
        '6_1'=>'立项会审核',
        '6_2'=>'立项会知情',
        '6_3'=>'立项会投票审核',
        '6_4'=>'立项会投票发布',
        //新的子流程7-风控报告编写
        '7'=>'新建风控报告',
        '7_1'=>'报告编写',
        '7_2'=>'报告初审',
        '7_3'=>'报告最终审核',
        '7_4'=>'报告归档',
       //新的子流程8-风控会召开流程
        '8'=>'新建风控会',
        '8_1'=>'风控会审核',
        '8_2'=>'风控会知情',
        '8_3'=>'风控会投票审核',
        '8_4'=>'风控会投票发布',
        //新的子流程9-投委会召开流程
        '9'=>'新建投委会',
        '9_1'=>'投委会审核',
        '9_2'=>'投委会知情',
        '9_3'=>'投委会投票审核',
        '9_4'=>'投委会投票发布',
        //签约流程-子流程风控意见出具流程
        '10'=>'新建风控审核意见书',
        '10_1'=>'风控审核流程审核',
        '10_2'=>'风控审核流程分配人员',
        '10_3'=>'风控审核流程提交文档',
        '10_4'=>'风控审核流程复审',
        '10_5'=>'风控审核意见法务知情',
        '10_6'=>'归档',
      //合同预签流程
        '11'=>'新建合同',
        '11_1'=>'合同知情',
        '11_2'=>'合同编辑',
        '11_3'=>'法务审核合同',
        '11_4'=>'副总裁审核合同',
        '11_5'=>'总裁审核合同',
        '11_6'=>'法务知情',
        '11_7'=>'合同归档',
        //合同审核流程
        '12'=>'新建合同审核流程',
        '12_1'=>'合同审核',
        '12_2'=>'法务知情',
        '12_3'=>'归档',
        //线下签约流程
        '13'=>'新建线下签约流程',
        '13_1'=>'签约总裁办知情',
        '13_2'=>'提交签约文档',
        '13_3'=>'线下签约知情',
        '13_4'=>'线下签约结束',
        //放款流程-商票上传流程
        '14'=>'新建商票上传流程',
        '14_1'=>'商票知情',
        '14_2'=>'商票上传',
        '14_3'=>'结束',
        //放款审核流程
        '15'=>'新建放款表',
        '15_1'=>'放款流程知情',
        '15_2'=>'放款法务审核',
        '15_3'=>'放款风控A轮初审',
        '15_4'=>'放款风控B轮初审',
        '15_5'=>'放款风控总监知情',
        '15_6'=>'放款风控总监审核',
        '15_7'=>'放款副总裁审核',
        '15_8'=>'放款总裁审核',
        '15_9'=>'放款财务审批',
        '15_10'=>'放款出纳上传资料',
        '15_11'=>'放款结束',
        //日常利息归还
        '16'=>'新建日常利息',
        '16_1'=>'日常利息财务知情',
        '16_2'=>'日常利息出纳上传资料',
        '16_3'=>'按流水挑拣项目',
        '16_4'=>'项管总监知情',
        '16_5'=>'结束',
        //商票退票流程
        '17'=>'新建商票退票',
        '17_1'=>'商票知情',
        '17_2'=>'商票法务审核',
        '17_3'=>'商票风控A轮初审',
        '17_4'=>'商票风控B轮初审',
        '17_5'=>'商票风控总监知情',
        '17_6'=>'商票风控总监审核',
        '17_7'=>'商票副总裁审核',
        '17_8'=>'商票总裁审核',
        '17_9'=>'商票财务审批',
        '17_10'=>'商票出纳上传资料',
        '17_11'=>'放款结束',
    ),
    //文件角色等级划分，1为普通的文件，所有人都可以查看，不需要做特殊处理
    'fileLevel'=>array(
        //项目部的机密文件夹，
        2=>array(
          'role_id'=>'2,14,16'
        ),

    ),
    //页面权限，方法池
    'pageAuthFun'=>array(
        1=>'/Admin/Project/edit',
        2=>'/Admin/Project/file',
        3=>'/Admin/Project/exchange',
        4=>'/Admin/Project/editSubProcess',
        5=>'/Admin/Project/remark',
        6=>'/Admin/Project/ProjectMeetingCheckFile',
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
        '审核/提交'=>4,
        '备注'=>5,
        '查看流程'=>6,
    ),
    'process'=>'22222'
);
