<?php

/**
 * @name 个人健康日记平台-C-首页
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-04-24
 * @version 2020-05-30
 */

namespace app\index\controller;

use app\BaseController;
use app\common\controller\GoogleAuth;
use think\facade\Config;
use think\facade\Session;

class Index extends BaseController
{
	public function index()
	{
		die('health ok!');
	}


	public function main()
	{
		if (!Session::has('userInfo')) {
			return redirect('/index/logout');
		}

		return view('index', [
			'staticPath' => Config::get('app.app_host'),
			'token' => createToken()
		]);
	}


	public function login()
	{
		return view('login', ['staticPath' => Config::get('app.app_host')]);
	}


	public function logout()
	{
		Session::clear();
		return redirect('/index/login');
	}


	public function toLogin()
	{
		$userName = inputPost('userName', 0, 1);
		$pin = inputPost('userPin', 0, 1);

		$userInfo = Config::get('app.user_info');
		$salt = $userInfo['salt'];

		if (sha1($salt . md5($userName . $pin) . $pin) === $userInfo['pin']) {
			if (Config::get('app.use_2fa_token') === true) {
				$ticket = sha1(time() . $pin);

				Session::set('loginTicket', $ticket);
				Session::set('loginUserInfo', $userInfo);

				return packApiData(4031, 'need two factor auth', ['ticket' => $ticket], '请输入二步验证TOKEN', false);
			} else {
				$this->setLoginState($userInfo);
				return packApiData(200, 'success', ['url' => '/index/main'], '', false);
			}
		} else {
			return packApiData(403, 'Invalid userName or password', [], '用户名或密码错误');
		}
	}


	public function checkToken()
	{
		$token = inputPost('token', 0, 1);
		$ticket = inputPost('ticket', 0, 1);

		if ($ticket === Session::get('loginTicket')) {
			$userInfo = Session::get('loginUserInfo');
			$tokenKey = $userInfo['tokenKey'];
		} else {
			return packApiData(4001, 'Invalid ticket', [], '登录状态已失效！<br>请重新输入密码登录，再进行二步验证', false);
		}

		$ga = new GoogleAuth;
		$checkResult = $ga->verifyCode($tokenKey, $token);

		if ($checkResult) {
			$this->setLoginState($userInfo);

			return packApiData(200, 'success', ['url' => '/index/main'], '', false);
		} else {
			return packApiData(403, 'Invalid token', [], 'Token无效，请重新输入', false);
		}
	}


	private function setLoginState($userInfo = '')
	{
		Session::delete('loginTicket');
		Session::delete('loginUserInfo');
		Session::set('userInfo', $userInfo);

		return true;
	}


	public function redirect()
	{
		$key = inputGet('key', 0);

		if ($key === 'testkey') {
			die(header('location: https://baidu.com'));
		} else {
			return view('common@/error', ['status' => 'error', 'errorTips' => '非法外链key<br>如有疑问，请联系技术支持']);
		}
	}
}
