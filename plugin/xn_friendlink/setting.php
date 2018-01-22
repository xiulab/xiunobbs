<?php

/*
	Xiuno BBS 4.0 插件实例：友情链接插件设置
	admin/plugin-setting-xn_friendlink.htm
*/

!defined('DEBUG') AND exit('Access Denied.');

$action = param(3);

if(empty($action)) {
	
	$linklist = db_find('friendlink', array(), array('rank'=>-1), 1, 1000, 'linkid');
	$maxid = db_maxid('friendlink', 'linkid');
	
	if($method == 'GET') {
		
		include _include(APP_PATH.'plugin/xn_friendlink/setting.htm');
		
	} else {
		
		$rowidarr = param('rowid', array(0));
		$namearr = param('name', array(''));
		$urlarr = param('url', array(''));
		$rankarr = param('rank', array(0));
		
		$arrlist = array();
		foreach($rowidarr as $k=>$v) {
			if(empty($namearr[$k]) && empty($urlarr[$k]) && empty($rankarr[$k])) continue;
			$arr = array(
				'linkid'=>$k,
				'name'=>$namearr[$k],
				'url'=>$urlarr[$k],
				'rank'=>$rankarr[$k],
			);
			if(!isset($linklist[$k])) {
				db_create('friendlink', $arr);
			} else {
				db_update('friendlink', array('linkid'=>$k), $arr);
			}
		}
		
		// 删除
		$deletearr = array_diff_key($linklist, $rowidarr);
		foreach($deletearr as $k=>$v) {
			db_delete('friendlink', array('linkid'=>$k));
		}
		
		message(0, '保存成功');
	}
}
?>