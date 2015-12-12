<?php

include './plugin/xn_sphinx/sphinxapi.class.php';

function sphinx_search($keyword) {
		$fid = 0;
		$daterange = 0;
		$orderby = 'match';
		$page = 1;
		$pagesize = 60;
		
                global $conf, $time;
                $cl = new SphinxClient();
                $cl->SetServer($conf['sphinx_host'], $conf['sphinx_port']);
                $cl->SetConnectTimeout(3);
                $cl->SetArrayResult(TRUE);
                $cl->SetWeights(array(100, 1, 5));     	// 标题权重100，内容权重1，作者权重10
                $fid && $cl->SetFilter('fid', array($fid));
                $daterange && $cl->setFilterRange('dateline', $time - $daterange * 86400, $time);
                $cl->SetMatchMode(SPH_MATCH_ALL);
                if($orderby == 'match') {
                	  $cl->SetSortMode (SPH_SORT_RELEVANCE);	// 如果不设置，默认按照权重排序！但是TMD是正序！
                } elseif($orderby == 'timeasc') {
                	$cl->SetSortMode (SPH_SORT_ATTR_ASC, 'tid');
                } elseif($orderby == 'timedesc') {
                	$cl->SetSortMode (SPH_SORT_ATTR_DESC, 'tid');
                }
                
                //$cl->SetSortMode (SPH_SORT_ATTR_DESC, 'tid');	// 如果不设置，默认按照权重排序！但是TMD是正序！
                
		/*
		$cl->SetMatchMode ( SPH_MATCH_EXTENDED );	//设置模式
		$cl->SetRankingMode ( SPH_RANK_PROXIMITY );	//设置评分模式
		$cl->SetFieldWeights (array('subject'=>100,'message'=>10,'username'=>1));//设置字段的权重，如果area命中，那么权重算2
		$cl->SetSortMode ('SPH_SORT_EXPR','@weight');	//按照权重排序
		*/
		
		// --------------> 优先搜索增量索引
		$newlist = array();
		$forums = array();
		if($page == 1) {
			$cl->SetLimits(0, $pagesize, 1000);	// 最大结果集
	                $res = $cl->Query($keyword, $conf['sphinx_deltasrc']); // * 为所有的索引
	                if(!empty($cl->_error)) {
	                      return xn_error(-1, 'Sphinx 错误：'.$cl->_error);
	                }
	                if(!empty($res) && !empty($res['total'])) {
	                       $deltamatch = $res['matches'];
	                }
	                $res['matches'] && arrlist_change_key($res['matches'], 'id');
	                
	                $newlist = array();
	                $forums = array();
	                foreach((array)$res['matches'] as $v) {
	                        if(empty($v['attrs'])) continue;
	                        if(empty($v['attrs']['fid'])) continue;
	                        $fid = $v['attrs']['fid'];
	                        
	                        $thread = thread_read($v['attrs']['tid']);
	                        if(empty($thread)) continue;
	                        if(stripos($thread['subject'], $keyword) === FALSE) continue;
	                        $thread['subject'] = str_replace($keyword, '<span class="red">'.$keyword.'</span>', $thread['subject']);
	                        $newlist[] = $thread;
	                }
		}
		
		// --------------> 再搜索主索引
                $start = ($page - 1) * $pagesize;
                $cl->SetLimits($start, $pagesize, 1000);	// 最大结果集
                $res = $cl->Query($keyword, $conf['sphinx_datasrc']);
                if(!empty($cl->_error)) {
                       return xn_error(-1, 'Sphinx 错误：'.$cl->_error);
                }
                if(empty($res) || empty($res['total'])) {
                       $res['matches'] = $deltamatch;
                } else {
                	 arrlist_change_key($res['matches'], 'id');
                }

                $threadlist = array();
                foreach((array)$res['matches'] as $v) {
                        if(empty($v['attrs'])) continue;
                        if(empty($v['attrs']['fid'])) continue;
                        $fid = $v['attrs']['fid'];
                        
                        $thread = thread_read($v['attrs']['tid']);
                        if(empty($thread)) continue;
                        $thread['subject'] = str_replace($keyword, '<span class="red">'.$keyword.'</span>', $thread['subject']);
                        $threadlist[] = $thread;
                }
                $arrlist = $newlist + $threadlist;
                return $arrlist;
        }
        
?>