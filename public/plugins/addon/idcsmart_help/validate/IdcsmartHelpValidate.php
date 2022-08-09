<?php
namespace addon\idcsmart_help\validate;

use think\Validate;
use addon\idcsmart_help\IdcsmartHelp;

/**
 * 帮助中心验证
 */
class IdcsmartHelpValidate extends Validate
{
    protected $rule = [
        'id'                            => 'require|integer|gt:0',
        'title'                         => 'require|max:150',
        'addon_idcsmart_help_type_id'   => 'require|integer|gt:0',
        'keywords'                      => 'max:150',
        'attachment'                    => 'array',
        'content'                       => 'require',
        'hidden'                        => 'require|in:0,1',
        'name'                          => 'require|max:100',
        'index'                         => 'require|checkIndex:thinkphp',
    ];

    protected $message = [
        'id.require'                            => 'id_error',
        'id.integer'                            => 'id_error',
        'id.gt'                                 => 'id_error',
        'title.require'                         => 'title_require',
        'title.max'                             => 'title_max',
        'addon_idcsmart_help_type_id.require'   => 'addon_idcsmart_help_type_id_error',
        'addon_idcsmart_help_type_id.integer'   => 'addon_idcsmart_help_type_id_error',
        'addon_idcsmart_help_type_id.gt'        => 'addon_idcsmart_help_type_id_error',
        'keywords.max'                          => 'keywords_max',
        'attachment.array'                      => 'param_error',
        'content.require'                       => 'content_require',
        'hidden.require'                        => 'param_error',
        'hidden.in'                             => 'param_error',
        'name.require'                          => 'name_require',
        'name.max'                              => 'name_max',
        'index.require'                         => 'param_error',
        'index.checkIndex'                      => 'param_error',
    ];

    protected $scene = [
        'create' => ['title', 'addon_idcsmart_help_type_id', 'keywords', 'attachment', 'content', 'hidden'],
        'update' => ['id', 'title', 'addon_idcsmart_help_type_id', 'keywords', 'attachment', 'content', 'hidden'],
        'hidden' => ['id', 'hidden'],
        'create_type' => ['name'],
        'update_type' => ['id','name'],
        'index' => ['index'],
    ];

    public function checkIndex($value)
    {
        if(!is_array($value)){
            return false;
        }
        if(!empty(array_diff_key($value, array_values($value)))){
            return false;
        }
        if(count($value)!=6){
            return false;
        }
        if(!empty($value)){
            if(count(array_filter(array_column($value, 'id')))!=count(array_filter(array_unique(array_column($value, 'id'))))){
                return false;
            }
        }
        
        foreach ($value as $k => $v) {
            if(isset($v['id'])){
                if(!is_integer($v['id'])){
                    return false;
                }
                if($v['id']<0){
                    return false;
                }
            }else{
                return false;
            }
            if(isset($v['index_hot_show'])){
                if(!in_array($v['index_hot_show'], [0, 1])){
                    return false;
                }
            }else{
                return false;
            }
            if(isset($v['helps'])){
                if(!is_array($v['helps'])){
                    return false;
                }
                if(!empty($v['helps']) && empty($v['id'])){
                    return false;
                }
                if(!empty($v['helps'])){
                    if(count($v['helps'])>3){
                        return false;
                    }
                    if(count(array_filter(array_column($v['helps'], 'id')))!=count(array_filter(array_unique(array_column($v['helps'], 'id'))))){
                        return false;
                    }
                }
                foreach ($v['helps'] as $hk => $hv) {
                    if(isset($hv['id'])){
                        if(!is_integer($hv['id'])){
                            return false;
                        }
                        if($hv['id']<=0){
                            return false;
                        }
                    }else{
                        return false;
                    }
                }
            }
            
        }
        return true;
    }
}