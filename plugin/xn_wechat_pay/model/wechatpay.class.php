<?php

/**
 *
 * 接口访问类，包含所有微信支付API列表的封装，
 * 每个接口有默认超时时间（除提交被扫支付为10s，上报超时时间为1s外，其他均为6s）
 * @author QQ 78378766
 *
 */
class wechatpay
{
	public $payconf = array();
	
	function __construct (&$payconf)
	{
		$payconf['wxpay_notify_url'] = http_url_path() . 'notify.php';
		$payconf['wxpay_report_levenl'] = 0;
		$this->payconf = $payconf;
	}
	
	/**
	 *
	 * 统一下单，WxPayUnifiedOrder中out_trade_no、body、total_fee、trade_type必填
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 *
	 * @param WxPayUnifiedOrder $input
	 * @param int $timeOut
	 *
	 * @throws Exception
	 * @return 成功时返回，其他抛异常
	 */
	public function unifiedOrder ($input, $timeOut = 6)
	{
		global $uid, $ip, $time;
		empty( $uid ) AND message(1, '登陆以后才能充值!');
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		//检测必填参数
		if ( empty( $input['content'] ) ) {
			message(1, "缺少商品或支付单简要描述");
		} elseif ( empty( $input['total_fee'] ) ) {
			message(1, "缺少支付价格！");
		}
		$input['trade_type'] = empty( $input['trade_type'] ) ? 'JSAPI' : $input['trade_type'];
		//关联参数
		if ( $input['trade_type'] == 'JSAPI' && empty( $input['openid'] ) ) {
			message(1, "缺少必要参数！");
		} elseif ( $input['trade_type'] == 'NATIVE' && empty( $input['product_id'] ) ) {
			message(1, "必须传入产品ID");
		}
		$startTimeStamp = $this->getMillisecond();//请求开始时间
		$_input['appid'] = $this->payconf['wx_appkey'];//公众账号ID
		$_input['body'] = $input['content'];//支付信息
		$_input['mch_id'] = $this->payconf['wx_mkid'];//商户号
		$_input['nonce_str'] = $this->getNonceStr();//随机字符串
		$_input['out_trade_no'] = 'wx' . $startTimeStamp . $uid;
		$_input['notify_url'] = $this->payconf['wxpay_notify_url'];
		$_input['openid'] = $input['openid'];//公众账号ID
		
		$_input['spbill_create_ip'] = $ip;//终端ip
		$_input['time_start'] = date("YmdHis");//订单开始时间
		$_input['time_expire'] = date("YmdHis", $time + 600);//订单失效时间
		$_input['total_fee'] = $input['total_fee'];
		$_input['trade_type'] = $input['trade_type'];
		$_input['sign'] = $this->MakeSign($_input);
		$xml = $this->ToXml($_input);
		$response = $this->postXmlCurl($xml, $url, false, $timeOut);
		$result = $this->FromXml($response);
		//xn_log(xn_json_encode($_input),'unifiedOrder');
		if ( $result['result_code'] != 'SUCCESS' ) {
			exit( xn_json_encode($result) );
		}
		$this->reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间
		$result['out_trade_no'] = $_input['out_trade_no'];
		
		return $result;
	}
	
	/**
	 *
	 * 查询订单，WxPayOrderQuery中out_trade_no、transaction_id至少填一个
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 *
	 * @param WxPayOrderQuery $input
	 * @param int $timeOut
	 *
	 * @throws Exception
	 * @return 成功时返回，其他抛异常
	 */
	public function orderQuery ($input, $timeOut = 6)
	{
		$url = "https://api.mch.weixin.qq.com/pay/orderquery";
		//检测必填参数
		if ( empty( $input['out_trade_no'] ) && empty( $input['transaction_id'] ) ) {
			message(1, "订单查询接口中，out_trade_no、transaction_id至少填一个！");
		}
		$input['appid'] = $this->payconf['wx_appkey'];//公众账号ID
		$input['mch_id'] = $this->payconf['wx_mkid'];//商户号
		$input['nonce_str'] = $this->getNonceStr();//随机字符串
		
		$input['sign'] = $this->MakeSign($input);//签名
		$xml = $this->ToXml($input);
		$startTimeStamp = $this->getMillisecond();//请求开始时间
		$response = $this->postXmlCurl($xml, $url, false, $timeOut);
		$result = $this->FromXml($response);
		xn_log(xn_json_encode($input), 'orderQuery');
		if ( $result['result_code'] != 'SUCCESS' ) {
			exit( xn_json_encode($result) );
		}
		$this->reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间
		return $result;
	}
	
	public function GetJsApiParameters ($UnifiedOrderResult)
	{
		global $time;
		if ( empty( $UnifiedOrderResult['prepay_id'] ) ) {
			message(1, "参数错误");
		}
		$input['appId'] = $this->payconf['wx_appkey'];
		$input['timeStamp'] = "$time";
		$input['nonceStr'] = $this->getNonceStr();
		$input['package'] = 'prepay_id=' . $UnifiedOrderResult['prepay_id'];
		$input['signType'] = 'MD5';
		$input['paySign'] = $this->MakeSign($input);
		
		return json_encode($input);
	}
	
	/**
	 * 输出xml字符
	 * @throws Exception
	 **/
	public function ToXml ($input)
	{
		if ( !is_array($input) || count($input) <= 0 )
			message(1, "数组数据异常！");
		$xml = '<xml>';
		foreach ( $input as $key => $val ) {
			$xml .= '<' . $key . '>' . $val . '</' . $key . '>';
		}
		$xml .= '</xml>';
		
		return $xml;
	}
	
	/**
	 * 将xml转为array
	 *
	 * @param string $xml
	 *
	 * @throws Exception
	 */
	public function FromXml ($xml)
	{
		if ( empty( $xml ) )
			return false;
		//将XML转为array
		//禁止引用外部xml实体
		libxml_disable_entity_loader(true);
		
		return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
	}
	
	/**
	 * 格式化参数格式化成url参数
	 */
	public static function ToUrlParams ($input)
	{
		$buff = '';
		unset( $input['sign'] );
		foreach ( $input as $k => $v ) {
			if ( !empty( $v ) && !is_array($v) ) {
				$buff .= $k . '=' . $v . '&';
			}
		}
		$buff = trim($buff, '&');
		
		return $buff;
	}
	
	/**
	 * 生成签名
	 * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
	 */
	public function MakeSign ($input)
	{
		//签名步骤一：按字典序排序参数
		ksort($input);
		$string = $this->ToUrlParams($input);
		//签名步骤二：在string后加入KEY
		$string = $string . '&key=' . $this->payconf['wx_mksecret'];
		//签名步骤三：MD5加密
		$string = md5($string);
		//签名步骤四：所有字符转为大写
		$result = strtoupper($string);
		
		return $result;
	}
	
	/**
	 *
	 * 测速上报，该方法内部封装在report中，使用时请注意异常流程
	 * WxPayReport中interface_url、return_code、result_code、user_ip、execute_time_必填
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 *
	 * @param WxPayReport $input
	 * @param int $timeOut
	 *
	 * @throws Exception
	 * @return 成功时返回，其他抛异常
	 */
	public function report ($input, $timeOut = 1)
	{
		$url = "https://api.mch.weixin.qq.com/payitil/report";
		if ( empty( $input['return_code'] ) ) {
			message(1, "返回状态码，缺少必填参数return_code！");
		}
		if ( empty( $input['result_code'] ) ) {
			message(1, "业务结果，缺少必填参数result_code！");
		}
		$input['appid'] = $this->payconf['appkey'];//公众账号ID
		$input['mch_id'] = $this->payconf['mkid'];//商户号
		$input['user_ip'] = $_SERVER['ip'];//终端ip
		$input['time'] = date("YmdHis");//商户上报时间
		$input['nonce_str'] = $this->getNonceStr();//随机字符串
		$input['sign'] = $this->MakeSign($input);//签名
		$xml = $this->ToXml($input);
		
		$startTimeStamp = $this->getMillisecond();//请求开始时间
		$response = $this->postXmlCurl($xml, $url, false, $timeOut);
		
		return $response;
	}
	
	/**
	 *
	 * 产生随机字符串，不长于32位
	 *
	 * @param int $length
	 *
	 * @return 产生的随机字符串
	 */
	public static function getNonceStr ($length = 32)
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		$str = "";
		for ( $i = 0; $i < $length; $i++ ) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		
		return $str;
	}
	
	/**
	 * 直接输出xml
	 *
	 * @param string $xml
	 */
	public static function replyNotify ($xml)
	{
		echo $xml;
	}
	
	/**
	 *
	 * 上报数据， 上报的时候将屏蔽所有异常流程
	 *
	 * @param string $usrl
	 * @param int $startTimeStamp
	 * @param array $data
	 */
	private function reportCostTime ($url, $startTimeStamp, $data)
	{
		//如果不需要上报数据
		if ( $this->payconf['wxpay_report_levenl'] == 0 )
			return;
		//如果仅失败上报
		if ( $this->payconf['wxpay_report_levenl'] == 1 && array_key_exists("return_code", $data) && $data["return_code"] == "SUCCESS" && array_key_exists("result_code", $data) && $data["result_code"] == "SUCCESS" )
			return;
		
		//上报逻辑
		$endTimeStamp = $this->getMillisecond();
		$input['interface_url'] = $url;
		$input['execute_time_'] = $endTimeStamp - $startTimeStamp;
		//返回状态码
		if ( array_key_exists("return_code", $data) ) {
			$input['return_code'] = $data["return_code"];
		}
		//返回信息
		if ( array_key_exists("return_msg", $data) ) {
			$input['return_msg'] = $data["return_msg"];
		}
		//业务结果
		if ( array_key_exists("result_code", $data) ) {
			$input['result_code'] = $data["return_code"];
		}
		//错误代码
		if ( array_key_exists("err_code", $data) ) {
			$input['err_code'] = $data["err_code"];
		}
		//错误代码描述
		if ( array_key_exists("err_code_des", $data) ) {
			$input['err_code_des'] = $data["err_code_des"];
		}
		//商户订单号
		if ( array_key_exists("out_trade_no", $data) ) {
			$input['out_trade_no'] = $data["out_trade_no"];
		}
		//设备号
		if ( array_key_exists("device_info", $data) ) {
			$input['device_info'] = $data["device_info"];
		}
		
		try {
			$this->report($input);
		} catch ( Exception $e ) {
			//不做任何处理
		}
	}
	
	public static function getMillisecond ()
	{
		//获取毫秒的时间戳
		list( $t1, $t2 ) = explode(' ', microtime());
		
		return (string)sprintf('%.0f', ( floatval($t1) + floatval($t2) ) * 1000);
	}
	
	/**
	 * 以post方式提交xml到对应的接口url
	 *
	 * @param string $xml   需要post的xml数据
	 * @param string $url   url
	 * @param bool $useCert 是否需要证书，默认不需要
	 * @param int $second   url执行超时时间，默认30s
	 *
	 * @throws Exception
	 */
	private function postXmlCurl ($xml, $url, $useCert = false, $second = 30)
	{
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $second);
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);//严格校验
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, false);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//运行curl
		$data = curl_exec($ch);
		//返回结果
		if ( $data ) {
			curl_close($ch);
			
			return $data;
		} else {
			$error = curl_errno($ch);
			curl_close($ch);
			message(1, "curl出错，错误码:$error");
		}
	}
}

?>
