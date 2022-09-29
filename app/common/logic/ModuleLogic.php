<?php 
namespace app\common\logic;

use app\common\model\HostModel;
use app\common\model\ProductModel;
use app\common\model\ServerModel;
use think\facade\View;

/**
 * @title 模块逻辑
 * @desc 模块逻辑
 * @use  app\common\logic\ModuleLogic
 */
class ModuleLogic
{
	// 模块目录
	protected $path = WEB_ROOT . 'plugins/server/';

	/**
	 * 时间 2022-05-27
	 * @title 获取模块列表
	 * @desc 获取模块列表
	 * @author hh
	 * @version v1
	 * @return  string [].name - 模块名称
	 * @return  string [].display_name - 模块显示名称
	 */
	public function getModuleList(): array
	{
		$modules = [];
		if(is_dir($this->path)){
		    if($handle = opendir($this->path)){
		        while(($file = readdir($handle)) !== false){
		        	if($file != '.' && $file != '..' && is_dir($this->path . $file) && preg_match('/^[a-z][a-z0-9_]{0,99}$/', $file)){
		        	    if($ImportModule = $this->importModule($file)){
		        			if(method_exists($ImportModule, 'metaData')){
		        				$metaData = call_user_func([$ImportModule, 'metaData']);
		        				$modules[] = [
		        					'name'=>$file,
		        					'display_name'=>$metaData['display_name'] ?: $file,
		        				];
		        			}else{
		        				$modules[] = [
		        					'name'=>$file,
		        					'display_name'=>$file,
		        				];
		        			}
		        		}
		        	}
		        }
		        closedir($handle);
		    }
		}
		return $modules;
	}

	/**
	 * 时间 2022-05-27
	 * @title 测试连接
	 * @desc 测试连接
	 * @author hh
	 * @version v1
	 * @param   ServerModel ServerModel - 接口模型
	 * @return  int status - 200=连接成功,400=连接失败
	 * @return  string msg - 信息
	 */
	public function testConnect(ServerModel $ServerModel): array
	{
		$module = $ServerModel['module'];
		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'testConnect')){
				// 获取模块通用参数
				$res = call_user_func([$ImportModule, 'testConnect'], ['server'=>$ServerModel]);
				$res = $this->formatResult($res, lang('module_test_connect_success'), lang('module_test_connect_fail'));
			}else{
				$res['status'] = 400;
				$res['msg'] = lang('undefined_test_connect_function');
			}
		}else{
			$res['status'] = 400;
			$res['msg'] = lang('module_file_is_not_exist');
		}
		return $res;
	}

	/**
	 * 时间 2022-05-27
	 * @title 第一次使用模块创建接口后
	 * @desc 第一次使用模块创建接口后
	 * @author hh
	 * @version v1
	 * @param   string $module - 模块名称
	 */
	public function afterCreateFirstServer($module)
	{
		// 可以把添加表的操作放这里
		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'afterCreateFirstServer')){
				call_user_func([$ImportModule, 'afterCreateFirstServer']);
			}
		}
	}

	/**
	 * 时间 2022-05-27
	 * @title 删除最后一个使用该模块的接口
	 * @desc 删除最后一个使用该模块的接口
	 * @author hh
	 * @version v1
	 * @param   string $module - 模块名称
	 */
	public function afterDeleteLastServer($module)
	{
		// 可以把删表放这里
		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'afterDeleteLastServer')){
				call_user_func([$ImportModule, 'afterDeleteLastServer']);
			}
		}
	}

	/**
	 * 时间 2022-05-16
	 * @title 产品开通
	 * @desc 产品开通
	 * @author hh
	 * @version v1
	 * @param   HostModel $HostModel - 产品模型
	 * @return  array
	 * @return  int status - 状态,200=成功,400=失败
	 * @return  string msg - 信息
	 */
	public function createAccount(HostModel $HostModel): array
	{
		// 模块调用
		$module = $HostModel->getModule();
		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'createAccount')){
				// 获取模块通用参数
				$params = $HostModel->getModuleParams();
				$res = call_user_func([$ImportModule, 'createAccount'], $params);
				return $this->formatResult($res, lang('module_create_success'), lang('module_create_success'));
			}else{
				return ['status'=>200, 'msg'=>lang('module_create_success')];
			}
		}
		return ['status'=>200, 'msg'=>lang('module_create_success')];
	}

	/**
	 * 时间 2022-05-16
	 * @title 产品暂停
	 * @desc 产品暂停
	 * @author hh
	 * @version v1
	 * @param   HostModel $HostModel - 产品模型
	 * @return  array
	 * @return  int status - 状态,200=成功,400=失败
	 * @return  string msg - 信息
	 */
	public function suspendAccount(HostModel $HostModel): array
	{
		// 模块调用
		$module = $HostModel->getModule();
		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'suspendAccount')){
				// 获取模块通用参数
				$params = $HostModel->getModuleParams();
				$res = call_user_func([$ImportModule, 'suspendAccount'], $params);
				return $this->formatResult($res, lang('module_suspend_success'), lang('module_suspend_fail'));
			}else{
				return ['status'=>200, 'msg'=>lang('module_suspend_success')];
			}
		}
		return ['status'=>200, 'msg'=>lang('module_suspend_success')];
	}

	/**
	 * 时间 2022-05-16
	 * @title 产品解除暂停
	 * @desc 产品解除暂停
	 * @author hh
	 * @version v1
	 * @param   HostModel $HostModel - 产品模型
	 * @return  array
	 * @return  int status - 状态,200=成功,400=失败
	 * @return  string msg - 信息
	 */
	public function unsuspendAccount(HostModel $HostModel): array
	{
		// 模块调用
		$module = $HostModel->getModule();
		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'unsuspendAccount')){
				// 获取模块通用参数
				$params = $HostModel->getModuleParams();
				$res = call_user_func([$ImportModule, 'unsuspendAccount'], $params);
				return $this->formatResult($res, lang('module_unsuspend_success'), lang('module_unsuspend_fail'));
			}else{
				return ['status'=>200, 'msg'=>lang('module_unsuspend_success')];
			}
		}	
		return ['status'=>200, 'msg'=>lang('module_unsuspend_success')];
	}

	/**
	 * 时间 2022-05-16
	 * @title 产品删除
	 * @desc 产品删除
	 * @author hh
	 * @version v1
	 * @param   HostModel $HostModel - 产品模型
	 * @return  array
	 * @return  int status - 状态,200=成功,400=失败
	 * @return  string msg - 信息
	 */
	public function terminateAccount(HostModel $HostModel): array
	{
		// 模块调用
		$module = $HostModel->getModule();
		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'terminateAccount')){
				// 获取模块通用参数
				$params = $HostModel->getModuleParams();
				$res = call_user_func([$ImportModule, 'terminateAccount'], $params);
				return $this->formatResult($res, lang('delete_success'), lang('delete_fail'));
			}else{
				return ['status'=>200, 'msg'=>lang('delete_success')];
			}
		}
		return ['status'=>200, 'msg'=>lang('delete_success')];
	}

	/**
	 * 时间 2022-05-16
	 * @title 续费订单支付后调用
	 * @desc 续费订单支付后调用
	 * @author hh
	 * @version v1
	 * @param   HostModel $HostModel - 产品模型
	 */
	public function renew(HostModel $HostModel): void
	{
		// 模块调用
		$module = $HostModel->getModule();
		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'renew')){
				// 获取模块通用参数
				$params = $HostModel->getModuleParams();
				call_user_func([$ImportModule, 'renew'], $params);
			}
		}
		// 不需要返回东西
	}

	/**
	 * 时间 2022-05-26
	 * @title 升降级配置项完成后调用
	 * @desc 升降级配置项完成后调用
	 * @author hh
	 * @version v1
	 * @param HostModel HostModel - 产品模型
	 * @param mixed params - 自定义参数
	 */
	public function changePackage(HostModel $HostModel, $params)
	{
		$module = $HostModel->getModule();
		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'changePackage')){
				// 获取模块通用参数
				$moduleParams = $HostModel->getModuleParams();
				$moduleParams['custom'] = $params;
				$res = call_user_func([$ImportModule, 'changePackage'], $moduleParams);
			}
		}
		// 不需要返回
	}

	/**
	 * 时间 2022-06-01
	 * @title 升降级商品完成后调用
	 * @desc 升降级商品完成后调用
	 * @author hh
	 * @version v1
	 * @param   HostModel HostModel - 已经关联新商品的产品模型
	 * @param   mixed params - 自定义参数
	 */
	public function changeProduct(HostModel $HostModel, $params)
	{
		$module = $HostModel->getModule();
		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'changeProduct')){
				// 获取模块通用参数
				$moduleParams = $HostModel->getModuleParams();
				$moduleParams['custom'] = $params;
				$res = call_user_func([$ImportModule, 'changeProduct'], $moduleParams);
			}
		}
		// 不需要返回
	}

	/**
	 * 时间 2022-05-26
	 * @title 购物车价格计算
	 * @desc 购物车价格计算
	 * @author hh
	 * @version v1
	 * @param   ProductModel $ProductModel - 产品模型
	 * @param   mixed  $params   []  自己定义的参数
	 * @return  int status - 状态(200=成功,400=失败)
	 * @return  array data - 购物车数据
	 * @return  float data.price - 配置项金额
	 * @return  string data.billing_cycle - 周期名称
	 * @return  int data.duration - 周期时长
	 * @return  string data.description - 订单子项描述
	 * @return  string data.content - 购物车配置显示,支持模板
	 */
	public function cartCalculatePrice($ProductModel, $params = [], $qty=1)
	{
		$result = [];

		$module = $ProductModel->getModule();

		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'cartCalculatePrice')){
				// 获取模块通用参数
				$result = call_user_func([$ImportModule, 'cartCalculatePrice'], ['product'=>$ProductModel, 'custom'=>$params, 'qty'=>$qty]);
				// TODO 是否判断返回/格式化
				// if(!isset($result['status']) || !isset($result['data']['price']) || !isset($result['data']['billing_cycle']) || !isset($result['data']['duration']) || !isset($result['data']['description']) || !isset($result['data']['content'])){
					
					
					
				// }
			}
		}
		if(empty($result)){
			$result = [
				'status'=>400,
				'msg'=>lang('module_file_is_not_exist'),
			];
		}
		return $result;
	}

	/**
	 * 时间 2022-05-16
	 * @title 后台商品接口配置输出
	 * @desc 后台商品接口配置输出
	 * @author hh
	 * @version v1
	 * @param   HostModel $HostModel - 产品模型
	 * @return  string
	 */
	public function serverConfigOption($module, ProductModel $ProductModel)
	{
		$res = '';
		// 模块调用
		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'serverConfigOption')){
				// 获取模块通用参数
				$res = call_user_func([$ImportModule, 'serverConfigOption'], ['product'=>$ProductModel]);
				$res = $this->formatTemplate($module, $res);
			}
		}
		return $res;
	}

	/**
	 * 时间 2022-05-31
	 * @title 后台商品保存(暂时未用)
	 * @desc 后台商品保存
	 * @author hh
	 * @version v1
	 * @param   ProductModel ProductModel - 商品模型
	 * @param   array params - 自定义参数
	 */
	public function productSave(ProductModel $ProductModel, $params)
	{
		$module = $ProductModel->getModule();
		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'productSave')){
				// 获取模块通用参数
				call_user_func([$ImportModule, 'productSave'], ['product'=>$ProductModel, 'custom'=>$params]);
			}
		}
	}

	/**
	 * 时间 2022-05-16
	 * @title 产品前台内页输出
	 * @desc 产品前台内页输出
	 * @author hh
	 * @version v1
	 * @param   HostModel HostModel - 产品模型
	 * @return  string
	 */
	public function clientArea(HostModel $HostModel): string
	{
		$res = '';
		// 模块调用
		$module = $HostModel->getModule();
		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'clientArea')){
				// 获取模块通用参数
				$params = $HostModel->getModuleParams();
				$res = call_user_func([$ImportModule, 'clientArea'], $params);
				$res = $this->formatTemplate($module, $res);
			}
		}
		return $res;
	}

	/**
	 * 时间 2022-05-16
	 * @title 产品后台内页输出
	 * @desc 产品后台内页输出
	 * @author hh
	 * @version v1
	 * @param   HostModel HostModel - 产品模型
	 * @return  string
	 */
	public function adminArea(HostModel $HostModel): string
	{
		$res = '';
		// 模块调用
		$module = $HostModel->getModule();
		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'adminArea')){
				// 获取模块通用参数
				$params = $HostModel->getModuleParams();
				$res = call_user_func([$ImportModule, 'adminArea'], $params);
				$res = $this->formatTemplate($module, $res);
			}
		}
		return $res;
	}

	/**
	 * 时间 2022-05-30
	 * @title 前台商品配置页面
	 * @desc 前台商品配置输出,购物车,单独订购,升降级商品
	 * @author hh
	 * @version v1
	 * @param   ProductModel ProductModel - 产品模型
	 * @return  string
	 */
	public function clientProductConfigOption(ProductModel $ProductModel, $tag = ''): string
	{
		$res = '';
		$module = $ProductModel->getModule();
		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'clientProductConfigOption')){
				// 获取模块通用参数
				$res = call_user_func([$ImportModule, 'clientProductConfigOption'], ['product'=>$ProductModel, 'tag'=>$tag]);
				$res = $this->formatTemplate($module, $res);
			}
		}
		return $res;
	}

	/**
	 * 时间 2022-05-31
	 * @title 后台商品配置页面
	 * @desc 后台商品配置输出,新建订单,升降级商品
	 * @author hh
	 * @version v1
	 * @param   ProductModel ProductModel - 产品模型
	 * @return  string
	 */
	public function adminProductConfigOption(ProductModel $ProductModel, $tag = ''): string
	{
		$res = '';
		$module = $ProductModel->getModule();
		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'adminProductConfigOption')){
				// 获取模块通用参数
				$res = call_user_func([$ImportModule, 'adminProductConfigOption'], ['product'=>$ProductModel, 'tag'=>$tag]);
				$res = $this->formatTemplate($module, $res);
			}
		}
		return $res;
	}

	/**
	 * 时间 2022-05-31
	 * @title 前台产品升降级配置输出(暂时未用)
	 * @desc 前台产品升降级配置输出
	 * @author hh
	 * @version v1
	 * @param   HostModel HostModel - 产品模型
	 * @return  string
	 */
	public function clientChangeConfigOption(HostModel $HostModel): string
	{
		$res = '';
		$module = $HostModel->getModule();
		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'clientChangeConfigOption')){
				// 获取模块通用参数
				$params = $HostModel->getModuleParams();
				$res = call_user_func([$ImportModule, 'clientChangeConfigOption'], $params);
				$res = $this->formatTemplate($module, $res);
			}
		}
		return $res;
	}

	/**
	 * 时间 2022-05-31
	 * @title 后台产品升降级配置输出(暂时未用)
	 * @desc 后台产品升降级配置输出
	 * @author hh
	 * @version v1
	 * @param   HostModel HostModel - 产品模型
	 * @return  string
	 */
	public function adminChangeConfigOption(HostModel $HostModel): string
	{
		$res = '';
		$module = $HostModel->getModule();
		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'adminChangeConfigOption')){
				// 获取模块通用参数
				$params = $HostModel->getModuleParams();
				$res = call_user_func([$ImportModule, 'adminChangeConfigOption'], $params);
				$res = $this->formatTemplate($module, $res);
			}
		}
		return $res;
	}

	/**
	 * 时间 2022-05-31
	 * @title 升降级配置项计算价格(暂时未用)
	 * @desc 升降级配置项计算价格
	 * @author hh
	 * @version v1
	 * @param   HostModel HostModel - 产品模型
	 * @param   array params - 产品模型
	 * @return  array
	 */
	public function changeConfigOptionCalculatePrice(HostModel $HostModel, $params): array
	{
		$result = [];
		$module = $HostModel->getModule();
		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'changeConfigOptionCalculatePrice')){
				// 获取模块通用参数
				$result = call_user_func([$ImportModule, 'changeConfigOptionCalculatePrice'], ['host'=>$HostModel, 'product'=>ProductModel::find($HostModel['product_id']), 'custom'=>$params]);
				// TODO 是否判断返回/格式化

			}
		}
		if(empty($result)){
			$result = [
				'status'=>400,
				'msg'=>lang('module_file_is_not_exist'),
			];
		}
		return $result;
	}

	/**
	 * 时间 2022-05-30
	 * @title 在结算之后调用
	 * @desc 在结算之后调用,这时候可以存入产品配置项关联关系
	 * @author hh
	 * @version v1
	 * @param   ProductModel ProductModel - 商品模型
	 * @param   int hostId - 产品ID
	 * @param   array params - 自定义参数
	 */
	public function afterSettle($ProductModel, $hostId, $params): void
	{
		$module = $ProductModel->getModule();
		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'afterSettle')){
				call_user_func([$ImportModule, 'afterSettle'], ['product'=>$ProductModel, 'host_id'=>$hostId, 'custom'=>$params]);
			}
		}
	}

	/**
	 * 时间 2022-05-16
	 * @title 自定义后台方法
	 * @desc 自定义后台方法
	 * @author hh
	 * @version v1
	 * @param   string module - 模块名称
	 * @return  mixed params - 自定义参数
	 */
	public function customAdminFunction($module, $params)
	{
		$res = [];
		// 验证模块格式是否正确
		if(!$this->checkModule($module)){
			$res['status'] = 400;
			$res['msg'] = '模块格式错误';
			return json($res);
		}
		$controller = $params['controller'] ?? '';
		$method = $params['method'] ?? '';
		if(empty($controller) || empty($method)){
			$res['status'] = 400;
			$res['msg'] = '模块格式错误';
			return json($res);
		}
		$controller = parse_name($controller.'_controller', 1);
		$method = parse_name($method, 1, false);

		$class = '\server\\'.$module.'\\controller\\admin\\'.$controller;
		if(class_exists($class)){
			$class = new $class();

			if(method_exists($class, $method)){
				$res = call_user_func([$class, $method], $params);
			}else{
				$res['status'] = 400;
				$res['msg'] = '模块或方法不存在';
				$res = json($res);
			}
		}else{
			$res['status'] = 400;
			$res['msg'] = '模块或方法不存在';
			$res = json($res);
		}
		// if($this->importModule($module)){
		// 	// 执行模块操作
		// 	$func = $module . '_CustomAdminFunction';
		// 	if(function_exists($func)){
		// 		$res = call_user_func($func, $params);
		// 		$res = $this->formatResult($res);
		// 	}
		// }
		// if(empty($res)){
		// 	$res['status'] = 400;
		// 	$res['msg'] = '模块或方法不存在';
		// }
		return $res;
	}

	/**
	 * 时间 2022-06-08
	 * @title 自定义前台方法
	 * @desc 自定义前台方法
	 * @author hh
	 * @version v1
	 * @param   string $module - 模块名称
	 * @param   mixed $params - POST的其他参数
	 * @return  array
	 */
	public function customClientFunction($module, $params)
	{
		$res = [];
		// 验证模块格式是否正确
		if(!$this->checkModule($module)){
			$res['status'] = 400;
			$res['msg'] = '模块格式错误';
			return json($res);
		}
		$controller = $params['controller'] ?? '';
		$method = $params['method'] ?? '';
		if(empty($controller) || empty($method)){
			$res['status'] = 400;
			$res['msg'] = '模块格式错误';
			return json($res);
		}
		$controller = parse_name($controller.'_controller', 1);
		$method = parse_name($method, 1, false);

		$class = '\server\\'.$module.'\\controller\\home\\'.$controller;
		if(class_exists($class)){
			$class = new $class();

			if(method_exists($class, $method)){
				$res = call_user_func([$class, $method], $params);
			}else{
				$res['status'] = 400;
				$res['msg'] = '模块或方法不存在';
				$res = json($res);
			}
		}else{
			$res['status'] = 400;
			$res['msg'] = '模块或方法不存在';
			$res = json($res);
		}
		return $res;
	}

	/**
	 * 时间 2022-06-02
	 * @title 获取当前产品所有周期价格
	 * @desc 获取当前产品所有周期价格
	 * @author hh
	 * @version v1
	 * @param   HostModel HostModel - 产品模型
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  array data - 数据
	 * @return  float data[].price - 金额
	 * @return  string data[].billing_cycle - 周期名称
	 * @return  int data[].duration - 周期时长(秒)
	 */
	public function durationPrice(HostModel $HostModel)
	{
		$res = [];
		$module = $HostModel->getModule();
		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'durationPrice')){
				// 获取模块通用参数
				$moduleParams = $HostModel->getModuleParams();
				$res = call_user_func([$ImportModule, 'durationPrice'], $moduleParams);
				// TODO 验证返回
			}
		}
		if(empty($res)){
			$res = ['status'=>400, 'msg'=>'module_file_is_not_exist'];
		}
		return $res;
	}

	/**
	 * 时间 2022-06-16
	 * @title 获取商品所有配置项
	 * @desc 获取商品所有配置项
	 * @author hh
	 * @version v1
	 * @param   ProductModel ProductModel - 商品模型
	 * @return  array
	 */
	public function allConfigOption(ProductModel $ProductModel){
		$res = [];
		$module = $ProductModel->getModule();
		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'allConfigOption')){
				$res = call_user_func([$ImportModule, 'allConfigOption'], ['product'=>$ProductModel]);
				// TODO 验证返回
				
			}
		}
		if(empty($res)){
			// 未实现该方法返回成功
			$res = ['status'=>200, 'msg'=>'module_file_is_not_exist', 'data'=>[] ];
		}
		return $res;
	}

	/**
	 * 时间 2022-08-04
	 * @title 获取当前产品配置项
	 * @desc 获取当前产品配置项
	 * @author hh
	 * @version v1
	 * @param   HostModel HostModel - 产品模型
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  array data - 数据
	 */
	public function currentConfigOptioin(HostModel $HostModel)
	{
		$res = [];
		$module = $HostModel->getModule();
		if($ImportModule = $this->importModule($module)){
			if(method_exists($ImportModule, 'currentConfigOptioin')){
				// 获取模块通用参数
				$moduleParams = $HostModel->getModuleParams();
				$res = call_user_func([$ImportModule, 'currentConfigOptioin'], $moduleParams);
				// TODO 验证返回
			}
		}
		// if(empty($res)){
		// 	$res = ['status'=>400, 'msg'=>'module_file_is_not_exist'];
		// }
		return $res;
	}

	/**
	 * 时间 2022-06-08
	 * @title 验证模块名称是否正确
	 * @desc 验证模块名称是否正确
	 * @author hh
	 * @version v1
	 * @param   string $module - 模块名称
	 * @return  bool
	 */
	protected function checkModule($module){
		return (bool)preg_match('/^[a-z][a-z0-9_]{0,99}$/', $module);
	}

	/**
	 * 时间 2022-05-16
	 * @title 引入商品模块文件
	 * @desc 引入商品模块文件
	 * @author hh
	 * @version v1
	 * @param   string module - 模块类型
	 * @return  bool|object - - false=没有对应类,object=成功实例化模块类
	 */
	protected function importModule($module)
	{
		if(!empty($module)){
			$className = parse_name($module, 1);

			$class = '\server\\'.$module.'\\'.$className;

			if(class_exists($class)){
				return new $class();
			}
		}
		return false;
	}

	/**
	 * 时间 2022-05-26
	 * @title 格式化文本返回
	 * @desc 格式化文本返回
	 * @author hh
	 * @version v1
	 * @param   string $module 模块名称
	 * @param   mixed  $res    模块返回
	 * @return  string
	 */
	private function formatTemplate($module, $res): string
	{
		$html = '';
		if(is_array($res)){
			// 认为是使用模板的方式来输出内容,格式大概如下
			// [
			// 	   'template'=>'abc.html',
			// 	   'vars'=>[
			// 	  		'aaaa'=>'bbb'
			// 	   ]
			// ]
			$template_file = $this->path . $module . '/' . $res['template'];
			if(file_exists($template_file)){
				if(!empty($res['vars'])){
					View::assign($res['vars']);
				}
				// 调用方法变量
				$html = View::fetch($template_file);
			}else{
				$html = lang('module_cannot_find_template_file');
			}
		}else if(is_string($res)){
			$html = $res;
		}else{
			$html = (string)$res;
		}
		return $html;
	}

	/**
	 * 时间 2022-05-13
	 * @title 格式化系统操作返回
	 * @desc 格式化系统操作返回
	 * @author hh
	 * @version v1
	 * @param  mixed res - 操作返回 required
	 * @param  string successMsg - 成功返回没有提示信息时,会用该信息提示
	 * @param  string failMsg - 失败返回没有提示信息时,会用该信息提示
	 * @return  array
	 */
	private function formatResult($res, $successMsg = '', $failMsg = ''): array
	{
		$result = [];
		// 不兼容原来的老模块写法,都必须按标准返回
		if(is_array($res)){
			$result = $res;
			
			if($result['status'] === 400){
				$result['msg'] = $result['msg'] ?? ($failMsg ?: lang('module_operate_fail'));
			}else if($result['status'] === 200){
				$result['msg'] = $result['msg'] ?? ($successMsg ?: lang('module_operate_success'));
			}else{
				$result = [];
				$result['status'] = 400;
				$result['msg'] = lang('module_res_format_error');
			}
		}else{
			$result = [];
			$result['status'] = 400;
			$result['msg'] = lang('module_res_format_error');
			// 原模块返回判断(废弃)
			// if($res === null || $res == 'success' || $res == 'ok'){
			// 	$result['status'] = 200;
			// 	$result['msg'] = '操作成功';
			// }else{
			// 	$result['status'] = 400;
			// 	$result['msg'] = (string)$res;
			// }
		}
		return $result;
	}


}



