<?php exit;
 		$tagcatelist = arrlist_change_key($tagcatelist, 'cateid');
		$cate_name_arr = param('cate_name', array(''));
		$cate_rank_arr = param('cate_rank', array(0));
		$cate_enable_arr = param('cate_enable', array(0));
		$cate_id_arr = array_keys($cate_name_arr);
		$cate_id_arr_old = arrlist_values($tagcatelist, 'cateid');
		
		//error_log(print_r($cate_id_arr, 1), 3, 'd:/cate_id_arr.txt');
		//error_log(print_r($tagcatelist, 1), 3, 'd:/tagcatelist.txt');
		
		// 新增 + 更新 / new + update
		foreach($cate_id_arr as $k) {
			$arr = array(
				'cateid'=>$k,
				'fid'=>$_fid,
				'name'=>$cate_name_arr[$k],
				'rank'=>$cate_rank_arr[$k],
				'enable'=>array_value($cate_enable_arr, $k),
			);
			if(isset($tagcatelist[$k])) {
				tag_cate_update($k, $arr);
			} else {
				tag_cate_create($arr);
			}
		}
		// 删除 / delete
		$cate_id_delete = array_diff($cate_id_arr_old, $cate_id_arr);
		foreach($cate_id_delete as $k) {
			tag_cate_delete($k);
		}
		
		// tag
		$taglist = tag_fetch_from_catelist($tagcatelist);
		$taglist = arrlist_change_key($taglist, 'tagid');
		$tag_name_arr = param('tag_name', array(''));
		$tag_rank_arr = param('tag_rank', array(0));
		$tag_enable_arr = param('tag_enable', array(0));
		$tag_cate_id_arr = param('tag_cate_id', array(0));
		$tag_id_arr = array_keys($tag_name_arr);
		$tag_id_arr_old = arrlist_values($taglist, 'tagid');
		foreach($tag_id_arr as $k) {
			$arr = array(
				'tagid'=>$k,
				'cateid'=>array_value($tag_cate_id_arr, $k),
				'name'=>$tag_name_arr[$k],
				'rank'=>$tag_rank_arr[$k],
				'enable'=>array_value($tag_enable_arr, $k),
			);
			if(isset($taglist[$k])) {
				tag_update($k, $arr);
			} else {
				tag_create($arr);
			}
		}
		$tag_id_delete = array_diff($tag_id_arr_old, $tag_id_arr);
		foreach($tag_id_delete as $k) {
			tag_delete($k);
		}
		
?>