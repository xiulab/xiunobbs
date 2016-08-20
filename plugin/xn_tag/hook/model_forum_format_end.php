<?php exit;
	// todo:
	$forum['tagcatelist'] = tag_cate_find_by_fid($forum['fid']);
	$forum['tagcatemap'] = arrlist_change_key($forum['tagcatelist'], 'cateid');
	$forum['tagmap'] = tag_fetch_from_catelist($forum['tagcatelist']);
	
?>