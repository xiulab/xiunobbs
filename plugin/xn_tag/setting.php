<?php

/*
	Xiuno BBS 4.0 插件实例：TAG 插件设置
	admin/plugin-setting-xn_tag.htm
*/

!defined('DEBUG') AND exit('Access Denied.');

$action = param(3);

if(empty($action)) {
	
	$linklist = db_find('tag', array(), array('rank'=>-1), 1, 1000, 'linkid');
	$maxid = db_maxid('tag', 'linkid');
	
	if($method == 'GET') {
		
		include '../plugin/xn_tag/setting.htm';
		
	} else {
		
		$rowidarr = param('rowid', array(0));
		$namearr = param('name', array(''));
		$urlarr = param('url', array(''));
		$rankarr = param('rank', array(0));
		
		unset($rowidarr[0]);
		unset($namearr[0]);
		unset($urlarr[0]);
		unset($rankarr[0]);
		
		$arrlist = array();
		foreach($rowidarr as $k=>$v) {
			$arr = array(
				'linkid'=>$k,
				'name'=>$namearr[$k],
				'url'=>$urlarr[$k],
				'rank'=>$rankarr[$k],
			);
			if(!isset($linklist[$k])) {
				db_create('tag', $arr);
			} else {
				db_update('tag', array('linkid'=>$k), $arr);
			}
		}
		
		// 删除
		$deletearr = array_diff_key($linklist, $rowidarr);
		foreach($deletearr as $k=>$v) {
			db_delete('tag', array('linkid'=>$k));
		}
		
		message(0, '保存成功');
	}
}
?>