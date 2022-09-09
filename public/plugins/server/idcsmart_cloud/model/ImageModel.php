<?php 
namespace server\idcsmart_cloud\model;

use think\Model;
use think\db\Query;
use app\common\model\ProductModel;
use app\common\model\HostModel;
use app\common\model\OrderModel;
use server\idcsmart_cloud\logic\ToolLogic;

class ImageModel extends Model
{
	protected $name = 'module_idcsmart_cloud_image';

    // 设置字段信息
    protected $schema = [
        'id'                					=> 'int',
        'product_id'        					=> 'int',
        'name'              					=> 'string',
        'enable'       							=> 'int',
        'charge'								=> 'int',
        'price'									=> 'float',
        'filename'								=> 'string',
        'image_type'							=> 'string',
        'module_idcsmart_cloud_image_group_id' 	=> 'int',
        'create_time'       					=> 'int',
        'update_time'       					=> 'int',
    ];

	/**
	 * 时间 2022-06-21
	 * @title 镜像列表
	 * @desc 镜像列表
	 * @author hh
	 * @version v1
	 * @param   int param.product_id - 商品ID
	 * @param   string param.image_type - 镜像类型(system=官方镜像,app=应用镜像)
	 * @param   string param.module_idcsmart_cloud_image_group_id - 镜像分组ID
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  array data.list - 列表数据
	 * @return  int data.list[].id - 镜像ID
	 * @return  string data.list[].name - 镜像名称
	 * @return  int data.list[].enable - 是否启用(0=禁用,1=启用)
	 * @return  int data.list[].charge - 是否付费(0=不付费,1=付费)
	 * @return  string data.list[].price - 价格
	 * @return  int data.list[].module_idcsmart_cloud_image_group_id - 镜像分组ID
	 * @return  string data.list[].icon - 图标
	 * @return  array data.list[].data_center - 镜像数据中心数据
	 * @return  int data.list[].data_center[].module_idcsmart_cloud_data_center_id - 数据中心ID
	 * @return  int data.list[].data_center[].enable - 是否启用(0=禁用,1=启用)
	 * @return  int data.list[].data_center[].is_exist - 是否存在(0=不存在,1=存在)
	 * @return  array data.data_center - 数据中心数据
	 * @return  int data.data_center[].id - 数据中心ID
	 * @return  string data.data_center[].country - 国家
	 * @return  string data.data_center[].city - 城市
	 * @return  string data.data_center[].area - 区域
	 */
	public function imageList($param)
	{
		$where = [];
		$where[] = ['product_id', '=', $param['product_id']];
		if(!empty($param['image_type'])){
			$where[] = ['image_type', '=', $param['image_type']];
		}
		if(isset($param['module_idcsmart_cloud_image_group_id']) && !empty($param['module_idcsmart_cloud_image_group_id'])){
			$where[] = ['module_idcsmart_cloud_image_group_id', '=', $param['module_idcsmart_cloud_image_group_id']];
		}

		$image = $this->where($where)
					->field('id,name,enable,charge,price,module_idcsmart_cloud_image_group_id,icon')
					->select()
					->toArray();

		$allDataCenter = DataCenterModel::where('product_id', $param['product_id'])
						->field('id,country,city,area')
						->order('order', 'asc')
						->order('order', 'id')
						->select()
						->toArray();

		if(!empty($allDataCenter)){
			$dataCenterLink = ImageDataCenterLinkModel::alias('idcl')
						->whereIn('module_idcsmart_cloud_data_center_id', array_column($allDataCenter, 'id'))
						->select()
						->toArray();
		}

		$dataCenteLinkArr = [];
		foreach($dataCenterLink as $v){
			$imageId = $v['module_idcsmart_cloud_image_id'];
			unset($v['module_idcsmart_cloud_image_id']);
			$dataCenteLinkArr[$imageId][] = $v;
		}
		foreach($image as $k=>$v){
			$image[$k]['data_center'] = $dataCenteLinkArr[$v['id']] ?? [];
		}

		$result = [
			'status'=>200,
			'msg'=>lang_plugins('success_message'),
			'data'=>[
				'list'=>$image,
				'data_center'=>$allDataCenter
			]
		];
		return $result;
	}

	/**
	 * 时间 2022-06-21
	 * @title 修改镜像
	 * @desc 修改镜像
	 * @author hh
	 * @version v1
	 * @param   int param.id - 镜像ID require
	 * @param   int param.charge - 是否付费(0=不付费,1=付费)
	 * @param   float param.price - 金额
	 * @param   string param.icon - 图标(应用镜像生效)
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function updateImage($param){
		$image = $this->find($param['id']);
		if(empty($image)){
			return ['status'=>400, 'msg'=>lang_plugins('image_not_found')];
		}
		if($image['image_type'] == 'system' && isset($param['icon'])){
			unset($param['icon']);
		}

		$param['update_time'] = time();
		$this->update($param, ['id'=>$param['id']], ['charge','price','icon','update_time']);

		$desc = [
            'charge'=>lang_plugins('is_charge'),
            'price'=>lang_plugins('price'),
            'icon'=>lang_plugins('icon'),
        ];

        $description = ToolLogic::createEditLog($image, $param, $desc);
        if(!empty($description)){
            $description = lang_plugins('log_modify_image_success', [
                '{name}'=>$image['name'],
                '{detail}'=>$description,
            ]);
            active_log($description, 'product', $image['product_id']);
        }

		return ['status'=>200, 'msg'=>lang_plugins('update_success')];
	}

	/**
	 * 时间 2022-06-21
	 * @title 启用/禁用操作系统
	 * @desc 启用/禁用操作系统
	 * @author hh
	 * @version v1
	 * @param   int id - 操作系统ID require
	 * @param   int enable - 是否启用(0=禁用,1=启用) require
	 * @param   int module_idcsmart_cloud_data_center_id 0 数据中心ID
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 */
	public function enableImage($param){
		$image = $this->find($param['id']);
		if(empty($image)){
			return ['status'=>400, 'msg'=>lang_plugins('image_not_found')];
		}
		if(!empty($param['module_idcsmart_cloud_data_center_id'])){
			$dataCenter = DataCenterModel::find($param['module_idcsmart_cloud_data_center_id']);
			if(empty($dataCenter)){
				return ['status'=>400, 'msg'=>lang_plugins('data_center_not_found')];
			}
			$where = [];
			$where[] = ['module_idcsmart_cloud_image_id', '=', $param['id']];
			$where[] = ['module_idcsmart_cloud_data_center_id', '=', $param['module_idcsmart_cloud_data_center_id']];

			$this->startTrans();
			try{
				$ImageDataCenterLinkModel = ImageDataCenterLinkModel::where($where)->find();
				if(empty($ImageDataCenterLinkModel)){
					ImageDataCenterLinkModel::create([
						'module_idcsmart_cloud_image_id'=>$param['id'],
						'module_idcsmart_cloud_data_center_id'=>$param['module_idcsmart_cloud_data_center_id'],
						'enable'=>$param['enable']
					]);
				}else{
					ImageDataCenterLinkModel::where($where)->update(['enable'=>$param['enable']]);
				}
				// if($param['enable'] == 1){
					// $this->update($param, ['id'=>$param['id']], ['enable','update_time']);
				// }else{
					// 是否所有都改为当前状态
					$reverse = abs($param['enable'] - 1);

					$other = ImageDataCenterLinkModel::where('module_idcsmart_cloud_image_id', $param['id'])
							->where('enable', $reverse)
							->find();
					if(empty($other)){
						$param['update_time'] = time();
						$this->update($param, ['id'=>$param['id']], ['enable','update_time']);
					}
				// }
				$this->commit();
			}catch(\Exception $e){
				$this->rollback();
				return ['status'=>400, 'msg'=>$e->getMessage()];
			}
		}else{
			$this->startTrans();
			try{
				$param['update_time'] = time();
				$this->update($param, ['id'=>$param['id']], ['enable','update_time']);

				ImageDataCenterLinkModel::where('module_idcsmart_cloud_image_id', $param['id'])->update(['enable'=>$param['enable']]);

				$this->commit();
			}catch(\Exception $e){
				$this->rollback();
				return ['status'=>400, 'msg'=>$e->getMessage()];
			}
		}
		return ['status'=>200, 'msg'=>lang_plugins('update_success')];
	}

	/**
	 * 时间 2022-06-22
	 * @title 获取可用官方镜像
	 * @desc 获取可用官方镜像
	 * @author hh
	 * @version v1
	 * @param   int param.product_id - 商品ID require
	 * @param   int param.data_center_id - 数据中心ID
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  array data.list - 镜像数据
	 * @return  int  data.list[].id - 镜像组ID
	 * @return  string data.list[].name - 镜像组名称
	 * @return  string data.list[].icon - 图标
	 * @return  int data.list[].image[].id - 镜像ID
	 * @return  string data.list[].image[].name - 镜像名称
	 * @return  int data.list[].image[].charge - 是否付费(0=免费,1=付费)
	 * @return  string data.list[].image[].price - 价格
	 */
	public function getEnableSystemImage($param){
		$imageId = ImageDataCenterLinkModel::where(function(Query $query) use ($param) {
						if(!empty($param['data_center_id'])){
							$query->where('module_idcsmart_cloud_data_center_id', $param['data_center_id']);
						}
					})
					->where('enable', 1)
					->where('is_exist', 1)
					->column('DISTINCT module_idcsmart_cloud_image_id');

		$result = [
			'status'=>200,
			'msg'=>lang_plugins('success_message'),
			'data'=>[],
		];
		if(!empty($imageId)){
			$where = [];
			$where[] = ['i.product_id', '=', $param['product_id']];
			$where[] = ['i.id', 'IN', $imageId];
			$where[] = ['i.enable', '=', 1];
			$where[] = ['ig.enable', '=', 1];

			$image = $this
					->alias('i')
					->field('i.id,i.name,i.charge,i.price,i.module_idcsmart_cloud_image_group_id')
					->leftJoin('module_idcsmart_cloud_image_group ig', 'i.module_idcsmart_cloud_image_group_id=ig.id')
					->where($where)
					->select()
					->toArray();

			if(!empty($image)){
				$imageArr = [];
				$imageGroupIdArr = [];

				foreach($image as $v){
					$v['price'] = amount_format($v['price']);
					$imageGroupId = $v['module_idcsmart_cloud_image_group_id'];
					unset($v['module_idcsmart_cloud_image_group_id']);
					$imageArr[$imageGroupId][] = $v;
					$imageGroupIdArr[] = $imageGroupId;
				}
				$imageGroupIdArr = array_unique($imageGroupIdArr);

				$imageGroup = ImageGroupModel::field('id,name')
							->whereIn('id', $imageGroupIdArr)
							->select()
							->toArray();
				foreach($imageGroup as $k=>$v){
					$imageGroup[$k]['icon'] = '/plugins/server/idcsmart_cloud/view/img/'.$v['name'].'.png';
					$imageGroup[$k]['image'] = $imageArr[$v['id']] ?? [];
				}
				$result['data']['list'] = $imageGroup;
			}
		}
		return $result;
	}

	/**
	 * 时间 2022-07-22
	 * @title 镜像对比列表
	 * @desc 镜像对比列表
	 * @author hh
	 * @version v1
	 * @param   int productId - 商品ID
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  string data.list[].server_param - 接口参数
	 * @return  int data.list[].server_id - 接口ID
	 * @return  string data.list[].server_name - 接口名称
	 * @return  int data.list[].data_center_id - 数据中心ID
	 * @return  string data.list[].country - 国家
	 * @return  string data.list[].city - 城市
	 * @return  string data.list[].area - 区域
	 * @return  int data.list[].system_image_num - 系统镜像数量
	 * @return  int data.list[].app_image_num - 应用镜像数量
	 * @return  int data.list[].image[].id - 镜像ID
	 * @return  string data.list[].image[].name - 镜像名称
	 * @return  int data.list[].image[].is_exist - 是否存在(0=不存在,1=存在)
	 */
	public function imageCompare($productId){
		$result = [
			'status' => 200,
			'msg'    => lang_plugins('success_message'),
			'data'   => [
				'list' => []
			]
		];
		if(empty($productId)){
			return $result;
		}

		$image = ImageModel::field('id,name')
				->where('product_id', $productId)
				->select()
				->toArray();
		if(empty($image)){
			return $result;
		}

		$ImageDataCenterServerLink = ImageDataCenterServerLinkModel::alias('idcsl')
									->field('idcsl.server_id,idcsl.module_idcsmart_cloud_data_center_id,idcsl.module_idcsmart_cloud_image_id')
									->leftJoin('module_idcsmart_cloud_data_center dc', 'idcsl.module_idcsmart_cloud_data_center_id=dc.id')
									->where('dc.product_id', $productId)
									->select()
									->toArray();

		$exist = [];
		foreach($ImageDataCenterServerLink as $v){
			$exist[$v['module_idcsmart_cloud_data_center_id']][$v['server_id']][$v['module_idcsmart_cloud_image_id']] = 1;
		}
		
		$data = DataCenterServerLinkModel::alias('dcsl')
				->field('dcsl.server_param,s.id server_id,s.name server_name,dc.id data_center_id,dc.country,dc.area,dc.city,count(i1.id) system_image_num,count(i2.id) app_image_num')
				->leftJoin('module_idcsmart_cloud_data_center dc', 'dcsl.module_idcsmart_cloud_data_center_id=dc.id')
				->leftJoin('server s', 'dcsl.server_id=s.id')
				->leftJoin('module_idcsmart_cloud_image_data_center_server_link idcsl', 'dcsl.server_id=idcsl.server_id AND dcsl.module_idcsmart_cloud_data_center_id=idcsl.module_idcsmart_cloud_data_center_id')
				->leftJoin('module_idcsmart_cloud_image i1', 'idcsl.module_idcsmart_cloud_image_id=i1.id AND i1.image_type="system"')
				->leftJoin('module_idcsmart_cloud_image i2', 'idcsl.module_idcsmart_cloud_image_id=i2.id AND i2.image_type="app"')
				->group('dcsl.server_id,dcsl.module_idcsmart_cloud_data_center_id')
				->where('dc.product_id', $productId)
				->select()
				->toArray();
		foreach($data as $k=>$v){
			foreach($image as $kk=>$vv){
				$image[$kk]['is_exist'] = $exist[$v['data_center_id']][$v['server_id']][$vv['id']] ?? 0;
			}
			$data[$k]['image'] = $image;
		}

		$result['data']['list'] = $data;
		return $result;
	}

	/**
	 * 时间 2022-07-29
	 * @title 检查产品是够购买过镜像
	 * @desc 检查产品是够购买过镜像
	 * @author hh
	 * @version v1
	 * @param   int param.id - 产品ID require
	 * @param   int param.image_id - 镜像ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  string data.price - 需要支付的金额(0.00表示镜像免费或已购买)
	 */
	public function checkHostImage($param){
		// 验证产品和用户
        $host = HostModel::find($param['id']);
        if(empty($host) || $host['status'] != 'Active'){
            return ['status'=>400, 'msg'=>lang_plugins('host_not_create')];
        }
        // 前台判断
        $app = app('http')->getName();
        if($app == 'home'){
            if($host['client_id'] != get_client_id()){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_exist')];
            }
        }
        $hostLink = HostLinkModel::where('host_id', $param['id'])->find();
        if(empty($hostLink)){
        	return ['status'=>400, 'msg'=>lang_plugins('host_not_create')];
        }
		$image = ImageModel::find($param['image_id'] ?? 0);
		if(empty($image)){
			return ['status'=>400, 'msg'=>lang_plugins('image_not_found')];
		}
		$imageDataCenterLink = ImageDataCenterLinkModel::where('module_idcsmart_cloud_image_id', $param['image_id'])
							->where('module_idcsmart_cloud_data_center_id', $hostLink['module_idcsmart_cloud_data_center_id'])
							->where('enable', 1)
							->find();
		if(empty($imageDataCenterLink)){
			return ['status'=>400, 'msg'=>lang_plugins('image_not_found')];
		}
		$result = [
			'status' => 200,
			'msg'    => lang_plugins('success_message'),
			'data'   => []
		];
		if($image['charge'] == 1){
			$res = HostImageLinkModel::where('host_id', $param['id'])->where('module_idcsmart_cloud_image_id', $param['image_id'])->find();
			if(!empty($res)){
				$result['data']['price'] = '0.00';
			}else{
				$result['data']['price'] = amount_format($image['price']);
			}
		}else{
			$result['data']['price'] = '0.00';
		}
		return $result;
	}

	/**
	 * 时间 2022-07-29
	 * @title 生成购买镜像订单
	 * @desc 生成购买镜像订单
	 * @author hh
	 * @version v1
	 * @param   int param.id - 产品ID require
	 * @param   int param.image_id - 镜像ID require
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  string msg - 提示信息
	 * @return  string data.id - 订单ID
	 */
	public function createImageOrder($param){
		$res = $this->checkHostImage($param);
		if($res['status'] == 400){
			return $res;
		}
		if($res['data']['price'] == 0){
			return ['status'=>400, 'msg'=>lang_plugins('no_need_to_buy_this_image')];
		}

		$image = ImageModel::find($param['image_id']);
		$description = lang_plugins("buy_image", ['name'=>$image['name']]);

		$OrderModel = new OrderModel();

        $data = [
            'host_id'     => $param['id'],
            'client_id'   => get_client_id(),
            'type'        => 'upgrade_config',
            'amount'      => $res['data']['price'],
            'description' => $description,
            'config_options' => [
            	'type'     => 'buy_image',
                'image_id' => $param['image_id'],
            ]
        ];
        return $OrderModel->createOrder($data);
	}

}

