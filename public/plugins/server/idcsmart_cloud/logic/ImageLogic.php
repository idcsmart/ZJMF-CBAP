<?php 
namespace server\idcsmart_cloud\logic;

use app\common\model\ServerModel;
use server\idcsmart_cloud\model\DataCenterModel;
use server\idcsmart_cloud\model\DataCenterServerLinkModel;
use server\idcsmart_cloud\model\ImageModel;
use server\idcsmart_cloud\model\ImageGroupModel;
use server\idcsmart_cloud\model\ImageDataCenterLinkModel;
use server\idcsmart_cloud\model\ImageDataCenterServerLinkModel;
use server\idcsmart_cloud\idcsmart_cloud\IdcsmartCloud;
use addon\idcsmart_cloud\logic\IdcsmartCloudLogic;
use think\facade\Cache;

class ImageLogic{

	/**
	 * 时间 2022-06-29
	 * @title
	 * @desc
	 * @url
	 * @method  POST
	 * @author hh
	 * @version v1
	 * @param   string x             - x
	 * @return  [type] [description]
	 */
	public static function getProductImage($productId){
		$result = ['status'=>200, 'msg'=>lang_plugins('success_message')];

		$dataCenterId = DataCenterModel::where('product_id', $productId)->column('id');
		if(empty($dataCenterId)){
			return $result;
		}
		$serverId = DataCenterServerLinkModel::whereIn('module_idcsmart_cloud_data_center_id', $dataCenterId)->column('server_id');
		if(empty($serverId)){
			return $result;
		}

		$cacheKey = 'SYNC_IDCSMART_CLOUD_IMAGE_'.$productId;
		if(Cache::has($cacheKey)){
			return $result;
		}
		Cache::set($cacheKey, 1, 180);

		$server = ServerModel::whereIn('id', $serverId)
					->select()
					->toArray();

        $systemImageParams = [];
        $customImageParams = [];
        foreach ($server as $key => $value) {
        	$value['password'] =  aes_password_decode($value['password']);

        	$linkId = $value['id'];
        	if(!isset($systemImageParams[ $linkId ])){
        		$systemImageParams[ $linkId ] = [];

        		$systemImageParams[$linkId]['url'] = $value['url'];
        		$systemImageParams[$linkId]['username'] = $value['username'];
        		$systemImageParams[$linkId]['password'] = $value['password'];
        		$systemImageParams[$linkId]['id'] = $value['id'];

        		// 暂时参数一样
        		$customImageParams = $systemImageParams;
        	}
        }

        $IdcsmartCloudLogic = new IdcsmartCloudLogic();

        $systemRes = $IdcsmartCloudLogic->idcsmartCloudBatchRequest('images/system', $systemImageParams, 'GET');
        $customRes = $IdcsmartCloudLogic->idcsmartCloudBatchRequest('images/custom', $systemImageParams, 'GET');

        $systemImage = [];
		$customImage = [];

		foreach($systemRes as $v){
			if(empty($systemImage)){
				continue;
			}
			if(isset($v['status']) && $v['status'] == 200 && !empty($v['data'])){
				$systemImage = $v['data'];
			}
		}
		foreach($customRes as $v){
			if(isset($v['status']) && $v['status'] == 200 && !empty($v['data'])){
				$customImage = array_merge($customImage, $v['data']);
			}
		}
		// 获取当前已添加的所有镜像文件名
		$image = ImageModel::field('id,filename')->where('product_id', $productId)->select()->toArray();
		$image = array_column($image, 'id', 'filename');

		$imageLink = [
			'Windows',
			'CentOS',
			'Ubuntu',
			'Debian',
			'ESXi',
			'XenServer',
			'FreeBSD',
			'Fedora',
			'other',
			'rescue',
			'ArchLinux',
		];

		// 自动添加组
		$imageGroup = ImageGroupModel::field('id,name')->where('product_id', $productId)->select()->toArray();
		$imageGroup = array_column($imageGroup, 'id', 'name');
		foreach($imageLink as $v){
			if(empty($imageGroup[$v])){
				$ImageGroupModel = ImageGroupModel::create(['product_id'=>$productId, 'name'=>$v, 'description'=>'']);
				$imageGroup[ $v ] = $ImageGroupModel->id;
			}
		}
		// 自动添加并关联
		foreach($systemImage as $v){
			if(!isset($image[$v['filename']])){
				// 自动添加镜像
				$data = [
					'name'=>$v['name'],
					'filename'=>$v['filename'],
					'product_id'=>$productId,
					'enable'=>1,
					'image_type'=>'system'
				];
				$data['module_idcsmart_cloud_image_group_id'] = $imageGroup[$imageLink[$v['image_group_id'] - 1]] ?? $imageGroup['other'];
				$data['icon'] = ($imageLink[$v['image_group_id'] - 1] ?? 'other').'.png';

				$newImage = ImageModel::create($data);

				$imageDataCenterLink = [];
				foreach($dataCenterId as $vv){
					$imageDataCenterLink[] = [
						'module_idcsmart_cloud_image_id'=>$newImage->id,
						'module_idcsmart_cloud_data_center_id'=>$vv,
						'enable'=>1
					];
				}
				ImageDataCenterLinkModel::insertAll($imageDataCenterLink);
			}
		}
		// 应用镜像
		foreach($customImage as $v){
			if(!isset($image[$v['filename']])){
				// 自动添加镜像
				$data = [
					'name'=>$v['name'],
					'filename'=>$v['filename'],
					'product_id'=>$productId,
					'enable'=>1,
					'image_type'=>'app'
				];

				$newImage = ImageModel::create($data);

				$imageDataCenterLink = [];
				foreach($dataCenterId as $vv){
					$imageDataCenterLink[] = [
						'module_idcsmart_cloud_image_id'=>$newImage->id,
						'module_idcsmart_cloud_data_center_id'=>$vv,
						'enable'=>1
					];
				}
				ImageDataCenterLinkModel::insertAll($imageDataCenterLink);

				$image[$v['filename']] = $newImage->id;
			}
		}
		Cache::delete($cacheKey);
		return $result;
	}

	/**
	 * 时间 2022-06-29
	 * @title 刷新镜像每个数据中心镜像存在状态
	 * @desc 刷新镜像每个数据中心镜像存在状态
	 * @author hh
	 * @version v1
	 * @param   int productId - 商品ID
	 * @return  int status - 状态码(200=成功,400=失败)
	 * @return  int msg - 提示信息
	 */
	public static function refreshStatus($productId){
		$result = [
			'status'=>200,
			'msg'  =>lang_plugins('success_message'),
		];
		// 获取当前商品所有镜像
		$image = ImageModel::where('product_id', $productId)
				->field('id,filename')
				->select()
				->toArray();
		if(empty($image)){
			return $result;
		}
		$cacheKey = 'REFRESH_IDCSMART_CLOUD_IMAGE_'.$productId;
		if(Cache::has($cacheKey)){
			return $result;
		}
		Cache::set($cacheKey, 1, 180);

		$image = array_column($image, 'id', 'filename');

		$DataCenter = DataCenterServerLinkModel::alias('dcsl')
						->field('dcsl.server_param,dc.id,s.id server_id,s.url,s.username,s.password')
						->leftJoin('module_idcsmart_cloud_data_center dc', 'dcsl.module_idcsmart_cloud_data_center_id=dc.id')
						->leftJoin('server s', 'dcsl.server_id=s.id')
						->where('dc.product_id', $productId)
						->select()
						->toArray();

        $params = [];
        foreach ($DataCenter as $key => $value) {
        	$value['password'] =  aes_password_decode($value['password']);

        	$linkId = $value['id'] .'_'. $value['server_id'];
        	$serverParam = ToolLogic::formatParam($value['server_param']);
        	if(!isset($params[ $linkId ])){
        		$params[ $linkId ] = [];

        		$params[$linkId]['url'] = $value['url'];
        		$params[$linkId]['username'] = $value['username'];
        		$params[$linkId]['password'] = $value['password'];
        		$params[$linkId]['data_center_id'] = $value['id'];
        		$params[$linkId]['id'] = $value['server_id'];

        		if(isset($serverParam['area'])){
        			$params[$linkId]['data'] = [
        				'type' => 'area',
        				'id'   => $serverParam['area'],
        			];
        		}
        		if(isset($serverParam['node'])){
        			$params[$linkId]['data'] = [
        				'type' => 'node',
        				'id'   => $serverParam['node'],
        			];
        		}
        	}
        }
        $imageStatus = [];

        $ImageDataCenterServerLinkModel = new ImageDataCenterServerLinkModel();
        $IdcsmartCloudLogic = new IdcsmartCloudLogic();
        $res = $IdcsmartCloudLogic->idcsmartCloudBatchRequest('node_images', $params, 'GET');
        foreach($res as $k=>$v){
        	// 成功的才刷新状态,访问不到不刷新
        	if(isset($v['status']) && $v['status'] == 200 && !empty($v['data'])){
        		$kArr = explode('_', $k);
    			$dataCenterId = $kArr[0];
    			$serverId = $kArr[1];

    			$imageDataCenterServerLink = [];
        		foreach($v['data'] as $kk=>$vv){
        			if(!isset($vv['image'])){
        				continue;
        			}
        			foreach($vv['image'] as $vvv){
        				if(!isset($vvv['filename'])){
        					continue;
        				}
        				$imageId = $image[ $vvv['filename'] ] ?? 0;
        				if(empty($imageId)){
        					continue;
        				}
        				// 存在
        				$imageStatus[$dataCenterId][$imageId] = 1;
        				$imageDataCenterServerLink[] = [
        					'server_id'=>$serverId,
        					'module_idcsmart_cloud_data_center_id'=>$dataCenterId,
        					'module_idcsmart_cloud_image_id'=>$imageId,
        				];
        			}
        		}
        		ImageDataCenterServerLinkModel::where('server_id', $serverId)->where('module_idcsmart_cloud_data_center_id', $dataCenterId)->delete();
    			if(!empty($imageDataCenterServerLink)){
    				$ImageDataCenterServerLinkModel->insertAll($imageDataCenterServerLink);
    			}
        	}
        }
        ImageDataCenterLinkModel::startTrans();
        try{
        	foreach($imageStatus as $k=>$v){
        		$exist = array_keys($v);

        		ImageDataCenterLinkModel::where('module_idcsmart_cloud_data_center_id', $k)->update(['is_exist'=>0]);
        		ImageDataCenterLinkModel::where('module_idcsmart_cloud_data_center_id', $k)->whereIn('module_idcsmart_cloud_image_id', $exist)->update(['is_exist'=>1]);
        	}
        	ImageDataCenterLinkModel::commit();
        }catch(\Exception $e){
        	ImageDataCenterLinkModel::rollback();
        }
        Cache::delete($cacheKey);
		return $result;
	}


}