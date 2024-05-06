<?php 
namespace server\idcsmart_common\logic;

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
		if(preg_match('/^[a-zA-Z0-9!\@#$&*,.:;~\-+=_?|<>(){}\[\]][a-zA-Z0-9!\@#$&*,.:;~\-+=_?\/|<>(){}\[\]]{6,64}$/', $password) && preg_match('/[a-z]/', $password) && preg_match('/[A-Z]/', $password) && preg_match('/\d/', $password)){
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

	/**
	 * 时间 2024-02-18
	 * @title 验证IP段格式
	 * @desc 验证IP段格式,如192.168.1.0/24,支持/8-/24
	 * @author hh
	 * @version v1
	 * @param   string $str - 验证的IP段 require
	 * @return  string ipsection - IP段
	 * @return  string first_ip - 起始IP
	 * @return  string last_ip - 结束IP
	 * @return  string prefix - 掩码长度
	 */
	public static function checkIps($str)
	{
	    if (strpos($str, '/') === false) {
	        return false;
	    }
	    $arr = explode('/', $str);
	    $ip = $arr[0];
	    if(!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
	        return false;
	    }
	    $prefix = $arr[1];
	    $ip_arr = explode('.', $ip);
	    if ($prefix < 32 && $prefix > 24) {
	        $count = pow(2, 32 - $prefix);
	        $in_arr = false;
	        for ($i = 0; $i <= 255; $i += $count) {
	            if ($i == $ip_arr[3]) {
	                $in_arr = true;
	                break;
	            }
	        }
	        if (!$in_arr) {
	            return false;
	        }
	        $ipsection = $ip;
	        $first_ip = $ip;
	        $last_ip = long2ip(ip2long($first_ip) + pow(2, 32 - $prefix) - 1);
	    } else if ($prefix == 24) {
	        if ($ip_arr[3] != 0) {
	            return false;
	        }
	        $ipsection = $ip;
	        $first_ip = $ip;
	        $last_ip = $ip_arr[0] . '.' . $ip_arr[1] . '.' . $ip_arr[2] . '.255';
	    } else if ($prefix < 24 && $prefix > 16) {
	        if ($ip_arr[3] != 0) {
	            return false;
	        }

	        $count = pow(2, 24 - $prefix);
	        $in_arr = false;
	        for ($i = 0; $i <= 255; $i += $count) {
	            if ($i == $ip_arr[2]) {
	                $in_arr = true;
	                break;
	            }
	        }
	        if (!$in_arr) {
	            return false;
	        }
	        $ipsection = $ip;
	        $first_ip = $ip;
	        $last_ip = long2ip(ip2long($first_ip) + pow(2, 32 - $prefix) - 1);
	    } else if ($prefix == 16) {
	        if ($ip_arr[2] != 0 || $ip_arr[3] != 0) {
	            return false;
	        }
	        $ipsection = $ip;
	        $first_ip = $ip;
	        $last_ip = $ip_arr[0] . '.' . $ip_arr[1] . '.255.255';
	    } else if ($prefix < 16 && $prefix > 8) {
	        if ($ip_arr[2] != 0 || $ip_arr[3] != 0) {
	            return false;
	        }

	        $count = pow(2, 16 - $prefix);
	        $in_arr = false;
	        for ($i = 0; $i <= 255; $i += $count) {
	            if ($i == $ip_arr[1]) {
	                $in_arr = true;
	                break;
	            }
	        }
	        if (!$in_arr) {
	            return false;
	        }
	        $ipsection = $ip;
	        $first_ip = $ip;
	        $last_ip = long2ip(ip2long($first_ip) + pow(2, 32 - $prefix) - 1);
	    } else if ($prefix == 8) {
	        if ($ip_arr[1] != 0 || $ip_arr[2] != 0 || $ip_arr[3] != 0) {
	            return false;
	        }
	        $ipsection = $ip;
	        $first_ip = $ip;
	        $last_ip = $ip_arr[0] . '.255.255.255';
	    } else {
	        return false;
	    }
	    return ['ipsection' => $ipsection, 'first_ip' => $first_ip, 'last_ip' => $last_ip, 'prefix' => $prefix];
	}

	/**
	 * 时间 2024-02-18
	 * @title 验证VPCIP段
	 * @desc 验证IP段格式,是否属于他们或他们子网192.168.0.0/16、172.16.0.0/12、10.0.0.0/8
	 * @author hh
	 * @version v1
	 * @param   string $str - IP段 require
	 * @return  bool
	 */
	public static function checkVpcIps($str){
	    $res = self::checkIps($str);
	    if(!$res){
	        return false;
	    }
	    if($res['prefix'] > 28 || $res['prefix'] < 16){
	        return false;
	    }
	    // 是否属于他们或他们子网 192.168.0.0/16、172.16.0.0/12、10.0.0.0/8
	    $range = [
	        [
	            'start'=>ip2long('192.168.0.0'),
	            'end'=>ip2long('192.168.255.255')
	        ],
	        [
	            'start'=>ip2long('172.16.0.0'),
	            'end'=>ip2long('172.31.255.255'),
	        ],
	        [
	            'start'=>ip2long('10.0.0.0'),
	            'end'=>ip2long('10.255.255.255')
	        ]
	    ];
	    $first_ip = ip2long($res['first_ip']);
	    $last_ip = ip2long($res['last_ip']);

	    $result = false;
	    foreach($range as $v){
	        if($first_ip >= $v['start'] && $last_ip <= $v['end']){
	            $result = true;
	            break;
	        }
	    }
	    return $result;
	}

	/**
	 * 时间 2024-02-18
	 * @title 解压到reserver
	 * @desc  解压到reserver
	 * @author hh
	 * @version v1
	 */
	public static function unzipToReserver(){
		$zip = WEB_ROOT . 'plugins/server/idcsmart_common/data/abc.zip';
		$target_dir = WEB_ROOT . 'plugins/reserver/idcsmart_common/';
		if(file_exists($target_dir.'route.php')){
			return true;
		}
		$ZipArchive = new \ZipArchive();
        $res = $ZipArchive->open($zip);
        if ( $res === true) {
            if (!is_dir($target_dir)){
                mkdir($target_dir, 0755, true);
            }
            $ZipArchive->extractTo($target_dir);
            //关闭
            $ZipArchive->close();
        }
        return $res;
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
