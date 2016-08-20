<?php

/*
	Xiuno BBS 4.0 插件实例：微信公众号接入
	admin/plugin-setting-xn_wechat_public.htm
*/

!defined('DEBUG') AND exit( 'Access Denied.' );
include '../plugin/xn_wechat_public/model/wechat.class.php';
$wechat['setting'] = array( 'index' => array( 'url' => url("plugin-setting-xn_wechat_public"), 'text' => lang('wechat_setting') ), 'menu' => array( 'url' => url("plugin-setting-xn_wechat_public-menu"), 'text' => lang('wechat_menu') ), );
$type = param(3, 'index');
if ( $type == 'index' ) {
	$follow = kv_get('follow');
	$header['title'] = lang('wechat_setting');
	$header['mobile_title'] = lang('wechat_setting');
	if ( $method == 'GET' ) {
		
		$input = array();
		
		$input['wx_appkey'] = form_text('wx_appkey', $conf['wx_appkey']);
		$input['wx_appsecret'] = form_text('wx_appsecret', $conf['wx_appsecret']);
		$input['wx_token'] = form_text('wx_token', $conf['wx_token']);
		$input['follow'] = form_textarea('follow', $follow, '100%', 100);
		$input['wx_auto'] = form_select('wx_auto', array( 0 => '绑定账号', 1 => '自动建号' ), $conf['wx_auto']);
		

		
		include '../plugin/xn_wechat_public/view/htm/setting.htm';
		
	} else {
		
		$wx_appkey = param('wx_appkey', '', false);
		$wx_appsecret = param('wx_appsecret', '', false);
		$wx_token = param('wx_token', '', false);
		
		$follow = param('follow', '', false);
		$wx_auto = param('wx_auto', 0);
		
		$replace = array();
		$replace['wx_appkey'] = $wx_appkey;
		$replace['wx_appsecret'] = $wx_appsecret;
		$replace['wx_token'] = $wx_token;
		$replace['wx_auto'] = $wx_auto;
		
		file_replace_var('../conf/conf.php', $replace);
		kv_set('follow', $follow);
		message(0, lang('modify_successfully'));
	}
} elseif ( $type == 'menu' ) {
	
	
	$wechat_class = new wechat($conf);
	$header['title'] = lang('wechat_menu');
	$header['mobile_title'] = lang('wechat_menu');
	
	$wxmenu = kv_get('wechat_menu');
	
	$pmenu = param('menu', 0);
	//'scancode_push'=>'扫码事件','scancode_waitmsg'=>'扫码登录'
	$menu_radio = array( 'click' => '点击推事件', 'view' => '跳转URL' );
	
	if ( !empty( $pmenu ) ) {
		$wechat_menu = array_to_tree($wxmenu);
		foreach ( $wechat_menu as $k => $wechat ) {
			$x = $k - 1;
			if ( !empty( $wechat['son'] ) ) {
				$data['button'][$x] = array( 'name' => urlencode($wechat['name']) );
				foreach ( $wechat['son'] as $_k => $_wechat ) {
					if ( $_wechat['type'] == 'click' ) {
						$_wechat['name'] AND $data['button'][$x]['sub_button'][] = array( 'type' => 'click', 'name' => urlencode($_wechat['name']), 'key' => urlencode($_wechat['key']) );
					} elseif ( $_wechat['type'] == 'scancode_waitmsg' ) {
						$_wechat['name'] AND $data['button'][$x]['sub_button'][] = array( 'type' => 'scancode_waitmsg', 'name' => urlencode($_wechat['name']), 'key' => 'rselfmenu_0_0', 'sub_button' => array() );
					} elseif ( $_wechat['type'] == 'scancode_push' ) {
						$_wechat['name'] AND $data['button'][$x]['sub_button'][] = array( 'type' => 'scancode_push', 'name' => urlencode($_wechat['name']), 'key' => 'rselfmenu_0_1', 'sub_button' => array() );
					} else {
						$_wechat['name'] AND $data['button'][$x]['sub_button'][] = array( 'type' => 'view', 'name' => urlencode($_wechat['name']), 'url' => $_wechat['url'] );
					}
				}
				if ( empty( $data['button'][$x]['sub_button'] ) ) {
					if ( $wechat['type'] == 'click' ) {
						$data['button'][$x] = array( 'type' => 'click', 'name' => urlencode($wechat['name']), 'key' => urlencode($wechat['key']) );
					} elseif ( $wechat['type'] == 'scancode_waitmsg' ) {
						$data['button'][$x] = array( 'type' => 'scancode_waitmsg', 'name' => urlencode($wechat['name']), 'key' => 'rselfmenu_0_0', 'sub_button' => array() );
					} elseif ( $wechat['type'] == 'scancode_push' ) {
						$data['button'][$x] = array( 'type' => 'scancode_push', 'name' => urlencode($wechat['name']), 'key' => 'rselfmenu_0_1', 'sub_button' => array() );
					} else {
						$data['button'][$x] = array( 'type' => 'view', 'name' => urlencode($wechat['name']), 'url' => $wechat['url'] );
					}
				}
			}
		}
		
		$data = urldecode(xn_json_encode($data));
		$return = $wechat_class->create_menu($data);
		message($return['code'], $return['msg']);
	} elseif ( $method == 'GET' ) {
		for ( $i = 1; $i < 4; $i++ ) {
			$wechat_menu[$i]['id'] = !empty( $wxmenu[$i]['id'] ) ? $wxmenu[$i]['id'] : $i;
			$wechat_menu[$i]['pid'] = !empty( $wxmenu[$i]['pid'] ) ? $wxmenu[$i]['pid'] : '';
			$wechat_menu[$i]['name'] = !empty( $wxmenu[$i]['name'] ) ? $wxmenu[$i]['name'] : '';
			$wechat_menu[$i]['key'] = !empty( $wxmenu[$i]['key'] ) ? $wxmenu[$i]['key'] : '';
			$wechat_menu[$i]['url'] = !empty( $wxmenu[$i]['url'] ) ? $wxmenu[$i]['url'] : '';
			$wechat_menu[$i]['type'] = form_select("types[$i]", $menu_radio, !empty( $wxmenu[$i]['type'] ) ? $wxmenu[$i]['type'] : 'view');
			for ( $ii = 1; $ii < 6; $ii++ ) {
				$x = 3 + $ii + ( $i - 1 ) * 5;
				$wechat_menu[$i]['son'][$x]['id'] = !empty( $wxmenu[$x]['id'] ) ? $wxmenu[$x]['id'] : $x;
				$wechat_menu[$i]['son'][$x]['pid'] = !empty( $wxmenu[$x]['pid'] ) ? $wxmenu[$x]['pid'] : '';
				$wechat_menu[$i]['son'][$x]['name'] = !empty( $wxmenu[$x]['name'] ) ? $wxmenu[$x]['name'] : '';
				$wechat_menu[$i]['son'][$x]['key'] = !empty( $wxmenu[$x]['key'] ) ? $wxmenu[$x]['key'] : '';
				$wechat_menu[$i]['son'][$x]['url'] = !empty( $wxmenu[$x]['url'] ) ? $wxmenu[$x]['url'] : '';
				$wechat_menu[$i]['son'][$x]['type'] = form_select("types[$x]", $menu_radio, !empty( $wxmenu[$x]['type'] ) ? $wxmenu[$x]['type'] : 'view');
			}
		}
		
		include '../plugin/xn_wechat_public/view/htm/setting_menu.htm';
	} else {
		$ids = param('ids', array());
		$pids = param('pids', array());
		$names = param('names', array());
		$keys = param('keys', array());
		$urls = param('urls', array());
		$types = param('types', array());
		foreach ( $names as $k => $v ) {
			$wechat_menu[$k]['id'] = $ids[$k];
			$wechat_menu[$k]['pid'] = $pids[$k];
			$wechat_menu[$k]['name'] = $names[$k];
			$wechat_menu[$k]['key'] = $keys[$k];
			$wechat_menu[$k]['url'] = $urls[$k];
			$wechat_menu[$k]['type'] = $types[$k];
		}
		kv_set('wechat_menu', $wechat_menu);
		//xn_log('保存微信公众号菜单', 'admin_log');
		message(0, lang('modify_successfully'));
	}
	
}
?>