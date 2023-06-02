<?php 
namespace server\common_cloud\model;

use think\Model;
use think\db\Query;
use app\common\model\ServerModel;
use app\common\model\ProductModel;
use app\common\model\CountryModel;
// use server\idcsmart_cloud\logic\ToolLogic;

class ImageGroupModel extends Model{

	protected $name = 'module_common_cloud_image_group';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'product_id'    => 'int',
        'name'          => 'string',
    ];



}