<?php

/**
 * @name 个人健康日记平台-R-控制台路由
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-05-24
 * @version 2020-05-24
 */

use think\facade\Route;

Route::rule('enum/index', 'Enum/index');
Route::rule('user/index', 'User/index');
Route::rule('log/file/list', 'log.File/index');
Route::rule('log/api/list', 'log.Api/index');

Route::post('enum/delete','Enum/delete');