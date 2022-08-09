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
    ];

    protected $scene = [
        'create' => ['title', 'addon_idcsmart_news_type_id', 'keywords', 'attachment', 'content', 'hidden'],
        'update' => ['id', 'title', 'addon_idcsmart_news_type_id', 'keywords', 'attachment', 'content', 'hidden'],
        'hidden' => ['id', 'hidden'],
        'create_type' => ['name'],
        'update_type' => ['id','name'],
    ];
}