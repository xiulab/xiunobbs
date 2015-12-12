<?php

/*
	功能：演示调用自带函数
*/

chdir('../../../');

include './xiunophp/xiunophp.php';

// 试用下框架自带的加密解密函数，结果可以直接通过 URL 传递。
$s = 'hello, world';

$s2 = encrypt($s);
echo "encrypt: $s2\r\n";

$s3 = decrypt($s2);
echo "decrypt: $s3\r\n";

/* 

结果输出：

encrypt: 3JgCL2iH_2FuBLfckc4BZfVg_3d_3d
decrypt: hello, world

*/

?>