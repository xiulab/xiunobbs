<?php

!defined('DEBUG') AND exit('Access Denied.');

include './xiunophp/image.func.php';
include './xiunophp/xn_html_safe.func.php';
include './model/article.func.php';

$action = param(1);

if($action == 'list') {

	$header['title'] = '文章管理';
	
	$cateid   = param(2, 0);
	$keyword  = trim(urldecode(param(3)));
	$page     = param(4, 0);
	$pagesize = 20;

	$cond = array();
	$cateid AND $cond['cateid'] = $cateid;
	$keyword AND $cond['subject'] = array('LIKE'=>$keyword);

	$n = article_count($cond);
	$page = page($page, $n, $pagesize);
	$articlelist = article_find($cond, array('articleid'=>-1), $page, $pagesize);
	$pages = pages("admin/article-list-$cateid-".urlencode($keyword).'-{page}.htm', $n, $page, $pagesize);

	include "./admin/view/article_list.htm";

} elseif($action == 'create') {

	if($method == 'GET') {

		$header['title'] = '创建文章';

		$articleid = article_maxid() + 1;

		include "./admin/view/article_create.htm";

	} elseif ($method == 'POST') {

		$articleid       = param(2, 0);
		$cateid          = param('cateid', 0);
		$subject         = param('subject');
		$brief           = param('brief');
		$message         = param('message', '', FALSE);
		$cover           = param('cover');
		$seo_title       = param('seo_title');
		$seo_keywords    = param('seo_keywords');
		$seo_description = param('seo_description');

		!$cateid AND message(1, '文章分类未指定');

		$arr = array(
			'cateid'          => $cateid,
			'subject'         => $subject,
			'brief'           => $brief,
			'message'         => $message,
			'cover'           => $cover,
			'uid'             => $uid,
			'create_date'     => $time,
			'update_date'     => $time,
			'ip'              => $longip,
			'seo_title'       => $seo_title,
			'seo_keywords'    => $seo_keywords,
			'seo_description' => $seo_description,
		);
		$r = article_replace($articleid, $arr);
		$r !== FALSE ? message(0, '创建成功') : message(11, '创建失败');

	}

} elseif($action == 'update') {

	if($method == 'GET') {

		$articleid   	= param(2, 0);
		$header['title']  = '更新文章';
		$article	= article_read($articleid);

		array_htmlspecialchars($article);

		include "./admin/view/article_update.htm";

	} elseif($method == 'POST') {
		
		$articleid       = param(2, 0);
		$cateid          = param('cateid', 0);
		$subject         = param('subject');
		$brief           = param('brief');
		$message         = param('message', '', FALSE);
		$cover           = param('cover');
		$seo_title       = param('seo_title');
		$seo_keywords    = param('seo_keywords');
		$seo_description = param('seo_description');

		!$cateid AND message(1, '请指定文章分类');
		!$subject AND message(2, '请填写标题');
		!$message AND message(3, '请填写内容');

		$arr = array(
			'cateid'          => $cateid,
			'subject'         => $subject,
			'brief'           => $brief,
			'message'         => $message,
			'cover'           => $cover,
			'update_date'     => $time,
			'seo_title'       => $seo_title,
			'seo_keywords'    => $seo_keywords,
			'seo_description' => $seo_description,
		);
		$r = article_update($articleid, $arr);
		
		$r !== FALSE ? message(0,'更新成功') : message(11, '更新失败');

	}

} elseif($action == 'delete') {

	if($method != 'POST') message(-1, 'Method Error.');

	$articleid = param('articleid', 0);

	$state = article_delete($articleid);
	$state ? message(0, '删除成功') : message(11, '删除失败');

} elseif($action == 'read') {
	
	$articleid   = param(2, 0);
	$article = article_read($articleid);
	include "./admin/view/article_read.htm";
	
} elseif($action == 'upload') {
	
	$upfile = param('upfile', '', FALSE);
	empty($upfile) AND message(-1, 'upfile 数据为空');
	$json = xn_json_decode($upfile);
	empty($json) AND message(-1, '数据有问题: json 为空');
	
	$name = $json['name'];
	$width = $json['width'];
	$height = $json['height'];
	$data = base64_decode($json['data']);
	$size = strlen($data);
	
	$types = include './conf/attach.conf.php';
	$allowtypes = $types['all'];
	$filename = $uid.'_'.attach_safe_name($name, $allowtypes);

	$dir = date('ymd', $time).'/';
	$path = $conf['upload_path'].'article/'.$dir;
	$url = $conf['upload_url'].'article/'.$dir.$filename;
	!IN_SAE AND !is_dir($path) AND (mkdir($path, 0777, TRUE) OR message(-2, '目录创建失败'));
	
	file_put_contents($path.$filename, $data) OR message(-1, '写入文件失败');
	message(0, array('url'=>$url, 'width'=>$width, 'height'=>$height, 'name'=>$filename));
}

?>
