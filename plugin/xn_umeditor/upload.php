<?php

define('DEBUG', 1);
define('BASE_HREF', '../../');
define('SKIP_ROUTE', TRUE); // 跳过路由处理，否则 index.php 中会中断流程
chdir('../../');
include './index.php';

if($method != 'POST') exit('Not Allow');

$width = param('width', 0);
$height = param('height', 0);
$name = param('name');
$data = param('data', '', FALSE);

empty($data) AND message(-1, '数据为空');
$data = base64_decode_image_data($data);
$size = strlen($data);
$size > 2048000 AND message(-1, '文件尺寸太大，不能超过 2M，当前大小：'.$size);

$ext = file_ext($name, 4);
!in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'bmp')) AND message(-1, '不允许的格式:'.$ext);
$tmpanme = $uid.'_'.xn_rand(15).'.'.$ext; // 凑够 32 个字节，对齐。
$tmpfile = $conf['upload_path'].'tmp/'.$tmpanme;
$tmpurl = $conf['upload_url'].'tmp/'.$tmpanme;

file_put_contents($tmpfile, $data) OR message(-1, '写入文件失败');

// 保存到 session，发帖成功以后，关联到帖子。
empty($_SESSION['tmp_files']) AND $_SESSION['tmp_files'] = array();
$n = count($_SESSION['tmp_files']); // 此处并发下会有问题
$attach = array(
	'url'=>$tmpurl, 
	'path'=>$tmpfile, 
	'orgfilename'=>$name, 
	'filetype'=>'image', 
	'filesize'=>filesize($tmpfile), 
	'width'=>$width, 
	'height'=>$height, 
	'isimage'=>1, 
	'aid'=>'_'.$n
);
$_SESSION['tmp_files'][$n] = $attach;

unset($attach['path']);
message(0, $attach);

?>