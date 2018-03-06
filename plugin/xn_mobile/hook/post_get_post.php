$kv_mobile = kv_get('mobile_setting');

if(empty($user['mobile']) && $kv_mobile['force_post_bind_on']) {
	message(-1, jump('根据国家实名制相关法律法规，请绑定手机号。', url('my-mobile')));
}