<?php

function search_type() {
	return kv_get('xn_search_type');
}

function search_message_format($s) {
	$s = xn_substr(str_replace('&amp;nbsp;', ' ', htmlspecialchars(strip_tags($s))), 0, 200);
	return $s;
}

function search_keyword_highlight($s, $keyword_arr) {
	foreach($keyword_arr as $keyword) {
		$s = str_ireplace($keyword, '<span class="text-danger">'.$keyword.'</span>', $s);
	}
	return $s;
}

function search_keyword_safe($s) {
	$s = str_replace(array('\'', '\\', '"', '%', '<', '>', '`', '*', '&', '#'), '', $s);
	$s = preg_replace('#\s+#', ' ', $s);
	$s = trim($s);
	//$s = preg_replace('#[^\w\-\x4e00-\x9fa5]+#i', '', $s);
	return $s;
}

// Chinese character unicode
function search_cn_encode($s) {
	// 对 UTF-8 字符的汉字进行编码，转化为 mysql 可以索引的 word
        $r = '';
        $len = strlen($s);
        for($i = 0; $i < $len; $i++) {
                $o = ord($s[$i]);
                if($o < 0x80) {
                        if(($o >= 48 && $o <= 57) || ($o >= 97 && $o <= 122) || $o == 0x20) {
				$r .= $s[$i]; // 0-9 a-z
			} elseif($o >= 65 && $o <= 90) {
                                $r .= strtolower($s[$i]); // A-Z
                        } else {
                                $r .= ' ';
                        }
                } else {
                        $z = $s[$i].$s[$i+1].$s[$i+2];
                        $i += 2;
                        $t = iconv('UTF-8', 'UCS-2', $z);
                        //echo bin2hex($t);exit;
                        $r .= '  u'.bin2hex($t).' '; // uF1F2
                }
        }
        $r = preg_replace('#\s\w{1}\s#', ' ', $r);
	$r = trim(preg_replace('#\s+#', ' ', $r));
        return $r;
}


?>