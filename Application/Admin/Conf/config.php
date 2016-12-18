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
    'LOAD_EXT_CONFIG' => 'process,industries',
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
        'pre' => '待审核',
        'suf' => '审核后',
        'com' => '驳回'
    ),
    //消息推送权限设置
     'messAuth'=>array(
        1=>array(
            //状态描述
           'depict'=>'项目管理流程',
            //对应的proLevel子状态集合
           'sub'=>array('-1','0','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18'),
        ),
         2=>array(
             'depict'=>'签约流程',
             'sub'=>array()
         ),
         3=>array(
             'depict'=>'放款流程',
             'sub'=>array()
         ),
         4=>array(
             'depict'=>'项目完结流程',
             'sub'=>array()
         )
     ),
    'proLevel' => array(
        //立项总流程
        0 => '项目经理_立项',
        1 => '分配跟进人',
        2 =>  '立项归档',
        3 =>  '立项_结束',
        //新的子流程1
        4=> '新建知情',
        5=> '知情发起审核',
        6=>'通知相关部门',
        7=>'知情人员分配',
        8=>'上传知情资料'
/*        4 => '召开立项会',//项管专员-新建项目立项流程-预勾选股权和法务相关人选
        5 => '立项会特例审核',//项管总监-需审核才能发送消息给股权部和风控部的人去上传资料，也可以驳回给项管专员
        6 =>'立项会资料上传',//需股权部和风控部老大分配给下面的人去上传资料*/
        //新的子流程2
       // 7 =>'风控项目审核',//项管专员-发起和预勾选要通知风控部下载资料
        //5 => '风控会初审',
    )
);
