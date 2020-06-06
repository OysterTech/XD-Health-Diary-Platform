<?php

/**
 * @name 小丁健康日记平台-M-应用配置
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-06-04
 * @version 2020-06-06
 */

namespace app\common\model;

use think\Model;

class Config extends Model
{
	public static function get(string $name = '')
	{
		return self::where('name', $name)->find()['value'];
	}
}
