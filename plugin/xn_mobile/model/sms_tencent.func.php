<?php



/**
 * 发送Util类
 *
 */
class TencentSmsSenderUtil
{
	/**
	 * 生成随机数
	 *
	 * @return int 随机数结果
	 */
	public function getRandom()
	{
		return rand(100000, 999999);
	}
	/**
	 * 生成签名
	 *
	 * @param string $appid		 sdkappid
	 * @param string $appkey		sdkappid对应的appkey
	 * @param string $curTime	   当前时间
	 * @param array  $phoneNumbers  手机号码
	 * @return string  签名结果
	 */
	public function calculateSig($appkey, $random, $curTime, $phoneNumbers)
	{
		$phoneNumbersString = $phoneNumbers[0];
		for ($i = 1; $i < count($phoneNumbers); $i++) {
			$phoneNumbersString .= ("," . $phoneNumbers[$i]);
		}
		return hash("sha256", "appkey=".$appkey."&random=".$random
			."&time=".$curTime."&mobile=".$phoneNumbersString);
	}
	/**
	 * 生成签名
	 *
	 * @param string $appid		 sdkappid
	 * @param string $appkey		sdkappid对应的appkey
	 * @param string $curTime	   当前时间
	 * @param array  $phoneNumbers  手机号码
	 * @return string  签名结果
	 */
	public function calculateSigForTemplAndPhoneNumbers($appkey, $random,
		$curTime, $phoneNumbers)
	{
		$phoneNumbersString = $phoneNumbers[0];
		for ($i = 1; $i < count($phoneNumbers); $i++) {
			$phoneNumbersString .= ("," . $phoneNumbers[$i]);
		}
		return hash("sha256", "appkey=".$appkey."&random=".$random
			."&time=".$curTime."&mobile=".$phoneNumbersString);
	}
	public function phoneNumbersToArray($nationCode, $phoneNumbers)
	{
		$i = 0;
		$tel = array();
		do {
			$telElement = new stdClass();
			$telElement->nationcode = $nationCode;
			$telElement->mobile = $phoneNumbers[$i];
			array_push($tel, $telElement);
		} while (++$i < count($phoneNumbers));
		return $tel;
	}
	/**
	 * 生成签名
	 *
	 * @param string $appid		 sdkappid
	 * @param string $appkey		sdkappid对应的appkey
	 * @param string $curTime	   当前时间
	 * @param array  $phoneNumbers  手机号码
	 * @return string  签名结果
	 */
	public function calculateSigForTempl($appkey, $random, $curTime, $phoneNumber)
	{
		$phoneNumbers = array($phoneNumber);
		return $this->calculateSigForTemplAndPhoneNumbers($appkey, $random,
			$curTime, $phoneNumbers);
	}
	/**
	 * 生成签名
	 *
	 * @param string $appid		 sdkappid
	 * @param string $appkey		sdkappid对应的appkey
	 * @param string $curTime	   当前时间
	 * @return string 签名结果
	 */
	public function calculateSigForPuller($appkey, $random, $curTime)
	{
		return hash("sha256", "appkey=".$appkey."&random=".$random
			."&time=".$curTime);
	}
	/**
	 * 发送请求
	 *
	 * @param string $url	  请求地址
	 * @param array  $dataObj  请求内容
	 * @return string 应答json字符串
	 */
	public function sendCurlPost($url, $dataObj)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dataObj));
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		$ret = curl_exec($curl);
		if (false == $ret) {
			// curl_exec failed
			$result = "{ \"result\":" . -2 . ",\"errmsg\":\"" . curl_error($curl) . "\"}";
		} else {
			$rsp = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if (200 != $rsp) {
				$result = "{ \"result\":" . -1 . ",\"errmsg\":\"". $rsp
						. " " . curl_error($curl) ."\"}";
			} else {
				$result = $ret;
			}
		}
		curl_close($curl);
		return $result;
	}
}



/**
 * 单发短信类
 *
 */
class SmsSingleSender
{
	private $url;
	private $appid;
	private $appkey;
	private $util;
	/**
	 * 构造函数
	 *
	 * @param string $appid  sdkappid
	 * @param string $appkey sdkappid对应的appkey
	 */
	public function __construct($appid, $appkey)
	{
		$this->url = "https://yun.tim.qq.com/v5/tlssmssvr/sendsms";
		$this->appid =  $appid;
		$this->appkey = $appkey;
		$this->util = new TencentSmsSenderUtil();
	}
	/**
	 * 普通单发
	 *
	 * 普通单发需明确指定内容，如果有多个签名，请在内容中以【】的方式添加到信息内容中，否则系统将使用默认签名。
	 *
	 * @param int	$type		短信类型，0 为普通短信，1 营销短信
	 * @param string $nationCode  国家码，如 86 为中国
	 * @param string $phoneNumber 不带国家码的手机号
	 * @param string $msg		 信息内容，必须与申请的模板格式一致，否则将返回错误
	 * @param string $extend	  扩展码，可填空串
	 * @param string $ext		 服务端原样返回的参数，可填空串
	 * @return string 应答json字符串，详细内容参见腾讯云协议文档
	 */
	public function send($type, $nationCode, $phoneNumber, $msg, $extend = "", $ext = "")
	{
		$random = $this->util->getRandom();
		$curTime = time();
		$wholeUrl = $this->url . "?sdkappid=" . $this->appid . "&random=" . $random;
		// 按照协议组织 post 包体
		$data = new stdClass();
		$tel = new stdClass();
		$tel->nationcode = "".$nationCode;
		$tel->mobile = "".$phoneNumber;
		$data->tel = $tel;
		$data->type = (int)$type;
		$data->msg = $msg;
		$data->sig = hash("sha256",
			"appkey=".$this->appkey."&random=".$random."&time="
			.$curTime."&mobile=".$phoneNumber, FALSE);
		$data->time = $curTime;
		$data->extend = $extend;
		$data->ext = $ext;
		return $this->util->sendCurlPost($wholeUrl, $data);
	}
	/**
	 * 指定模板单发
	 *
	 * @param string $nationCode  国家码，如 86 为中国
	 * @param string $phoneNumber 不带国家码的手机号
	 * @param int	$templId	 模板 id
	 * @param array  $params	  模板参数列表，如模板 {1}...{2}...{3}，那么需要带三个参数
	 * @param string $sign		签名，如果填空串，系统会使用默认签名
	 * @param string $extend	  扩展码，可填空串
	 * @param string $ext		 服务端原样返回的参数，可填空串
	 * @return string 应答json字符串，详细内容参见腾讯云协议文档
	 */
	public function sendWithParam($nationCode, $phoneNumber, $templId = 0, $params,
		$sign = "", $extend = "", $ext = "")
	{
		$random = $this->util->getRandom();
		$curTime = time();
		$wholeUrl = $this->url . "?sdkappid=" . $this->appid . "&random=" . $random;
		// 按照协议组织 post 包体
		$data = new stdClass();
		$tel = new stdClass();
		$tel->nationcode = "".$nationCode;
		$tel->mobile = "".$phoneNumber;
		$data->tel = $tel;
		$data->sig = $this->util->calculateSigForTempl($this->appkey, $random, $curTime, $phoneNumber);
		$data->tpl_id = $templId;
		$data->params = $params;
		$data->sign = $sign;
		$data->time = $curTime;
		$data->extend = $extend;
		$data->ext = $ext;
		return $this->util->sendCurlPost($wholeUrl, $data);
	}
}


/*
	result: 1026
	errmsg: '手机号内容频率限制'
	
	Array ( [result] => 0 [errmsg] => OK [ext] => [sid] => 8:3rlKRUUk6jVtAfpJAWU20180218 [fee] => 1 )

*/
function sms_tencent_send_code($tomobile, $code, $appid, $appkey) {
	
	/*
	$kv = kv_get('xn_mobile');
	$appid = $kv['appid'];
	$appkey = $kv['appkey'];
	$sign = $kv['sign'];
	*/
	
	/*
	// 短信应用SDK AppID
	$appid = 14001234567; // 1400开头
	
	// 短信应用SDK AppKey
	$appkey = "xxxxxxxxxxxxxxxxxxxxxxxxxxx";
	*/
	
	// 短信模板ID，需要在短信应用中申请
	//$templateId = 7839;  // NOTE: 这里的模板ID`7839`只是一个示例，真实的模板ID需要在短信控制台中申请
	
	$message = '您的验证码：'.$code.'，该验证码5分钟内有效。';
	
	try {
		$ssender = new SmsSingleSender($appid, $appkey);
		$result = $ssender->send(0, "86", $tomobile, $message, "", "");
		$arr = json_decode($result, 1);
		if($arr && $arr['errmsg'] == 'OK') {
			xn_log(print_r($arr, 1), 'sms_success');
			return TRUE;
		} else {
			xn_log(print_r($arr, 1), 'sms_error');
			return FALSE;
		}
	} catch(Exception $e) {
		xn_log($e->getMessage(), 'sms_error');
		return FALSE;
	}

}

?>