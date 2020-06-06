<?php

/**
 * @name 小丁健康日记平台-C-附件
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-05-01
 * @version 2020-05-05
 */

namespace app\api\controller;

use app\BaseController;
use app\common\model\ActivityList;
use app\common\model\SeeFileLog;
use Upyun\Upyun;
use Upyun\Config;

class File extends BaseController
{
	public function toUpload()
	{
		$serviceConfig = new Config('image-xie-xshgzs', 'xie', 'y7f3wrzgNUL1dxJJwdt5DhjjF5WP8FIw');
		$client = new Upyun($serviceConfig);

		$uploadFile = $_FILES['file'];
		$file = fopen($uploadFile['tmp_name'], 'r');
		$res = $client->write('/' . date('Y/m/') . $uploadFile['name'], $file);

		if (isset($res['x-upyun-content-length']) && $res['x-upyun-content-length'] == $uploadFile['size']) {
			return packApiData(200, 'success', ['url' => 'https://cdn.xie.xshgzs.com/' . date('Y/m/') . $uploadFile['name']]);
		} else {
			return packApiData(500, 'server error', ['file' => $uploadFile, 'uploadCloud' => $res]);
		}
	}

	public function getList()
	{
		$id = inputPost('id', 0, 1);
		$u = \think\facade\Session::get('userId');

		// 记录
		$addLogQuery = SeeFileLog::create([
			'user_id' => \think\facade\Session::get('userId'),
			'activity_id' => $id,
			'ip'=>getIP()
		]);
		if ($addLogQuery->id < 1) return packApiData(5001, 'Failed to record seeing behavior', [], '行为记录失败');

		// 获取图片url
		$query = ActivityList::where('id', $id)->find();
		if ($query !== null) $list = json_decode($query->img, true);
		else return packApiData(5002, 'Data not found', [], '文件查询失败');

		// 生成又拍云token
		$upyunKey = 'mX2iTCWyrbOhK15xtdDEugj9HvMslQkU';
		$expireTime = time() + 600; // 10分钟有效
		foreach ($list as $num => $url) {
			$name = substr($url, 26);
			$sign = substr(md5($upyunKey . '&' . $expireTime . '&' . $name), 12, 8) . $expireTime;
			$list[$num] = $url . '?_upt=' . $sign;
		}

		return packApiData(200, 'success', ['list' => $list]);
	}
}
