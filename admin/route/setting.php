<?php

!defined('DEBUG') AND exit('Access Denied.');

$action = param(1);

include './model/smtp.func.php';

if(empty($action) || $action == 'base') {
	
	if($method == 'GET') {
		
		$input = array();
		$input['sitename'] = form_text('sitename', $conf['sitename']);
		$input['runlevel'] = form_radio('runlevel', array(0=>'站点关闭', 1=>'管理员可读写', 2=>'会员可读', 3=>'会员可读写', 4=>'所有人只读', 5=>'所有人可读写'), $conf['runlevel']);
		
		$setting = kv_get('setting');	// 首页数据
		empty($setting) AND $setting = array('sitebrief'=>'');
		$sitebrief = $setting['sitebrief']; // 站点介绍
		
		include './admin/view/setting_base.htm';
		
	} else {
		
		$sitebrief = param('sitebrief', '', FALSE);
		$sitename = param('sitename', '', FALSE);
		$runlevel = param('runlevel', 0);
		
		$setting = array('sitebrief'=>$sitebrief);
		kv_set('setting', $setting);
		cache_delete('setting');
		
		$conf['sitename'] = $sitename;
		$conf['runlevel'] = $runlevel;
		
		conf_save() OR message(-1, '写入配置文件失败');
	
		message(0, '修改成功');
	}

} elseif($action == 'smtp') {

	if($method == 'GET') {
		$header['title']    = 'SMTP 管理';
	
		$smtplist = smtp_find();
		$maxid = smtp_maxid();
		
		$default = array('host'=>'smtp.sina.com', 'port'=>25, 'user'=>'xxxx', 'email'=>'xxxx@sina.com', 'pass'=>'xxxx');
		empty($smtplist) AND $smtplist = array($default);
	
		$input_user_create_email_on = form_radio_yes_no('user_create_email_on', $conf['user_create_email_on']);
		$input_user_find_pw_on = form_radio_yes_no('user_find_pw_on', $conf['user_find_pw_on']);
		
		include "./admin/view/setting_smtp.htm";
	
	} else {
	
	}
}
?>