<?php

// 创建新帖
!defined('DEBUG') AND exit('Access Denied.');

include './xiunophp/xn_html_safe.func.php';
include './xiunophp/image.func.php';

$action = param(1);

$user = user_read($uid);
empty($user) AND $user = user_guest();

if($action == 'create') {
	
	$tid = param(2);
	$quick = param(3);
		
	$thread = thread_read($tid);
	empty($thread) AND message(3, '主题不存在:'.$tid);
	
	$fid = $thread['fid'];
	
	$forum = forum_read($fid);
	empty($forum) AND message(3, '板块不存在:'.$fid);
	
	$r = forum_access_user($fid, $gid, 'allowpost');
	if(!$r) {
		if($gid == 0) {
			$r = forum_access_user($fid, 101, 'allowpost');
			$r AND user_login_check($user);
		}
		message(10, '您（'.$user['groupname'].'）无权限在此版块发帖');
	}
	
	$conf['ipaccess_on'] AND !ipaccess_check($longip, 'posts') AND message(-1, '您的 IP 今日回帖数达到上限，请明天再来。');
	
	if($method == 'GET') {
		
		check_standard_browser();
		
		include './pc/view/post_create.htm';
		
	} else {
		
		$agree = param('agree', 0);
		$message = param('message', '', FALSE);
		!trim(str_replace(array('　', '&nbsp;', '<br>', '<br/>', '<br />'), '', $message)) AND message(2, '内容不能为空');
		$gid != 1 AND $message = xn_html_safe($message);
		$gid != 1 AND $message = badword_filter($message, $badword);
		$message === FALSE AND message(2, '内容中包含敏感关键词: '.$badword);
		mb_strlen($message, 'UTF-8') > 2048000 AND message('内容太长');
		$quick AND $message = nl2br(str_replace("\t", "&nbsp; &nbsp; &nbsp; &nbsp; ", $message));
		
		// 检测是否灌水
		post_check_flood($gid, $tid, $message) AND message(2, '系统检测到您可能在灌水');
		
		// 检测是否超过最大回复数
		$thread['posts'] >= 1000 AND message(-1, '该主题已经达到最大回复数 1000，不能再回复，请另起主题。');
		$thread['top'] > 0 AND thread_top_cache_delete();
		
		# 论坛帖子数据，一页显示，不分页。
		$post = array(
			'tid'=>$tid,
			'uid'=>$uid,
			'create_date'=>$time,
			'userip'=>$longip,
			'sid'=>$sid,
			'isfirst'=>0,
			'message'=>$message,
		);
		$pid = post_create($post, $fid);
		empty($pid) AND message(1, '创建帖子失败');
		
		// 喜欢，不通过服务端，客户端 ajax 发起请求更简洁
		//if($agree) {
		//	$r = agree_update($thread['uid'], $thread['firstpid'], $tid, $fid, 1);
		//}
		
		// 最新发帖
		// thread_top_create($fid, $tid);

		$post = post_read($pid);
		$post['floor'] = $thread['posts'] + 1;
		$postlist = array($post);
		
		$allowpost = forum_access_user($fid, $gid, 'allowpost');
		$allowupdate = forum_access_mod($fid, $gid, 'allowupdate');
		$allowdelete = forum_access_mod($fid, $gid, 'allowdelete');
		
		ob_start();
		include './pc/view/post_list_body.inc.htm';
		$s = ob_get_clean();
		$conf['ipaccess_on'] AND ipaccess_inc($longip, 'posts');
		message(0, $s);
	
	}
	
} elseif($action == 'update') {

	$pid = param(2);
	$post = post_read($pid);
	empty($post) AND message(-1, '帖子不存在:'.$pid);
	
	$tid = $post['tid'];
	$thread = thread_read($tid);
	empty($thread) AND message(-1, '主题不存在:'.$tid);
	
	$fid = $thread['fid'];
	$forum = forum_read($fid);
	empty($forum) AND message(1, '板块不存在:'.$fid);
	
	
	$isfirst = $post['isfirst'];
	
	!forum_access_user($fid, $gid, 'allowpost') AND message(-1, '您（'.$user['groupname'].'）无权限在此版块回帖');
	$allowupdate = forum_access_mod($fid, $gid, 'allowupdate');
	!$allowupdate AND !$post['allowupdate'] AND message(-1, '无权编辑该贴');
	
	if($method == 'GET') {
		
		$forumlist_allowthread = forum_list_access_filter($forumlist, $gid, 'allowthread');
		$forumarr = xn_json_encode(arrlist_key_values($forumlist_allowthread, 'fid', 'name'));
		$post['message'] = htmlspecialchars($post['message']);
		
		// 将未插入帖子的附件加入到末尾。
		$attachlist = $imagelist = $filelist = array();
		if($post['images'] || $post['files']) {
			$attachlist = attach_find_by_pid($post['pid']);
			list($imagelist, $filelist) = attach_list_not_in_message($attachlist, $post['message']);
			$post['message'] .= post_attach_list_add($imagelist, $filelist);
		}
		
		check_standard_browser();
		include './pc/view/post_update.htm';
		
	} elseif($method == 'POST') {
		
		$subject = htmlspecialchars(param('subject', '', FALSE));
		$message = param('message', '', FALSE);
		$seo_url = strtolower(param('seo_url'));
		
		empty($message) AND message(2, '内容不能为空');
		$gid != 1 AND $message = xn_html_safe($message);
		mb_strlen($message, 'UTF-8') > 2048000 AND message('内容太长');
		
		$arr = array();
		if($isfirst) {
			$newfid = param('fid');
			$forum = forum_read($newfid);
			empty($forum) AND message(1, '板块不存在:'.$newfid);
			
			if($fid != $newfid) {
				!forum_access_user($fid, $gid, 'allowthread') AND message(-1, '您（'.$user['groupname'].'）无权限在此版块回帖');
				$post['uid'] != $uid AND !forum_access_mod($fid, $gid, 'allowupdate') AND message(-1, '您（'.$user['groupname'].'）无权限在此版块编辑帖子');
				$arr['fid'] = $newfid;
			}
			if($seo_url != $thread['seo_url'] && $conf['seo_url_rewrite'] && $group['allowcustomurl']) {
				$seo_url = preg_replace('#[\W]#', '-', $seo_url); // 只允许英文和 - 
				$seo_url AND thread_read_by_seo_url($seo_url) AND message(4, '自定义的 URL 已经存在，请修改。'); // 这里可能有并发问题，seo_url 并非 UNIQUE KEY
				strlen($seo_url) > 128 AND message(3, '自定义 URL 太长');
				$arr['seo_url'] = $seo_url;
			}
			if($subject != $thread['subject']) {
				mb_strlen($subject, 'UTF-8') > 80 AND message(1, '标题最长80个字符');
				$arr['subject'] = $subject;
			}
			$arr AND thread_update($tid, $arr) === FALSE AND message(-1, '更新主题失败');
		}
		$r = post_update($pid, array('message'=>$message));
		$r === FALSE AND message(-1, '更新帖子失败');
		
		message(0, array('pid'=>$pid, 'subject'=>$subject, 'message'=>$message));
	}
	
} elseif($action == 'delete') {

	$pid = param(2, 0);
	
	if($method != 'POST') message(-1, '方法不对');
	
	$post = post_read($pid);
	empty($post) AND message(-1, '帖子不存在:'.$pid);
	
	$tid = $post['tid'];
	$thread = thread_read($tid);
	empty($thread) AND message(-1, '主题不存在:'.$tid);
	
	$fid = $thread['fid'];
	$forum = forum_read($fid);
	empty($forum) AND message(-1, '板块不存在:'.$fid);
	
	$isfirst = $post['isfirst'];
	
	!forum_access_user($fid, $gid, 'allowpost') AND message(-1, '您（'.$user['groupname'].'）无权限在此版块回帖');
	$allowdelete = forum_access_mod($fid, $gid, 'allowdelete');
	!$allowdelete AND !$post['allowdelete'] AND message(-1, '无权删除该帖');
	
	if($isfirst) {
		// 清除所有的回复。喜欢。还有相关资源
		thread_delete($tid);
	} else {
		post_delete($pid);
		post_list_cache_delete($tid);
	}
	
	
	message(0, '删除成功');
	
// 接受 base64 文件上传
} elseif($action == 'upload') {
	
	// 允许的文件后缀名
	$types = include './conf/attach.conf.php';
	$allowtypes = $types['all'];
	
	empty($uid) AND message(-1, '游客不允许上传文件');
	empty($group['allowattach']) AND $gid != 1 AND message(-1, '您无权上传');
	
	$conf['ipaccess_on'] AND !ipaccess_check($longip, 'attachs') AND message(-1, '您的 IP 今日上传附件数达到上限，请明天再来。');
	$conf['ipaccess_on'] AND !ipaccess_check($longip, 'attachsizes') AND message(-1, '您的 IP 今日上传附件尺寸达到上限，请明天再来。');
	
	$isimage = param(2, 0);
	$tid = 0;
	$fid = 0;
	
	$upfile = param('upfile', '', FALSE);
	empty($upfile) AND message(-1, 'upfile 数据为空');
	$json = xn_json_decode($upfile);
	empty($json) AND message(-1, '数据有问题: json 为空');
	
	$name = $json['name'];
	$width = $json['width'];
	$height = $json['height'];
	$data = base64_decode($json['data']);
	$size = strlen($data);
	$type = attach_type($name, $types);
	
	empty($data) AND message(-1, '数据有问题, data 为空');
	
	if($isimage && $conf['tietuku_on']) {
		include './plugin/xn_tietuku/tietuku.func.php';
		$tmpfile = tempnam($conf['tmp_path'], 'tmp_');
		file_put_contents($tmpfile, $data);
		$r = tietuku_upload_file($tmpfile);
		$r === FALSE AND message($errno, $errstr);
		unlink($tmpfile);
		message(0, array('url'=>$r['linkurl'], 'name'=>$name, 'width'=>$r['width'], 'height'=>$r['height']));
	}
	
	$day = date('Ymd', $time);
	$path = $conf['upload_path'].'attach/'.$day;
	$url = $conf['upload_url'].'attach/'.$day;
	!IN_SAE AND !is_dir($path) AND (mkdir($path, 0777, TRUE) OR message(-2, '目录创建失败'));
	
	$savename = $uid.'_'.attach_safe_name($name, $allowtypes);
	
	$destfile = $path.'/'.$savename;
	$desturl = $url.'/'.$savename;
	
	attach_create(array(
		'tid'=>$tid,
		'pid'=>0,
		'uid'=>$uid,
		'filesize'=>$size,
		'width'=>$width,
		'height'=>$height,
		'filename'=>$day.'/'.$savename,
		'filetype'=>$type,
		'orgfilename'=>$name,
		'create_date'=>$time,
		'comment'=>'',
		'downloads'=>'0',
		'isimage'=>$isimage
	)) OR message(-1, '保存附件数据失败');
	
	file_put_contents($destfile, $data) OR message(-1, '写入文件失败');
	
	$ext = file_ext($destfile);
	if($width > 0 && $ext != 'gif') {
		image_thumb($destfile, $destfile, $width, $height);
	}
	
	$conf['ipaccess_on'] AND ipaccess_inc($longip, 'attachs');
	$conf['ipaccess_on'] AND ipaccess_inc($longip, 'attachsizes', $size);
	
	if($ext == 'gif') {
		list($width, $height, $type, $attr) = getimagesize($destfile);
	}
	message(0, array('url'=>$desturl, 'name'=>$name, 'width'=>$width, 'height'=>$height));
	
} else {
	
	message(-1, '没有此功能');
	
}

?>