<?php

/**
 * 签名助手 2017/11/19
 *
 * Class AliyunSignatureHelper
 */
class AliyunSignatureHelper {

	/**
	 * 生成签名并发起请求
	 *
	 * @param $accessKeyId string AccessKeyId (https://ak-console.aliyun.com/)
	 * @param $accessKeySecret string AccessKeySecret
	 * @param $domain string API接口所在域名
	 * @param $params array API具体参数
	 * @param $security boolean 使用https
	 * @return bool|\stdClass 返回API接口调用结果，当发生错误时返回false
	 */
	public function request($accessKeyId, $accessKeySecret, $domain, $params, $security=false) {
		$apiParams = array_merge(array (
			"SignatureMethod" => "HMAC-SHA1",
			"SignatureNonce" => uniqid(mt_rand(0,0xffff), true),
			"SignatureVersion" => "1.0",
			"AccessKeyId" => $accessKeyId,
			"Timestamp" => gmdate("Y-m-d\TH:i:s\Z"),
			"Format" => "JSON",
		), $params);
		ksort($apiParams);

		$sortedQueryStringTmp = "";
		foreach ($apiParams as $key => $value) {
			$sortedQueryStringTmp .= "&" . $this->encode($key) . "=" . $this->encode($value);
		}

		$stringToSign = "GET&%2F&" . $this->encode(substr($sortedQueryStringTmp, 1));

		$sign = base64_encode(hash_hmac("sha1", $stringToSign, $accessKeySecret . "&",true));

		$signature = $this->encode($sign);

		$url = ($security ? 'https' : 'http')."://{$domain}/?Signature={$signature}{$sortedQueryStringTmp}";

		try {
			$content = $this->fetchContent($url);
			return xn_json_decode($content);
		} catch(Exception $e) {
			return false;
		}
	}

	private function encode($str)
	{
		$res = urlencode($str);
		$res = preg_replace("/\+/", "%20", $res);
		$res = preg_replace("/\*/", "%2A", $res);
		$res = preg_replace("/%7E/", "~", $res);
		return $res;
	}

	private function fetchContent($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"x-sdk-client" => "php/2.0.0"
		));

		if(substr($url, 0,5) == 'https') {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}

		$rtn = curl_exec($ch);

		if($rtn === false) {
			trigger_error("[CURL_" . curl_errno($ch) . "]: " . curl_error($ch), E_USER_ERROR);
		}
		curl_close($ch);

		return $rtn;
	}
}



/*
 * 此文件用于验证短信服务API接口，供开发时参考
 * 执行验证前请确保文件为utf-8编码，并替换相应参数为您自己的信息，并取消相关调用的注释
 * 建议验证前先执行Test.php验证PHP环境
 *
 * 2017/11/30
 */

/**
 * 发送短信
 */
function sms_aliyun_send_code($tomobile, $code, $appid, $appkey, $sign, $templateid = '') {

	$params = array ();

	// *** 需用户填写部分 ***

	// fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
	$accessKeyId = $appid;
	$accessKeySecret = $appkey;

	// fixme 必填: 短信接收号码
	$params["PhoneNumbers"] = $tomobile;

	// fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
	$params["SignName"] = $sign;

	// fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
	$params["TemplateCode"] = $templateid ? $templateid : "SMS_125028481";

	// fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
	$params['TemplateParam'] = Array (
		"code" => $code,
	);

	// fixme 可选: 设置发送短信流水号
	$params['OutId'] = "12345";

	// fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
	$params['SmsUpExtendCode'] = "1234567";


	// *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
	if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
		$params["TemplateParam"] = xn_json_encode($params["TemplateParam"]);
	}

	// 初始化SignatureHelper实例用于设置参数，签名以及发送请求
	$helper = new AliyunSignatureHelper();

	$arr = array_merge($params, array(
			"RegionId" => "cn-hangzhou",
			"Action" => "SendSms",
			"Version" => "2017-05-25",
		));
	// 此处可能会抛出异常，注意catch
	$r = $helper->request(
		$accessKeyId,
		$accessKeySecret,
		"dysmsapi.aliyuncs.com",
		$arr
	);
	
	/*
		Array
		(
		    [Message] => OK
		    [RequestId] => 423576B9-104C-4147-BC87-4B9904C17336
		    [BizId] => 912210418933889663^0
		    [Code] => OK
		)
	*/
	if($r && $r['Message'] == 'OK') {
		xn_log(print_r($arr, 1), 'sms_success');
		return TRUE;
	} else {
		xn_log(print_r($arr, 1), 'sms_error');
		return FALSE;
	}
}

?>