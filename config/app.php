<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
	// 应用地址
	'app_host'         => env('app.host', 'aa'),
	// 应用的命名空间
	'app_namespace'    => '',
	// 是否启用路由
	'with_route'       => true,
	// 默认应用
	'default_app'      => 'index',
	// 默认时区
	'default_timezone' => 'Asia/Shanghai',

	// 应用映射（自动多应用模式有效）
	'app_map'          => [],
	// 域名绑定（自动多应用模式有效）
	'domain_bind'      => [],
	// 禁止URL访问的应用列表（自动多应用模式有效）
	'deny_app_list'    => [],

	// 异常页面的模板文件
	'exception_tmpl'   => app()->getThinkPath() . 'tpl/think_exception.tpl',

	// 错误显示信息,非调试模式有效
	'error_message'    => '页面错误！请稍后再试～',
	// 显示错误信息
	'show_error_msg'   => true,

	// +---------------------------
	// | 自定义
	// +---------------------------

	/**
	 * 是否开启双因子认证（GA）
	 * @version 2020-05-10
	 */
	'use_2fa_token' => false,

	/**
	 * 又拍云文件储存域名
	 * @version 2020-05-24
	 */
	'upyun_domain' => env('app.UPYUN_DOMAIN', ''),

	/**
	 * 登录用户信息
	 * @version 2020-05-30
	 */
	'user_info' => [
		'userName' => env('user.NAME', ''),
		'nickName' => env('user.NICK_NAME', ''),
		'salt' => env('user.SALT', ''),
		'pin' => env('user.PIN', ''),
		'tokenKey' => env('user.TOKEN_KEY', '')
	],
];
