<?php

!defined('DEBUG') AND exit('Forbidden');

$keyword = param(1);
$range = param(2, 1);

$keyword_decode = search_keyword_safe(xn_urldecode($keyword));
$threadlist = array();
$pagination = '';
$active = '';

$search_conf = kv_get('search_conf');
$search_type = $search_conf['type'];
$search_range = $search_conf['range'];

if($keyword) {
	// 搜索结果
	if($search_type == 'fulltext') {
		
		if($range == 1) {
			$arrlist = db_sql_find("SELECT * FROM bbs_thread_search WHERE MATCH(message) AGAINST ('$keyword_decode') LIMIT 50;");
			$tids = arrlist_values($arrlist, 'tid');
			$threadlist = thread_find_by_tids($tids);
			arrlist_multisort($threadlist, 'tid', FALSE);
			foreach($threadlist as &$thread) {
				$thread['subject'] = search_keyword_highlight($thread['subject'], $keyword_decode);
			}
		} else {
			$posts = 0;
			$arrlist = db_sql_find("SELECT * FROM bbs_post_search WHERE MATCH(message) AGAINST ('$keyword_decode') LIMIT 50;");
			
			$pids = arrlist_values($arrlist, 'pid');
			$postlist = post_find_by_pids($pids);
			arrlist_multisort($postlist, 'pid', FALSE);
			foreach($postlist as &$post) {
				$post['message_fmt'] = search_message_format($post['message_fmt']);
				$post['message_fmt'] = search_keyword_highlight($post['message_fmt'], $keyword_decode);
				$post['filelist'] = array();
				$post['floor'] = 0;
				$thread = thread_read_cache($post['tid']);
				$post['subject'] = search_keyword_highlight($thread['subject'], $keyword_decode);
			}
			
		}
		
	} elseif($search_type == 'like') {
		
		if($range == 1) {
			$threadlist = db_sql_find("SELECT * FROM bbs_thread WHERE subject LIKE '%$keyword_decode%' LIMIT 50;");
			arrlist_multisort($threadlist, 'tid', FALSE);
			foreach($threadlist as &$thread) {
				thread_format($thread);
				$thread['subject'] = search_keyword_highlight($thread['subject'], $keyword_decode);
			}
		} else {
			$posts = 0;
			$postlist = db_sql_find("SELECT * FROM bbs_post WHERE message LIKE '%$keyword_decode%' LIMIT 50;");
			arrlist_multisort($postlist, 'pid', FALSE);
			foreach($postlist as &$post) {
				post_format($post);
				$post['message_fmt'] = search_message_format($post['message_fmt']);
				$post['message_fmt'] = search_keyword_highlight($post['message_fmt'], $keyword_decode);
				$post['filelist'] = array();
				$post['floor'] = 0;
				$thread = thread_read_cache($post['tid']);
				$post['subject'] = search_keyword_highlight($thread['subject'], $keyword_decode);
			}
		}
		
	} elseif($search_type == 'sphinx') {
		
		$range = 1;
		
		$threadlist = search_by_sphinx($keyword);
		foreach($threadlist as &$thread) {
			$thread['subject'] = search_keyword_highlight($thread['subject'], $keyword_decode);
		}
		
	} elseif($search_type == 'site_url') {
		
		$range = 1;
		
		$url = str_replace('{keyword}', $keyword, $search_conf['site_url']);
		http_location($url);
		
	}
}

include _include(APP_PATH.'plugin/xn_search/htm/search.htm');



function search_message_format($s) {
	$s = xn_substr(str_replace('&amp;nbsp;', ' ', htmlspecialchars(strip_tags($s))), 0, 200);
	return $s;
}

function search_keyword_highlight($s, $keyword) {
	$s = str_ireplace($keyword, '<span class="text-danger">'.$keyword.'</span>', $s);
	return $s;
}

function search_keyword_safe($s) {
	$s = str_replace(array('\'', '\\', '"', '%', '<', '>', '`', '*', '&', '#'), '', $s);
	//$s = preg_replace('#[^\w\-\x4e00-\x9fa5]+#i', '', $s);
	return $s;
}

function search_by_sphinx($keyword, $orderby = 'match', $page = 1, $pagesize = 50) {
	global $search_conf;
        include APP_PATH.'plugin/xn_search/lib/sphinxapi.class.php';
        $cl = new SphinxClient();
        $cl->SetServer($search_conf['sphinx_host'], $search_conf['sphinx_port']);
        $cl->SetConnectTimeout(3);
        $cl->SetArrayResult(TRUE);
        $cl->SetWeights(array(100, 1, 5));     	// 标题权重100，内容权重1，作者权重10
        $cl->SetMatchMode(SPH_MATCH_ALL);
        if($orderby == 'match') {
        	  $cl->SetSortMode (SPH_SORT_RELEVANCE);	// 如果不设置，默认按照权重排序！但是TMD是正序！
        } elseif($orderby == 'timeasc') {
        	$cl->SetSortMode (SPH_SORT_ATTR_ASC, 'tid');
        } elseif($orderby == 'timedesc') {
        	$cl->SetSortMode (SPH_SORT_ATTR_DESC, 'tid');
        }

	// --------------> 优先搜索增量索引
	$newlist = array();
	$forums = array();
	if($page == 1) {
		$cl->SetLimits(0, $pagesize, 1000);	// 最大结果集
                $res = $cl->Query($keyword, $search_conf['sphinx_index']); // * 为所有的索引
                if(!empty($cl->_error)) {
                     message(-1, 'Sphinx 错误：'.$cl->_error);
                }
                if(!empty($res) && !empty($res['total'])) {
                       $deltamatch = $res['matches'];
                }
                $res['matches'] && arrlist_change_key($res['matches'], 'id');
                
                $newlist = array();
                $forums = array();
                foreach((array)$res['matches'] as $v) {
                        if(empty($v['attrs'])) continue;
                        $thread = thread_read($v['attrs']['tid']);
                        if(empty($thread)) continue;
                        $newlist[] = $thread;
                }
	}
	
	// --------------> 再搜索主索引
        //$pagesize = 30;
        $start = ($page - 1) * $pagesize;
        $cl->SetLimits($start, $pagesize, 1000);	// 最大结果集
        $res = $cl->Query($keyword, $search_conf['sphinx_delta_index']);
        if(!empty($cl->_error)) {
              message(-1, 'Sphinx 错误：'.$cl->_error);
        }
        if(empty($res) || empty($res['total'])) {
               $res['matches'] = $deltamatch;
        } else {
        	arrlist_change_key($res['matches'], 'id');
        }

        $threadlist = array();
        foreach((array)$res['matches'] as $v) {
                if(empty($v['attrs'])) continue;
                $thread = thread_read($v['attrs']['tid']);
                if(empty($thread)) continue;
                $threadlist[] = $thread;
        }
        $arrlist = $newlist + $threadlist;
        return $arrlist;
}

?>