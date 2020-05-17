<?php

/**
 * @name 个人健康日记平台-C-后台
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-04-21
 * @version 2020-05-17
 */

namespace app\console\controller;

use app\BaseController;

class Index extends BaseController
{
    public function index()
    {
        return view('home');
    }
}
