<?php

/**
 * @name 小丁健康日记平台-C-首页
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-04-24
 * @version 2020-06-25
 */

namespace app\index\controller;

use app\BaseController;
use app\common\controller\GoogleAuth;
use app\common\model\Link;
use app\common\model\Config as AppConf;
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
		if (!Session::has('userName')) {
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

		$userInfo = AppConf::get('userInfo');
		$userInfo = json_decode($userInfo, true);

		if (checkPassword($pin, $userName)) {
			if (AppConf::get('use2faToken') === '1') {
				$ticket = sha1(time() . $pin);

				Session::set('loginTicket', $ticket);
				Session::set('loginUserInfo', $userInfo);

				return packApiData(4031, 'need two factor auth', ['ticket' => $ticket], '请输入二步验证TOKEN', false);
			} else {
				$this->setLoginState($userInfo['name'], $userInfo['nickName']);
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
			$this->setLoginState($userInfo['name'], $userInfo['nickName']);

			return packApiData(200, 'success', ['url' => '/index/main'], '', false);
		} else {
			return packApiData(403, 'Invalid token', [], 'Token无效，请重新输入', false);
		}
	}


	private function setLoginState($userName = '', $nickName = '')
	{
		Session::delete('loginTicket');
		Session::delete('loginUserInfo');
		Session::set('userName', $userName);
		Session::set('nickName', $nickName);

		return true;
	}


	/**
	 * redirect 跳转外链
	 */
	public function redirect()
	{
		$key = inputGet('key', 1);

		if (strlen($key) != 40) {
			return view('common@/error', ['status' => 'error', 'errorTips' => '非法外链KEY<br>如有疑问，请联系技术支持']);
		}

		$query = Link::where('link_key', $key)
			->find();

		if ($query !== []) {
			Link::where('link_key', $key)
				->inc('count')
				->update();

			die(header('location: ' . $query['url']));
		} else {
			return view('common@/error', ['status' => 'error', 'errorTips' => '非法外链KEY<br>如有疑问，请联系技术支持']);
		}
	}
}
