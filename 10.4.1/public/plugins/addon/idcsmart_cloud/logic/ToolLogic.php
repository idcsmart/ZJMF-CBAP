<?php 
namespace addon\idcsmart_cloud\logic;

class ToolLogic
{
	/**
	 * 时间 2022-06-24
	 * @title 格式化参数
	 * @desc 格式化参数
	 * @author hh
	 * @version v1
	 * @param   string param - 要格式的参数
	 * @return  array
	 */
	public static function formatParam($param){
		$res = [];

		if(!empty($param)){
			$param = str_replace("\r", '', $param);
			$param = explode("\n", $param);

			foreach($param as $v){
				$v = explode('=', $v);
				$key = array_shift($v);
				$val = implode('=', $v);
				$res[$key] = $val;
			}
		}
		return $res;
	}
}
