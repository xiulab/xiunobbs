<?php

//error_reporting(E_ALL);

//$errno = $errstr = 0;
//
//// 使用全局变量记录错误信息
//function xn_error($no, $str, $return = FALSE) {
//	global $errno, $errstr;
//	$errno = $no;
//	$errstr = $str;
//	return $return;
//}
//

//$ACCESSKEY = '00f47da319173e011683b6f4c63b46f8fe8a9471';
//$SECRETKEY = '835f098af34d9c809bd919a6719cf6ab1825a576';
//$albumid = 12779;

// $conf = array('tietuku_token' => '00f47da319173e011683b6f4c63b46f8fe8a9471:ak9XYzQ5YmhIalIwYlNwMFJwaVB6Vm9XMFBjPQ==:eyJkZWFkbGluZSI6MTQ0MDY2MzkzOSwiYWN0aW9uIjoiZ2V0IiwidWlkIjoiNTgyNyIsImFpZCI6IjEyNzc5IiwiZnJvbSI6ImZpbGUifQ==');

if(!function_exists('curl_file_create')) {
    function curl_file_create($filename, $mimetype = '', $postname = '') {
        return "@$filename;filename=".($postname ? $postname : basename($filename)).($mimetype ? ";type=$mimetype" : '');
    }
}

function http_post_file($url, $postdata){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
       // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect: 100-continue'));
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        $output = curl_exec($ch);
        if($output === FALSE) {
                echo "curl error:".curl_error($ch)."\r\n";
        }
        curl_close($ch);
        return $output;
}

/*
Array
(
    [width] => 1080
    [height] => 960
    [type] => jpg
    [size] => 164795
    [ubburl] => [url=http://tietuku.com/43032dff494754d52][img]http://i3.tietuku.com/43032dff494754d5.jpg[/img][/url]
    [linkurl] => http://i3.tietuku.com/43032dff494754d5.jpg
    [htmlurl] => <a href='http://tietuku.com/43032dff494754d52' target='_blank'><img src='http://i3.tietuku.com/43032dff494754d5.jpg' /></a>
    [s_url] => http://i3.tietuku.com/43032dff494754d5s.jpg
    [t_url] => http://i3.tietuku.com/43032dff494754d5t.jpg
)
*/
function tietuku_upload_file($filepath, $filename = '') {
	global $conf;
	$token = $conf['tietuku_token'];
	$post_url = 'http://up.tietuku.com/';
	if(!is_file($filepath)) {
		return xn_error(-1, '文件不存在');
	} elseif(filesize($filepath) == 0) {
		return xn_error(-1, '文件大小为 0');
	}
	
	//$token = '00f47da319173e011683b6f4c63b46f8fe8a9471:ak9XYzQ5YmhIalIwYlNwMFJwaVB6Vm9XMFBjPQ==:eyJkZWFkbGluZSI6MTQ0MDY2MzkzOSwiYWN0aW9uIjoiZ2V0IiwidWlkIjoiNTgyNyIsImFpZCI6IjEyNzc5IiwiZnJvbSI6ImZpbGUifQ==';
	!function_exists('curl_file_create') AND message(-1, '请开启 PHP 的 CURL 模块。');
	$postdata = array();
	$postdata['Token'] = $token;
	$postdata['file'] = curl_file_create($filepath, $filename);
	$s = http_post_file($post_url, $postdata);
	$arr = json_decode($s, 1);
	if(empty($arr) || empty($arr['size'])) {
		return xn_error(-3, '返回数据格式有问题：'.$s);
	}
	return $arr;
}

//$arr = tietuku_upload_file('e:/image/1.jpg');
//$arr = tietuku_upload_file('e:/2.txt');
//print_r($arr);
//echo $errstr;
//
?>