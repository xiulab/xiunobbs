<?php

!defined('DEBUG') AND exit('Access Denied.');

$action = param(1);

if(empty($action) || $action == 'list') {
	
	if($method == 'GET') {
		
		$header['title']    = '用户组管理';
	
		include "./admin/view/group_list.htm";
	
	} elseif($method == 'POST') {
		
		conf_save() OR message(-1, '保存到配置文件 conf/conf.php 失败，请检查文件的可写权限。');
		
		$gidarr = param('_gid', array(0));
		$namearr = param('name', array(''));
		$creditsfromarr = param('creditsfrom', array(0));
		$creditstoarr = param('creditsto', array(0));
		
		$arrlist = array();
		foreach ($gidarr as $k=>$v) {
			$arr = array(
				'gid'=>$k,
				'name'=>$namearr[$k],
				'creditsfrom'=>$creditsfromarr[$k],
				'creditsto'=>$creditstoarr[$k],
			);
			if(!isset($grouplist[$k])) {
				// 添加
				group_create($arr);
			} else {
				// 编辑
				group_update($k, $arr);
			}
		}
		
		// 删除
		$deletearr = array_diff_key($grouplist, $gidarr);
		foreach($deletearr as $k=>$v) {
			group_delete($k);
		}
		
		group_list_cache_delete();
		
		message(0, '保存成功');
	}
	
} elseif($action == 'update') {
	
} elseif($action == 'delete') {
	
	if($method == 'POST') {
		$_gid = param(2, 0);
		group_delete($_gid);
		group_list_cache_delete();
	}
	
} else {
	
	http_404();
	
}
?>