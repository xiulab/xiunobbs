<?php

!defined('DEBUG') AND exit('Access Denied.');

include './xiunophp/form.func.php';

$action = param(1);

if($action == 'base') {
	
	$conffile = './conf/conf.php';
	$readable = is_writeable($conffile);
	
	if($method == 'GET') {
		
		$input = array();
		$input['sitename'] = form_text('sitename', $conf['sitename']);
		$input['runlevel'] = form_radio('runlevel', array(0=>'站点关闭', 1=>'管理员可读写', 2=>'会员可读', 3=>'会员可读写', 4=>'所有人只读', 5=>'所有人可读写'), $conf['runlevel']);
		
		$setting = kv_get('setting');	// 首页数据
		empty($setting) AND $setting = array('sitebrief'=>'', 'seo_title'=>'', 'seo_keywords'=>'', 'seo_description'=>'', 'footer_code'=>'');
		$sitebrief = $setting['sitebrief']; // 站点介绍
		
		$input['seo_title'] = form_text('seo_title', $setting['seo_title'], '100%');
		$input['seo_keywords'] = form_text('seo_keywords', $setting['seo_keywords'], '100%');
		$input['seo_description'] = form_text('seo_description', $setting['seo_description'], '100%');
		$input['footer_code'] = form_textarea('footer_code', $setting['footer_code'], '100%', '50px');
		
		include './admin/view/setting.htm';
		
	} else {
	
		$sitebrief = param('sitebrief', '', FALSE);
		$seo_title = param('seo_title', '', FALSE);
		$seo_keywords = param('seo_keywords', '', FALSE);
		$seo_description = param('seo_description', '', FALSE);
		$footer_code = param('footer_code', '', FALSE);
		$setting = array('sitebrief'=>$sitebrief, 'seo_title'=>$seo_title, 'seo_keywords'=>$seo_keywords, 'seo_description'=>$seo_description, 'footer_code'=>$footer_code);
		kv_set('setting', $setting);
		cache_delete('setting');
		
		empty($readable) AND message(-1, '配置文件 conf/conf.php 不可写，请手工修改。');
		
		$sitename = param('sitename', '', FALSE);
		
		$runlevel = param('runlevel', 0);
		
		$conf['sitename'] = $sitename;
		$conf['runlevel'] = $runlevel;
		
		conf_save();
	
		message(0, '修改成功');
	}
	
	/*
} elseif($action == 'smtp') {
	
	if($method == 'GET') {
		$mailconf = kv_get('smtp');
		
		$sendtype = &$mailconf['sendtype'];
		$smtplist = &$mailconf['smtplist'];
		
		$input = array();
		$input['sendtype'] = form::get_radio('sendtype', array(0=>'PHP内置mail函数 ', 1=>'SMTP 方式'), $sendtype);
		
		$this->view->assign('error', $error);
		$this->view->assign('smtplist', $smtplist);
		$this->view->assign('input', $input);
		
		// hook admin_conf_mail_view_before.php
		
		$this->view->display('conf_mail.htm');
	} else {
		$email = param('email', array(''));
		$host = param('host', array(''));
		$port = param('port', array(''));
		$user = param('user', array(''));
		$pass = param('pass', array(''));
		$delete = param('delete', array(''));
		$sendtype = param('sendtype', array(''));
		$smtplist = array();
		foreach($email as $k=>$v) {
			empty($port[$k]) && $port[$k] = 25;
			if(in_array($k, $delete)) continue;
			if(empty($email[$k]) || empty($host[$k]) || empty($user[$k])) continue;
			$smtplist[$k] = array('email'=>$email[$k], 'host'=>$host[$k], 'port'=>$port[$k], 'user'=>$user[$k], 'pass'=>$pass[$k]);
		}
		
		kv_set('mail_conf', $mailconf);
		
		$mail_smtplist = $smtplist;
		
	}
	$error = array();
		
		*/
		
} elseif($action == 'create') {


}

?>