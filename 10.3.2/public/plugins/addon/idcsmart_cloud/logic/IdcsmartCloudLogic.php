<?php
namespace addon\idcsmart_cloud\logic;

use addon\idcsmart_cloud\IdcsmartCloud;
use server\idcsmart_cloud\model\HostLinkModel as HLM1;
use server\common_cloud\model\HostLinkModel as HLM2;
use server\mf_cloud\model\HostLinkModel as HLM3;
use app\common\model\HostModel;
use think\facade\Cache;

class IdcsmartCloudLogic
{
	public function snapshotList()
	{
		$clientId = get_client_id();
        $params = [];
        $hostArr = [];

        if(class_exists("server\\idcsmart_cloud\\model\\HostLinkModel")){
            $hosts = HLM1::alias('hl')
                ->field('h.id,h.name,h.status,h.due_time,dc.country,dc.country_code,dc.city,dc.area,hl.ip,hl.rel_id,h.server_id,s.url,s.username,s.password')
                ->join('host h', 'hl.host_id=h.id')
                ->leftJoin('module_idcsmart_cloud_data_center dc', 'hl.module_idcsmart_cloud_data_center_id=dc.id')
                ->leftJoin('server s', 'h.server_id=s.id')
                ->where('h.client_id', $clientId)
                ->where('s.module', 'idcsmart_cloud')
                ->where('s.status', 1)
                ->where('hl.rel_id', '>', 0)
                ->group('h.id')
                ->select()
                ->toArray();

            foreach ($hosts as $key => $value) {
                $value['password'] =  aes_password_decode($value['password']);
                if(!isset($params[$value['server_id']])){
                    $params[$value['server_id']]['url'] = $value['url'];
                    $params[$value['server_id']]['username'] = $value['username'];
                    $params[$value['server_id']]['password'] = $value['password'];
                    $params[$value['server_id']]['id'] = $value['server_id'];
                    $params[$value['server_id']]['data']['per_page'] = 9999;
                }
                $params[$value['server_id']]['data']['hostid'][] = $value['rel_id']; 
                $hostArr[$value['rel_id']] = [
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'ip' => $value['ip'],
                    'country' => $value['country'],
                    'country_code' => $value['country_code'],
                    'city' => $value['city'],
                    'area' => $value['area'],
                ];
            }
        }
		
        if(class_exists("server\\common_cloud\\model\\HostLinkModel")){
            $hosts = HLM2::alias('hl')
                ->field('h.id,h.name,h.status,h.due_time,dc.country,dc.country_code,dc.city,dc.area,hl.ip,hl.rel_id,h.server_id,s.url,s.username,s.password')
                ->join('host h', 'hl.host_id=h.id')
                ->leftJoin('module_common_cloud_data_center dc', 'hl.module_common_cloud_data_center_id=dc.id')
                ->leftJoin('server s', 'h.server_id=s.id')
                ->where('h.client_id', $clientId)
                ->where('s.module', 'common_cloud')
                ->where('s.status', 1)
                ->where('hl.rel_id', '>', 0)
                ->group('h.id')
                ->select()
                ->toArray();

            foreach ($hosts as $key => $value) {
                $value['password'] =  aes_password_decode($value['password']);
                if(!isset($params[$value['server_id']])){
                    $params[$value['server_id']]['url'] = $value['url'];
                    $params[$value['server_id']]['username'] = $value['username'];
                    $params[$value['server_id']]['password'] = $value['password'];
                    $params[$value['server_id']]['id'] = $value['server_id'];
                    $params[$value['server_id']]['data']['per_page'] = 9999;
                }
                $params[$value['server_id']]['data']['hostid'][] = $value['rel_id']; 
                $hostArr[$value['rel_id']] = [
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'ip' => $value['ip'],
                    'country' => $value['country'],
                    'country_code' => $value['country_code'],
                    'city' => $value['city'],
                    'area' => $value['area'],
                ];
            }
        }

        if(class_exists("server\\mf_cloud\\model\\HostLinkModel")){
            $hosts = HLM3::alias('hl')
                ->field('h.id,h.name,h.status,h.due_time,dc.country,dc.country_code,dc.city,dc.area,hl.ip,hl.rel_id,h.server_id,s.url,s.username,s.password')
                ->join('host h', 'hl.host_id=h.id')
                ->leftJoin('module_mf_cloud_data_center dc', 'hl.module_mf_cloud_data_center_id=dc.id')
                ->leftJoin('server s', 'h.server_id=s.id')
                ->where('h.client_id', $clientId)
                ->where('s.module', 'mf_cloud')
                ->where('s.status', 1)
                ->where('hl.rel_id', '>', 0)
                ->group('h.id')
                ->select()
                ->toArray();

            foreach ($hosts as $key => $value) {
                $value['password'] =  aes_password_decode($value['password']);
                if(!isset($params[$value['server_id']])){
                    $params[$value['server_id']]['url'] = $value['url'];
                    $params[$value['server_id']]['username'] = $value['username'];
                    $params[$value['server_id']]['password'] = $value['password'];
                    $params[$value['server_id']]['id'] = $value['server_id'];
                    $params[$value['server_id']]['data']['per_page'] = 9999;
                }
                $params[$value['server_id']]['data']['hostid'][] = $value['rel_id']; 
                $hostArr[$value['rel_id']] = [
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'ip' => $value['ip'],
                    'country' => $value['country'],
                    'country_code' => $value['country_code'],
                    'city' => $value['city'],
                    'area' => $value['area'],
                ];
            }
        }

        $list = [];
        $res = $this->idcsmartCloudBatchRequest('disks/snapshots', $params, 'GET');

        if(is_array($res)){
        	foreach ($res as $key => $value) {
        		if($value['status']==200){
                    if(!isset($value['data']['data'])){
                        continue;
                    }
        			foreach ($value['data']['data'] as $k => $v) {
        				$list[] = [
        					'id' => $v['id'],
        					'name' => $v['name'],
        					'create_time' => strtotime($v['create_time']),
                            'host_id' => $hostArr[$v['hostid']]['id'],
        					'host_name' => $hostArr[$v['hostid']]['name'],
        					'ip' => $hostArr[$v['hostid']]['ip'],
        					'country' => $hostArr[$v['hostid']]['country'],
        					'country_code' => $hostArr[$v['hostid']]['country_code'],
        					'city' => $hostArr[$v['hostid']]['city'],
        					'area' => $hostArr[$v['hostid']]['area'],
                            'notes' => $v['remarks']
        				];
        			}
        		}
        	}
        	return ['list' => $list, 'count' => count($list)];
        }else{
        	return ['list' => [], 'count' => 0];
        }
            
	}

	public function backupList()
	{
		$clientId = get_client_id();
        $params = [];
        $hostArr = [];

        if(class_exists("server\\idcsmart_cloud\\model\\HostLinkModel")){
            $hosts = HLM1::alias('hl')
                ->field('h.id,h.name,h.status,h.due_time,dc.country,dc.country_code,dc.city,dc.area,hl.ip,hl.rel_id,h.server_id,s.url,s.username,s.password')
                ->join('host h', 'hl.host_id=h.id')
                ->leftJoin('module_idcsmart_cloud_data_center dc', 'hl.module_idcsmart_cloud_data_center_id=dc.id')
                ->leftJoin('server s', 'h.server_id=s.id')
                ->where('h.client_id', $clientId)
                ->where('s.module', 'idcsmart_cloud')
                ->where('s.status', 1)
                ->where('hl.rel_id', '>', 0)
                ->group('h.id')
                ->select()
                ->toArray();

            foreach ($hosts as $key => $value) {
                $value['password'] =  aes_password_decode($value['password']);
                if(!isset($params[$value['server_id']])){
                    $params[$value['server_id']]['url'] = $value['url'];
                    $params[$value['server_id']]['username'] = $value['username'];
                    $params[$value['server_id']]['password'] = $value['password'];
                    $params[$value['server_id']]['id'] = $value['server_id'];
                    $params[$value['server_id']]['data']['per_page'] = 9999;
                }
                $params[$value['server_id']]['data']['hostid'][] = $value['rel_id']; 
                $hostArr[$value['rel_id']] = [
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'ip' => $value['ip'],
                    'country' => $value['country'],
                    'country_code' => $value['country_code'],
                    'city' => $value['city'],
                    'area' => $value['area'],
                ];
            }
        }
        
        if(class_exists("server\\common_cloud\\model\\HostLinkModel")){
            $hosts = HLM2::alias('hl')
                ->field('h.id,h.name,h.status,h.due_time,dc.country,dc.country_code,dc.city,dc.area,hl.ip,hl.rel_id,h.server_id,s.url,s.username,s.password')
                ->join('host h', 'hl.host_id=h.id')
                ->leftJoin('module_common_cloud_data_center dc', 'hl.module_common_cloud_data_center_id=dc.id')
                ->leftJoin('server s', 'h.server_id=s.id')
                ->where('h.client_id', $clientId)
                ->where('s.module', 'common_cloud')
                ->where('s.status', 1)
                ->where('hl.rel_id', '>', 0)
                ->group('h.id')
                ->select()
                ->toArray();

            foreach ($hosts as $key => $value) {
                $value['password'] =  aes_password_decode($value['password']);
                if(!isset($params[$value['server_id']])){
                    $params[$value['server_id']]['url'] = $value['url'];
                    $params[$value['server_id']]['username'] = $value['username'];
                    $params[$value['server_id']]['password'] = $value['password'];
                    $params[$value['server_id']]['id'] = $value['server_id'];
                    $params[$value['server_id']]['data']['per_page'] = 9999;
                }
                $params[$value['server_id']]['data']['hostid'][] = $value['rel_id']; 
                $hostArr[$value['rel_id']] = [
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'ip' => $value['ip'],
                    'country' => $value['country'],
                    'country_code' => $value['country_code'],
                    'city' => $value['city'],
                    'area' => $value['area'],
                ];
            }
        }

        if(class_exists("server\\mf_cloud\\model\\HostLinkModel")){
            $hosts = HLM3::alias('hl')
                ->field('h.id,h.name,h.status,h.due_time,dc.country,dc.country_code,dc.city,dc.area,hl.ip,hl.rel_id,h.server_id,s.url,s.username,s.password')
                ->join('host h', 'hl.host_id=h.id')
                ->leftJoin('module_mf_cloud_data_center dc', 'hl.module_mf_cloud_data_center_id=dc.id')
                ->leftJoin('server s', 'h.server_id=s.id')
                ->where('h.client_id', $clientId)
                ->where('s.module', 'mf_cloud')
                ->where('s.status', 1)
                ->where('hl.rel_id', '>', 0)
                ->group('h.id')
                ->select()
                ->toArray();

            foreach ($hosts as $key => $value) {
                $value['password'] =  aes_password_decode($value['password']);
                if(!isset($params[$value['server_id']])){
                    $params[$value['server_id']]['url'] = $value['url'];
                    $params[$value['server_id']]['username'] = $value['username'];
                    $params[$value['server_id']]['password'] = $value['password'];
                    $params[$value['server_id']]['id'] = $value['server_id'];
                    $params[$value['server_id']]['data']['per_page'] = 9999;
                }
                $params[$value['server_id']]['data']['hostid'][] = $value['rel_id']; 
                $hostArr[$value['rel_id']] = [
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'ip' => $value['ip'],
                    'country' => $value['country'],
                    'country_code' => $value['country_code'],
                    'city' => $value['city'],
                    'area' => $value['area'],
                ];
            }
        }


        $list = [];
        $res = $this->idcsmartCloudBatchRequest('disks/backups', $params, 'GET');
        if(is_array($res)){
        	foreach ($res as $key => $value) {
        		if($value['status']==200){
                    if(!isset($value['data']['data'])){
                        continue;
                    }
        			foreach ($value['data']['data'] as $k => $v) {
        				$list[] = [
        					'id' => $v['id'],
        					'name' => $v['name'],
        					'create_time' => strtotime($v['create_time']),
                            'host_id' => $hostArr[$v['hostid']]['id'],
        					'host_name' => $hostArr[$v['hostid']]['name'],
        					'ip' => $hostArr[$v['hostid']]['ip'],
        					'country' => $hostArr[$v['hostid']]['country'],
        					'country_code' => $hostArr[$v['hostid']]['country_code'],
        					'city' => $hostArr[$v['hostid']]['city'],
        					'area' => $hostArr[$v['hostid']]['area'],
                            'notes' => $v['remarks']
        				];
        			}
        		}
        	}
        	return ['list' => $list, 'count' => count($list)];
        }else{
        	return ['list' => [], 'count' => 0];
        }
            
	}

	public function templateList()
	{
		$clientId = get_client_id();
        $params = [];
        $hostArr = [];

        if(class_exists("server\\idcsmart_cloud\\model\\HostLinkModel")){
            $hosts = HLM1::alias('hl')
                ->field('h.id,h.name,h.status,h.due_time,dc.country,dc.country_code,dc.city,dc.area,hl.ip,hl.rel_id,h.server_id,s.url,s.username,s.password')
                ->join('host h', 'hl.host_id=h.id')
                ->leftJoin('module_idcsmart_cloud_data_center dc', 'hl.module_idcsmart_cloud_data_center_id=dc.id')
                ->leftJoin('server s', 'h.server_id=s.id')
                ->where('h.client_id', $clientId)
                ->where('s.module', 'idcsmart_cloud')
                ->where('s.status', 1)
                ->where('hl.rel_id', '>', 0)
                ->group('h.id')
                ->select()
                ->toArray();

            foreach ($hosts as $key => $value) {
                $value['password'] =  aes_password_decode($value['password']);
                if(!isset($params[$value['server_id']])){
                    $params[$value['server_id']]['url'] = $value['url'];
                    $params[$value['server_id']]['username'] = $value['username'];
                    $params[$value['server_id']]['password'] = $value['password'];
                    $params[$value['server_id']]['id'] = $value['server_id'];
                    $params[$value['server_id']]['data']['per_page'] = 9999;
                }
                $params[$value['server_id']]['data']['hostid'][] = $value['rel_id']; 
                $hostArr[$value['rel_id']] = [
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'ip' => $value['ip'],
                    'country' => $value['country'],
                    'country_code' => $value['country_code'],
                    'city' => $value['city'],
                    'area' => $value['area'],
                ];
            }
        }
        
        if(class_exists("server\\common_cloud\\model\\HostLinkModel")){
            $hosts = HLM2::alias('hl')
                ->field('h.id,h.name,h.status,h.due_time,dc.country,dc.country_code,dc.city,dc.area,hl.ip,hl.rel_id,h.server_id,s.url,s.username,s.password')
                ->join('host h', 'hl.host_id=h.id')
                ->leftJoin('module_common_cloud_data_center dc', 'hl.module_common_cloud_data_center_id=dc.id')
                ->leftJoin('server s', 'h.server_id=s.id')
                ->where('h.client_id', $clientId)
                ->where('s.module', 'common_cloud')
                ->where('s.status', 1)
                ->where('hl.rel_id', '>', 0)
                ->group('h.id')
                ->select()
                ->toArray();

            foreach ($hosts as $key => $value) {
                $value['password'] =  aes_password_decode($value['password']);
                if(!isset($params[$value['server_id']])){
                    $params[$value['server_id']]['url'] = $value['url'];
                    $params[$value['server_id']]['username'] = $value['username'];
                    $params[$value['server_id']]['password'] = $value['password'];
                    $params[$value['server_id']]['id'] = $value['server_id'];
                    $params[$value['server_id']]['data']['per_page'] = 9999;
                }
                $params[$value['server_id']]['data']['hostid'][] = $value['rel_id']; 
                $hostArr[$value['rel_id']] = [
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'ip' => $value['ip'],
                    'country' => $value['country'],
                    'country_code' => $value['country_code'],
                    'city' => $value['city'],
                    'area' => $value['area'],
                ];
            }
        }

        if(class_exists("server\\mf_cloud\\model\\HostLinkModel")){
            $hosts = HLM3::alias('hl')
                ->field('h.id,h.name,h.status,h.due_time,dc.country,dc.country_code,dc.city,dc.area,hl.ip,hl.rel_id,h.server_id,s.url,s.username,s.password')
                ->join('host h', 'hl.host_id=h.id')
                ->leftJoin('module_mf_cloud_data_center dc', 'hl.module_mf_cloud_data_center_id=dc.id')
                ->leftJoin('server s', 'h.server_id=s.id')
                ->where('h.client_id', $clientId)
                ->where('s.module', 'mf_cloud')
                ->where('s.status', 1)
                ->where('hl.rel_id', '>', 0)
                ->group('h.id')
                ->select()
                ->toArray();

            foreach ($hosts as $key => $value) {
                $value['password'] =  aes_password_decode($value['password']);
                if(!isset($params[$value['server_id']])){
                    $params[$value['server_id']]['url'] = $value['url'];
                    $params[$value['server_id']]['username'] = $value['username'];
                    $params[$value['server_id']]['password'] = $value['password'];
                    $params[$value['server_id']]['id'] = $value['server_id'];
                    $params[$value['server_id']]['data']['per_page'] = 9999;
                }
                $params[$value['server_id']]['data']['hostid'][] = $value['rel_id']; 
                $hostArr[$value['rel_id']] = [
                    'id' => $value['id'],
                    'name' => $value['name'],
                    'ip' => $value['ip'],
                    'country' => $value['country'],
                    'country_code' => $value['country_code'],
                    'city' => $value['city'],
                    'area' => $value['area'],
                ];
            }
        }

        $list = [];
        $res = $this->idcsmartCloudBatchRequest('templates', $params, 'GET');
        if(is_array($res)){
        	foreach ($res as $key => $value) {
        		if($value['status']==200){
                    if(!isset($value['data']['data'])){
                        continue;
                    }
        			foreach ($value['data']['data'] as $k => $v) {
        				$list[] = [
        					'id' => $v['id'],
        					'name' => $v['name'],
        					'create_time' => strtotime($v['create_time']),
                            'host_id' => $hostArr[$v['hostid']]['id'],
        					'host_name' => $hostArr[$v['hostid']]['name'],
        					'ip' => $hostArr[$v['hostid']]['ip'],
        					'country' => $hostArr[$v['hostid']]['country'],
        					'country_code' => $hostArr[$v['hostid']]['country_code'],
        					'city' => $hostArr[$v['hostid']]['city'],
        					'area' => $hostArr[$v['hostid']]['area'],
        				];
        			}
        		}
        	}
        	return ['list' => $list, 'count' => count($list)];
        }else{
        	return ['list' => [], 'count' => 0];
        }
            
	}

	// 批量调用魔方云接口
    public function idcsmartCloudBatchRequest($path, $param = [], $request = 'POST')
    {
    	// 检查哪些接口已登录
    	$data = [];
    	foreach ($param as $key => $value) {
    		$token = $this->getCache('MODULE_IDCSMART_CLOUD_'.$value['id']);
    		if(!empty($token)){
    			$value['header'] = [
					'access-token: '.$token,
				];
				$value['url'] = rtrim($value['url'], '/') . '/v1/' . 'user_info';
    			$data[$value['id']] = $value; 
    		}
    	}

    	if(!empty($data)){
    		// 调用批量curl方法
			$res = batch_curl($data, 30, 'GET');

			foreach ($res as $k => $v) {
				if(!empty($v['error'])){
					$this->deleteCache('MODULE_IDCSMART_CLOUD_'.$data[$k]['id']);
				}else{
					if($v['http_code'] >= 200 && $v['http_code'] < 300){
					}else if($v['http_code'] == 401){
						// 登录过期
						$this->deleteCache('MODULE_IDCSMART_CLOUD_'.$data[$k]['id']);
					}else{
						$this->deleteCache('MODULE_IDCSMART_CLOUD_'.$data[$k]['id']);
					}
				}
			}
    	}

    	$result = [];

		// 未登录的调用登录
		$data = [];
    	foreach ($param as $key => $value) {
    		$token = $this->getCache('MODULE_IDCSMART_CLOUD_'.$value['id']);
    		if(empty($token)){
    			$value['data'] = [
					'username' => $value['username'],
					'password' => $value['password'],
				];
    			$value['url'] = rtrim($value['url'], '/') . '/v1/' . 'login'; // 检查token是否可用
    			$data[$value['id']] = $value; 
    		}
    	}

    	if(!empty($data)){
	    	// 调用批量curl方法
			$res = batch_curl($data);
			foreach ($res as $k => $v) {
				if(!empty($v['error'])){
					$result[$k] = ['status'=>400, 'msg'=>'CURL_ERROR: '.$v['error']];
					$this->deleteCache('MODULE_IDCSMART_CLOUD_'.$data[$k]['id']);
				}else{
					$content = json_decode($v['content'] ?? '', true) ?: [];
					if($v['http_code'] >= 200 && $v['http_code'] < 300){
						$token = trim($v['content'], '"');

						$this->setCache('MODULE_IDCSMART_CLOUD_'.$data[$k]['id'], $token, 12*3600);
					}else{
						$result[$k] = ['status'=>400, 'msg'=>$content['error'] ?? lang_plugins('fail_message')];
						$this->deleteCache('MODULE_IDCSMART_CLOUD_'.$data[$k]['id']);
					}
				}
		        
			}
		}

		// 已登录的调用接口
		$data = [];
    	foreach ($param as $key => $value) {
    		$token = $this->getCache('MODULE_IDCSMART_CLOUD_'.$value['id']);
    		if(!empty($token)){
    			$value['header'] = [
					'access-token: '.$token,
				];;
    			$value['url'] = rtrim($value['url'], '/') . '/v1/' . $path; // 检查token是否可用
    			$data[$key] = $value; 
    		}
    	}

    	if(!empty($data)){
	    	// 调用批量curl方法
			$res = batch_curl($data, 30, $request);

			foreach ($res as $k => $v) {
				if(!empty($v['error'])){
					$result[$k] = ['status'=>400, 'msg'=>'CURL_ERROR: '.$v['error']];
				}else{
					$content = json_decode($v['content'] ?? '', true) ?: [];
					if($v['http_code'] >= 200 && $v['http_code'] < 300){
						$result[$k] = [
							'status' => 200,
							'data'	 => $content,
						];
					}else if($v['http_code'] == 401){
						$result[$k] = ['status'=>400, 'msg'=>$v['error']];
					}else{
						$result[$k] = ['status'=>400, 'msg'=>$content['error'] ?? lang_plugins('fail_message')];
					}
				}
		        
			}
		}

		return $result;
    }

	/* 功能方法 */

	/**
	 * 时间 2022-06-08
	 * @title 设置缓存
	 * @desc 设置缓存
	 * @author hh
	 * @version v1
	 * @param   string $name 缓存名称
	 * @param   mixed  $value 缓存内容
	 * @param   int    $time   缓存时间(秒)
	 * @return  mixed  缓存内容
	 */
	public function setCache($name, $value, $time){
		return $name ? Cache::set($name, $value, $time) : false;
	}

	/**
	 * 时间 2022-06-08
	 * @title 获取缓存
	 * @desc 获取缓存
	 * @author hh
	 * @version v1
	 * @param   string $name 缓存名称
	 * @return  mixed
	 */
	public function getCache($name){
		return $name ? Cache::get($name) : false;
	}

	/**
	 * 时间 2022-06-08
	 * @title 删除缓存
	 * @desc 删除缓存
	 * @author hh
	 * @version v1
	 * @param   string $name 缓存名称
	 * @return  bool
	 */
	public function deleteCache($name){
		return $name ? Cache::delete($name) : false;
	}
}