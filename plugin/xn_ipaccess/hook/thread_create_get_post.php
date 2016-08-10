	!ipaccess_check($longip, 'threads') AND message(-1, '您的 IP 今日主题数达到上限。');
	!ipaccess_check_seriate_threads() AND message(-1, '您的 IP 今日连续主题数已经达到上限。');