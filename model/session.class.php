<?php 

class Session {
	public $session = array();
	public $invalid = FALSE;
	public function open($save_path, $session_name) { 
		//echo "sess_open($save_path,$session_name) \r\n";
		return true;
	}
	
	public function close() {
		global $sid, $uid, $fid, $time;
		//echo "sess_close() \r\n";
		
		if($this->invalid) return TRUE;
		
		if(!empty($_SERVER['APP_PATH'])) chdir($_SERVER['APP_PATH']);
		
		$update = array(
			'uid'=>$uid,
			'fid'=>$fid,
			'url'=>$_SERVER['REQUEST_URI_NO_PATH'],
			'last_date'=>$time,
		);
		$update = array_diff_value($update, $this->session);
		
		db_update('session', array('sid'=>$sid), $update);
		
		return true;
	}
	
	// 如果 cookie 中没有 bbs_sid, php 会自动生成 sid，作为参数
	public function read($sid) { 
		global $longip, $time;
		//echo "sess_read() sid: $sid <br>\r\n";
		if(empty($sid)) {
			// 查找刚才是不是已经插入一条了？  如果相隔时间特别短，并且 data 为空，则删除。
			// 测试是否支持 cookie，如果不支持 cookie，则不生成 sid
			$sid = session_id();
			$this->sess_new($sid);
			return '';
		}
		$arr = db_find_one('session', array('sid'=>$sid));
		if(empty($arr)) {
			$this->sess_new($sid);
			return '';
		}
		if($arr['bigdata'] == 1) {
			$arr2 = db_find_one('session_data', array('sid'=>$sid));
			$arr['data'] = $arr2['data'];
		}
		$this->session = $arr;
		return $arr ? $arr['data'] : '';
	}
	
	public function sess_new($sid) {
		global $uid, $fid, $time, $longip, $conf;
		
		$agent = _SERVER('HTTP_USER_AGENT');
		
		// 干掉同 ip 的 sid，仅仅在遭受攻击的时候
		//db_delete('session', array('ip'=>$longip));
		
		$cookie_test = _COOKIE('cookie_test');
		if($cookie_test) {
			$cookie_test_decode = xn_decrypt($cookie_test, $conf['auth_key']);
			$this->invalid = ($cookie_test_decode != md5($agent.$longip));
			setcookie('cookie_test', '', $time - 86400, '');
		} else {
			$cookie_test = xn_encrypt(md5($agent.$longip), $conf['auth_key']);
			setcookie('cookie_test', $cookie_test, $time + 86400, '');
			$this->invalid = FALSE;
			return;
		}
		
		// 可能会暴涨
		$url = _SERVER('REQUEST_URI_NO_PATH');
		
		$arr = array(
			'sid'=>$sid,
			'uid'=>$uid,
			'fid'=>$fid,
			'url'=>$url,
			'last_date'=>$time,
			'fid'=>$fid,
			'data'=> '',
			'ip'=> $longip,
			'useragent'=> $agent,
			'bigdata'=> 0,
		);
		db_insert('session', $arr);
		
	}
	
	public function write($sid, $data) {
		global $time, $uid, $fid, $longip;
		
		if($this->invalid) return TRUE;
		
		if(!empty($_SERVER['APP_PATH'])) chdir($_SERVER['APP_PATH']);
		
		$url = _SERVER('REQUEST_URI_NO_PATH');
		$agent = _SERVER('HTTP_USER_AGENT');
		$arr = array(
			'uid'=>$uid,
			'fid'=>$fid,
			'url'=>$url,
			'last_date'=>$time,
			'fid'=>$fid,
			'data'=> $data,
			'ip'=> $longip,
			'useragent'=> $agent,
			'bigdata'=> 0,
		);
		
		// 判断数据是否超长
		$data = addslashes($data);
		$len = strlen($data);
		if($len > 255 && $this->session['bigdata'] == 0) {
			// 这里可能并发
			db_replace('session_data', array('sid'=>$sid));
		}
		if($len <= 255) {
			$update = array_diff_value($arr, $this->session);
			db_update('session', array('sid'=>$sid), $update);
			if(!empty($this->session) && $this->session['bigdata'] == 1) {
				db_delete('session_data', array('sid'=>$sid));
			}
		} else {
			$arr['data'] = '';
			$arr['bigdata'] = 1;
			$update = array_diff_value($arr, $this->session);
			$update AND db_update('session', array('sid'=>$sid), $update);
			$update2 = array_diff_value(array('data'=>$arr['data'], 'last_date'=>$time), $this->session);
			$update2 AND db_update('session_data', array('sid'=>$sid), $update2);
		}
		return TRUE;
	}
	
	public function destroy($sid) { 
		//echo "sess_destroy($sid) \r\n";
		db_delete('session', array('sid'=>$sid));
		db_delete('session_data', array('sid'=>$sid));
		return TRUE; 
	}
	
	public function gc($maxlifetime) {
		//echo "sess_gc($maxlifetime) \r\n";
		global $time;
		$expiry = $time - $maxlifetime;
		db_delete('session', array('last_date'=>array('<'=>$expiry)));
		db_delete('session_data', array('last_date'=>array('<'=>$expiry)));
		return TRUE; 
	}
	function __destruct() {
		$this->write();
	}
};

function sess_start() {
	global $conf, $sid;
	ini_set('session.name', 'bbs_sid');
	
	ini_set('session.use_cookies', 'On');
	ini_set('session.use_only_cookies', 'On');
	ini_set('session.cookie_domain', '');
	ini_set('session.cookie_path', '');	// 为空则表示当前目录和子目录
	ini_set('session.cookie_secure', 'Off'); // 打开后，只有通过 https 才有效。
	ini_set('session.cookie_lifetime', 86400);
	ini_set('session.cookie_httponly', 'On'); // 打开后 js 获取不到 HTTP 设置的 cookie, 有效防止 XSS，这个对于安全很重要，除非有 BUG，否则不要关闭。
	
	ini_set('session.gc_maxlifetime', $conf['online_hold_time']);	// 活动时间 $conf['online_hold_time']
	ini_set('session.gc_probability', 1); 	// 垃圾回收概率 = gc_probability/gc_divisor
	ini_set('session.gc_divisor', 500); 	// 垃圾回收时间 5 秒，在线人数 * 10 
	
	$sess = new Session();
	session_set_save_handler(
		array($sess, 'open'),
		array($sess, 'close'),
		array($sess, 'read'),
		array($sess, 'write'),
		array($sess, 'destroy'),
		array($sess, 'gc')
	); 
	
	// register_shutdown_function 会丢失当前目录，需要 chdir()
	//$_SERVER['APP_PATH'] = getcwd();
	
	// 这个比须有，否则 ZEND 会提前释放 $db 资源
	register_shutdown_function('session_write_close');
	
	session_start();
	
	$sid = session_id();
	
	//echo "sess_start() sid: $sid <br>\r\n";
}

?>