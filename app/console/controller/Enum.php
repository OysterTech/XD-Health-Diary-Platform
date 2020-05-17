<?php

/**
 * @name 个人健康日记平台-C-后台-枚举
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-05-09
 * @version 2020-05-17
 */

namespace app\console\controller;

use app\BaseController;
use app\common\model\EnumList;
use app\common\model\EnumType;

class Enum extends BaseController
{
	public function index()
	{
		return view('/enum/index', ['token' => createToken()]);
	}


	public function getTypeListForZtree()
	{
		checkToken(inputGet('token', 0, 1));

		$query = EnumType::select();

		if (count($query) >= 1) {
			$nodes = [];

			foreach ($query as $key => $info) {
				$nodes[$key]['id'] = $info['id'];
				$nodes[$key]['pId'] = 0;
				$nodes[$key]['name'] = $info['name'];
			}

			return packApiData(200, 'success', ['nodes' => $nodes]);
		} else {
			return packApiData(404, 'Enum type not found', $query);
		}
	}


	public function addType()
	{
		$name = inputPost('name', 0, 1);

		$query = EnumType::create(['name' => $name]);

		if ($query->id > 0) return packApiData(200, 'success');
		else return packApiData(500, 'Database error', $query, '新增枚举类型失败');
	}


	public function addEnum()
	{
		$typeId = inputPost('typeId', 0, 1);
		$value = inputPost('value', 0, 1);

		$query = EnumList::create([
			'type_id' => $typeId,
			'value' => $value
		]);

		if ($query->id > 0) return packApiData(200, 'success');
		else return packApiData(500, 'Database error', $query, '新增枚举值失败');
	}
}
