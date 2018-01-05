<?php exit;
		// todo:
		/*
		$tag_cate_id_arr = param('tag_cate_id', array(0));
		foreach($tag_cate_id_arr as $tag_cate_id => $tagid) {
			tag_thread_create($tagid, $tid);
		}
		*/
		
		$tagids = param('tagid', array(0));
		
		$tagcatemap = $forum['tagcatemap'];
		foreach($forum['tagcatemap'] as $cate) {
			$defaulttagid = $cate['defaulttagid'];
			$isforce = $cate['isforce'];
			$catetags = array_keys($cate['tagmap']);
			$intersect = array_intersect($catetags, $tagids); // 比较数组交集
			// 判断是否强制
			if($isforce) {
				if(empty($intersect)) {
					message(-1, '请选择'.$cate['name']);
				}
			}
			// 判断是否默认
			if($defaulttagid) {
				if(empty($intersect)) {
					array_push($tagids, $defaulttagid);
				}
			}
		}
		
		foreach($tagids as $tagid) {
			$tagid AND tag_thread_create($tagid, $tid);
		}
		
?>