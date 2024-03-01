<?php 
namespace addon\idcsmart_cloud\idcsmart_cloud;

use think\facade\Cache;

/**
 * 云操作类,v3.3.8
 */
class IdcsmartCloud{

	protected $username = '';  // 用户名
	protected $password = '';  // 密码
	protected $url 		= '';  // 基础地址,包含二级目录
	protected $cache    = '';  // 登录缓存标识
	protected $timeout  = 30;  // 超时时间

	public function __construct($config){
		$this->username = $config['username'];
		$this->password = $config['password'];
		$this->url 		= rtrim($config['url'], '/') . '/v1/';
		if(!empty($config['id'])){
			$this->cache    = 'MODULE_IDCSMART_CLOUD_'.$config['id'];
		}
	}

	/* 用户 */

	/**
	 * 时间 2022-06-08
	 * @title 创建用户
	 * @author hh
	 * @version v1
	 * @param   array $params 创建参数
	 */
	public function userCreate(array $params){
		return $this->request('user', $params, 'POST');
	}

	/**
	 * 时间 2022-06-08
	 * @title 验证用户名是否已存在
	 * @author hh
	 * @version v1
	 * @param   string $username 用户名
	 */
	public function userCheck(string $username){
		return $this->request('user/check', ['username'=>$username], 'POST');
	}

	/* 安全组 */

	/**
	 * 时间 2022-06-09
	 * @title 安全组详情
	 * @author hh
	 * @version v1
	 * @param   int $id 安全组ID
	 */
	public function securityGroupDetail(int $id, int $get_all_rule = 1){
		return $this->request('security_groups/'.$id, ['get_all_rule'=>$get_all_rule]);
	}

	/**
	 * 时间 2022-06-09
	 * @title 创建安全组
	 * @author hh
	 * @version v1
	 * @param   array $params 请求参数
	 */
	public function securityGroupCreate(array $params){
		return $this->request('security_groups', $params, 'POST');
	}

	/**
	 * 时间 2022-06-09
	 * @title 修改安全组
	 * @author hh
	 * @version v1
	 * @param   int $id 安全组ID
	 * @param   array $params 请求参数
	 */
	public function securityGroupModify(int $id, array $params){
		return $this->request('security_groups/'.$id, $params, 'PUT');
	}

	/**
	 * 时间 2022-06-09
	 * @title 删除安全组
	 * @author hh
	 * @version v1
	 * @param   int $id 安全组ID
	 */
	public function securityGroupDelete(int $id){
		return $this->request('security_groups/'.$id, [], 'DELETE');
	}

	/**
	 * 时间 2022-06-09
	 * @title 安全组规则列表
	 * @author hh
	 * @version v1
	 * @param   int $id 安全组ID
	 * @param   array $params 请求参数
	 */
	public function securityGroupRuleList(int $id, array $params){
		return $this->request('security_groups/'.$id.'/rules', $params);
	}

	/**
	 * 时间 2022-06-09
	 * @title 添加安全组规则
	 * @author hh
	 * @version v1
	 * @param   int $id 安全组ID
	 * @param   array $params 请求参数
	 */
	public function securityGroupRuleCreate(int $id, $params){
		return $this->request('security_groups/'.$id.'/rules', $params, 'POST');
	}

	/**
	 * 时间 2022-06-09
	 * @title 修改安全组规则
	 * @author hh
	 * @version v1
	 * @param   int $id 安全组规则ID
	 * @param   array $params 请求参数
	 */
	public function securityGroupRuleModify(int $id, array $params){
		return $this->request('security_group_rules/'.$id, $params, 'PUT');
	}

	/**
	 * 时间 2022-06-09
	 * @title 删除安全组规则
	 * @author hh
	 * @version v1
	 * @param   int $id 安全组规则ID
	 */
	public function securityGroupRuleDelete(int $id){
		return $this->request('security_group_rules/'.$id, [], 'DELETE');
	}

	/**
	 * 时间 2022-06-09
	 * @title 批量删除安全组规则
	 * @author hh
	 * @version v1
	 * @param   array $id 安全组规则ID
	 */
	public function securityGroupRuleBatchDelete(array $id){
		return $this->request('security_group_rules', $id, 'DELETE');
	}

	/**
	 * 时间 2022-09-08
	 * @title 关联安全组
	 * @author hh
	 * @version v1
	 * @param   int $id 安全组ID
	 * @param   array $params 请求参数
	 */
	public function linkSecurityGroup(int $id, array $params){
		return $this->request('security_groups/'.$id.'/links', $params, 'POST');
	}

	/**
	 * 时间 2022-09-08
	 * @title 解除关联安全组
	 * @author hh
	 * @version v1
	 * @param   int $id 实例ID
	 * @param   array $params 请求参数
	 */
	public function delLinkSecurityGroup(int $id){
		return $this->request('clouds/'.$id.'/security_groups', [], 'DELETE');
	}

	/**
	 * 时间 2022-06-08
	 * @title 登录
	 * @author hh
	 * @version v1
	 * @param   bool $force 是否强制登录(忽略缓存)
	 * @param   bool $test  测试缓存是否可用
	 */
	public function login(bool $force = false, bool $test = false){
		if(!$force){
			$token = $this->getCache($this->cache);

			if(!empty($token)){
				// 验证token是否可用
				if($test){
					$result = $this->userInfo();
					if($result['status'] == 200){
						$result = [
							'status'=>200,
							'data'=>[
								'token'=>$token,
							],
						];
					}
				}else{
					$result = [
						'status'=>200,
						'data'=>[
							'token'=>$token,
							'cache'=>true,  // 使用缓存
						],
					];
				}
				return $result;
			}
		}
		// 重新登录
		$url = $this->url . 'login';

		$data = [
			'username'=>$this->username,
			'password'=>$this->password,
		];
		$res = curl($url, $data, $this->timeout, 'POST');
		if(!empty($res['error'])){
			return ['status'=>400, 'msg'=>'CURL_ERROR: '.$res['error']];
		}
		if($res['http_code'] >= 200 && $res['http_code'] < 300){
			$token = trim($res['content'], '"');

			$this->setCache($this->cache, $token, 12*3600);
			$result = [
				'status'=>200,
				'data'=>[
					'token'=>$token,
				],
			];
		}else{
			$this->deleteCache($this->cache);

			$content = json_decode($res['content'] ?? '', true) ?: [];
			$result = [
				'status'=>400,
				'msg'=>$content['error'] ?? '登录失败',
			];
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

	/**
	 * 时间 2022-06-08
	 * @title 请求
	 * @author hh
	 * @version v1
	 * @param   string $path    地址
	 * @param   array  $data    数据
	 * @param   string $request 请求方式
	 */
	public function request($path, $data = [], $request = 'GET'){
		$loginRes = $this->login();
		if($loginRes['status'] != 200){
			return $loginRes;
		}
		$header = [
			'access-token: '.$loginRes['data']['token'],
		];

		$url = $this->url . $path;

		// 调用公共curl方法
		$res = curl($url, $data, $this->timeout, $request, $header);
		if(!empty($res['error'])){
			return ['status'=>400, 'msg'=>'CURL_ERROR: '.$res['error']];
		}
		$content = json_decode($res['content'] ?? '', true) ?: [];
		if($res['http_code'] >= 200 && $res['http_code'] < 300){
			$result = [
				'status' => 200,
				'data'	 => $content,
			];
		}else if($res['http_code'] == 401){
			// 登录过期,尝试重新登录并重新调用
			if(isset($loginRes['data']['cache'])){
				$res = $this->login(true);
				if($res['status'] != 200){
					return $res;
				}
				$result = $this->request($path, $data, $request);
			}else{
				$result = ['status'=>400, 'msg'=>$res['error']];
			}
		}else{
			$result = ['status'=>400, 'msg'=>$content['error'] ?? '执行失败'];
		}
		$result['http_code'] = $res['http_code'];
		return $result;
	}
}

