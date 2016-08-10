	!ipaccess_check($longip, 'attachs') AND message(-1, '您的 IP 今日附件数达到上限。');
	!ipaccess_check($longip, 'attachsizes') AND message(-1, '您的 IP 今日附件总大小达到上限。');