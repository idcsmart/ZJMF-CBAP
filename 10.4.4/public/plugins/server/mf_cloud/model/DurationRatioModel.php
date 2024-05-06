<?php 
namespace server\mf_cloud\model;

use app\common\model\ProductDurationRatioModel;

/**
 * @title 周期比例模型
 * @use server\mf_cloud\model\DurationRatioModel
 */
class DurationRatioModel extends ProductDurationRatioModel
{
    // 关联的周期表名
    protected $linkTable = 'module_mf_cloud_duration';

}