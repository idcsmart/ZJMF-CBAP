<?php 

use think\facade\Route;

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

# 前台,可登录不可登录的接口
Route::group('console/v1',function (){

	// 订购页面
    Route::get('product/:id/mf_cloud/order_page', "\\server\\mf_cloud\\controller\\home\\CloudController@orderPage");
    Route::get('product/:id/mf_cloud/image', "\\server\\mf_cloud\\controller\\home\\CloudController@imageList");
    Route::post('product/:id/mf_cloud/duration', "\\server\\mf_cloud\\controller\\home\\CloudController@getAllDurationPrice");
    Route::get('product/:id/mf_cloud/config_limit', "\\server\\mf_cloud\\controller\\home\\CloudController@getAllConfigLimit");
    Route::get('product/:id/mf_cloud/vpc_network/search', "\\server\\mf_cloud\\controller\\home\\CloudController@vpcNetworkSearch");
    Route::get('product/:id/mf_cloud/line/:line_id', "\\server\\mf_cloud\\controller\\home\\CloudController@lineConfig");
    Route::get('product/:id/mf_cloud/data_center', "\\server\\mf_cloud\\controller\\home\\CloudController@dataCenterSelect");

    // vnc
    Route::get('mf_cloud/:id/vnc', "\\server\\mf_cloud\\controller\\home\\CloudController@vncPage");


})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\Check::class);

// 前台需要登录的接口
Route::group('console/v1',function (){

	Route::post('product/:id/mf_cloud/validate_settle', "\\server\\mf_cloud\\controller\\home\\CloudController@validateSettle");
	Route::get('mf_cloud', "\\server\\mf_cloud\\controller\\home\\CloudController@list");
	Route::get('mf_cloud/:id', "\\server\\mf_cloud\\controller\\home\\CloudController@detail");
	Route::get('mf_cloud/:id/part', "\\server\\mf_cloud\\controller\\home\\CloudController@detailPart");
	Route::get('mf_cloud/:id/status', "\\server\\mf_cloud\\controller\\home\\CloudController@status");
	Route::post('mf_cloud/:id/on', "\\server\\mf_cloud\\controller\\home\\CloudController@on");
	Route::post('mf_cloud/:id/off', "\\server\\mf_cloud\\controller\\home\\CloudController@off");
	Route::post('mf_cloud/:id/reboot', "\\server\\mf_cloud\\controller\\home\\CloudController@reboot");
	Route::post('mf_cloud/:id/hard_off', "\\server\\mf_cloud\\controller\\home\\CloudController@hardOff");
	Route::post('mf_cloud/:id/hard_reboot', "\\server\\mf_cloud\\controller\\home\\CloudController@hardReboot");
	Route::post('mf_cloud/:id/vnc', "\\server\\mf_cloud\\controller\\home\\CloudController@vnc");
	// Route::get('mf_cloud/:id/vnc', "\\server\\mf_cloud\\controller\\home\\CloudController@vncPage");
	Route::post('mf_cloud/:id/reset_password', "\\server\\mf_cloud\\controller\\home\\CloudController@resetPassword");
	Route::post('mf_cloud/:id/rescue', "\\server\\mf_cloud\\controller\\home\\CloudController@rescue");
	Route::post('mf_cloud/:id/rescue/exit', "\\server\\mf_cloud\\controller\\home\\CloudController@exitRescue");
	Route::post('mf_cloud/:id/reinstall', "\\server\\mf_cloud\\controller\\home\\CloudController@reinstall");
	Route::get('mf_cloud/:id/chart', "\\server\\mf_cloud\\controller\\home\\CloudController@chart");
	Route::get('mf_cloud/:id/disk', "\\server\\mf_cloud\\controller\\home\\CloudController@disk");
	Route::post('mf_cloud/:id/disk/:disk_id/unmount', "\\server\\mf_cloud\\controller\\home\\CloudController@diskUnmount");
	Route::post('mf_cloud/:id/disk/:disk_id/mount', "\\server\\mf_cloud\\controller\\home\\CloudController@diskMount");
	Route::get('mf_cloud/:id/snapshot', "\\server\\mf_cloud\\controller\\home\\CloudController@snapshot");
	Route::post('mf_cloud/:id/snapshot', "\\server\\mf_cloud\\controller\\home\\CloudController@snapshotCreate");
	Route::post('mf_cloud/:id/snapshot/restore', "\\server\\mf_cloud\\controller\\home\\CloudController@snapshotRestore");
	Route::delete('mf_cloud/:id/snapshot/:snapshot_id', "\\server\\mf_cloud\\controller\\home\\CloudController@snapshotDelete");
	Route::get('mf_cloud/:id/backup', "\\server\\mf_cloud\\controller\\home\\CloudController@backup");
	Route::post('mf_cloud/:id/backup', "\\server\\mf_cloud\\controller\\home\\CloudController@backupCreate");
	Route::post('mf_cloud/:id/backup/restore', "\\server\\mf_cloud\\controller\\home\\CloudController@backupRestore");
	Route::delete('mf_cloud/:id/backup/:backup_id', "\\server\\mf_cloud\\controller\\home\\CloudController@backupDelete");
	Route::get('mf_cloud/:id/flow', "\\server\\mf_cloud\\controller\\home\\CloudController@flowDetail");
	Route::get('mf_cloud/:id/log', "\\server\\mf_cloud\\controller\\home\\CloudController@log");
	Route::get('mf_cloud/:id/image/check', "\\server\\mf_cloud\\controller\\home\\CloudController@checkHostImage");
	Route::post('mf_cloud/:id/image/order', "\\server\\mf_cloud\\controller\\home\\CloudController@createImageOrder");
	Route::get('mf_cloud/:id/remote_info', "\\server\\mf_cloud\\controller\\home\\CloudController@remoteInfo");
	Route::get('mf_cloud/:id/ip', "\\server\\mf_cloud\\controller\\home\\CloudController@ipList");
	Route::post('mf_cloud/:id/disk/price', "\\server\\mf_cloud\\controller\\home\\CloudController@calBuyDiskPrice");
	Route::post('mf_cloud/:id/disk/order', "\\server\\mf_cloud\\controller\\home\\CloudController@createBuyDiskOrder");
	Route::post('mf_cloud/:id/disk/resize', "\\server\\mf_cloud\\controller\\home\\CloudController@calResizeDiskPrice");
	Route::post('mf_cloud/:id/disk/resize/order', "\\server\\mf_cloud\\controller\\home\\CloudController@createResizeDiskOrder");

	Route::get('mf_cloud/:id/backup_config', "\\server\\mf_cloud\\controller\\home\\CloudController@calBackupConfigPrice");
	Route::post('mf_cloud/:id/backup_config/order', "\\server\\mf_cloud\\controller\\home\\CloudController@createBackupConfigOrder");

	Route::get('mf_cloud/:id/ip_num', "\\server\\mf_cloud\\controller\\home\\CloudController@calIpNumPrice");
	Route::post('mf_cloud/:id/ip_num/order', "\\server\\mf_cloud\\controller\\home\\CloudController@createIpNumOrder");

	Route::post('mf_cloud/:id/vpc_network', "\\server\\mf_cloud\\controller\\home\\CloudController@createVpcNetwork");
	Route::get('mf_cloud/:id/vpc_network', "\\server\\mf_cloud\\controller\\home\\CloudController@vpcNetworkList");
	Route::put('mf_cloud/:id/vpc_network/:vpc_network_id', "\\server\\mf_cloud\\controller\\home\\CloudController@vpcNetworkUpdate");
	Route::delete('mf_cloud/:id/vpc_network/:vpc_network_id', "\\server\\mf_cloud\\controller\\home\\CloudController@vpcNetworkDelete");
	Route::put('mf_cloud/:id/vpc_network', "\\server\\mf_cloud\\controller\\home\\CloudController@changeVpcNetwork");
	Route::get('mf_cloud/:id/real_data', "\\server\\mf_cloud\\controller\\home\\CloudController@cloudRealData");

	Route::get('mf_cloud/:id/common_config', "\\server\\mf_cloud\\controller\\home\\CloudController@calCommonConfigPrice");
	Route::post('mf_cloud/:id/common_config/order', "\\server\\mf_cloud\\controller\\home\\CloudController@createCommonConfigOrder");

	
	    
})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])
->middleware(\app\http\middleware\CheckHome::class)
->middleware(\app\http\middleware\ParamFilter::class)
->middleware(\server\mf_cloud\middleware\CheckAuthMiddleware::class);


# 后台,可登录不可登录的接口
Route::group(DIR_ADMIN . '/v1',function (){
    
    // Route::get('mf_cloud/:id/vnc', "\\server\\mf_cloud\\controller\\admin\\CloudController@vncPage");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\Check::class);

// 后台接口
Route::group(DIR_ADMIN . '/v1',function (){
    
    // 周期
	Route::post('mf_cloud/duration', "\\server\\mf_cloud\\controller\\admin\\DurationController@create");
	Route::get('mf_cloud/duration', "\\server\\mf_cloud\\controller\\admin\\DurationController@list");
	Route::put('mf_cloud/duration/:id', "\\server\\mf_cloud\\controller\\admin\\DurationController@update");
	Route::delete('mf_cloud/duration/:id', "\\server\\mf_cloud\\controller\\admin\\DurationController@delete");
	
	// CPU配置
	Route::post('mf_cloud/cpu', "\\server\\mf_cloud\\controller\\admin\\CpuController@create");
	Route::get('mf_cloud/cpu', "\\server\\mf_cloud\\controller\\admin\\CpuController@list");
	Route::put('mf_cloud/cpu/:id', "\\server\\mf_cloud\\controller\\admin\\CpuController@update");
	Route::delete('mf_cloud/cpu/:id', "\\server\\mf_cloud\\controller\\admin\\CpuController@delete");
	Route::get('mf_cloud/cpu/:id', "\\server\\mf_cloud\\controller\\admin\\CpuController@index");

	// 内存配置
	Route::post('mf_cloud/memory', "\\server\\mf_cloud\\controller\\admin\\MemoryController@create");
	Route::get('mf_cloud/memory', "\\server\\mf_cloud\\controller\\admin\\MemoryController@list");
	Route::put('mf_cloud/memory/:id', "\\server\\mf_cloud\\controller\\admin\\MemoryController@update");
	Route::delete('mf_cloud/memory/:id', "\\server\\mf_cloud\\controller\\admin\\MemoryController@delete");
	Route::get('mf_cloud/memory/:id', "\\server\\mf_cloud\\controller\\admin\\MemoryController@index");

	// 系统盘配置
	Route::post('mf_cloud/system_disk', "\\server\\mf_cloud\\controller\\admin\\SystemDiskController@create");
	Route::get('mf_cloud/system_disk', "\\server\\mf_cloud\\controller\\admin\\SystemDiskController@list");
	Route::put('mf_cloud/system_disk/:id', "\\server\\mf_cloud\\controller\\admin\\SystemDiskController@update");
	Route::delete('mf_cloud/system_disk/:id', "\\server\\mf_cloud\\controller\\admin\\SystemDiskController@delete");
	Route::get('mf_cloud/system_disk/:id', "\\server\\mf_cloud\\controller\\admin\\SystemDiskController@index");
	Route::get('mf_cloud/system_disk/type', "\\server\\mf_cloud\\controller\\admin\\SystemDiskController@diskTypeList");

	// 数据盘配置
	Route::post('mf_cloud/data_disk', "\\server\\mf_cloud\\controller\\admin\\DataDiskController@create");
	Route::get('mf_cloud/data_disk', "\\server\\mf_cloud\\controller\\admin\\DataDiskController@list");
	Route::put('mf_cloud/data_disk/:id', "\\server\\mf_cloud\\controller\\admin\\DataDiskController@update");
	Route::delete('mf_cloud/data_disk/:id', "\\server\\mf_cloud\\controller\\admin\\DataDiskController@delete");
	Route::get('mf_cloud/data_disk/:id', "\\server\\mf_cloud\\controller\\admin\\DataDiskController@index");
	Route::get('mf_cloud/data_disk/type', "\\server\\mf_cloud\\controller\\admin\\DataDiskController@diskTypeList");

	// 系统盘性能限制
	Route::post('mf_cloud/system_disk_limit', "\\server\\mf_cloud\\controller\\admin\\SystemDiskController@diskLimitCreate");
	Route::get('mf_cloud/system_disk_limit', "\\server\\mf_cloud\\controller\\admin\\SystemDiskController@diskLimitList");
	Route::put('mf_cloud/system_disk_limit/:id', "\\server\\mf_cloud\\controller\\admin\\SystemDiskController@diskLimitUpdate");
	Route::delete('mf_cloud/system_disk_limit/:id', "\\server\\mf_cloud\\controller\\admin\\SystemDiskController@diskLimitDelete");

	// 数据盘性能限制
	Route::post('mf_cloud/data_disk_limit', "\\server\\mf_cloud\\controller\\admin\\DataDiskController@diskLimitCreate");
	Route::get('mf_cloud/data_disk_limit', "\\server\\mf_cloud\\controller\\admin\\DataDiskController@diskLimitList");
	Route::put('mf_cloud/data_disk_limit/:id', "\\server\\mf_cloud\\controller\\admin\\DataDiskController@diskLimitUpdate");
	Route::delete('mf_cloud/data_disk_limit/:id', "\\server\\mf_cloud\\controller\\admin\\DataDiskController@diskLimitDelete");

	// 数据中心
	Route::post('mf_cloud/data_center', "\\server\\mf_cloud\\controller\\admin\\DataCenterController@create");
	Route::get('mf_cloud/data_center', "\\server\\mf_cloud\\controller\\admin\\DataCenterController@list");
	Route::put('mf_cloud/data_center/:id', "\\server\\mf_cloud\\controller\\admin\\DataCenterController@update");
	Route::delete('mf_cloud/data_center/:id', "\\server\\mf_cloud\\controller\\admin\\DataCenterController@delete");
	Route::get('mf_cloud/data_center/select', "\\server\\mf_cloud\\controller\\admin\\DataCenterController@dataCenterSelect");

	// 配置限制
	Route::post('mf_cloud/config_limit', "\\server\\mf_cloud\\controller\\admin\\ConfigLimitController@create");
	Route::get('mf_cloud/config_limit', "\\server\\mf_cloud\\controller\\admin\\ConfigLimitController@list");
	Route::put('mf_cloud/config_limit/:id', "\\server\\mf_cloud\\controller\\admin\\ConfigLimitController@update");
	Route::delete('mf_cloud/config_limit/:id', "\\server\\mf_cloud\\controller\\admin\\ConfigLimitController@delete");

	// 操作系统分类
	Route::post('mf_cloud/image_group', "\\server\\mf_cloud\\controller\\admin\\ImageController@imageGroupCreate");
	Route::get('mf_cloud/image_group', "\\server\\mf_cloud\\controller\\admin\\ImageController@imageGroupList");
	Route::put('mf_cloud/image_group/:id', "\\server\\mf_cloud\\controller\\admin\\ImageController@imageGroupUpdate");
	Route::delete('mf_cloud/image_group/:id', "\\server\\mf_cloud\\controller\\admin\\ImageController@imageGroupDelete");
	Route::put('mf_cloud/image_group/order', "\\server\\mf_cloud\\controller\\admin\\ImageController@imageGroupOrder");

	// 操作系统
	Route::post('mf_cloud/image', "\\server\\mf_cloud\\controller\\admin\\ImageController@imageCreate");
	Route::get('mf_cloud/image', "\\server\\mf_cloud\\controller\\admin\\ImageController@imageList");
	Route::put('mf_cloud/image/:id', "\\server\\mf_cloud\\controller\\admin\\ImageController@imageUpdate");
	Route::delete('mf_cloud/image/:id', "\\server\\mf_cloud\\controller\\admin\\ImageController@imageDelete");
	Route::get('mf_cloud/image/sync', "\\server\\mf_cloud\\controller\\admin\\ImageController@imageSync");
	Route::put('mf_cloud/image/:id/enable', "\\server\\mf_cloud\\controller\\admin\\ImageController@toggleImageEnable");

	// 其他设置
	Route::put('mf_cloud/config', "\\server\\mf_cloud\\controller\\admin\\ConfigController@save");
	Route::get('mf_cloud/config', "\\server\\mf_cloud\\controller\\admin\\ConfigController@index");
	Route::put('mf_cloud/config/disk_limit_enable', "\\server\\mf_cloud\\controller\\admin\\ConfigController@toggleDiskLimitEnable");
	Route::post('mf_cloud/config/check_clear', "\\server\\mf_cloud\\controller\\admin\\ConfigController@checkClear");

	// 线路
	Route::post('mf_cloud/line', "\\server\\mf_cloud\\controller\\admin\\LineController@create");
	Route::put('mf_cloud/line/:id', "\\server\\mf_cloud\\controller\\admin\\LineController@update");
	Route::delete('mf_cloud/line/:id', "\\server\\mf_cloud\\controller\\admin\\LineController@delete");
	Route::get('mf_cloud/line/:id', "\\server\\mf_cloud\\controller\\admin\\LineController@index");

	Route::post('mf_cloud/line/:id/line_bw', "\\server\\mf_cloud\\controller\\admin\\LineController@lineBwCreate");
	Route::get('mf_cloud/line_bw/:id', "\\server\\mf_cloud\\controller\\admin\\LineController@lineBwIndex");
	Route::put('mf_cloud/line_bw/:id', "\\server\\mf_cloud\\controller\\admin\\LineController@lineBwUpdate");
	Route::delete('mf_cloud/line_bw/:id', "\\server\\mf_cloud\\controller\\admin\\LineController@lineBwDelete");

	Route::post('mf_cloud/line/:id/line_flow', "\\server\\mf_cloud\\controller\\admin\\LineController@lineFlowCreate");
	Route::get('mf_cloud/line_flow/:id', "\\server\\mf_cloud\\controller\\admin\\LineController@lineFlowIndex");
	Route::put('mf_cloud/line_flow/:id', "\\server\\mf_cloud\\controller\\admin\\LineController@lineFlowUpdate");
	Route::delete('mf_cloud/line_flow/:id', "\\server\\mf_cloud\\controller\\admin\\LineController@lineFlowDelete");

	Route::post('mf_cloud/line/:id/line_defence', "\\server\\mf_cloud\\controller\\admin\\LineController@lineDefenceCreate");
	Route::get('mf_cloud/line_defence/:id', "\\server\\mf_cloud\\controller\\admin\\LineController@lineDefenceIndex");
	Route::put('mf_cloud/line_defence/:id', "\\server\\mf_cloud\\controller\\admin\\LineController@lineDefenceUpdate");
	Route::delete('mf_cloud/line_defence/:id', "\\server\\mf_cloud\\controller\\admin\\LineController@lineDefenceDelete");

	Route::post('mf_cloud/line/:id/line_ip', "\\server\\mf_cloud\\controller\\admin\\LineController@lineIpCreate");
	Route::get('mf_cloud/line_ip/:id', "\\server\\mf_cloud\\controller\\admin\\LineController@lineIpIndex");
	Route::put('mf_cloud/line_ip/:id', "\\server\\mf_cloud\\controller\\admin\\LineController@lineIpUpdate");
	Route::delete('mf_cloud/line_ip/:id', "\\server\\mf_cloud\\controller\\admin\\LineController@lineIpDelete");

	// 推荐配置
	Route::post('mf_cloud/recommend_config', "\\server\\mf_cloud\\controller\\admin\\RecommendConfigController@create");
	Route::get('mf_cloud/recommend_config', "\\server\\mf_cloud\\controller\\admin\\RecommendConfigController@list");
	Route::put('mf_cloud/recommend_config/:id', "\\server\\mf_cloud\\controller\\admin\\RecommendConfigController@update");
	Route::delete('mf_cloud/recommend_config/:id', "\\server\\mf_cloud\\controller\\admin\\RecommendConfigController@delete");


	// Route::delete('mf_cloud/:id/template/:template_id', "\\server\\mf_cloud\\controller\\admin\\CloudController@templateDelete");

})->allowCrossDomain([
    'Access-Control-Allow-Origin'        => $origin,
    'Access-Control-Allow-Credentials'   => 'true',
    'Access-Control-Max-Age'             => 600,
])->middleware(\app\http\middleware\CheckAdmin::class);

// Route::get(DIR_ADMIN . '/v1/mf_cloud/:id/vnc', "\\server\\mf_cloud\\controller\\admin\\CloudController@vncPage");