<?php

// hook model_tag_thread_start.php

// ------------> 最原生的 CURD，无关联其他数据。

function tag_thread_create($tagid, $tid) {
	// hook model_tag_thread_create_start.php
	$arr = array('tagid'=>$tagid, 'tid'=>$tid);
	$r = db_create('tag_thread', $arr);
	// hook model_tag_thread_create_end.php
	return $r;
}

function tag_thread_delete($tagid, $tid) {
	// hook model_tag_thread_delete_start.php
	$r = db_delete('tag_thread', array('tagid'=>$tagid, 'tid'=>$tid));
	// hook model_tag_thread_delete_end.php
	return $r;
}

function tag_thread_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	// hook model_tag_thread_find_start.php
	$tag_threadlist = db_find('tag_thread', $cond, $orderby, $page, $pagesize);
	// hook model_tag_thread_find_end.php
	return $tag_threadlist;
}

function tag_thread_delete_by_tagid($tagid) {
	$r = db_delete('tag_thread', array('tagid'=>$tagid));
	return $r;
}

/*

Array
(
    [0] => Array
        (
            [tagid] => 5
            [tid] => 50
        )

    [1] => Array
        (
            [tagid] => 3
            [tid] => 50
        )

)
Array
(
    [0] => Array
        (
            [cateid] => 2
            [fid] => 1
            [name] => 类型
            [rank] => 2
            [enable] => 1
            [defaulttagid] => 0
            [isforce] => 0
            [taglist] => Array
                (
                    [0] => Array
                        (
                            [tagid] => 6
                            [cateid] => 2
                            [name] => 恐怖
                            [rank] => 3
                            [enable] => 1
                            [style] => warning
                        )

                    [1] => Array
                        (
                            [tagid] => 5
                            [cateid] => 2
                            [name] => 战争
                            [rank] => 2
                            [enable] => 1
                            [style] => primary
                        )

                    [2] => Array
                        (
                            [tagid] => 4
                            [cateid] => 2
                            [name] => 娱乐
                            [rank] => 1
                            [enable] => 1
                            [style] => danger
                        )

                )

            [tagmap] => Array
                (
                    [6] => Array
                        (
                            [tagid] => 6
                            [cateid] => 2
                            [name] => 恐怖
                            [rank] => 3
                            [enable] => 1
                            [style] => warning
                        )

                    [5] => Array
                        (
                            [tagid] => 5
                            [cateid] => 2
                            [name] => 战争
                            [rank] => 2
                            [enable] => 1
                            [style] => primary
                        )

                    [4] => Array
                        (
                            [tagid] => 4
                            [cateid] => 2
                            [name] => 娱乐
                            [rank] => 1
                            [enable] => 1
                            [style] => danger
                        )

                )

        )

    [1] => Array
        (
            [cateid] => 1
            [fid] => 1
            [name] => 地区
            [rank] => 1
            [enable] => 1
            [defaulttagid] => 0
            [isforce] => 0
            [taglist] => Array
                (
                    [0] => Array
                        (
                            [tagid] => 1
                            [cateid] => 1
                            [name] => 大陆
                            [rank] => 3
                            [enable] => 1
                            [style] => secondary
                        )

                    [1] => Array
                        (
                            [tagid] => 2
                            [cateid] => 1
                            [name] => 香港
                            [rank] => 2
                            [enable] => 1
                            [style] => primary
                        )

                    [2] => Array
                        (
                            [tagid] => 3
                            [cateid] => 1
                            [name] => 欧美
                            [rank] => 1
                            [enable] => 1
                            [style] => dark
                        )

                )

            [tagmap] => Array
                (
                    [1] => Array
                        (
                            [tagid] => 1
                            [cateid] => 1
                            [name] => 大陆
                            [rank] => 3
                            [enable] => 1
                            [style] => secondary
                        )

                    [2] => Array
                        (
                            [tagid] => 2
                            [cateid] => 1
                            [name] => 香港
                            [rank] => 2
                            [enable] => 1
                            [style] => primary
                        )

                    [3] => Array
                        (
                            [tagid] => 3
                            [cateid] => 1
                            [name] => 欧美
                            [rank] => 1
                            [enable] => 1
                            [style] => dark
                        )

                )

        )

)

*/
// 根据 tagcatelist 排序，输出 tagids
function tag_thread_find_tagid_by_tid($tid, $tagcatelist) {
	$tagids = array();
	
	// 排序的问题
	$tagthreadlist = tag_thread_find(array('tid'=>$tid), array(), 1, 1000);
	
	//print_r($tagthreadlist);
	//print_r($tagcatelist);
	
	// 这是无序的
	$tagids = arrlist_values($tagthreadlist, 'tagid');
	
	// 按照排序
	$tagidarr = array();
	foreach ($tagcatelist as $v) {
		foreach ($v['taglist'] as $v2) {
			if(in_array($v2['tagid'], $tagids)) {
				$tagidarr[] = $v2['tagid'];
			}
		}
	}
	
	return $tagidarr;
}

// hook model_tag_thread_end.php

?>