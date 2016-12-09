<?php
return array(
	//'配置项'=>'配置值'
    'default_m_layer' => 'Model',
    'Page_Title'=>'国投保理信息系统',
    'UPLOAD_CONFIG' => array(
        'maxSize' => 83886080,
        'saveName' => array('uniqid', ''),
        'exts' => array('jpg', 'gif', 'png', 'jpeg', 'pdf', 'doc', 'docx', 'ppt', 'pptx', 'xlsx', 'xls', 'rar', 'tif'),
        'autoSub' => true,
        'callback' => 'my_file_exists'
    ),
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
    'MAIL_ADDRESS'=>'yefan@gtfactoring.com', // 邮箱地址
    'MAIL_SMTP'=>'smtp.exmail.qq.com', // 邮箱SMTP服务器
    'MAIL_LOGINNAME'=>'yefan@gtfactoring.com', // 邮箱登录帐号
    'MAIL_PASSWORD'=>'a154289920A', // 邮箱密码
    'MAIL_CHARSET'=>'UTF-8',//编码
    'MAIL_AUTH'=>true,//邮箱认证
    'MAIL_HTML'=>true,//true HTML格式 false TXT格式  
    'checkuser'=>array(
        1=>array('21'),
        2=>array('9999'), 
        3=>array('24'),
        4=>array('17'),
        5=>array('20'),
        6=>array('19'),
        7=>array('22'),
        8=>array('13'),
    ),
    'roles'=>array(
        1=>'请款申请人',
        21=>'法务负责人',
        9999=>'货后风险控制人', //这个角色是由风控总监指定的，可变动，由project的after_loan_admin确定
        24=>'货中审核人',
        17=>'风控总监',
        18=>'风控专员',
        20=>'副总裁',
        19=>'总裁',
        22=>'出纳',
        13=>'财务部负责人',

    ),
    //页面权限状态设置
    'authpage'=>array(
        'pre'=>'待审核',
        'suf'=>'审核后',
        'com'=>'驳回'
    ),
    'proLevel'=>array(
        1=>'分配跟进人',
        2=>'召开立项会',
        3=>'风控会初审',
    )
);
