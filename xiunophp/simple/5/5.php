<?php

/*
	功能：演示操作 Xcache 缓存
	注意：
		缓存类型自行配置 conf.php 文件中的 cache type
		返回 NULL 表示结果不存在，返回 FALSE 表示错误。
*/

chdir('../../../');

// 请自行修改 conf.php 中的 cache 配置
$conf = include './simple/5/conf.php';

include './xiunophp/xiunophp.php';

cache_set('key1', 'value1');

echo cache_get('key1');

cache_delete('test');

cache_truncate();

/*

结果输出：

value1

*/

?>