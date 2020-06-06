<?php

/**
 * @name 小丁健康日记平台-C-后台-接口日志
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-05-30
 * @version 2020-06-06
 */

namespace app\console\controller\log;

use app\BaseController;
use app\common\model\ApiRequestLog;
use app\common\model\Config as AppConf;

class Api extends BaseController
{
	public function index()
	{
		return view('/log/api/index', ['token' => createToken('console')]);
	}


	public function getList()
	{
		checkToken(inputPost('token', 0, 1), 'console');

		$page = inputPost('page', 1, 1) ?: 1;
		$perPage = inputPost('perPage', 1, 1) ?: 25;
		$filterData = inputPost('filterData', 1, 1) ?: [];

		$countQuery = new ApiRequestLog;
		$query = ApiRequestLog::field('request_id,path,code,ip,create_time');

		foreach ($filterData as $key => $value) {
			if ($key === 'idLast5') {
				// 根据ID最后5位查询，方便用户快查
				$countQuery = $countQuery->whereRaw('RIGHT(request_id,5)=:id', ['id' => $value]);
				$query = $query->whereRaw('RIGHT(request_id,5)=:id', ['id' => $value]);
			} else {
				// 其他条件
				$countQuery = $countQuery->where($key, $value);
				$query = $query->where($key, $value);
			}
		}

		$countQuery = $countQuery->count('id');
		$query = $query->order('id', 'desc')
			->limit(($page - 1) * $perPage, $perPage)
			->select();

		return packApiData(200, 'success', ['total' => $countQuery, 'list' => $query], '', false);
	}


	public function getDetail()
	{
		checkToken(inputGet('token', 0, 1), 'console');

		$id = inputGet('reqId', 0, 1);
		$type = inputGet('type', 0, 1);

		$query = ApiRequestLog::where('request_id', $id);

		if ($type == 'resData') {
			$query = $query->field('res_data');
		} else {
			$query = $query->withoutField('res_data');
		}

		$query = $query->find();
		return packApiData(200, 'success', ['detail' => $query], '', false);
	}


	public function toTruncate()
	{
		checkToken(inputPost('token', 0, 1), 'console');

		$pin = inputPost('password', 0, 1);

		// 先验证用户密码
		$userInfo = AppConf::get('userInfo');
		$userInfo = json_decode($userInfo, true);
		$userName = $userInfo['name'];
		$salt = $userInfo['salt'];

		if (sha1($salt . md5($userName . $pin) . $pin) === $userInfo['pin']) {
			\think\facade\Db::query('TRUNCATE api_request_log');
			return packApiData(200, 'success');
		} else {
			return packApiData(403, 'Invalid password', [], '密码错误，请重新输入');
		}
	}
}
