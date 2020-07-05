<?php

/**
 * @name 小丁健康日记平台-R-接口路由
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-05-01
 * @version 2020-07-04
 */

use think\facade\Route;

Route::group('enum', function () {
	Route::get('typeList', 'Enum/getTypeList');
	Route::get('list', 'Enum/getList');
});

Route::group('file', function () {
	Route::get('list', 'File/getList');
});

Route::group('activity', function () {
	Route::get('dayCount', 'Activity/dayCount');
	Route::get('list', 'Activity/getList');
	Route::post('create', 'Activity/toCreate');
	Route::delete('delete', 'Activity/toDelete');
});

Route::group('statistic', function () {
	Route::get('getLatestList', 'Statistic/getLatestList');
	Route::get('getTotalByEnum', 'Statistic/getTotalByEnum');
	Route::get('getListByEnumId', 'Statistic/getListByEnumId');
});
