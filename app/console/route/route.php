<?php

/**
 * @name 个人健康日记平台-R-控制台路由
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-05-24
 * @version 2020-05-31
 */

use think\facade\Route;

Route::rule('enum/index', 'Enum/index');
Route::rule('user/index', 'User/index');

Route::group('log/api', function () {
	Route::rule('index', 'log.Api/index');
	Route::rule('list', 'log.Api/getList');
	Route::rule('detail', 'log.Api/getDetail');
});

Route::group('log/file', function () {
	Route::rule('index', 'log.File/index');
	Route::rule('list', 'log.File/getList');
});

Route::post('enum/delete', 'Enum/delete');
