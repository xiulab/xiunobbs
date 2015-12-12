<?php

!defined('DEBUG') AND exit('Access Denied.');

include './xiunophp/image.func.php';
include './xiunophp/xn_html_safe.func.php';

$action = param(1);
if($action == 'list') {

	$header['title']    = '板块管理';

	$forumlist = forum_find();
	$maxfid = forum_maxid();
	
	
	include "./admin/view/forum_list.htm";

// 板块更新
} elseif($action == 'update') {

	if($method == 'GET') {

		$fid   = param(2, 0);
		$header['title'] = '板块更新';
		$forum = forum_read($fid);
		
		$grouplist = group_find();
		$accesslist = forum_access_find_by_fid($fid);
		if (empty($accesslist)) {
			foreach ($grouplist as $group) {
				$accesslist[$group['gid']] = $group; // 字段名相同，直接覆盖。
			}
		} else {
			foreach ($accesslist as &$access) {
				$access['name'] = $grouplist[$access['gid']]['name']; // 字段名相同，直接覆盖。
			}
		}
		array_htmlspecialchars($forum);

		include "./admin/view/forum_update.htm";

	} elseif($method == 'POST') {
		
		$fid = param(2, 0);
		$name = param('name');
		$rank = param('rank');
		$moduids = param('moduids');
		$moduids = forum_filter_moduid($moduids);
		
		$forum = forum_read($fid);
		
		empty($name) AND message(1, '论坛名称不能为空');
		
		// 列表页 ajax post 逐行提交
		$arr = array(
			'name'         => $name,
			'rank'         => $rank,
			'create_date'  => $time,
		);
		
		// 详情页的 POST 提交
		if(isset($_POST['brief'])) {
			
			empty($forum) AND message(11, '版块不存在');
			
			$brief = param('brief', '', FALSE);
			
			$accesson = param('accesson', 0);
			$moduids = param('moduids');
			$seo_title = param('seo_title');
			$seo_keywords = param('seo_keywords');
			
			$grouplist = group_list_cache();
			if($accesson) {
				$allowread = param('allowread', array(0));
				$allowthread = param('allowthread', array(0));
				$allowpost = param('allowpost', array(0));
				$allowagree = param('allowagree', array(0));
				//$allowattach = param('allowattach', array(0));
				$allowdown = param('allowdown', array(0));
				foreach ($grouplist as $gid=>$v) {
					$access = array (
						'allowread'=>array_value($allowread, $gid, 0),
						'allowthread'=>array_value($allowthread, $gid, 0),
						'allowpost'=>array_value($allowpost, $gid, 0),
						'allowagree'=>array_value($allowagree, $gid, 0),
						//'allowattach'=>array_value($allowattach, $gid, 0),
						'allowdown'=>array_value($allowdown, $gid, 0),
					);
					forum_access_replace($fid, $gid, $access);
				}
			} else {
				forum_access_delete_by_fid($fid);
			}
			$arr['accesson'] = $accesson;
			$arr['brief'] = $brief;
			$arr['moduids'] = $moduids;
			$arr['seo_title'] = $seo_title;
			$arr['seo_keywords'] = $seo_keywords;
		}
		
		if(empty($forum)) {
			$arr['fid'] = $fid;
			$r = forum_create($arr);
			$r !== FALSE ? message(0, '创建成功') : message(11, '创建失败');
		}
		
		$r = forum_update($fid, $arr);
		$r !== FALSE ? message(0, '更新成功') : message(12, '更新失败');
	}

} elseif($action == 'delete') {

	if($method != 'POST') message(-1, 'Method Error.');

	$fid = param(2, 0);
	$forum = forum_read($fid);
	empty($forum) AND message(1, '板块不存在');
	
	forum_count() == 1 AND message(-1, '不能删除最后一个版块。');
	
	$r = forum_delete($fid);
	$r !== FALSE ? message(0, '删除成功') : message(1, '删除失败');

	
} elseif($action == 'uploadicon') {
	
	$method != 'POST' AND message(-1, 'Method Error.');
	
	$fid = param(2, 0);

	$forum = forum_read($fid);
	empty($forum) AND message(1, '板块不存在');
	
	$upfile = param('upfile', '', FALSE);
	empty($upfile) AND message(-1, 'upfile 数据为空');
	$json = xn_json_decode($upfile);
	empty($json) AND message(-1, '数据有问题: json 为空');
	
	$name = $json['name'];
	$width = $json['width'];
	$height = $json['height'];
	$data = base64_decode($json['data']);
	$size = strlen($data);
	
	$filename = "$fid.png";
	$path = $conf['upload_path'].'forum/';
	$url = $conf['upload_url'].'forum/'.$filename;
	!IN_SAE AND !is_dir($path) AND (mkdir($path, 0777, TRUE) OR message(-2, '目录创建失败'));
	
	file_put_contents($path.$filename, $data) OR message(-1, '写入文件失败');
	
	forum_update($fid, array('icon'=>$time));
	
	message(0, $url);

} elseif($action == 'getname') {
	
	$uids = param(2);
	$arr = explode(',', $uids);
	$names = array();
	$err = '';
	foreach($arr as $_uid) {
		$_uid = intval($_uid);
		if(empty($_uid)) continue;
		$_user = user_read($_uid);
		if(empty($_user)) { $err .= "$_uid 不存在; "; continue; }
		if($_user['gid'] > 4) { $err .= "$_uid 不是斑竹; ";  continue; }
		$names[] = $_user['username'];
	}
	$s = implode(',', $names);
	$err ? message(1, $err) : message(0, $s);
	
} elseif($action == 'getuid') {
		
	$names = xn_urldecode(param(2));
	$arr = explode(',', $names);
	$ids = array();
	$err = '';
	foreach($arr as $name) {
		if(empty($name)) continue;
		$_user = user_read_by_username($name);
		if(empty($_user)) { $err .= "$name 不存在; "; continue; }
		if($_user['gid'] > 4) { $err .= "$name 不是斑竹; ";  continue; }
		$ids[] = $_user['uid'];
	}
	$s = implode(',', $ids);

	$err ? message(1, $err) : message(0, $s);
}

function forum_filter_moduid($moduids) {
	$moduids = trim($moduids);
	if(empty($moduids)) return '';
	$arr = explode(',', $moduids);
	$r = array();
	foreach($arr as $_uid) {
		$_uid = intval($_uid);
		$_user = user_read($_uid);
		if(empty($_user)) continue;
		if($_user['gid'] > 4) continue;
		$r[] = $_uid;
	}
	return implode(',', $r);
}

?>
