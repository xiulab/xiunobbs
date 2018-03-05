<?php exit;

$action2 = param(2);
empty($action2) AND $action2 = empty($user['mobile']) ? 'bind' : 'change';

if($action == 'mobile') {
	
	$kv_mobile = kv_get('mobile_setting');
	$login_type = $kv_mobile['login_type'];

	// 绑定手机号
	if($action2 == 'bind') {
		if($method == 'GET') {
			
			include _include(APP_PATH.'view/htm/user_mobile_bind.htm');
			
		} else {
			
			empty($kv_mobile['bind_on']) AND message(-1, '未开启绑定功能');
			
			$sess_range = _SESSION('range');
			$sess_range != 'create' AND message(-1, '验证码使用超出限定范围');
			
			$mobile = param('mobile');
			$code = param('code');
			empty($mobile) AND message('mobile', lang('please_input_mobile'));
			
			$code != _SESSION('code') AND message('code', lang('click_to_send_code'));
			
			// 手机是否注册
			!is_mobile($mobile, $err) AND message('mobile', $err);
			$_user = user_read_by_mobile($mobile);
			$_user AND message('mobile', lang('mobile_is_in_use'));
			
			user_update($uid, array('mobile'=>$mobile));
			
			unset($_SESSION['mobile']);
			unset($_SESSION['code']);
			unset($_SESSION['range']);
			
			message(0, '绑定成功');
		}
		
	// 修改手机号
	} elseif($action2 == 'change') {
		
		if($method == 'GET') {
			
			include _include(APP_PATH.'view/htm/user_mobile_change.htm');
			
		} else {
		
			empty($kv_mobile['bind_on']) AND message(-1, '未开启绑定功能');
			
			$sess_range = _SESSION('range');
			$sess_range != 'create' AND message(-1, '验证码使用超出限定范围');
			
			$mobile = param('mobile');
			$code = param('code');
			empty($mobile) AND message('mobile', lang('function_not_on'));
			
			$code != _SESSION('code') AND message('code', lang('click_to_send_code'));
			
			// 手机是否注册
			!is_mobile($mobile, $err) AND message('mobile', $err);
			$_user = user_read_by_mobile($mobile);
			$_user AND message('mobile', lang('mobile_is_in_use'));
			
			user_update($uid, array('mobile'=>$mobile));
			
			unset($_SESSION['mobile']);
			unset($_SESSION['code']);
			unset($_SESSION['range']);
			
			message(0, '绑定成功');
		}
	} else {
		message(-1, 'action error');
	}

}

?>