<?php

!defined('DEBUG') AND exit('Access Denied.');

$action = param(1);

include '../model/smtp.func.php';
smtp_init('../conf/smtp.conf.php');

// hook admin_setting_action_before.php

if($action == 'base') {
	
	if($method == 'GET') {
		
		$input = array();
		$input['sitename'] = form_text('sitename', $conf['sitename']);
		$input['runlevel'] = form_radio('runlevel', array(0=>'站点关闭', 1=>'管理员可读写', 2=>'会员可读', 3=>'会员可读写', 4=>'所有人只读', 5=>'所有人可读写'), $conf['runlevel']);
		$input['lang'] = form_select('lang', array('zh-cn'=>'简体中文', 'zh-tw'=>'繁体中文', 'en-us'=>'美式英语'), $conf['lang']);
		
		$setting = kv_get('setting');	// 首页数据
		empty($setting) AND $setting = array('sitebrief'=>'');
		$sitebrief = $setting['sitebrief']; // 站点介绍
		
		$header['title'] = '站点设置';
		$header['mobile_title'] = '站点设置';
		
		include './view/htm/setting_base.htm';
		
	} else {
		
		$sitebrief = param('sitebrief', '', FALSE);
		$sitename = param('sitename', '', FALSE);
		$runlevel = param('runlevel', 0);
		$_lang = param('lang');
		
		$setting = array('sitebrief'=>$sitebrief);
		kv_set('setting', $setting);
		
		$conf['sitename'] = $sitename;
		$conf['runlevel'] = $runlevel;
		$conf['lang'] = $_lang;
		
		conf_save('../conf/conf.php', $conf) OR message(-1, '写入配置文件失败');
	
		message(0, '修改成功');
	}

} elseif($action == 'smtp') {

	if($method == 'GET') {
		
		$header['title'] = 'SMTP 设置';
		$header['mobile_title'] = 'SMTP 设置';
	
		$smtplist = smtp_find();
		$maxid = smtp_maxid();
		
		$default = array('host'=>'smtp.sina.com', 'port'=>25, 'user'=>'xxxx', 'email'=>'xxxx@sina.com', 'pass'=>'xxxx');
		empty($smtplist) AND $smtplist = array($default);
	
		$input_user_create_email_on = form_radio_yes_no('user_create_email_on', $conf['user_create_email_on']);
		$input_user_resetpw_on = form_radio_yes_no('user_resetpw_on', $conf['user_resetpw_on']);
		
		include "./view/htm/setting_smtp.htm";
	
	} else {
		
		$user_create_email_on = param('user_create_email_on', 0);
		$user_resetpw_on = param('user_resetpw_on', 0);
		
		$conf['user_create_email_on'] = $user_create_email_on;
		$conf['user_resetpw_on'] = $user_resetpw_on;
		
		conf_save('../conf/conf.php', $conf) OR message(-1, '保存到配置文件 conf/conf.php 失败，请检查文件的可写权限。');
		
		$email = param('email', array(''));
		$host = param('host', array(0));
		$port = param('port', array(0));
		$user = param('user', array(''));
		$pass = param('pass', array(''));
		
		$smtplist = array();
		foreach ($email as $k=>$v) {
			$smtplist[$k] = array(
				'email'=>$email[$k],
				'host'=>$host[$k],
				'port'=>$port[$k],
				'user'=>$user[$k],
				'pass'=>$pass[$k],
			);
		}
		$r = file_put_content_try('../conf/smtp.conf.php', "<?php\r\nreturn ".var_export($smtplist,true).";\r\n?>");
		!$r AND message(-1, '保存数据到配置文件 conf/smtp.conf.php 失败，请检查文件的可写权限。');
		
		message(0, '保存成功');
	}
} else {
	http_404();
}

// hook admin_setting_action_after.php

?>