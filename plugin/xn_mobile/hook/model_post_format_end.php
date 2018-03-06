
static $kv_mobile = NULL;
empty($kv_mobile) AND $kv_mobile = kv_get('mobile_setting');

if($gid != 1) {

	if(empty($user['mobile']) && $kv_mobile['force_view_bind_on']) {
		$post['message_fmt'] = '<span class="text-muted text-small">[该用户未绑定手机，内容不能显示]</span>';
	}
	

}