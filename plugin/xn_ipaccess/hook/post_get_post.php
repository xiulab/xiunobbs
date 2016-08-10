	!ipaccess_check($longip, 'posts') AND message(-1, '您的 IP 今日回帖数达到上限。');
	!ipaccess_check_seriate_posts($tid) AND message(-1, '您的 IP 今日连续发帖数已经达到上限。');