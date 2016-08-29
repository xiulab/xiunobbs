<?php

/*
	Xiuno BBS 4.0 插件实例：搜索设置
	admin/plugin-setting-xn_search.htm
*/

!defined('DEBUG') AND exit('Access Denied.');


$action = param(3);

if(empty($action)) {
	if($method == 'GET') {
		
		$input = array();
		$input['search_type'] = form_radio('search_type', array('fulltext'=>lang('search_type_fulltext'), 'like'=>lang('search_type_like')), kv_get('xn_search_type'));
		$input['search_cutword_url'] = form_text('search_cutword_url', kv_get('xn_search_cutword_url'), '100%');
		
		include _include(APP_PATH.'plugin/xn_search/setting.htm');
		
	} else {
	
		kv_set('xn_search_type', param('search_type'));
		kv_set('xn_search_cutword_url', param('search_cutword_url'));
		
		message(0, '修改成功');
	}
	
// 切词、索引，跳转的方式开始执行任务，一次执行 10 条，如果超时，则重新开始任务。
} elseif($action == 'cutword') {

}
	
?>