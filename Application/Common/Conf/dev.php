<?php
return array(
	//'配置项'=>'配置值'
    'default_module'     => 'Admin', //默认模块
    'default_m_layer'       =>  'Logic', // 更改默认的模型层名称为Logic
    'url_model'          => '2', //URL模式
    'session_auto_start' => true, //是否开启session

    'URL_HTML_SUFFIX' => 'html|shtml|jsp',
    //数据库的配置
    //数据库配置信息
    'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => '192.168.8.4', // 服务器地址
//    'DB_HOST'   => 'localhost',
//    'DB_USER'   => 'root', // 数据库名
    'DB_PWD'    => '', // 密码
    'DB_NAME'   => 'gt', // 数据库名
    'DB_USER'   => 'gt', // 用户名
    'DB_PWD'    => 'gt', // 密码
    'DB_PORT'   => 3306, // 端口
    'DB_PARAMS' =>  array(), // 数据库连接参数
    'DB_PREFIX' => 'gt_', // 数据库表前缀 
    'DB_CHARSET'=> 'utf8', // 字符集
    'DB_DEBUG'  =>  TRUE, // 数据库调试模式 开启后可以记录SQL日志

    'LOAD_EXT_CONFIG' => array('USER'=>'web'), 
    
);