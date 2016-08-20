<?php

function array_value($arr, $key, $default = '') {
	return isset($arr[$key]) ? $arr[$key] : $default;
}

function array_filter_empty($arr) {
	foreach($arr as $k=>$v) {
		if(empty($v)) unset($arr[$k]);
	}
	return $arr;
}

/*
function array_isset_push(&$arr, $key, $value) {
	!isset($arr[$key]) AND $arr[$key] = array();
	$arr[$key][] = $value;
}
*/


function array_addslashes(&$var) {
	if(is_array($var)) {
		foreach($var as $k=>&$v) {
			array_addslashes($v);
		}
	} else {
		$var = addslashes($var);
	}
	return $var;
}

function array_stripslashes(&$var) {
	if(is_array($var)) {
		foreach($var as $k=>&$v) {
			array_stripslashes($v);
		}
	} else {
		$var = stripslashes($var);
	}
	return $var;
}

function array_htmlspecialchars(&$var) {
	if(is_array($var)) {
		foreach($var as $k=>&$v) {
			array_htmlspecialchars($v);
		}
	} else {
		$var = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $var);
	}
	return $var;
}

function array_trim(&$var) {
	if(is_array($var)) {
		foreach($var as $k=>&$v) {
			array_trim($v);
		}
	} else {
		$var = trim($var);
	}
	return $var;
}

// 比较数组的值，如果不相同则保留，以第一个数组为准
function array_diff_value($arr1, $arr2) {
	foreach ($arr1 as $k=>$v) {
		if(isset($arr2[$k]) && $arr2[$k] == $v ) unset($arr1[$k]);
	}
	return $arr1;
}

/*
	$data = array();
	$data[] = array('volume' => 67, 'edition' => 2);
	$data[] = array('volume' => 86, 'edition' => 1);
	$data[] = array('volume' => 85, 'edition' => 6);
	$data[] = array('volume' => 98, 'edition' => 2);
	$data[] = array('volume' => 86, 'edition' => 6);
	$data[] = array('volume' => 67, 'edition' => 7);
	arrlist_multisort($data, 'edition', TRUE);
*/
// 对多维数组排序
function arrlist_multisort($arrlist, $col, $asc = TRUE) {
	$colarr = array();
	foreach($arrlist as $k=>$arr) {
		$colarr[$k] = $arr[$col];
	}
	$asc = $asc ? SORT_ASC : SORT_DESC;
	array_multisort($colarr, $asc, $arrlist);
	return $arrlist;
}

// 对数组进行查找，排序，筛选，只支持一种条件排序
function arrlist_cond_orderby($arrlist, $cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	$resultarr = array();
	if(empty($arrlist)) return $arrlist;
	
	// 根据条件，筛选结果
	if($cond) {
		foreach($arrlist as $key=>$val) {
			$ok = TRUE;
			foreach($cond as $k=>$v) {
				if(!isset($val[$k]) || $val[$k] != $v) {
					$ok = FALSE;
					break;
				}
			}
			if($ok) $resultarr[$key] = $val;
		}
	} else {
		$resultarr = $arrlist;
	}

	if($orderby) {
		list($k, $v) = each($orderby);
		$resultarr = arrlist_multisort($resultarr, $k, $v == 1);
	}

	$start = ($page - 1) * $pagesize;

	$resultarr = array_assoc_slice($resultarr, $start, $pagesize);
	return $resultarr;
}

function array_assoc_slice($arrlist, $start, $length = 0) {
	if(isset($arrlist[0])) return array_slice($arrlist, $start, $length);
	$keys = array_keys($arrlist);
	$keys2 = array_slice($keys, $start, $length);
	$retlist = array();
	foreach($keys2 as $key) {
		$retlist[$key] = $arrlist[$key];
	}

	return $retlist;
}


// 从一个二维数组中取出一个 key=>value 格式的一维数组
function arrlist_key_values($arrlist, $key, $value = NULL) {
	$return = array();
	if($key) {
		foreach((array)$arrlist as $k=>$arr) {
			$return[$arr[$key]] = $value ? $arr[$value] : $k;
		}
	} else {
		foreach((array)$arrlist as $arr) {
			$return[] = $arr[$value];
		}
	}
	return $return;
}

/* php 5.5:
function array_column($arrlist, $key) {
	return arrlist_values($arrlist, $key);
}
*/

// 从一个二维数组中取出一个 values() 格式的一维数组，某一列key
function arrlist_values($arrlist, $key) {
	if(!$arrlist) return array();
	$return = array();
	foreach($arrlist as &$arr) {
		$return[] = $arr[$key];
	}
	return $return;
}

// 将 key 更换为某一列的值，在对多维数组排序后，数字key会丢失，需要此函数
function arrlist_change_key($arrlist, $key, $pre = '') {
	$return = array();
	if(empty($arrlist)) return $return;
	foreach($arrlist as &$arr) {
		$return[$pre.''.$arr[$key]] = $arr;
	}
	//$arrlist = $return;
	return $return;
}

// 根据某一列的值进行 chunk
function arrlist_chunk($arrlist, $key) {
	$r = array();
	if(empty($arrlist)) return $r;
	foreach($arrlist as &$arr) {
		!isset($r[$arr[$key]]) AND $r[$arr[$key]] = array();
		$r[$arr[$key]][] = $arr;
	}
	return $r;
}

?>