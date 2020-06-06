<?php

/**
 * @name 小丁健康日记平台-M-接口日志
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-05-09
 * @version 2020-05-30
 */

namespace app\common\model;

use think\Model;

class ApiRequestLog extends Model
{
	public function getResDataAttr($value)
	{
		return json_decode($value);
	}


	public function getReqDataAttr($value)
	{
		return json_decode($value);
	}
}
