<?php

!defined('DEBUG') AND exit('Access Denied.');

include './xiunophp/form.func.php';

$action = param(1);

if(empty($action)) {
	
	$conffile = './conf/conf.php';
	$readable = is_writeable($conffile);
	
	if($method == 'GET') {
		
		$input = array();
		$input['ipaccess_on'] = form_radio_yes_no('ipaccess_on', $conf['ipaccess_on']);
		$input['ipaccess_mails'] = form_text('mails', $conf['ipaccess']['mails']);
		$input['ipaccess_threads'] = form_text('threads', $conf['ipaccess']['threads']);
		$input['ipaccess_posts'] = form_text('posts', $conf['ipaccess']['posts']);
		$input['ipaccess_attachs'] = form_text('attachs', $conf['ipaccess']['attachs']);
		$input['ipaccess_attachsizes'] = form_text('attachsizes', $conf['ipaccess']['attachsizes']);
		
		include './admin/view/ip.htm';
		
	} else {
	
		$ipaccess_on = param('ipaccess_on', 0);
		$ipaccess = array(
			'mails' => param('mails', 0),
			'threads' => param('threads', 0),
			'posts' => param('posts', 0),
			'attachs' => param('attachs', 0),
			'attachsizes' => param('attachsizes', 0),
		);
		
		$conf['ipaccess_on'] = $ipaccess_on;
		$conf['ipaccess'] = array_merge($conf['ipaccess'], $ipaccess);
		conf_save();
		message(0, '修改成功');
	}
}

?>