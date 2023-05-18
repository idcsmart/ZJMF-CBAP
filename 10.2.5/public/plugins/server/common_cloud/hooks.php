<?php

use server\common_cloud\model\BackupConfigModel;
use server\common_cloud\model\ConfigModel;
use server\common_cloud\model\DataCenterModel;
use server\common_cloud\model\HostImageLinkModel;
use server\common_cloud\model\HostLinkModel;
use server\common_cloud\model\ImageGroupModel;
use server\common_cloud\model\ImageModel;
use server\common_cloud\model\PackageModel;

add_hook('after_product_delete', function($param){
	try{
		// 加入异常,有可能表不存在
		$imageId = ImageModel::where('product_id', $param['id'])->column('id');

		BackupConfigModel::where('product_id', $param['id'])->delete();
		ConfigModel::where('product_id', $param['id'])->delete();
		DataCenterModel::where('product_id', $param['id'])->delete();
		ImageGroupModel::where('product_id', $param['id'])->delete();
		ImageModel::where('product_id', $param['id'])->delete();
		PackageModel::where('product_id', $param['id'])->delete();
		
		if(!empty($imageId)){
			HostImageLinkModel::whereIn('image_id', $imageId)->delete();
		}
	}catch(\Exception $e){
		
	}
});