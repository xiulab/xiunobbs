<?php

/*
 * Copyright (C) qiaocms.com
 */

class wechat
{
	public $conf = array();
	public $data = array();
	private $access = array();
	private $error = array( '-1' => '系统繁忙，此时请开发者稍候再试', '0' => '请求成功', '40001' => '获取access_token时AppSecret错误，或者access_token无效。请开发者认真比对AppSecret的正确性，或查看是否正在为恰当的公众号调用接口', '40002' => '不合法的凭证类型', '40003' => '不合法的OpenID，请开发者确认OpenID（该用户）是否已关注公众号，或是否是其他公众号的OpenID', '40004' => '不合法的媒体文件类型', '40005' => '不合法的文件类型', '40006' => '不合法的文件大小', '40007' => '不合法的媒体文件id', '40008' => '不合法的消息类型', '40009' => '不合法的图片文件大小', '40010' => '不合法的语音文件大小', '40011' => '不合法的视频文件大小', '40012' => '不合法的缩略图文件大小', '40013' => '不合法的AppID，请开发者检查AppID的正确性，避免异常字符，注意大小写', '40014' => '不合法的access_token，请开发者认真比对access_token的有效性（如是否过期），或查看是否正在为恰当的公众号调用接口', '40015' => '不合法的菜单类型', '40016' => '不合法的按钮个数', '40017' => '不合法的按钮个数', '40018' => '不合法的按钮名字长度', '40019' => '不合法的按钮KEY长度', '40020' => '不合法的按钮URL长度', '40021' => '不合法的菜单版本号', '40022' => '不合法的子菜单级数', '40023' => '不合法的子菜单按钮个数', '40024' => '不合法的子菜单按钮类型', '40025' => '不合法的子菜单按钮名字长度', '40026' => '不合法的子菜单按钮KEY长度', '40027' => '不合法的子菜单按钮URL长度', '40028' => '不合法的自定义菜单使用用户', '40029' => '不合法的oauth_code', '40030' => '不合法的refresh_token', '40031' => '不合法的openid列表', '40032' => '不合法的openid列表长度', '40033' => '不合法的请求字符，不能包含\uxxxx格式的字符', '40035' => '不合法的参数', '40038' => '不合法的请求格式', '40039' => '不合法的URL长度', '40050' => '不合法的分组id', '40051' => '分组名字不合法', '40117' => '分组名字不合法', '40118' => 'media_id大小不合法', '40119' => 'button类型错误', '40120' => 'button类型错误', '40121' => '不合法的media_id类型', '40132' => '微信号不合法', '40137' => '不支持的图片格式', '41001' => '缺少access_token参数', '41002' => '缺少appid参数', '41003' => '缺少refresh_token参数', '41004' => '缺少secret参数', '41005' => '缺少多媒体文件数据', '41006' => '缺少media_id参数', '41007' => '缺少子菜单数据', '41008' => '缺少oauth code', '41009' => '缺少openid', '42001' => 'access_token超时，请检查access_token的有效期，请参考基础支持-获取access_token中，对access_token的详细机制说明', '42002' => 'refresh_token超时', '42003' => 'oauth_code超时', '43001' => '需要GET请求', '43002' => '需要POST请求', '43003' => '需要HTTPS请求', '43004' => '需要接收者关注', '43005' => '需要好友关系', '44001' => '多媒体文件为空', '44002' => 'POST的数据包为空', '44003' => '图文消息内容为空', '44004' => '文本消息内容为空', '45001' => '多媒体文件大小超过限制', '45002' => '消息内容超过限制', '45003' => '标题字段超过限制', '45004' => '描述字段超过限制', '45005' => '链接字段超过限制', '45006' => '图片链接字段超过限制', '45007' => '语音播放时间超过限制', '45008' => '图文消息超过限制', '45009' => '接口调用超过限制', '45010' => '创建菜单个数超过限制', '45015' => '回复时间超过限制', '45016' => '系统分组，不允许修改', '45017' => '分组名字过长', '45018' => '分组数量超过上限', '46001' => '不存在媒体数据', '46002' => '不存在的菜单版本', '46003' => '不存在的菜单数据', '46004' => '不存在的用户', '47001' => '解析JSON/XML内容错误', '48001' => 'api功能未授权，请确认公众号已获得该接口，可以在公众平台官网-开发者中心页中查看接口权限', '50001' => '用户未授权该api', '50002' => '用户受限，可能是违规后接口被封禁', '61451' => '参数错误(invalid parameter)', '61452' => '无效客服账号(invalid kf_account)', '61453' => '客服帐号已存在(kf_account exsited)', '61454' => '客服帐号名长度超过限制(仅允许10个英文字符，不包括@及@后的公众号的微信号)(invalid kf_acount length)', '61455' => '客服帐号名包含非法字符(仅允许英文+数字)(illegal character in kf_account)', '61456' => '客服帐号个数超过限制(10个客服账号)(kf_account count exceeded)', '61457' => '无效头像文件类型(invalid file type)', '61450' => '系统错误(system error)', '61500' => '日期格式错误', '61501' => '日期范围错误', '9001001' => 'POST数据参数不合法', '9001002' => '远端服务不可用', '9001003' => 'Ticket不合法', '9001004' => '获取摇周边用户信息失败', '9001005' => '获取商户信息失败', '9001006' => '获取OpenID失败', '9001007' => '上传文件缺失', '9001008' => '上传素材的文件类型不合法', '9001009' => '上传素材的文件尺寸不合法', '9001010' => '上传失败', '9001020' => '帐号不合法', '9001021' => '已有设备激活率低于50%，不能新增设备', '9001022' => '设备申请数不合法，必须为大于0的数字', '9001023' => '已存在审核中的设备ID申请', '9001024' => '一次查询设备ID数量不能超过50', '9001025' => '设备ID不合法', '9001026' => '页面ID不合法', '9001027' => '页面参数不合法', '9001028' => '一次删除页面ID数量不能超过10', '9001029' => '页面已应用在设备中，请先解除应用关系再删除', '9001030' => '一次查询页面ID数量不能超过50', '9001031' => '时间区间不合法', '9001032' => '保存设备与页面的绑定关系参数错误', '9001033' => '门店ID不合法', '9001034' => '设备备注信息过长', '9001035' => '设备申请参数不合法', '9001036' => '查询起始值begin不合法' );
	
	private $tmperror = array( '-1' => '系统繁忙，此时请开发者稍候再试', 0 => '请求成功', 40001 => "验证失败", 40002 => "不合法的凭证类型", 40003 => "不合法的OpenID", 40004 => "不合法的媒体文件类型", 40005 => "不合法的文件类型", 40006 => "不合法的文件大小", 40007 => "不合法的媒体文件id", 40008 => "不合法的消息类型", 40009 => "不合法的图片文件大小", 40010 => "不合法的语音文件大小", 40011 => "不合法的视频文件大小", 40012 => "不合法的缩略图文件大小", 40013 => "不合法的APPID", 41001 => "缺少access_token参数", 41002 => "缺少appid参数", 41003 => "缺少refresh_token参数", 41004 => "缺少secret参数", 41005 => "缺少多媒体文件数据", 41006 => "access_token超时", 42001 => "需要GET请求", 43002 => "需要POST请求", 43003 => "需要HTTPS请求", 44001 => "多媒体文件为空", 44002 => "POST的数据包为空", 44003 => "图文消息内容为空", 45001 => "多媒体文件大小超过限制", 45002 => "消息内容超过限制", 45003 => "标题字段超过限制", 45004 => "描述字段超过限制", 45005 => "链接字段超过限制", 45006 => "图片链接字段超过限制", 45007 => "语音播放时间超过限制", 45008 => "图文消息超过限制", 45009 => "接口调用超过限制", 46001 => "不存在媒体数据", 47001 => "解析JSON/XML内容错误" );
	
	function __construct ($conf = array())
	{
		$this->conf = $conf;
		
	}
	
	//验证签名
	public function valid ()
	{
		$token = $this->conf['wx_token'];
		$echoStr = param('echostr');
		$signature = param('signature');
		$timestamp = param('timestamp');
		$nonce = param('nonce');
		$tmpArr = array( $token, $timestamp, $nonce );
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);
		if ( $tmpStr == $signature ) {
			return $echoStr;
		}
	}
	
	public function get_access_token ()
	{
		if ( !empty( $this->data['access']['access_token'] ) )
			return $this->data['access'];
		$this->data['access'] = cache_get('wechat_access');
		if ( !empty( $this->data['access']['access_token'] ) )
			return $this->data['access'];
		$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->conf['wx_appkey'] . '&secret=' . $this->conf['wx_appsecret'];
		$json = $this->curl_get($url);
		$json = xn_json_decode($json);
		!empty( $json['errcode'] ) AND message($json['errcode'], $this->error[$json['errcode']]);
		!empty( $json ) AND cache_set('wechat_access', $json, 3500);
		$this->data['access'] = $json;
		
		return $json;
	}
	
	public function send_msg ($data = array())
	{
		if ( empty( $data['openid'] ) )
			return;
		$this->get_access_token();
		$access_token = $this->data['access']['access_token'];
		$msg = '{"touser": "' . $data['openid'] . '","msgtype": "text","text":{"content": "' . $data['desc'] . '"}}';
		$rt = $this->curl_post('https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $access_token, $msg);
		$json = json_decode($rt);
		$json->errcode != 0 AND xn_log($this->error[$json->errcode], 'wechat_send_msg_error');
		//message($json->errcode,$this->error[$json->errcode]);
	}
	
	public function get_user ($openid)
	{
		if ( empty( $openid ) )
			return false;
		$this->get_access_token();
		$access_token = $this->data['access']['access_token'];
		$url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $access_token . '&openid=' . $openid . '&lang=zh_CN';
		$json = $this->curl_get($url);
		$json = xn_json_decode($json);
		
		return $json;
	}
	
	public function qrcode ($user)
	{
		if ( empty( $user['uid'] ) )
			return false;
		$this->get_access_token();
		$access_token = $this->data['access']['access_token'];
		$url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $access_token;
		$json = $this->curl_post($url, '{"expire_seconds":604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": ' . $user['uid'] . '}}}');
		$json = xn_json_decode($json);
		if ( !empty( $json['ticket'] ) ) {
			$data = $this->curl_get('https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . UrlEncode($json['ticket']));
			empty( $data ) AND message(1, '二维码获取失败！');
			
			return $data;
		} else {
			message(1, '二维码获取失败！');
		}
	}
	
	public function create_menu ($data)
	{
		$this->get_access_token();
		$access_token = $this->data['access']['access_token'];
		$url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $access_token;
		$json = $this->curl_post($url, $data);
		//xn_log($json,'wechat');
		$json = json_decode($json);
		
		return array( 'code' => $json->errcode, 'msg' => $this->error[$json->errcode] );
	}
	
	public function get_menu ()
	{
		$this->get_access_token();
		$access_token = $this->data['access']['access_token'];
		$json = $this->curl_get('https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=' . $access_token);
		$json = xn_json_decode($json);
		
		return $json;
	}
	
	public function responseMsg ()
	{
		$postStr = file_get_contents("php://input");
		if ( empty( $postStr ) )
			exit( '无效访问' );
		$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
		$RX_TYPE = trim($postObj->MsgType);
		//消息类型分离
		switch ( $RX_TYPE ) {
			case "event" :
				//功能事件
				$result = $this->receiveEvent($postObj);
				break;
			case "text" :
				//文本
				$result = $this->receiveText($postObj);
				break;
			default :
				return;
		}
		
		return $result;
	}
	
	//接收文本消息
	private function receiveText ($object)
	{
		$keyword = trim($object->Content);
		$conf = $this->conf;
		if ( is_array($content) ) {
			if ( isset( $content[0] ) ) {
				$result = $this->transmitNews($object, $content);
			}
		} else {
			$result = $this->transmitText($object, $content);
		}
		
		return $result;
	}
	
	//接收事件消息
	private function receiveEvent ($object)
	{
		global $longip, $time, $uid, $user;
		//$content = $object->Event.' '.$object->EventKey . ' ';
		//xn_log($content,'wechat');
		switch ( $object->Event ) {
			case "subscribe" :
				$follow = kv_get('follow');
				$content = str_replace(array( 'sitename', 'time', 'username' ), array( $this->conf['sitename'], date('Y-n-j'), $user['username'] ), $follow);
				break;
			case "SCAN" :
				$content = '亲，扫码了，时间：' . date('Y-n-j H:i:s');
				break;
			case "CLICK" :
				$object->EventKey = trim($object->EventKey);
				switch ( $object->EventKey ) {
					case "sign" :
						$open_user = db_find_one('user_open_wechat', array( 'openid' => $object->FromUserName ));
						if ( !empty( $open_user['uid'] ) ) {
							$u = user_read($open_user['uid']);
							$array = array( '王冠上的苍蝇，并不比厕所里的苍蝇更高贵。', '少女诚可贵，少妇价更高，若有富婆在，二者皆可抛。', '我这辈子只有两件事不会：这也不会，那也不会。', '买了电脑不上宽带，就好比酒肉都准备好了却在吃饭前当了和尚。', '对不起是一种真诚，没关系是一种风度，如果你付出了真诚，却得不到风度，那只能说明对方的无知与粗俗。', '“特别能吃苦”这5个字，我想了想，我做到了前4个', '思想有多远，你就滚多远；光速有多快，你就滚多快。', '你是金子我是煤，你会发光，我会发热。别把我惹火了，小心我把你融化了。', '你胖了，你的男人对你的爱没变，但是平均在每块肉上的爱就少了。', '上帝看见你口渴，创造了水；上帝看见你饿，创造了米；上帝看见你没有可爱的朋友，创造了我；然而他也看见这世界上没有白痴，顺便也创造你。', '好几天没吃饭了，看谁都像烙饼。' );
							
							$content = $u['username'] . "您好,签到不易送你一句回复吧~\n\n" . $array[array_rand($array)];
						} else {
							$content = "签到失败,请先进入网站绑定用户";
						}
						break;
					default :
						$content = "即将呈现!";
						break;
				}
				break;
			case "VIEW" :
				$content = "跳转链接 " . $object->EventKey;
				break;
			default :
				$content = $object->Event;
		}
		
		if ( is_array($content) ) {
			if ( isset( $content[0]['PicUrl'] ) ) {
				return $this->transmitNews($object, $content);
			}
		} else {
			return $this->transmitText($object, $content);
		}
		
		return false;
	}
	
	private function message ($object, $content)
	{
		return $this->transmitText($object, $content);
	}
	
	//回复文本消息
	private static function transmitText ($object, $content)
	{
		global $time;
		if ( empty( $content ) )
			return;
		$xmlTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                </xml>";
		$result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, $time, $content);
		
		return $result;
	}
	
	//回复图文消息
	private static function transmitNews ($object, $newsArray)
	{
		global $time;
		if ( empty( $newsArray ) )
			return;
		$itemTpl = "<item>
                    <Title><![CDATA[%s]]></Title>
                    <Description><![CDATA[%s]]></Description>
                    <PicUrl><![CDATA[%s]]></PicUrl>
                    <Url><![CDATA[%s]]></Url>
                </item>";
		$item_str = "";
		foreach ( $newsArray as $item ) {
			$item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
		}
		$xmlTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[news]]></MsgType>
                    <ArticleCount>%s</ArticleCount>
                    <Articles>$item_str</Articles>
                </xml>";
		$result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, $time, count($newsArray));
		
		return $result;
	}
	
	private static function curl_get ($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
		//curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		$temp = curl_exec($ch);
		curl_close($ch);
		
		return $temp;
	}
	
	private static function curl_post ($url, $data)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
		//curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		$temp = curl_exec($ch);
		curl_close($ch);
		
		return $temp;
	}
	
	//日志记录
	private function logger ($log_content)
	{
		xn_log($log_content, 'wechat');
	}
	
}

?>