<?php

/**
 * @name 小丁健康日记平台-R-主页路由
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-05-01
 * @version 2020-05-09
 */

use think\facade\Route;

Route::rule('main', 'Index/main');
Route::rule('login', 'Index/login');
Route::rule('logout', 'Index/logout');
Route::get('redirect', 'Index/redirect');

Route::post('toLogin', 'Index/toLogin');
Route::post('checkToken', 'Index/checkToken');
