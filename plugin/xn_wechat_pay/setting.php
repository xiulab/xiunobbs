<?php

/*
	Xiuno BBS 4.0 插件实例：广告插件设置
	admin/plugin-setting-xn_ad.htm
*/

!defined('DEBUG') AND exit( 'Access Denied.' );
$header['title'] = lang('wechat_pay');
$header['mobile_title'] = lang('wechat_pay');
if ( $method == 'GET' ) {
	
	$input = array();
	
	$input['wx_mkid'] = form_text('wx_mkid', $conf['wx_mkid']);
	$input['wx_mksecret'] = form_text('wx_mksecret', $conf['wx_mksecret']);
	$input['wx_mkpr'] = form_text('wx_mkpr', $conf['wx_mkpr']);
	$input['wx_xcode'] = form_text('wx_xcode', $conf['wx_xcode']);
	include '../plugin/xn_wechat_pay/view/htm/setting.htm';
	
} else {
	
	$wx_mkid = param('wx_mkid', '', false);
	$wx_mksecret = param('wx_mksecret', '', false);
	$wx_mkpr = param('wx_mkpr', 0);
	$wx_xcode = param('wx_xcode', '/plugin/xn_wechat_pay/qrcode.jpeg', false);
	$replace = array();
	$replace['wx_mkid'] = $wx_mkid;
	$replace['wx_mksecret'] = $wx_mksecret;
	$replace['wx_mkpr'] = $wx_mkpr;
	$replace['wx_xcode'] = $wx_xcode;
	file_replace_var('../conf/conf.php', $replace);
	message(0, lang('modify_successfully'));
}
?>