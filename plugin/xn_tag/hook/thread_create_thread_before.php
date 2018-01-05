<?php exit;
		// todo:
		
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
			
		}
		
?>