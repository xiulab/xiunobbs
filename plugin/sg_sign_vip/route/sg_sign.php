<?php
/*
	Xiuno BBS 4.0 每日签到
	插件由查鸽信息网制作网址：http://cha.sgahz.net/
*/
!defined('DEBUG') AND exit('Access Denied.');
include _include(APP_PATH.'plugin/sg_sign_vip/model/sg_sign.func.php');
$active = param(1);
$active == 'list' ? $active = 'list' : $active = 'default';
$kv = kv_get('sg_sign');
switch ($kv['sign9']) {
case 'credits': 	$Unit = lang('sg_sign1'); 	break;
case 'golds': 	$Unit = lang('sg_sign2'); 	break;
case 'rmbs': 	$Unit = lang('sg_sign3'); 	break;
}
$sg_sign_set = sg_sign_read('sg_sign_set', 'id', 1);
$userinfo = sg_sign_read('sg_sign', 'uid', $uid);
$today = strtotime('today');
$yesterday = strtotime('yesterday');
	if($method == 'GET') {
		if($active == 'list') {
		$kv['sign11'] != 1 AND message(0, lang('sg_sign14').lang('close'));
		$pagesize = 10;
		$page = param(2, 1);
		$pagination = pagination(url("sg_sign-list-{page}"), $sg_sign_set['sg_signnum'], $page, $pagesize);
		$sg_signlist = sg_sign_find('sg_sign', 'uid', array(), array('stime'=>-1), $page, $pagesize);
		}else{
		$uidarr = explode(',',$sg_sign_set['sg_sign_top']);
		$sg_signlist = sg_sign_find('sg_sign', 'uid', array('uid'=>$uidarr), array('id'=>1), 1, 10);
		}
		include _include(APP_PATH.'plugin/sg_sign_vip/htm/sg_sign.htm');
	} elseif($method == 'POST') {
		empty($uid) AND message(0, lang('sg_sign4'));
		$kv = kv_get('sg_sign');
		$Credit = $kv['sign1'];
		$number = $sg_sign_set['sg_sign'];
		$number += 1;
		$username = $sg_sign_set['sg_sign_one'];
		$sg_sign_top = $sg_sign_set['sg_sign_top'];
		$sg_signnum =$sg_sign_set['sg_signnum'];
		if($number == 1){
			$Credit += $kv['sign5'];
		}else if($number >= 2 && $number <= 5) {
			$Credit += $kv['sign6'];
		}
		if($userinfo){
			if($userinfo['stime'] > $today){
				message(-1, lang('sg_sign5'));
			}else{
				if($userinfo['stime'] > $yesterday && $userinfo['stime'] < $today){
					if($userinfo['keepdays'] == 2){
						$Credit += $kv['sign2'];
						$message = lang('sg_sign6').$kv['sign2'].$Unit.'，';
					}else if($userinfo['keepdays'] == 6){
						$Credit += $kv['sign3'];
						$message = lang('sg_sign7').$kv['sign3'].$Unit.'，';
					}else if($userinfo['keepdays'] == 14) {
						$Credit += $kv['sign4'];
						$message = lang('sg_sign8').$kv['sign4'].$Unit.'，';
					}else if($userinfo['keepdays'] >= 14){
						$Credit += rand($kv['sign7'], $kv['sign8']);
						$message = lang('sg_sign9').rand($kv['sign7'], $kv['sign8']).$Unit.'，';
					}else{
						$message = '';
					}
					$keepdays = $userinfo['keepdays']+1;
				}else{
					$message = '';
					$keepdays = 1;
				}
				sg_sign_update('sg_sign', 'uid', $uid, array('id'=>$number,'stime'=>time(),'counts+'=>1,'credits+'=>$Credit,'todaycredits'=>$Credit,'keepdays'=>$keepdays ));
				$message = lang('sg_sign10', array('number'=>$number, 'message'=>$message, 'Credit'=>$Credit.$Unit));
			}
		}else{
			sg_sign_create('sg_sign', array('id'=>$number, 'uid'=>$uid, 'stime'=>time(), 'counts'=>1, 'credits'=>$Credit, 'todaycredits'=>$Credit, 'keepdays'=>1, 'user'=>$user['username'] ));
			$sg_signnum =$sg_sign_set['sg_signnum']+1;
			$message = lang('sg_sign10', array('number'=>$number, 'message'=>'', 'Credit'=>$Credit.$Unit));
		}
		if($number == 1){
			$username = $user['username'];
			$sg_sign_top = $uid;
		}else if($number >= 2 && $number <= 10){
			$sg_sign_top = $sg_sign_set['sg_sign_top'].','.$uid;
		}
		$setarr = array('sg_sign'=>$sg_sign_set['sg_sign']+1,'sg_signnum'=>$sg_signnum,'sg_sign_one'=>$username,'sg_sign_top'=>$sg_sign_top,'time'=>time());
		sg_sign_update('sg_sign_set', 'id', 1, $setarr);
		sg_sign_update('user', 'uid', $uid, array($kv['sign9'].'+'=>$Credit ));
		message(0, $message);
	}
?>