<?php

// 用来生成框架代码和目录
empty($args[1]) AND $args[1] = 'plugin.xiuno.com';
$appname = $args[1];

include '../xiunophp.php';

mkdir('./'.$appname);

mkdir("./$appname/tmp");
mkdir("./$appname/log");
mkdir("./$appname/upoad");
mkdir("./$appname/conf");


mkdir("./$appname/conf");
copy('./conf.default.php', "./$appname/conf/conf.php");

