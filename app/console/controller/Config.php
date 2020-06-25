<?php

/**
 * @name 小丁健康日记平台-C-后台-配置
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-06-21
 * @version 2020-06-25
 */

namespace app\console\controller;

use app\BaseController;
use app\common\model\Config as AppConf;

class Config extends BaseController
{
	public function index()
	{
		return view('/config/index', ['token' => createToken('console')]);
	}


	public function getList()
	{
		checkToken(inputPost('token', 0, 1), 'console');

		$page = inputPost('page', 1, 1) ?: 1;
		$perPage = inputPost('perPage', 1, 1) ?: 25;
		$filterData = inputPost('filterData', 1, 1) ?: [];

		$countQuery = new AppConf;
		$query = AppConf::field('name,chinese_name,value,create_time,update_time,ip');

		foreach ($filterData as $key => $value) {
			if ($key !== 'create_time' && $key !== 'update_time') {
				$countQuery = $countQuery->where($key, $value);
				$query = $query->where($key, $value);
			}
		}

		$countQuery = $countQuery->count('id');
		$query = $query->order('id', 'desc')
			->limit(($page - 1) * $perPage, $perPage)
			->select();

		return packApiData(200, 'success', ['total' => $countQuery, 'list' => $query]);
	}


	public function toCU()
	{
		checkToken(inputPost('token', 0, 1), 'console');

		$cuInfo = inputPost('cuInfo', 0, 1);
		$cuType = $cuInfo['operateType'];
		$name = $cuInfo['name'];
		$chineseName = $cuInfo['chineseName'];
		$value = $cuInfo['value'];

		if ($value == '') return packApiData(4001, 'Value cannot be null', [], '配置值不能为空');

		if ($cuType == 'update') {
			$query = AppConf::update(['value' => $value, 'chinese_name' => $chineseName], ['name' => $name]);

			if (!$query->isEmpty()) return packApiData(200, 'success');
			else return packApiData(500, 'Database error', ['error' => $query]);
		} elseif ($cuType == 'create') {
			$query = AppConf::create([
				'name' => $name,
				'value' => $value
			]);
			
			if ($query->id > 0) return packApiData(200, 'success');
			else return packApiData(500, 'Database error', ['error' => $query]);
		} else {
			return packApiData(5001, 'Invalid cu type', [], '非法操作行为');
		}
	}
}
