<?php 
namespace server\mf_cloud\model;

use think\Model;
use think\db\Query;

/**
 * @title 套餐升降机范围模型
 * @use   server\mf_cloud\model\RecommendConfigUpgradeRangeModel
 */
class RecommendConfigUpgradeRangeModel extends Model{

	protected $name = 'module_mf_cloud_recommend_config_upgrade_range';

    // 设置字段信息
    protected $schema = [
        'recommend_config_id'                => 'int',
        'rel_recommend_config_id'            => 'int',
    ];

}