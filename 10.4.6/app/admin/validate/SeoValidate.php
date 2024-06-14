<?php
namespace app\admin\validate;

use think\Validate;

/**
 * SEO验证
 */
class SeoValidate extends Validate
{
	protected $rule = [
		'id'            => 'require|integer|gt:0',
        'title'         => 'require|max:100',
        'page_address'  => 'require|max:100',
        'keywords'      => 'require|max:255',
        'description'   => 'require',
    ];

    protected $message = [
    	'id.require'            => 'id_error',
        'id.integer'            => 'id_error',
        'id.gt'                 => 'id_error',
        'title.require'         => 'seo_title_require',
        'title.max'             => 'seo_title_error',
        'page_address.require'  => 'seo_page_address_require',
        'page_address.max'      => 'seo_page_address_error',
        'keywords.require'      => 'seo_keywords_require',
        'keywords.max'          => 'seo_keywords_error',
        'description.require'   => 'seo_description_require',
    ];

    protected $scene = [
        'create' => ['title', 'page_address', 'keywords', 'description'],
        'update' => ['id', 'title', 'page_address', 'keywords', 'description'],
    ];

}