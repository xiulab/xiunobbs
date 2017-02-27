<?php

function pm_create($arr) {
        $pmid = db_create('pm', $arr);
        return $pmid;
}

function pm_update($pmid, $arr) {
        $r = db_update('pm', array('pmid'=>$pmid), $arr);
        return $r;
}

function pm_read($pmid) {
        $arr = db_find_one('pm', array('pmid'=>$pmid));
        return $arr;
}

function pm_delete($pmid) {
        $r = db_delete('pm', array('pmid'=>$pmid));
        return $r;
}

function pm_find($cond, $order = array(), $page = 1, $pagesize = 10) {
        $pmlist = db_find('pm', $cond, $order, $page, $pagesize);
        return $pmlist;
}

function pm_swap(&$v1, &$v2) {
        $t = $v1;
        $v1 = $v2;
        $v2 = $t;
}

// uid1 为 senduid
function pm_send($recvuid, $senduid, $message) {
        global $time;

        if(empty($recvuid) || empty($senduid)) {
                return FALSE;
        }

        $recvuser = user_read($recvuid);
        if(empty($recvuser)) {
                return FALSE;
        }
        
        $recent = pm_recent_read($recvuid, $senduid);
        if(empty($recent)) {
                pm_recent_create($recvuid, $senduid, 1);
        } else {
                pm_recent_update($recvuid, $senduid, array('count+'=>1, 'last_date'=>$time));
        }
        
        $pm = array(
                'uid1'=>min($senduid, $recvuid),
                'uid2'=>max($recvuid, $senduid),
                'senduid'=>$senduid,
                'message'=>$message,
                'create_date'=>$time
        );
        $pmid = pm_create($pm);

        user_update($recvuid, array('newpms'=>1));
        
        return $pmid;
}

// 获取两个人的聊天记录， $op = > | <
function pm_list($uid1, $uid2,  $op = '', $pmid = 0, $pagesize = 10) {
        if($uid1 > $uid2) {
                pm_swap($uid1, $uid2);
        }
        $cond = array('uid1'=>$uid1, 'uid2'=>$uid2);
        $op AND $cond['pmid'] = array($op=>$pmid);
        $pmlist = db_find('pm', $cond, array('pmid'=>-1), 1, $pagesize + 1);
        foreach($pmlist as &$pm) {
                pm_format($pm);
        }
        $pmlist = arrlist_multisort($pmlist, 'pmid', $asc = TRUE);
        return $pmlist;
}

function pm_format(&$pm) {
       if($pm['senduid'] == $pm['uid1']) {
               $pm['uid'] = $pm['uid1'];
       } else {
               $pm['uid'] = $pm['uid2'];
       }
       $pm['create_date_fmt'] = humandate($pm['create_date']);
}


// 添加一个最近联系人
function pm_recent_create($recvuid, $senduid, $count = 0) {
        global $time;
        $arr = array('recvuid'=>$recvuid, 'senduid'=>$senduid, 'last_date'=>$time, 'count'=>$count);
        $r = db_create('pm_recent', $arr);
        return $r;
}

function pm_recent_update($recvuid, $senduid, $update) {
        global $time;
        $r = db_update('pm_recent', array('recvuid'=>$recvuid, 'senduid'=>$senduid), $update);
        return $r;
}

function pm_recent_read($recvuid, $senduid) {
        $recent = db_find_one('pm_recent', array('recvuid'=>$recvuid, 'senduid'=>$senduid));
        return $recent;
}


// 删除一个最近联系人
function pm_recent_delete($recvuid, $senduid) {
        $r = db_delete('pm_recent', array('recvuid'=>$recvuid, 'senduid'=>$senduid));
        return $r;
}

// 替换的方式插入一个联系人
function pm_recent_replace($recvuid, $senduid) {
        global $time;
        $recent = pm_recent_read($recvuid, $senduid);
        if(empty($recent)) {
                pm_recent_create($recvuid, $senduid, 0);
        } else {
                pm_recent_update($recvuid, $senduid, array('count+'=>1));
        }
        return $r;
}

// 获取最近联系人列表，最多只显示50-100条，更多删除?
function pm_recent_list($uid) {
        $recentlist1 = db_find('pm_recent', array('recvuid'=>$uid), array('last_date'=>-1), 1, 50);
        $recentlist2 = db_find('pm_recent', array('senduid'=>$uid), array('last_date'=>-1), 1, 50);
        $recentlist = array_merge($recentlist1, $recentlist2);
        // 过滤相同的 uid
        $uidkeys = array();
        foreach($recentlist as $k=>&$recent) {
                $recvuid = $recent['recvuid'];
                $senduid = $recent['senduid'];
                if(isset($uidkeys[$recvuid])) {
                        unset($recentlist[$k]);
                        continue;
                }
                if(isset($uidkeys[$senduid])) {
                        unset($recentlist[$k]);
                        continue;
                }
                $uidkeys[$recvuid] = $recvuid;
                $uidkeys[$senduid] = $senduid;
                pm_recent_format($recent);
        }
        return $recentlist;
}

function pm_recent_format(&$recent) {
        $user = user_read($recent['senduid']);
        user_format($user);
        $recent = array(
                'uid'=>$user['uid'],
                'username'=>$user['username'],
                'avatar_url'=>$user['avatar_url'],
                'count'=>$recent['count'],
        );
}

// Chinese character unicode
function pm_cn_encode($s) {
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