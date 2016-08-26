if($thread['hide2'] > 0 && $thread['uid']!=$uid && !empty($kvhide['hide2'])){
	$message = '隐藏内容需要回复才能查看';
	foreach($postlist as $_post) {
		if($_post['uid']==$uid){
			$message = $first['message_fmt'];
		}
	}
	$first['message_fmt'] = $message;
}
$thread['hide1'] > 0 && !empty($kvhide['hide1']) && empty($uid) AND $first['message_fmt'] = '隐藏内容需要登陆才能查看';