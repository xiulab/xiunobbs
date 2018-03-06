<?php

!defined('DEBUG') AND exit('Access Denied.');

// 管理后台-所有消息 发送通知
$action = param(1);

if($action == 'create') {

	if($method == 'GET') {
		
		$input = array();
		$input['recvuid'] = form_text('recvuid', '');
		$input['message'] = form_textarea('message', '', '100%', 100);
		
		$header['title'] = lang('notice_admin_send_notice');
		$header['mobile_title'] =lang('notice_admin_send_notice');
				
		include _include(APP_PATH."plugin/huux_notice/view/htm/admin_notice_create.htm");
		
	} else {
		
		$message = param('message', '', FALSE);
		$recvuid = param('recvuid', 0);

		// 检查内容和接收人是否为空
		empty($message) AND message('message', lang('notice_admin_send_notice_message_empty'));
		empty($recvuid) AND message('recvuid', lang('notice_admin_send_notice_recvuid_empty'));

		// 检查接收人是否存在
		$recvuid_check = user__read($recvuid);
		$recvuid_check === FALSE AND message('recvuid', lang('notice_admin_send_notice_user_empty'));
		
		$nid = notice_send($uid, $recvuid, $message, $type = 1); // 1:通知
		$nid === FALSE AND message(-1, lang('notice_admin_send_notice_failed'));
		
		message(0, lang('notice_admin_send_notice_sucessfully'));
	}


}elseif($action == 'delete') {

	// 单条删除
	$nid = param('nid');
	$r = notice_delete($nid);
	$r === FALSE AND message(-1, lang('notice_delete_notice_failed'));
	message(0, lang('notice_delete_notice_sucessfully'));

}elseif($action == 'list'){

	$page = param(2, 1);
	$pagesize = 20;
	$active = 'default';
	$notices = notice_count(); //直接获取最新的
	$cond = array();
	$orderby = 'nid';

	$notice_menu = include _include(APP_PATH.'plugin/huux_notice/conf/notice_menu.conf.php');
	
	$noticelist = notice_find($cond, $page, $pagesize);
	$pagination = pagination(url("notice-list-{page}"), $notices, $page, $pagesize);

	$header['title'] = lang('notice_admin_notice_list');
	$header['mobile_title'] =lang('notice_admin_notice_list');

	include _include(APP_PATH."plugin/huux_notice/view/htm/admin_notice_list.htm");



}elseif($action == 'read'){
	//ajax返回message 暂时不需要
}

?>