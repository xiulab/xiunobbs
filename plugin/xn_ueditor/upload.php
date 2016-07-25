<?php

define('DEBUG', 1);

chdir('../../');

define('SKIP_ROUTE', TRUE); // 跳过路由处理，否则 index.php 中会中断流程

include './index.php';

$action = param('action');

if($action == 'config') {
	
	$s = xn_json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents("./plugin/xn_ueditor/ueditor/php/config.json")));
        $result =  xn_json_encode($s);
        echo $result;
        
} elseif($action == 'catchimage') {
	
} elseif($action == 'uploadimage') {
	
	$callback = param('callback');
	
	echo 123;exit;
	
/*Array
(
    [upfile] => Array
        (
            [name] => blob.png
            [type] => image/png
            [tmp_name] => C:\Windows\temp\phpAA92.tmp
            [error] => 0
            [size] => 2218
        )

)
$s = xn_json_encode(array(
	"state" => 'SUCCESS',
	"url" => 'view/img/logo.png',
	"title" => 'logo title',
	"original" => 'logo.png',
	"type" => 'png',
	"size" => 123,
));
echo "$callback($s)";exit;
file_put_contents('d:/1.txt', print_r( $_FILES, 1));
*/
	empty($_FILES['upfile']) AND exit("请上传文件");
	
	//$upfile = array_htmlspecialchars($_FILES['upfile']);
	$upfile = $_FILES['upfile'];
	$ext = file_ext($upfile['name']);
	
	$tmpanme = $uid.'_'.$time.'_'.xn_rand(32).$ext;
	$tmpfile = $conf['upload_path'].'tmp/'.$tmpanme;
	$tmpurl = $conf['upload_url'].'tmp/'.$tmpanme;
	
	move_uploaded_file($upfile['tmp_name'], $tmpfile) OR exit('保存文件失败。');
	
	
	
	$r = array(
		"state" => 'SUCCESS',
		"url" => $tmpurl,
		"title" => 'logo title',
		"original" => $upfile['name'],
		"type" => $ext,
		"size" => $upfile['size'],
	);
	$s = xn_json_encode($r);
	
        echo "$callback($s)";
}

?>
