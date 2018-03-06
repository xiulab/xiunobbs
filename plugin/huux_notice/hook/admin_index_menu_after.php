<?php exit;

$notice_menu = array(
		'notice' => array(
		'url'=>url('notice-list'), 
		'text'=>lang('notice'), 
		'icon'=>'icon-bell', 
		'tab'=> array (
			'list'=>array('url'=>url('notice-list'), 'text'=>lang('notice_admin_notice_list')),
			'post'=>array('url'=>url('notice-create'), 'text'=>lang('notice_admin_send_notice')),
		)
	));
$menu += $notice_menu;

?>