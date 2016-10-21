<?php
return array(
  'MODULE_ALLOW_LIST'  => array('Frontend','Backend'),
  'DEFAULT_MODULE' => 'Frontend', //默认模块
  'URL_MODEL' => '2', //URL模式
  'URL_HTML_SUFFIX'  => '',  // URL伪静态后缀设置
  'SESSION_AUTO_START' => true, //是否开启session
  'SESSION_OPTIONS' => array('expire'=>18144000),
  'URL_CASE_INSENSITIVE' => true,//url不区分大小写
  'VAR_PATHINFO' => 'r',
  'LOAD_EXT_CONFIG' =>array('set'=>'set'),
  //数据库配置信息
  'DB_TYPE'   => 'mysql', // 数据库类型
  'DB_HOST'   => 'localhost', // 服务器地址
  'DB_NAME'   => 'dev', // 数据库名
  'DB_USER'   => 'root', // 用户名
  'DB_PWD'    => '123456', // 密码
  'DB_PORT'   => 3306, // 端口
  'DB_PREFIX' => 'c_', // 数据库表前缀 
  'DB_CHARSET'=> 'utf8', // 字符集
  //域名部署
  'APP_SUB_DOMAIN_DEPLOY'   =>    1, // 开启子域名或者IP配置
  'APP_SUB_DOMAIN_RULES'    =>    
  		array(
  		'admin.pinkdream.cn' => 'Backend',
  		'shop.pinkdream.cn'   => 'Frontend',
   ),
   //'SHOW_PAGE_TRACE'=>true,
);