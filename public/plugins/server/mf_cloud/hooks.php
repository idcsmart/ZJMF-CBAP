<?php

use app\common\model\ProductModel;
use server\mf_cloud\model\DurationModel;

// 商品保存后预设商品默认值
add_hook('after_product_edit', function($param){
	$ProductModel = ProductModel::find($param['id']);
	if($ProductModel->getModule() != 'mf_cloud'){
		return false;
	}
	$productId = $ProductModel->id;
	$time = time();
	// 预设周期
	$add = DurationModel::value('id');
	if(empty($add)){
		$DurationModel = new DurationModel();
		$DurationModel->insertAll([
			[
				'product_id'    => $productId,
		        'name'          => '月',
		        'num'           => 1,
		        'unit'          => 'month',
		        'create_time'   => $time,
			],
			[
				'product_id'    => $productId,
		        'name'          => '季度',
		        'num'           => 3,
		        'unit'          => 'month',
		        'create_time'   => $time,
			],
			[
				'product_id'    => $productId,
		        'name'          => '半年',
		        'num'           => 6,
		        'unit'          => 'month',
		        'create_time'   => $time,
			],
			[
				'product_id'    => $productId,
		        'name'          => '年',
		        'num'           => 12,
		        'unit'          => 'month',
		        'create_time'   => $time,
			],
		]);
	}

});

add_hook('after_product_delete', function($param){
	/*
	try{
		// 加入异常,有可能表不存在
		// $imageId = ImageModel::where('product_id', $param['id'])->column('id');

		// BackupConfigModel::where('product_id', $param['id'])->delete();
		// ConfigModel::where('product_id', $param['id'])->delete();
		// DataCenterModel::where('product_id', $param['id'])->delete();
		// ImageGroupModel::where('product_id', $param['id'])->delete();
		// ImageModel::where('product_id', $param['id'])->delete();
		// PackageModel::where('product_id', $param['id'])->delete();
		
		// if(!empty($imageId)){
		// 	HostImageLinkModel::whereIn('image_id', $imageId)->delete();
		// }
	}catch(\Exception $e){
		
	}
	*/
});

add_hook('after_host_delete', function($param){
	try{
		HostLinkModel::where('host_id', $param['id'])->delete();
	}catch(\Exception $e){
		
	}
});
