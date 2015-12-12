<?php

/*
	功能：演示 Hello, world.
	注意：当通过 AJAX 请求的时候，输出格式为 json
*/

chdir('../../../');

include './xiunophp/xiunophp.php';

xn_message(0, 'Hello, world.');

/*

输出：

Hello, world.


AJAX 请求输出：

{code:0, message:"Hello, world."}

*/

?>