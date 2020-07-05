<?php

/**
 * @name 小丁健康日记平台-C-后台-枚举
 * @author Oyster Cheung <master@xshgzs.com>
 * @since 2020-05-09
 * @version 2020-06-25
 */

namespace app\console\controller;

use app\BaseController;
use app\common\model\ActivityEnum;
use app\common\model\EnumList;
use app\common\model\EnumType;
use think\facade\Db;
use think\facade\Session;

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
			// 先检查是否有活动已绑定此枚举值
			if (!Session::has('deleteEnum_noticeHaving')) {
				// 未曾检查过是否有活动绑定
				$checkBind = ActivityEnum::where('enum_id', $id)->count();

				if ($checkBind > 0) {
					// 如果有活动绑定，要求再次确认
					Session::set('deleteEnum_noticeHaving', 1);

					return packApiData(4002, 'Current enum has bound activity', ['total' => $checkBind], '当前枚举值已被[<font color="blue" style="font-size:23px;">' . $checkBind . '</font>]个活动绑定<hr>继续删除将同时删除<br>这部分活动的此枚举值<br>请您再次确认！');
				}
			} else {
				Session::delete('deleteEnum_noticeHaving');
				ActivityEnum::where('enum_id', $id)->delete();
			}

			$query = EnumList::destroy($id);

			if ($query === true) return packApiData(200, 'success');
			else return packApiData(5001, 'Database error', $query, '删除枚举值失败');
		} elseif ($type == 'type') {
			// 先检查是否有枚举子类型
			$checkChildType = EnumType::where('pid', $id)->count();

			if ($checkChildType > 0) return packApiData(4003, 'Current type have child type', ['child' => $checkChildType], '当前类型含有子类型，请先清空子类型再删除父类型');

			// 检查是否有枚举值
			if (Session::get('deleteEnumType_enum') === $id) {
				// 已经确认删除所有旗下枚举值
				Session::delete('deleteEnumType_enum');
				EnumList::where('type_id', $id)->delete();
			} else {
				$checkValue = EnumList::where('type_id', $id)->count();

				if ($checkValue > 0) {
					// 如果旗下有枚举值，检查是否有活动绑定
					if (Session::get('deleteEnumType_activityBind') === $id) {
						// 确认删除活动绑定关系
						Session::delete('deleteEnumType_activityBind');
						// 【TODO】mysql能不能在删除的时候join？不行就子查询
						Db::query('DELETE ae FROM activity_enum ae JOIN enum_list el ON el.id=ae.enum_id WHERE el.type_id=:id', ['id' => $id]);

						// 删完活动绑定关系，就要删枚举值了
						Session::set('deleteEnumType_enum', $id);
						return packApiData(4005, 'Current type has enum value', ['total' => $checkValue], '当前类型含有[<font color="blue" style="font-size:23px;">' . $checkValue . '</font>]个枚举值<hr>继续删除将同时删除这部分枚举值<br>请您再次确认！');
					} else {
						$checkActivity = ActivityEnum::alias('ae')
							->field('COUNT(*) total,el.value')
							->leftJoin('enum_list el', 'el.id=ae.enum_id')
							->where('el.type_id', $id)
							->group('el.value')
							->select();

						if (count($checkActivity) > 0) {
							// 有活动绑定，先提示删活动
							$tipsTableHtml = '';
							foreach ($checkActivity as $value) {
								$tipsTableHtml .= '<tr><th>' . $value['value'] . '</th><td>' . $value['total'] . '</td></tr>';
							}

							Session::set('deleteEnumType_activityBind', $id);
							return packApiData(4004, 'Current type has enum bound from activity', ['list' => $checkActivity], '当前类型含有以下枚举值被绑定<br>确认删除将解除所有绑定关系<hr><table class="table table-striped table-bordered table-hover" style="border-radius: 5px; border-collapse: separate;">' . $tipsTableHtml . '</table>');
						} else {
							// 没有活动绑定，提示删枚举值
							Session::set('deleteEnumType_enum', $id);
							return packApiData(4005, 'Current type has enum value', ['total' => $checkValue], '当前类型含有[<font color="blue" style="font-size:23px;">' . $checkValue . '</font>]个枚举值<hr>继续删除将同时删除这部分枚举值<br>请您再次确认！');
						}
					}
				}
				// 旗下没有枚举值，去删除枚举类型
			}

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

			if (!$query->isEmpty()) return packApiData(200, 'success');
			else return packApiData(5001, 'Database error', [$query], '修改枚举父类型失败');
		} elseif ($type == 'value') {
			$query = EnumList::update([
				'value' => $value
			], ['id' => $id]);

			if (!$query->isEmpty()) return packApiData(200, 'success');
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
