<?php

/**
 * @name 个人健康日记平台-C-记录
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-04-28
 * @version 2020-05-01
 */

namespace app\api\controller;

use app\BaseController;
use app\common\model\ActivityList;
use app\common\model\ActivityEnum;

class Activity extends BaseController
{
	public function toCreate()
	{
		checkToken(inputPost('token', 0, 1));

		$year = inputPost('year', 0, 1);
		$month = inputPost('month', 0, 1);
		$day = inputPost('day', 0, 1);
		$time = inputPost('time', 0, 1);
		$content = inputPost('content', 0, 1);
		$imageUrl = inputPost('imageUrl', 0, 1);
		$enumId = inputPost('enumId', 1, 1);

		// 新增记录
		$query = ActivityList::create([
			'year' => $year,
			'month' => $month,
			'day' => $day,
			'time' => $time,
			'content' => $content,
			'img' => $imageUrl
		]);

		if ($query->id) $id = $query->id;
		else return packApiData(500, 'Database error', $query);

		// 加入枚举
		if (count($enumId) > 0) {
			$enumList = [];

			foreach ($enumId as $value) {
				array_push($enumList, ['activity_id' => $id, 'enum_id' => $value]);
			}

			$enumQuery = new ActivityEnum;
			$enumQuery->saveAll($enumList);
		}

		return packApiData(200, 'success');
	}


	public function dayCount()
	{
		checkToken(inputGet('token', 0, 1));

		$year = inputGet('year', 0, 1);
		$month = inputGet('month', 0, 1);

		$query = \think\facade\Db::query('SELECT day,COUNT(*) AS count FROM activity_list WHERE year=:year AND month=:month GROUP BY day', ['year' => $year, 'month' => $month]);

		return packApiData(200, 'success', $query);
	}


	public function getList()
	{
		checkToken(inputGet('token', 0, 1));

		$year = inputGet('year', 0, 1);
		$month = inputGet('month', 0, 1);
		$day = inputGet('day', 0, 1);

		$query =  ActivityList::where('year', $year)
			->where('month', $month)
			->where('day', $day)
			->select();

		// 显示有多少份文件
		foreach ($query as $key => $data) {
			$query[$key]['img'] = ($data['img'] != '[""]') ? count(json_decode($data['img'])) : 0;
		}

		if (count($query) >= 1) return packApiData(200, 'success', $query);
		else return packApiData(404, 'Activity not found');
	}


	public function toDelete()
	{
		checkToken(inputPost('token', 0, 1));

		$id = inputPost('id', 0, 1);

		$query = ActivityList::destroy($id);

		if ($query === true) return packApiData(200, 'success');
		else return packApiData(500, 'Database error', $query);
	}
}
