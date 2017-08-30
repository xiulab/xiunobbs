<?php

function search_type() {
	static $search_conf = FALSE;
	if($search_conf === FALSE) $search_conf = kv_get('search_conf');;
	return $search_conf['type'];
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

// Chinese character unicode by axiuno@gmail.com
function search_cn_encode($s) {
	// 对 UTF-8 字符的汉字进行编码，转化为 mysql 可以索引的 word
        $r = '';
        
        // 替换特殊字符
        $special_arr = array(
        	'０' , '１' , '２' , '３' , '４' ,  
		'５' , '６' , '７' , '８' , '９' ,   
		'Ａ' , 'Ｂ' , 'Ｃ' , 'Ｄ' , 'Ｅ' ,  
		'Ｆ' , 'Ｇ' , 'Ｈ' , 'Ｉ' , 'Ｊ' ,   
		'Ｋ' , 'Ｌ' , 'Ｍ' , 'Ｎ' , 'Ｏ' ,  
		'Ｐ' , 'Ｑ' , 'Ｒ' , 'Ｓ' , 'Ｔ' ,   
		'Ｕ' , 'Ｖ' , 'Ｗ' , 'Ｘ' , 'Ｙ' ,  
		'Ｚ' , 'ａ' , 'ｂ' , 'ｃ' , 'ｄ' ,   
		'ｅ' , 'ｆ' , 'ｇ' , 'ｈ' , 'ｉ' ,  
		'ｊ' , 'ｋ' , 'ｌ' , 'ｍ' , 'ｎ' ,   
		'ｏ' , 'ｐ' , 'ｑ' , 'ｒ' , 'ｓ' ,  
		'ｔ' , 'ｕ' , 'ｖ' , 'ｗ' , 'ｘ' ,   
		'ｙ' , 'ｚ' , '－' , '　' , '：' ,  
		'．' , '，' , '／' , '％' , '＃' ,  
		'！' , '＠' , '＆' , '（' , '）' ,  
		'＜' , '＞' , '＂' , '＇' , '？' ,  
		'［' , '］' , '｛' , '｝' , '＼' ,  
		'｜' , '＋' , '＝' , '＿' , '＾' ,  
		'￥' , '￣' , '｀', '《', '》',
		'【', '】', '〖', '〗', '『', '』', 
		'我', '你', '不', '是', '的', '了',
		'nbsp', '　',
        );
        $s = str_replace($special_arr, '', $s);
        
        $len = strlen($s);
	
	$f1 = intval(base_convert('10000000', 2, 10)); 
	$f2 = intval(base_convert('11000000', 2, 10)); 
	$f3 = intval(base_convert('11100000', 2, 10)); 

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
			if($i + 5 >= $len) break;
			// 校验是否为正常的 UTF-8 字符，在 PHP7 某些版本下 iconv() 会导致 nginx 出现 502
			$b1 = ord($s[$i]);
			$b2 = ord($s[$i+1]);
			$b3 = ord($s[$i+2]);
			$b4 = ord($s[$i+3]);
			$b5 = ord($s[$i+4]);
			$b6 = ord($s[$i+5]);
			if(
				($b1 & $f3) == $f3 && 
				(($b2 & $f1) == $f1 || ($b2 & $f2) == $f2) && 
				($b3 & $f1) == $f1
				&&
				($b4 & $f3) == $f3 && 
				(($b5 & $f1) == $f1 || ($b5 & $f2) == $f2) && 
				($b6 & $f1) == $f1
				
			) {
				$z = $s[$i].$s[$i+1].$s[$i+2].$s[$i+3].$s[$i+4].$s[$i+5];
				$i += 2;
				//$t = iconv('UTF-8', 'UCS-2', $z);
				//$r .= '  u'.bin2hex($t).' '; // uF1F2
				$r .= '  '.$z.' '; // uF1F2
			} else {
				continue;
			}
                }
        }
        $r = preg_replace('#\s\w{1}\s#', ' ', $r);
	$r = trim(preg_replace('#\s+#', ' ', $r));
        return $r;
}


// Chinese character unicode by axiuno@gmail.com
function search_cn_encode_by_word($s) {
	// 对 UTF-8 字符的汉字进行编码，转化为 mysql 可以索引的 word
        $r = '';
        $len = strlen($s);
	
	$f1 = intval(base_convert('10000000', 2, 10)); 
	$f2 = intval(base_convert('11000000', 2, 10)); 
	$f3 = intval(base_convert('11100000', 2, 10)); 

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
			if($i + 2 >= $len) break;
			// 校验是否为正常的 UTF-8 字符，在 PHP7 某些版本下 iconv() 会导致 nginx 出现 502
			$b1 = ord($s[$i]);
			$b2 = ord($s[$i+1]);
			$b3 = ord($s[$i+2]);
			if(
				($b1 & $f3) == $f3 && 
				(($b2 & $f1) == $f1 || ($b2 & $f2) == $f2) && 
				($b3 & $f1) == $f1
			) {
				$z = $s[$i].$s[$i+1].$s[$i+2];
				$i += 2;
				$t = iconv('UTF-8', 'UCS-2', $z);
				$r .= '  u'.bin2hex($t).' '; // uF1F2
			} else {
				continue;
			}
                }
        }
        $r = preg_replace('#\s\w{1}\s#', ' ', $r);
	$r = trim(preg_replace('#\s+#', ' ', $r));
        return $r;
}


?>