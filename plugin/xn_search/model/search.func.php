<?php


/*
	$text = '这是一段需要切词的句子';
	$text = array(
			12=>'这是一段需要切词的句子',
			34=>'第二段需要切词的句子',
		);
	return:
		array(
			'word'=>'这是 一段 切词 句子'
		);
		array(
			12=>array(
				'word'=>'这是 一段 切词 句子'
			),
			34=>array(
				'word'=>'这是 一段 切词 句子'
			),
		);
*/
function search_cutword($text) {
	
	$search_conf = kv_get('search_conf');
	$cutword_url = $search_conf['cutword_url'];
	if(empty($cutword_url)) {
		message(-1, '请指定切词服务 URL');
	}
	$postdata = array('text'=>$text);
	$r = http_post($cutword_url, $postdata);
	$arrlist = xn_json_decode($r);
	if(empty($arrlist)) {
		xn_log('切词服务器返回出错：'.$r);
		return array();
	}
	$return = array();
	if(is_array($text)) {
		foreach ($text as $id=>$arr) {
			$return[$id] = implode(' ', arrlist_values($arr, 'word'));
		}
	} else {
		$return = implode(' ', arrlist_values($arrlist, 'word'));
	}
	return $return;
}

function search_type() {
	return kv_get('xn_search_type');
}

?>