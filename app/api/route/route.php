<?php

/**
 * @name 个人健康日记平台-R-接口路由
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-05-01
 * @version 2020-05-05
 */

use think\facade\Route;

Route::group('enum', function () {
	Route::rule('typeList', 'Enum/getTypeList');
	Route::rule('list', 'Enum/getList');
});

Route::group('file', function () {
	Route::rule('list', 'File/getList');
});

Route::group('activity', function () {
	Route::rule('dayCount', 'Activity/dayCount');
	Route::rule('list', 'Activity/getList');
	Route::post('create', 'Activity/toCreate');
	Route::delete('delete', 'Activity/toDelete');
});
