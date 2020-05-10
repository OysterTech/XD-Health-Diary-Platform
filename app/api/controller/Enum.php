<?php

/**
 * @name 个人健康日记平台-C-枚举
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-04-28
 * @version 2020-05-10
 */

namespace app\api\controller;

use app\BaseController;
use app\common\model\EnumList;
use app\common\model\EnumType;

class Enum extends BaseController
{
	public function getTypeList()
	{
		checkToken(inputGet('token', 0, 1));

		$query = EnumType::select();

		if (count($query) >= 1) return packApiData(200, 'success', ['data' => $query]);
		else return packApiData(500, 'Database error', $query);
	}


	public function getList()
	{
		checkToken(inputGet('token', 0, 1));

		$type = inputGet('type', 0, 1);

		$query = EnumList::where('type', $type)
			->select();

		if (count($query) >= 1) return packApiData(200, 'success', ['data' => $query]);
		else return packApiData(500, 'Database error', $query);
	}
}
