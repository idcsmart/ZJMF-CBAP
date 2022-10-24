<?php

use server\idcsmart_cloud\model\CalGroupModel;
use server\idcsmart_cloud\model\CalModel;
use server\idcsmart_cloud\model\BwTypeModel;
use server\idcsmart_cloud\model\BwModel;
use server\idcsmart_cloud\model\DataCenterModel;
use server\idcsmart_cloud\model\DataCenterServerLinkModel;
use server\idcsmart_cloud\model\ImageGroupModel;
use server\idcsmart_cloud\model\ImageModel;
use server\idcsmart_cloud\model\ImageDataCenterServerLinkModel;
use server\idcsmart_cloud\model\PackageModel;
use server\idcsmart_cloud\model\DurationPriceModel;
use app\common\model\OrderModel;
use app\common\model\OrderItemModel;
use server\idcsmart_cloud\logic\CloudLogic;

add_hook('after_server_delete', function($param){
	DataCenterServerLinkModel::where('server_id', $param['id'])->delete();
	ImageDataCenterServerLinkModel::where('server_id', $param['id'])->delete();
});

add_hook('after_product_delete', function($param){
	CalGroupModel::where('product_id', $param['id'])->delete();
	CalModel::where('product_id', $param['id'])->delete();
	BwTypeModel::where('product_id', $param['id'])->delete();
	BwModel::where('product_id', $param['id'])->delete();
	ImageGroupModel::where('product_id', $param['id'])->delete();
	ImageModel::where('product_id', $param['id'])->delete();
	DurationPriceModel::where('product_id', $param['id'])->delete();
	PackageModel::where('product_id', $param['id'])->delete();

	$dataCenterId = DataCenterModel::where('product_id', $param['id'])->column('id');
	if(!empty($dataCenterId)){
		DataCenterModel::whereIn('id', $dataCenterId)->delete();
		DataCenterServerLinkModel::whereIn('module_idcsmart_cloud_data_center_id', $dataCenterId)->delete();
	}
});

add_hook('order_paid', function($param){
	//$order = OrderModel::find($param['id']);
	$orderItem = OrderItemModel::where('order_id', $param['id'])->where('type', 'idcsmart_cloud_template')->find();
});

add_hook('task_run', function($param){
    if($param['type'] == 'module_idcsmart_cloud_change_vpc_network'){
  //       $success = false;
        $data = json_decode($param['task_data'], true);
        
		$CloudLogic = new CloudLogic($data['id']);

		$CloudLogic->changeVpcTaskRun($data);
		// $success = true;
    	return 'Finish';

        // return $success ? 'Finish' : 'Failed';
    }
});

