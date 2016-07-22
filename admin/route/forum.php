<?php

!defined('DEBUG') AND exit('Access Denied.');

$action = param(1);

// 不允许删除的版块
$system_forum = array(1);

if(empty($action) || $action == 'list') {
	
	if($method == 'GET') {
		
		$header['title']    = '版块管理';
	
		$maxfid = forum_maxid();
		
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
	$_forum = forum_read($_fid);
	empty($_forum) AND message(-1, '版块不存在');
	
	if($method == 'GET') {
		
		$header['title']    = '版块管理';
	
		$accesslist = forum_access_find_by_fid($_fid);
		if(empty($accesslist)) {
			foreach($grouplist as $group) {
				$accesslist[$group['gid']] = $group; // 字段名相同，直接覆盖。
			}
		} else {
			foreach($accesslist as &$access) {
				$access['name'] = $grouplist[$access['gid']]['name']; // 字段名相同，直接覆盖。
			}
		}
		
		array_htmlspecialchars($forum);
		
		$input = array();
		$input['name'] = form_text('name', $_forum['name']);
		$input['rank'] = form_text('rank', $_forum['rank']);
		$input['brief'] = form_textarea('brief', $_forum['brief'], '100%', 80);
		$input['announcement'] = form_textarea('announcement', $_forum['announcement'], '100%', 80);
		$input['accesson'] = form_checkbox('accesson', $_forum['accesson']);
		$input['moduids'] = form_text('moduids', $_forum['moduids']);
		
		include "./admin/view/forum_update.htm";
	
	} elseif($method == 'POST') {	
		
		$name = param('name');
		$rank = param('rank', 0);
		$brief = param('brief');
		$announcement = param('announcement');
		$moduids = param('moduids');
		$accesson = param('accesson', 0);
		
		$arr = array (
			'name' => $name,
			'rank' => $rank,
			'brief' => $brief,
			'announcement' => $announcement,
			'moduids' => $moduids,
			'accesson' => $accesson,
		);
		forum_update($_fid, $arr);
		
		if($accesson) {
			$allowread = param('allowread', array(0));
			$allowthread = param('allowthread', array(0));
			$allowpost = param('allowpost', array(0));
			$allowattach = param('allowattach', array(0));
			$allowdown = param('allowdown', array(0));
			foreach($grouplist as $_gid=>$v) {
				$access = array (
					'allowread'=>array_value($allowread, $_gid, 0),
					'allowthread'=>array_value($allowthread, $_gid, 0),
					'allowpost'=>array_value($allowpost, $_gid, 0),
					'allowattach'=>array_value($allowattach, $gid, 0),
					'allowdown'=>array_value($allowdown, $_gid, 0),
				);
				forum_access_replace($_fid, $_gid, $access);
			}
		} else {
			forum_access_delete_by_fid($_fid);
		}
		
		forum_list_cache_delete();
		
		message(0, '编辑成功');	
	}
	
} else {
	
	http_404();
	
}

?>