<?php
namespace addon\idcsmart_news\validate;

use think\Validate;
use addon\idcsmart_news\IdcsmartNews;

/**
 * 新闻中心验证
 */
class IdcsmartNewsValidate extends Validate
{
    protected $rule = [
        'id'                            => 'require|integer|gt:0',
        'title'                         => 'require|max:150',
        'addon_idcsmart_news_type_id'   => 'require|integer|gt:0',
        'keywords'                      => 'max:150',
        'attachment'                    => 'array',
        'content'                       => 'require',
        'hidden'                        => 'require|in:0,1',
        'name'                          => 'require|max:100',
        'list'                          => 'require|checkList:thinkphp',
        'cron_release'                  => 'require|in:0,1',
        'cron_release_time'             => 'requireIf:cron_release,1|integer|between:0,99999999999',
    ];

    protected $message = [
        'id.require'                            => 'id_error',
        'id.integer'                            => 'id_error',
        'id.gt'                                 => 'id_error',
        'title.require'                         => 'title_require',
        'title.max'                             => 'title_max',
        'addon_idcsmart_news_type_id.require'   => 'addon_idcsmart_news_type_id_error',
        'addon_idcsmart_news_type_id.integer'   => 'addon_idcsmart_news_type_id_error',
        'addon_idcsmart_news_type_id.gt'        => 'addon_idcsmart_news_type_id_error',
        'keywords.max'                          => 'keywords_max',
        'attachment.array'                      => 'param_error',
        'content.require'                       => 'content_require',
        'hidden.require'                        => 'param_error',
        'hidden.in'                             => 'param_error',
        'name.require'                          => 'name_require',
        'name.max'                              => 'name_max',
        'list.require'                          => 'param_error',
        'list.checkList'                        => 'param_error',
        'cron_release.require'                  => 'param_error',
        'cron_release.in'                       => 'param_error',
        'cron_release_time.requireIf'           => 'addon_idcsmart_news_cron_release_time_require',
        'cron_release_time.integer'             => 'addon_idcsmart_news_cron_release_time_format_error',
        'cron_release_time.between'             => 'addon_idcsmart_news_cron_release_time_format_error',
    ];

    protected $scene = [
        'create' => ['title', 'addon_idcsmart_news_type_id', 'keywords', 'attachment', 'content', 'cron_release', 'cron_release_time'],
        'update' => ['id', 'title', 'addon_idcsmart_news_type_id', 'keywords', 'attachment', 'content', 'cron_release', 'cron_release_time'],
        'hidden' => ['id', 'hidden'],
        'create_type' => ['list'],
        'update_type' => ['id','name'],
    ];

    public function checkList($value)
    {
        if(!is_array($value)){
            return false;
        }
        if(empty($value)){
            return false;
        }
        foreach ($value as $k => $v) {
            if(isset($v['name'])){
                if(!is_string($v['name'])){
                    return false;
                }
                if(empty($v['name'])){
                    return false;
                }
                if(mb_strlen($v['name'])>100){
                    return false;
                }
            }else{
                return false;
            }
        }
        return true;
    }
}