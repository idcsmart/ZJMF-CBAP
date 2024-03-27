<?php
namespace app\common\lib;

/**
 * @title 三方登录基类,三方登录需要继承该类
 * @use app\common\lib\Oauth
 */
class Oauth extends Plugin{

	/**
	 * 时间 2024-02-02
	 * @title 默认安装成功
	 * @desc  三方登录无需实现安装方法
	 * @author hh
	 * @version v1
	 * @return  bool
	 */
	public function install()
	{
		return true;
	}

	/**
	 * 时间 2024-02-02
	 * @title 默认卸载成功
	 * @desc  三方登录无需实现卸载方法
	 * @author hh
	 * @version v1
	 * @return  bool
	 */
	public function uninstall()
	{
		return true;
	}
}