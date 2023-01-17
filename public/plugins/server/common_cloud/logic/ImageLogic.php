<?php 
namespace server\common_cloud\logic;

use think\facade\Db;
use app\common\model\ServerModel;
use app\common\model\ProductModel;
use server\common_cloud\model\ImageModel;
use server\common_cloud\model\ImageGroupModel;
use server\common_cloud\idcsmart_cloud\IdcsmartCloud;
use think\facade\Cache;

class ImageLogic{

	/**
	 * 时间 2022-06-29
	 * @title 
	 * @desc 
	 * @author hh
	 * @version v1
	 * @param   string x             - x
	 * @return  [type] [description]
	 */
	public static function getProductImage($productId){
		$result = ['status'=>200, 'msg'=>lang_plugins('success_message')];

		$cacheKey = 'SYNC_IDCSMART_CLOUD_IMAGE_'.$productId;
		if(Cache::has($cacheKey)){
			return $result;
		}
		Cache::set($cacheKey, 1, 180);

		$ProductModel = ProductModel::find($productId);
		if(empty($ProductModel)){
			return ['status'=>400, 'msg'=>lang_plugins('product_id_error')];
		}
		if($ProductModel->getModule() != 'common_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
		if($ProductModel['type'] == 'server_group'){
			return ['status'=>400, 'msg'=>lang_plugins('only_link_server_can_sync_image')];
		}
		$ServerModel = ServerModel::find($ProductModel['rel_id']);
		$IdcsmartCloud = new IdcsmartCloud($ServerModel);

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
				$ImageGroupModel = ImageGroupModel::create(['product_id'=>$productId, 'name'=>$v]);
				$imageGroup[ $v ] = $ImageGroupModel->id;
			}
		}
		// TODO 获取之前先刷新镜像?
		$ServerModel = ServerModel::find($ProductModel['rel_id']);
		$ServerModel['password'] = aes_password_decode($ServerModel['password']);
		$IdcsmartCloud = new IdcsmartCloud($ServerModel);

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
						'filename'=>$v['filename'], // 暂时记录不知道有用没
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