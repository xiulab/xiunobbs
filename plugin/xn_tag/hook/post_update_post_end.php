<?php exit;
		// todo:
		/*
		$tag_cate_id_arr = param('tag_cate_id', array(0));
		
		
		$tagids_new = array_values($tag_cate_id_arr);
		$tagids_old = tag_thread_find_tagid_by_tid($tid);
		//print_r($tagids_new);print_r($tagids_old);exit;
		//新增的、删除的 
		$tag_id_delete = array_diff($tagids_old, $tagids_new);
		$tag_id_add = array_diff($tagids_new, $tagids_old);
		foreach($tag_id_delete as $tagid) {
			tag_thread_delete($tagid, $tid);
		}
		foreach($tag_id_add as $tagid) {
			tag_thread_create($tagid, $tid);
		}
		thread_update($tid, array('tagids'=>'', 'tagids_time'=>0));

		*/
		
		if($isfirst) {
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
						message(-1, '请选择 ['.$cate['name'].']');
					}
				}
				// 判断是否默认
				if($defaulttagid) {
					if(empty($intersect)) {
						array_push($tagids, $defaulttagid);
					}
				}
				
			}
			
			$tagids = array_diff($tagids, array(0));
			$tagids_new = $tagids;
			$tagids_old = tag_thread_find_tagid_by_tid($tid, $forum['tagcatelist']);
			$tag_id_delete = array_diff($tagids_old, $tagids_new);
			$tag_id_add = array_diff($tagids_new, $tagids_old);
			if($tag_id_delete) {
				foreach($tag_id_delete as $tagid) {
					$tagid AND tag_thread_delete($tagid, $tid);
				}
			}
			if($tag_id_add) {
				foreach($tag_id_add as $tagid) {
					$tagid AND tag_thread_create($tagid, $tid);
				}
			}
			thread_update($tid, array('tagids'=>'', 'tagids_time'=>0));
			/*
			foreach($tagids as $tagid) {
				$tagid AND tag_thread_create($tagid, $tid);
			}*/
		}
?>