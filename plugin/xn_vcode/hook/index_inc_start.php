<?php exit;

// 如果是 POST 请求，延迟 2-10ms，防止并发攻击
if($method == 'POST') {
	$n = rand(2000, 10000);
	function_exists('usleep') AND usleep($n);
}

?>