<?php 
namespace server\mf_cloud\model;

use think\Model;
use think\db\Query;
use app\common\model\ServerModel;
use app\common\model\ProductModel;
use app\common\model\CountryModel;
use server\mf_cloud\logic\ToolLogic;

/**
 * @title 配置价格模型
 * @use server\mf_cloud\model\PriceModel
 */
class PriceModel extends Model{

	protected $name = 'module_mf_cloud_price';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'product_id'    => 'int',
        'rel_type'     	=> 'int',
        'rel_id'     	=> 'int',
        'duration_id'   => 'int',
        'price'         => 'float',
    ];

    const REL_TYPE_OPTION = 0;
    const REL_TYPE_RECOMMEND_CONFIG = 1;
    


}