<?php

function qq_login_link($return_url) {
	$qqlogin = kv_get('qq_login');
	$appid = $qqlogin['appid'];
	$appkey = $qqlogin['appkey'];
	$return_url = urlencode($return_url);
	
	$scope = "get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_weibo";
	$state = md5(uniqid(rand(), TRUE)); //CSRF protection
	$login_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=$appid&redirect_uri=$return_url&state=$state&scope=$scope";
	return $login_url;
}

/*
Array
(
    [access_token] => F6890DF038193C8CEB040F2344592714
    [expires_in] => 7776000
)
*/
function qq_login_get_token($appid, $appkey, $code, $return_url) {
	$return_url = urlencode($return_url);
	$get_token_url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=$appid&redirect_uri=$return_url&client_secret=$appkey&code=$code";
	$s = https_get($get_token_url);
	if(strpos($s, "callback") !== false) {
		$lpos = strpos($s, "(");
		$rpos = strrpos($s, ")");
		$s  = substr($s, $lpos + 1, $rpos - $lpos -1);
		$arr = xn_json_decode($s);
		if(isset($arr['error'])) {
			$error = $arr['error'].'<br />'.$arr['error_description'];
			return xn_error(-1, $error);
		}
	}
	
	$params = array();
	parse_str($s, $params);
	
	if(empty($params["access_token"])) return xn_error(-1, 'access_token 解码出错。'.$s);

	// token 有效期三个月，这里不缓存，每次都去取
	$token = $params["access_token"];
	
	return $token;
}

function qq_login_get_openid_by_token($token) {
	$get_openid_url = "https://graph.qq.com/oauth2.0/me?access_token=$token";
	$s  = https_get($get_openid_url);
	if(strpos($s, "callback") !== false) {
		$lpos = strpos($s, "(");
		$rpos = strrpos($s, ")");
		$s  = substr($s, $lpos + 1, $rpos - $lpos -1);
	}
	
	$arr = xn_json_decode($s);
	if (isset($arr['error'])) {
		$error = $arr['error'].'<br />'.$arr['error_description'];
		return xn_error(-1, $error);
	}
	
	return $arr['openid'];
}

/*

Array
(
    [ret] => 0
    [msg] => 
    [nickname] => 黄
    [gender] => 男
    [figureurl] => http://qzapp.qlogo.cn/qzapp/100287386/6AD06D578F81042387C7F7BFD6D99E38/30
    [figureurl_1] => http://qzapp.qlogo.cn/qzapp/100287386/6AD06D578F81042387C7F7BFD6D99E38/50
    [figureurl_2] => http://qzapp.qlogo.cn/qzapp/100287386/6AD06D578F81042387C7F7BFD6D99E38/100
    [figureurl_qq_1] => http://q.qlogo.cn/qqapp/100287386/6AD06D578F81042387C7F7BFD6D99E38/40
    [figureurl_qq_2] => http://q.qlogo.cn/qqapp/100287386/6AD06D578F81042387C7F7BFD6D99E38/100
    [is_yellow_vip] => 0
    [vip] => 0
    [yellow_vip_level] => 0
    [level] => 0
    [is_yellow_year_vip] => 0
)
*/
function qq_login_get_user_by_openid($openid, $token, $appid) {
	$get_user_info_url = "https://graph.qq.com/user/get_user_info?access_token=$token&oauth_consumer_key=$appid&openid=$openid&format=json";
	$s = https_get($get_user_info_url);
	$arr = json_decode($s, true);
	return $arr;
}

// 从本地数据读取
function qq_login_read_user_by_openid($openid) {
	$arr = db_find_one("SELECT * FROM bbs_user_open_plat WHERE openid='$openid'");
	if($arr) {
		$arr2 = user_read($arr['uid']);
		if($arr2) {
			$arr = array_merge($arr, $arr2);
		} else {
			db_exec("DELETE FROM bbs_user_open_plat WHERE openid='$openid'");
			return FALSE;
		}
	}
	return $arr;
}

function qq_login_create_user($username, $avatar_url_2, $openid) {
	global $conf, $time, $longip;
	
	$arr = qq_login_read_user_by_openid($openid);
	if($arr) return xn_error(-2, '已经注册');
	
	// 自动产生一个用户名
	$r = user_read_by_username($username);
	if($r) {
		$username = $username.'_'.$time;
		$r = user_read_by_username($username);
		if($r) return xn_error(-1, '用户名被占用。');
	}
	// 自动产生一个 Email
	$email = "qq_$time@qq.com";
	$r = user_read_by_email($email);
	if($r) return xn_error(-1, 'Email 被占用');
	// 随机密码
	$password = md5(rand(1000000000, 9999999999).$time);
	$user = array(
		'username'=>$username,
		'email'=>$email,
		'password'=>$password,
		'gid'=>101,
		'salt'=>rand(100000, 999999),
		'create_date'=>$time,
		'create_ip'=>$longip,
		'avatar'=>0,
		'logins' => 1,
		'login_date' => $time,
		'login_ip' => $longip,
	);
	$uid = user_create($user);
	if(empty($uid)) return xn_error(-1, '注册失败');
	
	$user = user_read($uid);

	$r = db_exec("INSERT INTO bbs_user_open_plat SET uid='$uid', platid='1', openid='$openid'");
	if(empty($uid)) return xn_error(-1, '注册失败');
	
	runtime_set('users+', '1');
	runtime_set('todayusers+', '1');
	
	// 头像不重要，忽略错误。
	if($avatar_url_2) {
		$filename = "$uid.png";
		$dir = substr(sprintf("%09d", $uid), 0, 3).'/';
		$path = $conf['upload_path'].'avatar/'.$dir;
		!is_dir($path) AND mkdir($path, 0777, TRUE);
		
		$data = file_get_contents($avatar_url_2);
		file_put_contents($path.$filename, $data);
		
		user_update($uid, array('avatar'=>$time));
	}
	return $user;
	
}