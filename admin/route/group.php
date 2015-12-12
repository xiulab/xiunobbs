	<?php

!defined('DEBUG') AND exit('Access Denied.');

$action = param(1);

if($action == 'list') {

	$header['title']    = '用户组管理';

	$grouplist = group_find();
	$maxgid = group_maxid();
	
	include "./admin/view/group_list.htm";

// 用户组更新
} elseif($action == 'create') {
	
	$gid = param(2, 0);
	$group = group_read($gid);
	$group AND message(-1, '用户组已经存在!');
	
	$name = param('name');
	$agreesfrom = param('agreesfrom', 0);
	$agreesto = param('agreesto', 0);
	$maxagrees = param('maxagrees', 0);
	
	empty($name) AND message(1, '用户组名称不能为空');
	
	$arr = array(
		'gid'        => $gid,
		'name'       => $name,
		'agreesfrom' => $agreesfrom,
		'agreesto'   => $agreesto,
		'maxagrees'  => $maxagrees,
	);
	
	$r = group_create($arr);
	$r !== FALSE ? message(0, '创建成功') : message(-1, '创建失败');
	
// 用户组更新
} elseif($action == 'update') {

	$gid = param(2, 0);
	$group = group_read($gid);
	
	if($method == 'GET') {

		empty($group) AND message(1, '用户组不存在');
	
		include './admin/view/group_update.htm';
	} else {
		// 两种情况的提交 list/update
		$name = param('name');
		$agreesfrom = param('agreesfrom', 0);
		$agreesto = param('agreesto', 0);
		$maxagrees = param('maxagrees', 0);
		
		// 标示是不是更新详情
		$detail = param(3);
		
		$arr = array(
			'gid'        => $gid,
			'name'       => $name,
			'agreesfrom' => $agreesfrom,
			'agreesto'   => $agreesto,
			'maxagrees'  => $maxagrees,
		);
		if($detail == 'detail') {
			$allowread = param('allowread', 0);
			$allowthread = param('allowthread', 0);
			$allowpost = param('allowpost', 0);
			$allowattach = param('allowattach', 0);
			$allowdown = param('allowdown', 0);
			$allowagree = param('allowagree', 0);
			
			$allowtop = param('allowtop', 0);
			$allowupdate = param('allowupdate', 0);
			$allowdelete = param('allowdelete', 0);
			$allowmove = param('allowmove', 0);
			$allowbanuser = param('allowbanuser', 0);
			$allowdeleteuser = param('allowdeleteuser', 0);
			$allowviewip = param('allowviewip', 0);
			$allowcustomurl = param('allowcustomurl', 0);
			
			$arr2 = array(
				'allowread'  => $allowread,
				'allowthread'  => $allowthread,
				'allowpost'  => $allowpost,
				'allowattach'  => $allowattach,
				'allowdown'  => $allowdown,
				'allowagree'  => $allowagree,
				
				'allowtop'  => $allowtop,
				'allowupdate'  => $allowupdate,
				'allowdelete'  => $allowdelete,
				'allowmove'  => $allowmove,
				'allowbanuser'  => $allowbanuser,
				'allowdeleteuser'  => $allowdeleteuser,
				'allowviewip'  => $allowviewip,
				'allowcustomurl'  => $allowcustomurl,
			);
			$arr += $arr2;
		}
		
		// 更新
		$r = group_update($gid, $arr);
		$r !== FALSE ? message(0, '更新成功') : message(-1, '更新失败');
	}
	

} elseif($action == 'delete') {

	if($method != 'POST') message(-1, 'Method Error.');
	
	$gid = param(2, 0);
	$group = group_read($gid);
	empty($group) AND message(1, '用户组不存在');
	
	$gid <= 101 AND message(-1, '该用户组不允许删除！');
	$r = group_delete($gid);
	$r !== FALSE ? message(0, '删除成功') : message(1, '删除失败');

}

?>