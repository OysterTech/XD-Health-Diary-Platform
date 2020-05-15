<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\model\EnumType;
use app\common\model\EnumList;

class Enum extends BaseController
{
	public function index()
	{
		return view('/index/home');
	}
	
	
	public function getTypeList()
	{
		EnumType::select();
		return view('/index/home');
	}
	
	
	public function getList()
	{
		EnumList::select();
		return view('/index/home');
	}
}
