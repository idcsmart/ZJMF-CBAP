<?php
namespace addon\idcsmart_file_download\validate;

use think\Validate;
use addon\idcsmart_file_download\IdcsmartFileDownload;

/**
 * 文件下载验证
 */
class IdcsmartFileDownloadValidate extends Validate
{
    protected $rule = [
        'id'                            => 'require|integer|gt:0',
        'file'                          => 'require|checkFile:thinkphp',
        'name'                          => 'require|max:100',
        'addon_idcsmart_file_folder_id' => 'require|integer|gt:0',
        'visible_range'                 => 'require|in:all,host,product',
        'product_id'                    => 'requireIf:visible_range,product|array',
        'hidden'                        => 'require|in:0,1',
    ];

    protected $message = [
        'id.require'                            => 'id_error',
        'id.integer'                            => 'id_error',
        'id.gt'                                 => 'id_error',
        'file.require'                          => 'file_require',
        'file.checkFile'                        => 'param_error',
        'name.require'                          => 'idcsmart_file_download_name_require',
        'name.max'                              => 'idcsmart_file_download_name_max',
        'addon_idcsmart_file_folder_id.require' => 'addon_idcsmart_file_folder_id_error',
        'addon_idcsmart_file_folder_id.integer' => 'addon_idcsmart_file_folder_id_error',
        'addon_idcsmart_file_folder_id.gt'      => 'addon_idcsmart_file_folder_id_error',
        'visible_range.require'                 => 'visible_range_require',
        'visible_range.in'                      => 'param_error',
        'product_id.requireIf'                  => 'product_id_error',
        'product_id.array'                      => 'product_id_error',
        'hidden.require'                        => 'param_error',
        'hidden.in'                             => 'param_error',
    ];

    protected $scene = [
        'create' => ['file'],
        'update' => ['id', 'name', 'addon_idcsmart_file_folder_id', 'visible_range', 'product_id'],
        'hidden' => ['id', 'hidden'],
        'move' => ['id', 'addon_idcsmart_file_folder_id'],
        'create_folder' => ['name'],
        'update_folder' => ['id','name'],
    ];

    public function checkFile($value)
    {
        if(!is_array($value)){
            return false;
        }
        if(!empty($value)){
            if(count(array_filter(array_column($value, 'filename')))!=count(array_filter(array_unique(array_column($value, 'filename'))))){
                return false;
            }
        }
        foreach ($value as $k => $v) {
            if(!isset($v['name'])){
                return false;
            }
            if(empty($v['name'])){
                return false;
            }
            if(strlen($v['name'])>150){
                return false;
            }
            if(!isset($v['addon_idcsmart_file_folder_id'])){
                return false;
            }
            if(empty($v['addon_idcsmart_file_folder_id'])){
                return false;
            }
            if(!is_integer($v['addon_idcsmart_file_folder_id'])){
                return false;
            }
            if(!isset($v['filename'])){
                return false;
            }
            if(empty($v['filename'])){
                return false;
            }
            if(!isset($v['visible_range'])){
                return false;
            }
            if(empty($v['visible_range'])){
                return false;
            }
            if(!in_array($v['visible_range'], ['all', 'host', 'product'])){
                return false;
            }
            if(!isset($v['product_id']) && $v['visible_range']=='product'){
                return false;
            }
            if(isset($v['product_id'])){
                if(empty($v['product_id']) && $v['visible_range']=='product'){
                    return false;
                }
                if(!is_array($v['product_id']) && $v['visible_range']=='product'){
                    return false;
                }
            }
            
            if(!isset($v['hidden'])){
                return false;
            }
            if(!in_array($v['hidden'], [0, 1])){
                return false;
            }
        }
        return true;
    }
}