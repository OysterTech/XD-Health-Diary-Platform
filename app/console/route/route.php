<?php

/**
 * @name 小丁健康日记平台-R-控制台路由
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-05-24
 * @version 2020-06-06
 */

use think\facade\Route;

Route::rule('user/index', 'User/index');

Route::group('enum', function () {
	Route::rule('index', 'Enum/index');
	Route::rule('list', 'Enum/getList');
	Route::post('delete', 'Enum/delete');
});

Route::group('log/api', function () {
	Route::rule('index', 'log.Api/index');
	Route::rule('list', 'log.Api/getList');
	Route::rule('detail', 'log.Api/getDetail');
	Route::post('truncate', 'log.Api/toTruncate');
});

Route::group('log/file', function () {
	Route::rule('index', 'log.File/index');
	Route::rule('list', 'log.File/getList');
});
