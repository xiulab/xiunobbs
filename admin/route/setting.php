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
		$input['sitebrief'] = form_textarea('sitebrief', $conf['sitebrief'], '100%', 100);
		$input['runlevel'] = form_radio('runlevel', array(0=>lang('runlevel_0'), 1=>lang('runlevel_1'), 2=>lang('runlevel_2'), 3=>lang('runlevel_3'), 4=>lang('runlevel_4'), 5=>lang('runlevel_5')), $conf['runlevel']);
		$input['lang'] = form_select('lang', array('zh-cn'=>lang('lang_zh_cn'), 'zh-tw'=>lang('lang_zh_tw'), 'en-us'=>lang('lang_en_us')), $conf['lang']);
		
		$header['title'] = lang('admin_site_setting');
		$header['mobile_title'] =lang('admin_site_setting');
		
		include './view/htm/setting_base.htm';
		
	} else {
		
		$sitebrief = param('sitebrief', '', FALSE);
		$sitename = param('sitename', '', FALSE);
		$runlevel = param('runlevel', 0);
		$_lang = param('lang');
		
		$replace = array();
		$replace['sitename'] = $sitename;
		$replace['sitebrief'] = $sitebrief;
		$replace['runlevel'] = $runlevel;
		$replace['lang'] = $_lang;
		
		//conf_save('../conf/conf.php', $conf) OR message(-1, '写入配置文件失败');
		file_replace_var('../conf/conf.php', $replace);
	
		message(0, lang('modify_successfully'));
	}

} elseif($action == 'smtp') {

	if($method == 'GET') {
		
		$header['title'] = lang('admin_setting_smtp');
		$header['mobile_title'] = lang('admin_setting_smtp');
	
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
		
		$replace = array();
		$replace['user_create_email_on'] = $user_create_email_on;
		$replace['user_resetpw_on'] = $user_resetpw_on;
		file_replace_var('../conf/conf.php', $replace);
		
		//conf_save('../conf/conf.php', $conf) OR message(-1, '保存到配置文件 conf/conf.php 失败，请检查文件的可写权限。');
		
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
		$r = file_put_contents_try('../conf/smtp.conf.php', "<?php\r\nreturn ".var_export($smtplist,true).";\r\n?>");
		!$r AND message(-1, lang('conf/smtp.conf.php', array('file'=>'conf/smtp.conf.php')));
		
		message(0, lang('save_successfully'));
	}
} else {
	http_404();
}

// hook admin_setting_action_after.php

?>