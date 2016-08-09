<?php

!defined('DEBUG') AND exit('Access Denied.');

$action = param(1);

$user = user_read($uid);
user_login_check();

// hook attach_start.php

if(empty($action) || $action == 'create') {
	
	$width = param('width', 0);
	$height = param('height', 0);
	$name = param('name');
	$data = param('data', '', FALSE);
	
	// hook attach_create_start.php
	
	empty($data) AND message(-1, lang('data_is_empty'));
	$data = base64_decode_file_data($data);
	$size = strlen($data);
	$size > 2048000 AND message(-1, lang('filesize_too_large', array('maxsize'=>'2M', 'size'=>$size)));
	
	$ext = file_ext($name, 7);
	$filetypes = include './conf/attach.conf.php';
	!in_array($ext, $filetypes['all']) AND $ext = '_'.$ext;
	
	$tmpanme = $uid.'_'.xn_rand(15).'.'.$ext;
	$tmpfile = $conf['upload_path'].'tmp/'.$tmpanme;
	$tmpurl = $conf['upload_url'].'tmp/'.$tmpanme;
	
	file_put_contents($tmpfile, $data) OR message(-1, lang('write_to_file_failed'));
	
	// 保存到 session，发帖成功以后，关联到帖子。
	// save attach information to session, associate to post after create thread.
	$filetype = attach_type($name, $filetypes);
	empty($_SESSION['tmp_files']) AND $_SESSION['tmp_files'] = array();
	$n = count($_SESSION['tmp_files']);
	$attach = array(
		'url'=>$tmpurl, 
		'path'=>$tmpfile, 
		'orgfilename'=>$name, 
		'filetype'=>$filetype, 
		'filesize'=>filesize($tmpfile), 
		'width'=>$width, 
		'height'=>$height, 
		'isimage'=>0, 
		'aid'=>'_'.$n
	);
	$_SESSION['tmp_files'][$n] = $attach;
	
	unset($attach['path']);
	
	// hook attach_create_end.php
	
	message(0, $attach);

} elseif($action == 'delete') {
	
	$aid = param(2);
	
	// hook attach_delete_start.php
	
	if(substr($aid, 0, 1) == '_') {
		$key = intval(substr($aid, 1));
		$tmp_files = _SESSION('tmp_files');
		!isset($tmp_files[$key]) AND message(-1, lang('item_not_exists', array('item'=>$key)));
		$attach = $tmp_files[$key];
		!is_file($attach['path']) AND message(-1, lang('file_not_exists'));
		unlink($attach['path']);
		unset($_SESSION['tmp_files'][$key]);
	} else {
		$aid = intval($aid);
		$r = attach_delete($aid);
		$r ===  FALSE AND message(-1, lang('delete_failed'));
	}
	
	// hook attach_delete_delete.php
	
	message(0, 'delete_successfully');
	
}

// hook attach_end.php

?>