<?php

/**
 * @name 小丁健康日记平台-C-枚举
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-04-28
 * @version 2020-05-27
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

		$query = EnumType::where('is_show', 1)
			->where('is_delete', 0)
			->select();

		if (count($query) >= 1) return packApiData(200, 'success', ['data' => $query]);
		else return packApiData(404, 'Enum type not found', $query, '查询枚举类型失败');
	}


	public function getList()
	{
		checkToken(inputGet('token', 0, 1));

		$type = inputGet('type', 0, 1);

		$query = EnumList::where('type_id', $type)
			->where('is_delete', 0)
			->select();

		if (count($query) >= 1) return packApiData(200, 'success', ['data' => $query]);
		else return packApiData(404, 'Enum not found', $query, '查询枚举值失败');
	}
}
