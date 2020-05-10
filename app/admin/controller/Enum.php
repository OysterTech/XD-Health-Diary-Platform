<?php
namespace app\admin\controller;

use app\BaseController;
use app\common\model\EnumType;
use app\common\model\EnumList;

class Enum extends BaseController
{
	public function index()
	{
		return view('home');
	}
	
	
	public function getTypeList()
	{
		EnumType::select();
		return view('home');
	}
	
	
	public function getList()
	{
		EnumList::select();
		return view('home');
	}
}
