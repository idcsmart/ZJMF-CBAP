<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 物理服务器轮播图验证
 */
class PhysicalServerBannerValidate extends Validate
{
    protected $rule = [
        'id'            => 'require|integer|gt:0',
        'img'           => 'require',
        'url'           => 'require|max:255|url',
        'start_time'    => 'require|integer|egt:0',
        'end_time'      => 'require|integer|egt:start_time',
        'show'          => 'require|in:0,1',
        'notes'         => 'max:1000',
    ];

    protected $message = [
        'id.require'                => 'id_error',
        'id.integer'                => 'id_error',
        'id.gt'                     => 'id_error',
        'img.require'               => 'physical_server_banner_img_require',
        'url.require'               => 'physical_server_banner_url_require',
        'url.max'                   => 'physical_server_banner_url_error',
        'url.url'                   => 'physical_server_banner_url_error',
        'start_time.require'        => 'physical_server_banner_start_time_require',
        'start_time.integer'        => 'param_error',
        'start_time.egt'            => 'param_error',
        'end_time.require'          => 'physical_server_banner_end_time_require',
        'end_time.integer'          => 'param_error',
        'end_time.egt'              => 'physical_server_banner_end_time_egt',
        'show.require'              => 'param_error',
        'show.in'                   => 'param_error',
        'notes.max'                 => 'physical_server_banner_notes_max',
    ];

    protected $scene = [
        'create' => ['img', 'url', 'start_time', 'end_time', 'show', 'notes'],
        'update' => ['id', 'img', 'url', 'start_time', 'end_time', 'show', 'notes'],
        'show' => ['id', 'show'],
    ];
}