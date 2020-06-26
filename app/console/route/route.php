<?php

/**
 * @name 小丁健康日记平台-R-控制台路由
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-05-24
 * @version 2020-06-26
 */

use think\facade\Route;

Route::get('user/index', 'User/index');

Route::group('enum', function () {
	Route::get('index', 'Enum/index');
	Route::get('list', 'Enum/getList');
	Route::post('delete', 'Enum/delete');
	Route::post('toCU', 'Enum/toCU');
});

Route::group('config', function () {
	Route::get('index', 'Config/index');
	Route::get('getValue', 'Config/getValue');
	Route::get('getRemark', 'Config/getRemark');
	Route::post('list', 'Config/getList');
	Route::post('toCU', 'Config/toCU');
	Route::post('toModifyName', 'Config/toModifyName');
	Route::post('toDelete', 'Config/toDelete');
});

Route::group('log/api', function () {
	Route::get('index', 'log.Api/index');
	Route::post('list', 'log.Api/getList');
	Route::get('detail', 'log.Api/getDetail');
	Route::post('truncate', 'log.Api/toTruncate');
});

Route::group('log/file', function () {
	Route::get('index', 'log.File/index');
	Route::post('list', 'log.File/getList');
});
