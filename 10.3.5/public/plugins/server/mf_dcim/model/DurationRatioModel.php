<?php 
namespace server\mf_dcim\model;

use app\common\model\ProductDurationRatioModel;

/**
 * @title 周期比例模型
 * @use server\mf_dcim\model\DurationRatioModel
 */
class DurationRatioModel extends ProductDurationRatioModel{

    // 关联的周期表名
    protected $linkTable = 'module_mf_dcim_duration';

}