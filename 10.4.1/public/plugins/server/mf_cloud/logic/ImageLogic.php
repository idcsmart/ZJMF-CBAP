<?php 
namespace server\mf_cloud\logic;

use think\facade\Db;
use app\common\model\ServerModel;
use app\common\model\ProductModel;
use server\mf_cloud\model\ImageModel;
use server\mf_cloud\model\ImageGroupModel;
use server\mf_cloud\model\ResourcePackageModel;
use server\mf_cloud\idcsmart_cloud\IdcsmartCloud;
use think\facade\Cache;

class ImageLogic
{
	/**
	 * 时间 2022-06-29
	 * @title 拉取镜像
	 * @desc 拉取镜像
	 * @author hh
	 * @version v1
	 * @param   int productId - 商品ID require
	 * @return  int status - 状态(200=成功,400=失败)
	 * @return  string msg - 信息
	 */
	public static function getProductImage($productId)
	{
		$result = ['status'=>200, 'msg'=>lang_plugins('success_message')];

		$cacheKey = 'SYNC_MF_CLOUD_IMAGE_'.$productId;
		if(Cache::has($cacheKey)){
			return $result;
		}
		Cache::set($cacheKey, 1, 180);

		$ProductModel = ProductModel::find($productId);
		if(empty($ProductModel)){
			Cache::delete($cacheKey);
			return ['status'=>400, 'msg'=>lang_plugins('product_id_error')];
		}
		if($ProductModel->getModule() != 'mf_cloud'){
			Cache::delete($cacheKey);
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
		if($ProductModel['type'] == 'server_group'){
			Cache::delete($cacheKey);
			return ['status'=>400, 'msg'=>lang_plugins('only_link_server_can_sync_image')];
		}
		$ServerModel = ServerModel::find($ProductModel['rel_id']);
		$ServerModel['password'] = aes_password_decode($ServerModel['password']);
		$IdcsmartCloud = new IdcsmartCloud($ServerModel);

		$hash = ToolLogic::formatParam($ServerModel['hash']);
        $isAgent = isset($hash['account_type']) && $hash['account_type'] == 'agent';
        $IdcsmartCloud->setIsAgent($isAgent);

        if($isAgent){
        	// 代理商的镜像
        	$userInfo = $IdcsmartCloud->userInfo();
        	if($userInfo['status'] != 200){
        		Cache::delete($cacheKey);
				return ['status'=>400, 'msg'=>lang_plugins('sync_image_failed')];
			}
			// 获取设置的资源包
			$rid = ResourcePackageModel::where('product_id', $productId)->column('rid');
			if(empty($rid)){
				Cache::delete($cacheKey);
				return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_please_set_resource_package_first')];
			}
			if(!isset($userInfo['data']['resource_package'])){
				Cache::delete($cacheKey);
				return ['status'=>400, 'msg'=>lang_plugins('sync_image_failed')];
			}
			$remoteImage = [];
			foreach($userInfo['data']['resource_package'] as $v){
				if(in_array($v['id'], $rid)){
					$remoteImage = array_merge($remoteImage, $v['image']);
				}
			}
			if(!isset($remoteImage[0]['name'])){
				Cache::delete($cacheKey);
				return ['status'=>400, 'msg'=>lang_plugins('mf_cloud_vendors_cannot_sync_image')];
			}

			$imageGroup = ImageGroupModel::field('id,name')->where('product_id', $productId)->select()->toArray();
			$imageGroup = array_column($imageGroup, 'id', 'name') ?? [];

			$image = ImageModel::field('id,rel_image_id')->where('product_id', $productId)->select()->toArray();
			$image = array_column($image, 'id', 'rel_image_id');

			$data = [];
			foreach($remoteImage as $v){
				if(!isset($imageGroup[ $v['image_group_name'] ])){
					$ImageGroupModel = ImageGroupModel::create(['product_id'=>$productId, 'name'=>$v['image_group_name'], 'icon'=>$v['image_group_name'] ]);
					$imageGroup[ $v['image_group_name'] ] = $ImageGroupModel->id;
				}
				if(!isset($image[$v['image_id']])){
					$one = [
						'image_group_id'	=> $imageGroup[ $v['image_group_name'] ],
						'name'				=> $v['name'],
						'enable'			=> 1,
						'charge'			=> 0,
						'price'				=> 0.00,
						'product_id'		=> $productId,
						'rel_image_id'		=> $v['image_id'],
					];

					$data[] = $one;
				}
			}
			if(!empty($data)){
				$ImageModel = new ImageModel();
				$ImageModel->insertAll($data);
			}

			Cache::delete($cacheKey);
			return $result;
        }

		// 先获取镜像分组
		$remoteImageGroup = $IdcsmartCloud->getImageGroup(['per_page'=>50]);
		if($remoteImageGroup['status'] != 200){
			return ['status'=>400, 'msg'=>lang_plugins('sync_image_failed')];
		}
		$imageLink = [];
		foreach($remoteImageGroup['data']['data'] as $v){
			$imageLink[$v['id']] = $v['name'];
		}

		// 添加组
		$imageGroup = ImageGroupModel::field('id,name')->where('product_id', $productId)->select()->toArray();
		$imageGroup = array_column($imageGroup, 'id', 'name') ?? [];
		foreach($imageLink as $v){
			if(empty($imageGroup[$v])){
				$ImageGroupModel = ImageGroupModel::create(['product_id'=>$productId, 'name'=>$v, 'icon'=>$v]);
				$imageGroup[ $v ] = $ImageGroupModel->id;
			}
		}

		$res = $IdcsmartCloud->getImageList(['per_page'=>9999, 'status'=>1]);

		if($res['status'] == 200){
			// 获取当前产品已填加的镜像
			$image = ImageModel::field('id,rel_image_id')->where('product_id', $productId)->select()->toArray();
			$image = array_column($image, 'id', 'rel_image_id');

			$data = [];
			foreach($res['data']['data'] as $v){
				$status = array_column($v['info'], 'status');
				if(!in_array(1, $status) && !in_array(2, $status)){
					continue;
				}
				if(!isset($imageGroup[ $imageLink[$v['image_group_id']] ])){
					$ImageGroupModel = ImageGroupModel::create(['product_id'=>$productId, 'name'=>$imageLink[$v['image_group_id']] ]);
					$imageGroup[ $imageLink[$v['image_group_id']] ] = $ImageGroupModel->id;
				}
				if(!isset($image[$v['id']])){
					$one = [
						'image_group_id'=>$imageGroup[ $imageLink[$v['image_group_id']] ],
						'name'=>$v['name'],
						'enable'=>1,
						'charge'=>0,
						'price'=>0.00,
						'product_id'=>$productId,
						'rel_image_id'=>$v['id'],
					];

					$data[] = $one;
				}
			}
			if(!empty($data)){
				$ImageModel = new ImageModel();
				$ImageModel->insertAll($data);
			}
		}
		Cache::delete($cacheKey);
		return $result;
	}

}