<?php
return array (
  'db' => 
  array (
    'type' => 'pdo_mysql',
    'mysql' => 
    array (
      'master' => 
      array (
        'host' => 'localhost',
        'user' => 'root',
        'password' => 'root',
        'name' => 'test',
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
        'host' => '127.0.0.1:3306',
        'user' => 'root',
        'password' => 'root',
        'name' => 'xiuno',
        'charset' => 'utf8',
        'engine' => 'MyISAM',
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
    ),
    'redis' => 
    array (
      'host' => 'localhost',
      'port' => '6379',
    ),
  ),
  'tmp_path' => './tmp/',
  'log_path' => './log/',
  'static_url' => 'static/',
  'upload_url' => 'upload/',
  'upload_path' => './upload/',
  'sitename' => 'Xiuno BBS',
  'timezone' => 'Asia/Shanghai',
  'lang' => 'zh-cn',
  'runlevel' => 5,
  'runlevel_reason' => '站点正在维护中，请稍后访问。',
  'cookie_domain' => '',
  'cookie_path' => '/',
  'auth_key' => 'a551b6bcc2f445508f663d43c485432193de12ab622cfcd96c81dbe33cfbba8a',
  'pagesize' => 20,
  'postlist_pagesize' => 1000,
  'cache_thread_list_pages' => 10,
  'online_update_span' => 120,
  'online_hold_time' => 3600,
  'seo_url_rewrite' => 1,
  'upload_image_width' => 927,
  'new_thread_days' => 3,
  'order_default' => 'lastpid',
  'update_views_on' => 1,
  'agrees_level' => 
  array (
    0 => 30,
    1 => 80,
    2 => 150,
    3 => 300,
  ),
  'posts_level' => 
  array (
    0 => 10,
    1 => 50,
    2 => 100,
    3 => 500,
  ),
  'version' => '3.0',
  'cdn_on' => true,
  'cate' => 
  array (
    1 => '页脚文章',
    2 => '公司动态',
  ),
  'user_create_email_on' => 0,
  'user_find_pw_on' => 0,
  'banip_on' => 0,
  'ipaccess_on' => 0,
  'ipaccess' => 
  array (
    'mails' => 0,
    'users' => 0,
    'threads' => 0,
    'posts' => 0,
    'attachs' => 0,
    'attachsizes' => 0,
    'action1' => 0,
    'action2' => 0,
    'action3' => 0,
    'action4' => 0,
  ),
  'check_flood_on' => 0,
  'check_flood' => 
  array (
    'users' => 10,
    'posts' => 10,
    'threads' => 5,
  ),
  'badword_on' => 0,
  'tietuku_on' => 0,
  'tietuku_token' => '00f47da319173e011683b6f4c63b46f8fe8a9471:ak9XYzQ5YmhIalIwYlNwMFJwaVB6Vm9XMFBjPQ==:eyJkZWFkbGluZSI6MTQ0MDY2MzkzOSwiYWN0aW9uIjoiZ2V0IiwidWlkIjoiNTgyNyIsImFpZCI6IjEyNzc5IiwiZnJvbSI6ImZpbGUifQ==',
  'installed' => 0,
);
?>