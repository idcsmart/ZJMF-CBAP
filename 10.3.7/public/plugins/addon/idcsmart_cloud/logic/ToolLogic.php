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

	/**
	 * 时间 2022-06-24
	 * @title 云密码格式验证
	 * @desc 云密码格式验证
	 * @author hh
	 * @version v1
	 * @param   string password - 密码
	 * @return  bool
	 */
	public static function checkPassword($password){
		$res = false;
		if(preg_match('/^[a-zA-Z0-9!\@#$&*,.:;~\-+=_?|<>(){}\[\]][a-zA-Z0-9!\@#$&*,.:;~\-+=_?\/|<>(){}\[\]]{5,64}$/', $password) && preg_match('/[a-z]/', $password) && preg_match('/[A-Z]/', $password) && preg_match('/\d/', $password)){
			$res = true;
		}
		return $res;
	}

	/**
	 * 时间 2022-07-01
	 * @title 掩码长度转具体子网掩码 
	 * @desc 掩码长度转具体子网掩码
	 * @author hh
	 * @version v1
	 * @param   integer $num 掩码长度
	 */
	public static function numToSub($num=0){
	    $e=floor($num/8);
	    if($e==3){
	        $subnetmask='255.255.255.'.(pow(2,8)-pow(2,(8-($num%8))));
	    }elseif($e==2){
	        $subnetmask='255.255.'.(pow(2,8)-pow(2,(8-($num%8)))).'.0';
	    }elseif($e==1){
	        $subnetmask='255.'.(pow(2,8)-pow(2,(8-($num%8)))).'.0.0';
	    }elseif($e==0){
	        $subnetmask=(pow(2,8)-pow(2,(8-($num%8)))).'.0.0.0';
	    }else{
	        $subnetmask='255.255.255.255';
	    }
	    return $subnetmask;
	}

	/**
	 * 时间 2022-07-01
	 * @title 获取子网掩码长度
	 * @desc 获取子网掩码长度
	 * @author hh
	 * @version v1
	 * @param   string $subnetmask 子网掩码
	 */
	public static function subToNum($subnetmask=''){
	    $mask=explode(".",$subnetmask);
	    foreach($mask AS $k=>$v){
	        for($i=8;$i>=0;$i--){
	            if($v==(pow(2,8)-pow(2,(8-$i)))){
	                $num+=$i;
	                break;
	            }
	        }
	    }
	    return $num;
	}

	/**
	 * 时间 2022-07-01
	 * @title 单位转换
	 * @desc 单位转换
	 * @author hh
	 * @version v1
	 * @param   float   $data      要转换的数据
	 * @param   integer $decimal   进制
	 * @param   string  $to_unit   目标单位
	 * @param   string  $now_unit  当前单位
	 * @param   integer $point_num 保留位数
	 * @param   boolean $suffix    是否追加单位后缀
	 * @param   boolean $up        单位向上/单位向下
	 * @return  float|string
	 */
	public static function unitChange($data = '', $decimal = 1024, $to_unit = '', $now_unit = 'B', $point_num = 2, $suffix = true, $up = true){
	    $unit = ['B','KB','MB','GB','TB','PB'];
	    $count = array_search($now_unit, $unit) ?: 0;  // 当前单位
	    $r = array_search($to_unit, $unit);			   // 目标单位
	    if($r !== false){
	    	// 有目标单位,自动转换
			$count = $r - $count;
        	$data = $data/pow($decimal, $count);
	    }else{
	    	// 没有目标单位
	    	if($up){
	    		// 向上转换
	    		while($data >= $decimal){
		            $data /= $decimal;
		            $count++;
		            if($count>=5){
		                break;
		            }
		        }
	    	}else{
	    		while($data < 1){
	    			$data *= $decimal;
	    			$count--;
	    			if($count <= 0){
	    				break;
	    			}
	    		}
	    	}
	    }
	    if($suffix){
	        return round($data,$point_num).$unit[$count];
	    }else{
	        return round($data,$point_num);
	    }
	}

	public static function createEditLog($old, $new, $des, $no_detail = [], $after_update_value = []){
	    $res = '';
	    foreach($new as $k=>$v){
	        // 只记录修改后的值,批量修改
	        if(in_array($k, $after_update_value)){
	            $res .= lang_plugins('log_modify', ['{name}'=>$des[$k], '{value}'=>$v]);
	            continue;
	        }
	        $old[$k] = $old[$k] ?? '';
	        if(isset($des[$k]) && $v != $old[$k] && !empty($des[$k])){
	            // 敏感数据隐藏
	            if(in_array($k, $no_detail)){
	                $res .= ','.$des[$k].lang_plugins('change');
	                continue;
	            }
	            $old[$k] = $old[$k] === '' ? lang_plugins('null') : $old[$k];
	            $v = $v === '' || $v === null ? lang_plugins('null') : $v;
	            $res .= lang_plugins('log_common_modify', [
	                '{name}'=>$des[$k],
	                '{old}'=>$old[$k],
	                '{new}'=>$v,
	            ]);
	        }
	    }
	    return $res;
	}




}
