<?php
// 应用公共文件
use app\common\model\ApiRequestLog;
use think\facade\Request;
use think\facade\Session;

/**
 * checkToken 校验接口令牌
 * @param  string $token 令牌
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-05-01
 * @version 2020-05-03
 */
function checkToken($token = '')
{
	if (Session::has('token') && Session::get('token') === $token) {
		return true;
	} else {
		packApiData(403001, 'Invalid token', [], '接口令牌无效');
	}
}


/**
 * formatTime 将秒数换为时分秒
 * @param  int    $second  秒数
 * @return string          时分秒
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-01-22
 * @version 2020-02-16
 */
function formatTime($second = 0)
{
	$hour = floor($second / 3600);
	$minute = floor(($second - $hour * 3600) / 60);
	$second = floor(($second - $hour * 3600 - $minute * 60));
	return $hour . '时' . $minute . '分' . $second . '秒';
}


/**
 * getIP 获取IP地址
 * @return string IP地址
 */
function getIP()
{
	if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
		$cip = $_SERVER["HTTP_CLIENT_IP"];
	} elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
		$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	} elseif (!empty($_SERVER["REMOTE_ADDR"])) {
		$cip = $_SERVER["REMOTE_ADDR"];
	} else {
		$cip = "0.0.0.0";
	}
	return $cip;
}


/**
 * packApiData ajax返回统一标准json字符串
 * @param  integer $code       状态码
 * @param  string  $message    英文提示内容
 * @param  array   $data       返回数据
 * @param  string  $tips       中文提示语
 * @param  boolean  $needLog   是否需要日志记录
 * @return string/array/die
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2019-11-17
 * @version 2020-05-10
 */
function packApiData($code = 0, $message = '', $data = [], $tips = '', $needLog = true)
{
	$reqId = makeUUID();

	$r = array(
		'code' => $code,
		'message' => $message,
		'data' => $data,
		'tips' => $tips,
		'requestTime' => time(),
		'requestId' => $reqId
	);

	if ($code !== 403001 && $needLog === true) ApiRequestLog::create([
		'request_id' => $reqId,
		'path' => Request::baseUrl(),
		'ip' => getIP(),
		'code' => $code,
		'message' => $message,
		'req_data' => json_encode(Request::except(['token'])),
		'res_data' => json_encode($data),
	]);

	return json($r);
}


/**
 * 生成随机36位UUID
 * @return string 随机UUID
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2019-11-17
 * @version 2019-11-17
 */
function makeUUID()
{
	$hash = sha1(time() . md5(mt_rand(123456789, time())));
	return substr($hash, 4, 8) . '-' . substr($hash, 12, 4) . '-' . substr($hash, 16, 4) . '-' . substr($hash, 20, 4) . '-' . substr($hash, 24, 12);
}


/**
 * header自动跳转URL
 * @param string $path url
 */
function gotourl($path = '')
{
	$fullPath = url($path);
	die(header('location:' . $fullPath));
}


/**
 * 获取HTTP-Get数据
 * @param  string  $dataName  参数名称
 * @param  integer $allowNull 是否允许为空（0/1）
 * @param  integer $isAjax    是否为ajax请求（0/1）
 * @param  integer $errorCode isAjax=1时，参数缺失提醒的错误码
 * @param  string  $errorMsg  isAjax=1时，参数缺失提醒的错误内容
 * @param  string  $errorTips isAjax=1时，参数缺失提醒的错误汉字提醒
 * @return string             参数内容
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2019-11-17
 * @version 2019-12-23
 */
function inputGet($dataName = '', $allowNull = 0, $isAjax = 0, $errorCode = 0, $errorMsg = 'Lack parameter', $errorTips = '')
{
	$errorTips = $errorTips != '' ? $errorTips : '参数[' . $dataName . ']缺失';

	if (isset($_GET[$dataName])) {
		if ($allowNull != 1 && $_GET[$dataName] == "") {
			return $isAjax == 1 ? packApiData($errorCode, $errorMsg, [], $errorTips, 'die') : die();
		} else {
			return $_GET[$dataName];
		}
	} elseif ($allowNull == 1) {
		return;
	} else {
		return $isAjax == 1 ? packApiData($errorCode, $errorMsg, [], $errorTips, 'die') : die();
	}
}


/**
 * 获取HTTP-Post数据
 * @param  string  $dataName  参数名称
 * @param  integer $allowNull 是否允许为空（0/1）
 * @param  integer $isAjax    是否为ajax请求（0/1）
 * @param  integer $errorCode isAjax=1时，参数缺失提醒的错误码
 * @param  string  $errorMsg  isAjax=1时，参数缺失提醒的错误内容
 * @param  string  $errorTips isAjax=1时，参数缺失提醒的错误汉字提醒
 * @return string             参数内容
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2019-11-17
 * @version 2020-01-23
 */
function inputPost($dataName = '', $allowNull = 0, $isAjax = 0, $errorCode = 0, $errorMsg = 'Lack parameter', $errorTips = '')
{
	$errorTips = $errorTips != '' ? $errorTips : '参数[' . $dataName . ']缺失';

	// 支持json格式的post
	if (strpos(Request::header('content-type'), 'application/json') !== false) {
		$content = file_get_contents('php://input');
		$postdata = json_decode($content, true);
	} else {
		$postdata = $_POST;
	}

	if (isset($postdata[$dataName])) {
		if ($allowNull != 1 && $postdata[$dataName] == "") {
			return $isAjax == 1 ? packApiData($errorCode, $errorMsg, [], $errorTips, 'die') : die();
		} else {
			return $postdata[$dataName];
		}
	} elseif ($allowNull == 1) {
		return null;
	} else {
		return $isAjax == 1 ? packApiData($errorCode, $errorMsg, [], $errorTips, 'die') : die();
	}
}


/**
 * curl请求封装函数
 * @param  string  $url          请求URL
 * @param  string  $type         请求类型(get/post)
 * @param  array   $postData     需要POST的数据
 * @param  string  $postDataType POST数据类型(array/json)
 * @param  string  $returnType   返回数据类型(origin/json)
 * @param  integer $timeout      超时秒数
 * @param  string  $userAgent    UserAgent
 * @return string                返回结果(类型看returnType)
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2019-11-17
 * @version 2020-04-04
 */
function curl($url, $type = 'get', $postData = array(), $postDataType = 'array', $returnType = 'origin', $timeout = 5, $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36')
{
	if ($url == '' || $timeout <= 0) {
		return false;
	}

	$ch = curl_init((string) $url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, (int) $timeout);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);

	if ($type === 'post') {
		if ($postData == array()) {
			return false;
		} else if ($postDataType === 'json') {
			$postData = json_encode($postData);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
		}

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
	}

	$rtn = curl_exec($ch);
	if ($rtn === false) $rtn = 'curlError:' . curl_errno($ch);
	curl_close($ch);

	$rtn = ($returnType === 'json') ? json_decode($rtn, true) : $rtn;

	return $rtn;
}