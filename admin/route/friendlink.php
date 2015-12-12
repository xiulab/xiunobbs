<?php

!defined('DEBUG') AND exit('Access Denied.');

include './model/friendlink.func.php';

$action = param(1);

if($action == 'list') {

	$header['title']    = '友情链接管理';

	$friendlinklist = friendlink_find();
	$maxlinkid = friendlink_maxid();
	
//	print_r($friendlinklist);exit;
	empty($friendlinklist) AND $friendlinklist = array(array(
		'linkid'=>1, 'name'=>'站点名称', 'url'=>'http://', 'rank'=>0
	));

	include "./admin/view/friendlink_list.htm";

// 友情链接更新
} elseif($action == 'update') {

	$linkid = param(2, 0);
	$name = param('name');
	$url = param('url');
	$rank = param('rank');
	
	$friendlink = friendlink_read($linkid);
	$arr = array(
		'linkid'          => $linkid,
		'name'         => $name,
		'url'         => $url,
		'rank'         => $rank,
		'create_date'  => $time,
	);

	empty($name) AND message(1, '站点名称不能为空');
	if(empty($friendlink)) {
		$r = friendlink_create($arr);
		$r !== FALSE ? message(0, '创建成功') : message(10, '创建失败');
	}
	
	$r = friendlink_update($linkid, $arr);
	$r !== FALSE ? message(0, '更新成功') : message(11, '更新失败');

} elseif($action == 'delete') {

	if($method != 'POST') message(-1, 'Method Error.');

	$linkid = param(2, 0);
	$friendlink = friendlink_read($linkid);
	empty($friendlink) AND message(1, '友情链接不存在');
	
	$r = friendlink_delete($linkid);
	$r !== FALSE ? message(0, '删除成功') : message(1, '删除失败');

}

?>
