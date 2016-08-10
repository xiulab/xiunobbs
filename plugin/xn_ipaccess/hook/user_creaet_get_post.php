	!ipaccess_check($longip, 'users') AND message(-1, '您的 IP 今日注册用户数达到上限。');
	!ipaccess_check_seriate_users() AND message(-1, '您的 IP 今日连续注册用户已经达到上限。');