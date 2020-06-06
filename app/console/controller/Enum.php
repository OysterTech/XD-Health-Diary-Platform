<?php

/**
 * @name 小丁健康日记平台-C-后台-枚举
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-05-09
 * @version 2020-06-06
 */

namespace app\console\controller;

use app\BaseController;
use app\common\model\EnumList;
use app\common\model\EnumType;

class Enum extends BaseController
{
	public function index()
	{
		return view('/enum/index', ['token' => createToken('console')]);
	}


	public function getTypeListForZtree()
	{
		checkToken(inputGet('token', 0, 1), 'console');

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


	public function getList()
	{
		checkToken(inputGet('token', 0, 1), 'console');

		$type = inputGet('type', 0, 1);

		$query = EnumList::where('type_id', $type)
			->where('is_delete', 0)
			->select();

		if (count($query) >= 1) return packApiData(200, 'success', ['data' => $query]);
		else return packApiData(404, 'Enum not found', $query, '查询枚举值失败');
	}


	public function delete()
	{
		checkToken(inputPost('token', 0, 1), 'console');

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


	public function changeIsShow()
	{
		checkToken(inputPost('token', 0, 1), 'console');

		$id = inputPost('id', 0, 1);
		$isShow = inputPost('isShow', 0, 1);

		$query = EnumType::update([
			'is_show' => $isShow
		], ['id' => $id]);

		if ($query['is_show']) return packApiData(200, 'success');
		else return packApiData(500, 'Database error', [$query], '修改枚举父类型的显示状态失败');
	}


	public function toCU()
	{
		checkToken(inputPost('token', 0, 1), 'console');

		$cuInfo = inputPost('cuInfo', 0, 1);
		$cuType = $cuInfo['operateType'];
		$type = $cuInfo['type'];
		$id = $cuInfo['id'];
		$value = $cuInfo['value'];

		if ($cuType == 'update') {
			return self::update($id, $type, $value);
		} elseif ($cuType == 'create') {
			return self::create($id, $type, $value);
		} else {
			return packApiData(5002, 'Invalid cu type', [], '非法操作行为');
		}
	}


	private static function update($id = 0, $type = '', $value = '')
	{
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


	private static function create($id = 0, $type = '', $value = '')
	{
		if ($type === 'type') {
			$query = EnumType::create([
				'name' => $value,
				'pid' => $id
			]);
		} elseif ($type === 'value') {
			$query = EnumList::create([
				'type_id' => $id,
				'value' => $value
			]);
		} else {
			return packApiData(4001, 'Invaild create type', [], '新增操作类型无效');
		}

		if ($query->id > 0) return packApiData(200, 'success');
		else return packApiData(500, 'Database error', $query, '新增枚举值失败');
	}
}
