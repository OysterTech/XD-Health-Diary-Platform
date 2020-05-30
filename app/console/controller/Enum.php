<?php

/**
 * @name 个人健康日记平台-C-后台-枚举
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-05-09
 * @version 2020-05-29
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


	public function delete()
	{
		checkToken(inputGet('token', 0, 1));

		$deleteInfo = inputPost('info', 0, 1);
		$type = $deleteInfo['type'];
		$id = $deleteInfo['id'];

		// 根据类型来删除
		if ($type == 'value') {
			$query = EnumList::destroy($id);

			if ($query === true) return packApiData(200, 'success');
			else return packApiData(5001, 'Database error', $query, '删除枚举值失败');
		} elseif ($type == 'type') {
			// 先检查是否有子类型
			$checkChildQuery = EnumType::where('pid', $id)
				->select();

			if (count($checkChildQuery) >= 1) return packApiData(4002, 'This type have child type', ['child' => $checkChildQuery], '当前类型含有子类型，请先清空子类型再删除父类型');

			$query = EnumType::destroy($id);

			if ($query === true) return packApiData(200, 'success');
			else return packApiData(5002, 'Database error', $query, '删除枚举父类型失败');
		} else {
			return packApiData(4001, 'Invalid delete type', [], '删除操作类型无效');
		}
	}


	public function getTypeListForZtree()
	{
		checkToken(inputGet('token', 0, 1));

		$query = EnumType::where('is_delete', 0)
			->select();

		if (count($query) >= 1) {
			$nodes = [];

			foreach ($query as $key => $info) {
				$nodes[$key]['id'] = $info['id'];
				$nodes[$key]['pId'] = $info['pid'];
				$nodes[$key]['name'] = $info['name'];
			}

			return packApiData(200, 'success', ['nodes' => $nodes]);
		} else {
			return packApiData(404, 'Enum type not found', $query);
		}
	}


	public function update()
	{
		$id = inputPost('id', 0, 1);
		$type = inputPost('type', 0, 1);
		$value = inputPost('value', 0, 1);

		if ($type == 'type') {
			$query = EnumType::update([
				'name' => $value
			], ['id' => $id]);

			if ($query['name']) return packApiData(200, 'success');
			else return packApiData(5001, 'Database error', [$query], '修改枚举父类型失败');
		} elseif ($type == 'value') {
			$query = EnumList::update([
				'value' => $value
			], ['id' => $id]);

			if ($query['value']) return packApiData(200, 'success');
			else return packApiData(5001, 'Database error', [$query], '修改枚举值失败');
		} else {
			return packApiData(4001, 'Invalid update type', [], '修改操作类型无效');
		}
	}


	public function changeIsShow()
	{
		$id = inputPost('id', 0, 1);
		$isShow = inputPost('isShow', 0, 1);

		$query = EnumType::update([
			'is_show' => $isShow
		], ['id' => $id]);

		if ($query['is_show']) return packApiData(200, 'success');
		else return packApiData(500, 'Database error', [$query], '修改枚举父类型的显示状态失败');
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
