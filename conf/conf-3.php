<?php
return array (
  'db' => 
  array (
    'type' => 'pdo_mysql',
    'mysql' => 
    array (
      'master' => 
      array (
        'host' => '127.0.0.1',
        'user' => 'root',
        'password' => 'root',
        'name' => 'xiuno4',
        'tablepre' => 'bbs_',
        'charset' => 'utf8',
        'engine' => 'myisam',
      ),
      'slaves' => 
      array (
      ),
    ),
    'pdo_mysql' => 
    array (
      'master' => 
      array (
        'host' => '127.0.0.1',
        'user' => 'root',
        'password' => 'root',
        'name' => 'xiuno4',
        'tablepre' => 'bbs_',
        'charset' => 'utf8',
        'engine' => 'myisam',
      ),
      'slaves' => 
      array (
      ),
    ),
  ),
  'cache' => 
  array (
    'enable' => true,
    'type' => 'mysql',
    'memcached' => 
    array (
      'host' => 'localhost',
      'port' => '11211',
      'cachepre' => 'bbs_',
    ),
    'redis' => 
    array (
      'host' => 'localhost',
      'port' => '6379',
      'cachepre' => 'bbs_',
    ),
    'xcache' => 
    array (
      'cachepre' => 'bbs_',
    ),
    'yac' => 
    array (
      'cachepre' => 'bbs_',
    ),
    'apc' => 
    array (
      'cachepre' => 'bbs_',
    ),
    'mysql' => 
    array (
      'cachepre' => 'bbs_',
    ),
  ),
  'tmp_path' => './tmp/',
  'log_path' => './log/',
  'view_url' => 'view/',
  'upload_url' => 'upload/',
  'upload_path' => './upload/',
  'sitename' => 'Xiuno BBS',
  'sitebrief' => 'Site Brief',
  'timezone' => 'Asia/Shanghai',
  'lang' => 'zh-cn',
  'runlevel' => 5,
  'runlevel_reason' => 'The site is under maintenance, please visit later.',
  'cookie_domain' => '',
  'cookie_path' => '',
  'auth_key' => 'vlvpi9jvns8j7sn1nd08z41fn4zhnyirerv3x2stn1gsk7lc4pgbb4q2ljmfykwq',
  'pagesize' => 20,
  'postlist_pagesize' => 100,
  'cache_thread_list_pages' => 10,
  'online_update_span' => 120,
  'online_hold_time' => 3600,
  'session_delay_update' => 0,
  'upload_image_width' => 927,
  'order_default' => 'lastpid',
  'attach_dir_save_rule' => 'Ym',
  'update_views_on' => 1,
  'user_create_email_on' => 0,
  'user_resetpw_on' => 0,
  'admin_bind_ip' => 0,
  'cdn_on' => 0,
  'url_rewrite_on' => 0,
  'disabled_plugin' => 0,
  'version' => '4.0',
  'static_version' => '?1.0',
  'installed' => 1,
);
?>