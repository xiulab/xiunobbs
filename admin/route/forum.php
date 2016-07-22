<?php

!defined('DEBUG') AND exit('Access Denied.');

$action = param(1);

// 不允许删除的版块
$system_forum = array(1);

if(empty($action) || $action == 'list') {
	
	if($method == 'GET') {
		
		$header['title']    = '版块管理';
	
		include "./admin/view/forum_list.htm";
	
	} elseif($method == 'POST') {
		
		$fidarr = param('fid', array(0));
		$namearr = param('name', array(''));
		$rankarr = param('rank', array(0));
		$iconarr = param('icon', array(''));
		
		$arrlist = array();
		foreach ($fidarr as $k=>$v) {
			$arr = array(
				'fid'=>$k,
				'name'=>$namearr[$k],
				'rank'=>$rankarr[$k]
			);
			if(!isset($forumlist[$k])) {
				// 添加
				forum_create($arr);
			} else {
				
				forum_update($k, $arr);
			}
			// icon 处理
			if(!empty($iconarr[$k])) {
				
				$s = $iconarr[$k];
				$data = substr($s, strpos($s, ',') + 1);
				$data = base64_decode($data);
				
				$iconfile = "./upload/forum/$k.png";
				file_put_contents($iconfile, $data);
				
				forum_update($k, array('icon'=>$time));
			}
		}
		
		// 删除
		$deletearr = array_diff_key($forumlist, $fidarr);
		foreach($deletearr as $k=>$v) {
			if(in_array($k, $system_forum)) continue;
			forum_delete($k);
		}
		
		forum_list_cache_delete();
		
		message(0, '保存成功');
	}

} elseif($action == 'update') {
	
	$_fid = param(2, 0);
	$_group = group_read($_fid);
	empty($_group) AND message(-1, '用户组不存在');
	
	if($method == 'GET') {
		
		$header['title']    = '用户组管理';
	
		$input = array();
		$input['name'] = form_text('name', $_group['name']);
		$input['creditsfrom'] = form_text('creditsfrom', $_group['creditsfrom']);
		$input['creditsto'] = form_text('creditsto', $_group['creditsto']);
		$input['allowread'] = form_checkbox('allowread', $_group['allowread']);
		$input['allowthread'] = form_checkbox('allowthread', $_group['allowthread'] && $_fid != 0);
		$input['allowpost'] = form_checkbox('allowpost', $_group['allowpost'] && $_fid != 0);
		$input['allowattach'] = form_checkbox('allowattach', $_group['allowattach'] && $_fid != 0);
		$input['allowdown'] = form_checkbox('allowdown', $_group['allowdown']);
		$input['allowtop'] = form_checkbox('allowtop', $_group['allowtop']);
		$input['allowupdate'] = form_checkbox('allowupdate', $_group['allowupdate']);
		$input['allowdelete'] = form_checkbox('allowdelete', $_group['allowdelete']);
		$input['allowmove'] = form_checkbox('allowmove', $_group['allowmove']);
		$input['allowbanuser'] = form_checkbox('allowbanuser', $_group['allowbanuser']);
		$input['allowdeleteuser'] = form_checkbox('allowdeleteuser', $_group['allowdeleteuser']);
		$input['allowviewip'] = form_checkbox('allowviewip', $_group['allowviewip']);
		
		include "./admin/view/group_update.htm";
	
	} elseif($method == 'POST') {	
		
		$name = param('name');
		$creditsfrom = param('creditsfrom');
		$creditsto = param('creditsto');
		$allowread = param('allowread', 0);
		$allowthread = param('allowthread', 0);
		$allowpost = param('allowpost', 0);
		$allowattach = param('allowattach', 0);
		$allowdown = param('allowdown', 0);
		$arr = array (
			'name'       => $name,
			'creditsfrom' => $creditsfrom,
			'creditsto'   => $creditsto,
			'allowread'  => $allowread,
			'allowthread'  => $allowthread,
			'allowpost'  => $allowpost,
			'allowattach'  => $allowattach,
			'allowdown'  => $allowdown,
			
		);
		if($_fid >=1 && $_fid <= 5) {
			
			$allowtop = param('allowtop', 0);
			$allowupdate = param('allowupdate', 0);
			$allowdelete = param('allowdelete', 0);
			$allowmove = param('allowmove', 0);
			$allowbanuser = param('allowbanuser', 0);
			$allowdeleteuser = param('allowdeleteuser', 0);
			$allowviewip = param('allowviewip', 0);
			$arr += array(
				'allowtop'  => $allowtop,
				'allowupdate'  => $allowupdate,
				'allowdelete'  => $allowdelete,
				'allowmove'  => $allowmove,
				'allowbanuser'  => $allowbanuser,
				'allowdeleteuser'  => $allowdeleteuser,
				'allowviewip'  => $allowviewip
			);
		}
		group_update($_fid, $arr);
		message(0, '编辑成功');	
	}
	
} else {
	
	http_404();
	
}

?>