<?php

/*
	功能：演示使用类库对 HTML 代码进行安全过滤
	注意：
		缓存类型自行配置 conf.php 文件中的 cache type
		返回 NULL 表示结果不存在，返回 FALSE 表示错误。
*/

chdir('../../../');

include './xiunophp/xn_html_safe.func.php';

$s = '<div onclick="alert(123)">xss</div>';
$r = xn_html_safe($s);

echo $r;

/*

结果输出：

<div>xss</div>

*/

?>