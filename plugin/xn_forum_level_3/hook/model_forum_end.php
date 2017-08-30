<?php exit;

// 关联数据删除
function forum_delete_sons($fid) {
	$forum = forum_read($fid);
	$fidarr = array();
	// 大分类
	if($forum['fup'] == 0) {
		$forumlist = forum_find(array('fup'=>$fid));
		$fidarr = arrlist_values($forumlist, 'fid');
		if(is_array($forumlist)) {
			foreach($forumlist as $_forum) {
				$sublist = forum_find(array('fup'=>$_forum['fid']));
				is_array($sublist) AND $fidarr += arrlist_values($sublist, 'fid');
			}
		}
	// 二级，或者三级分类
	} else {
		$sublist = forum_find(array('fup'=>$fid));
		$fidarr = arrlist_values($sublist, 'fid');
	}
	foreach($fidarr as $_fid) {
		forum_delete($_fid);
	}
}

// 三级版块
function forum_list_tree($forumlist) {
	$catelist = array();
	foreach($forumlist as $forum) {
		if($forum['fup'] == 0) {
			$catelist[] = $forum;
		}
	}
	
	foreach($catelist as &$cate) {
		$forumlist2 = forum_find_son_list($forumlist, $cate['fid']);
		foreach($forumlist2 as &$forum) {
			$forum['forumlist'] = forum_find_son_list($forumlist, $forum['fid']);
		}
		$cate['forumlist'] = $forumlist2;
	}
	return $catelist;
}

// 三级下拉菜单
function forum_3_options($forumlist, $fid = 0) {
	$catelist = forum_find_son_list($forumlist, 0);
	$s = '';
	foreach ($catelist as $cate) {
		$selected = $cate['fid'] == $fid ? ' selected' : '';
		$s .= "<option value=\"$cate[fid]\"$selected>$cate[name] ($cate[threads])</option>\r\n";
		$forum2list = forum_find_son_list($forumlist, $cate['fid']);
		foreach($forum2list as $forum) {
			$selected = $forum['fid'] == $fid ? ' selected' : '';
			$s .= "<option value=\"$forum[fid]\"$selected>|-------- $forum[name] ($forum[threads])</option>\r\n";
			$sonlist = forum_find_son_list($forumlist, $forum['fid']);
			foreach($sonlist as $son) {
				$selected = $son['fid'] == $fid ? ' selected' : '';
				$s .= "<option value=\"$son[fid]\"$selected>|---------------- $son[name] ($son[threads])</option>\r\n";
			}
		}
	}
	return $s;
}

function forum_find_son_list($forumlist, $fid) {
	$arrlist = array();
	foreach($forumlist as $forum) {
		if($forum['fup'] == $fid) {
			$arrlist[] = $forum;
		}
	}
	return $arrlist;
}

function forum_find_sibling_list($forumlist, $fid) {
	$forum = $forumlist[$fid];
	$fup = $forum['fup'];
	return forum_find_son_list($forumlist, $fup);
}


// 是否为子版块
function forum_is_sub($fid) {
	global $forumlist;
	$forum = $forumlist[$fid];
	if($forum['fup'] == 0) return FALSE;
	$fup = $forum['fup'];
	$pforum = $forumlist[$fup];
	if($pforum['fup'] == 0) return FALSE;
	return TRUE;
}


?>