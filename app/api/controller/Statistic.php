<?php

/**
 * @name 个人健康日记平台-C-统计
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-05-01
 * @version 2020-05-17
 */

namespace app\api\controller;

use app\BaseController;
use app\common\model\ActivityList;

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

		if (count($query) > 0) return packApiData(200, 'success', $query);
		else return packApiData(404, 'Data not found', [], '最晚记录数据统计失败');
	}


	public function getTotalByEnum()
	{
		checkToken(inputGet('token', 0, 1));
		
		$year = inputGet('year', 0, 1);
		$month = inputGet('month', 0, 1);
		$type = inputGet('type', 0, 1);

		$query = \think\facade\Db::query('SELECT el.value AS name,COUNT(xen.key_id) AS value FROM activity_enum AS xen INNER JOIN (SELECT * FROM activity_list WHERE year=:year AND month=:month) AS xl ON xen.activity_id=xl.id LEFT JOIN enum_list AS el ON xen.enum_id=el.id WHERE el.type_id=:enumType GROUP BY el.value', ['year' => $year, 'month' => $month, 'enumType' => $type]);

		if (count($query) > 0) return packApiData(200, 'success', ['data' => $query]);
		else return packApiData(404, 'Data not found', [], '根据枚举值统计数据失败');
	}
}
