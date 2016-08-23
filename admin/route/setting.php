<?php

!defined('DEBUG') AND exit('Access Denied.');

$action = param(1);

include _include(APP_PATH.'model/smtp.func.php');
smtp_init(APP_PATH.'conf/smtp.conf.php');

// hook admin_setting_start.php

if($action == 'base') {
	
	// hook admin_setting_base_get_post.php
	
	if($method == 'GET') {
		
		// hook admin_setting_base_get_start.php
		
		$input = array();
		$input['sitename'] = form_text('sitename', $conf['sitename']);
		$input['sitebrief'] = form_textarea('sitebrief', $conf['sitebrief'], '100%', 100);
		$input['runlevel'] = form_radio('runlevel', array(0=>lang('runlevel_0'), 1=>lang('runlevel_1'), 2=>lang('runlevel_2'), 3=>lang('runlevel_3'), 4=>lang('runlevel_4'), 5=>lang('runlevel_5')), $conf['runlevel']);
		$input['user_create_email_on'] = form_radio_yes_no('user_create_email_on', $conf['user_create_email_on']);
		$input['lang'] = form_select('lang', array('zh-cn'=>lang('lang_zh_cn'), 'zh-tw'=>lang('lang_zh_tw'), 'en-us'=>lang('lang_en_us')), $conf['lang']);
		
		$header['title'] = lang('admin_site_setting');
		$header['mobile_title'] =lang('admin_site_setting');
		
		// hook admin_setting_base_get_end.php
		
		include _include(ADMIN_PATH.'view/htm/setting_base.htm');
		
	} else {
		
		$sitebrief = param('sitebrief', '', FALSE);
		$sitename = param('sitename', '', FALSE);
		$runlevel = param('runlevel', 0);
		$user_create_email_on = param('user_create_email_on', 0);
		$_lang = param('lang');
		
		// hook admin_setting_base_post_start.php
		
		$replace = array();
		$replace['sitename'] = $sitename;
		$replace['sitebrief'] = $sitebrief;
		$replace['runlevel'] = $runlevel;
		$replace['user_create_email_on'] = $user_create_email_on;
		$replace['lang'] = $_lang;
		
		file_replace_var(APP_PATH.'conf/conf.php', $replace);
	
		// hook admin_setting_base_post_end.php
		
		message(0, lang('modify_successfully'));
	}

} elseif($action == 'smtp') {

	// hook admin_setting_smtp_get_post.php
	
	if($method == 'GET') {
		
		// hook admin_setting_smtp_get_start.php
		
		$header['title'] = lang('admin_setting_smtp');
		$header['mobile_title'] = lang('admin_setting_smtp');
	
		$smtplist = smtp_find();
		$maxid = smtp_maxid();
		
		$default = array('host'=>'smtp.sina.com', 'port'=>25, 'user'=>'xxxx', 'email'=>'xxxx@sina.com', 'pass'=>'xxxx');
		empty($smtplist) AND $smtplist = array($default);
	
		$input_user_create_email_on = form_radio_yes_no('user_create_email_on', $conf['user_create_email_on']);
		$input_user_resetpw_on = form_radio_yes_no('user_resetpw_on', $conf['user_resetpw_on']);
		
		// hook admin_setting_smtp_get_end.php
		
		include _include(ADMIN_PATH."./view/htm/setting_smtp.htm");
	
	} else {
		
		// hook admin_setting_smtp_post_start.php
		
		$user_create_email_on = param('user_create_email_on', 0);
		$user_resetpw_on = param('user_resetpw_on', 0);
		
		$replace = array();
		$replace['user_create_email_on'] = $user_create_email_on;
		$replace['user_resetpw_on'] = $user_resetpw_on;
		
		// save to conf/conf.php
		file_replace_var(APP_PATH.'conf/conf.php', $replace);
		
		$email = param('email', array(''));
		$host = param('host', array(''));
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
		$r = file_put_contents_try(APP_PATH.'conf/smtp.conf.php', "<?php\r\nreturn ".var_export($smtplist,true).";\r\n?>");
		!$r AND message(-1, lang('conf/smtp.conf.php', array('file'=>'conf/smtp.conf.php')));
		
		// hook admin_setting_smtp_post_end.php
		
		message(0, lang('save_successfully'));
	}
}

// hook admin_setting_end.php

?>