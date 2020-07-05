<?php

/**
 * @name 小丁健康日记平台-C-统计
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-05-01
 * @version 2020-07-04
 */

namespace app\api\controller;

use app\BaseController;
use app\common\model\ActivityList;
use app\common\model\ActivityEnum;

class Statistic extends BaseController
{
	public function getLatestList()
	{
		checkToken(inputGet('token', 0, 1));

		$year = inputGet('year', 0, 1);
		$month = inputGet('month', 0, 1);

		$query = ActivityList::where('year', $year)
			->where('month', $month)
			->field('time,day')
			->order('time', 'desc')
			->limit(5)
			->select();

		if (count($query) > 0) return packApiData(200, 'success', $query, '', false);
		else return packApiData(404, 'Data not found', [], '最晚记录数据统计失败');
	}


	public function getTotalByEnum()
	{
		checkToken(inputGet('token', 0, 1));

		$year = inputGet('year', 0, 1);
		$month = inputGet('month', 0, 1);
		$type = inputGet('type', 0, 1);

		$query = \think\facade\Db::query(
			'SELECT e.value AS name,COUNT(acten.id) AS value,CONCAT(:showYear,"|",:showMonth,"|",e.id) AS id FROM activity_enum AS acten INNER JOIN (SELECT * FROM activity_list WHERE year=:year AND month=:month) AS act ON acten.activity_id=act.id LEFT JOIN enum_list AS e ON acten.enum_id=e.id WHERE e.type_id=:enumType GROUP BY e.value',
			['showYear' => $year, 'showMonth' => $month, 'year' => $year, 'month' => $month, 'enumType' => $type]
		);

		if (count($query) > 0) return packApiData(200, 'success', ['data' => $query], '', false);
		else return packApiData(404, 'Data not found', [], '根据枚举值统计数据失败');
	}


	public function getListByEnumId()
	{
		checkToken(inputGet('token', 0, 1));

		$id = explode('|', inputGet('id', 0, 1));

		if (count($id) !== 3 || strlen($id[0]) !== 4 || (int) $id[1] > 12 || (int) $id[1] < 1 || (int) $id[2] <= 0) {
			return packApiData(4001, 'Invalid id', [], '无效的枚举ID');
		} else {
			$year = $id[0];
			$month = $id[1];
			$enumId = $id[2];
		}

		$query = ActivityList::alias('act')
			->field('act.id AS activity_id,act.type,act.day,act.time,act.content,act.img,act.extra_param,act.create_time,act.update_time')
			->where('act.year', $year)
			->where('act.month', $month)
			->join('activity_enum acten', 'acten.activity_id=act.id')
			->where('acten.enum_id', $enumId)
			->order('day')
			->select()
			->toArray();

		// 显示有多少份文件
		foreach ($query as $key => $data) {
			$query[$key]['img'] = ($data['img'] != '[""]') ? count(json_decode($data['img'])) : 0;
		}

		return packApiData(200, 'success', ['data' => $query]);
	}
}
