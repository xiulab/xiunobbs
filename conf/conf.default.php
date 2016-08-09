<?php
return array (
  'db' => 
  array (
    'type' => 'mysql',
    'mysql' => 
    array (
      'master' => 
      array (
        'host' => 'localhost',
        'user' => 'root',
        'password' => 'root',
        'name' => 'test',
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
        'host' => 'localhost',
        'user' => 'root',
        'password' => 'root',
        'name' => 'test',
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
  'timezone' => 'Asia/Shanghai',
  'lang' => 'zh-cn',
  'runlevel' => 5,
  'runlevel_reason' => '站点正在维护中，请稍后访问。',
  'cookie_domain' => '',
  'cookie_path' => '',
  'auth_key' => 'efdkjfjiiiwurjdmclsldow753jsdj438',
  'pagesize' => 20,
  'postlist_pagesize' => 1000,
  'cache_thread_list_pages' => 10,
  'online_update_span' => 120,
  'online_hold_time' => 3600,
  'session_delay_update' => 0,
  'upload_image_width' => 927,
  'new_thread_days' => 3,
  'order_default' => 'lastpid',
  'update_views_on' => 1,
  'version' => '3.0',
  'cdn_on' => 1,
  'url_rewrite_on' => 0,
  'user_create_email_on' => 0,
  'user_resetpw_on' => 0,
  'installed' => 0,
);
?>