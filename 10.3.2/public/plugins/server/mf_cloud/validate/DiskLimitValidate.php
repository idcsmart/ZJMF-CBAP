<?php
namespace server\mf_cloud\validate;

use think\Validate;

/**
 * @title 性能限制验证
 * @use  server\mf_cloud\validate\DiskLimitValidate
 */
class DiskLimitValidate extends Validate{

	protected $rule = [
        'id'                => 'require|integer',
        'product_id'        => 'require|integer',
        'min_value'         => 'require|integer|between:0,1048576',
        'max_value'         => 'require|integer|between:1,1048576|gt:min_value',
        'read_bytes'        => 'integer|between:0,99999999',
        'write_bytes'       => 'integer|between:0,99999999',
        'read_iops'         => 'require|integer|between:0,99999999',
        'write_iops'        => 'require|integer|between:0,99999999',
    ];

    protected $message = [
        'id.require'                    => 'id_error',
        'id.integer'                    => 'id_error',
        'product_id.require'            => 'product_id_error',
        'product_id.integer'            => 'product_id_error',
        'min_value.require'             => 'please_input_disk_min_value',
        'min_value.integer'             => 'disk_min_value_format_error',
        'min_value.between'             => 'disk_min_value_format_error',
        'max_value.require'             => 'please_input_disk_max_value',
        'max_value.integer'             => 'disk_max_value_format_error',
        'max_value.between'             => 'disk_max_value_format_error',
        'max_value.gt'                  => 'disk_max_value_must_gt_disk_min_value',
        // 'read_bytes.require'            => 'please_input_read_bytes',
        'read_bytes.integer'            => 'read_bytes_format_error',
        'read_bytes.between'            => 'read_bytes_format_error',
        // 'write_bytes.require'           => 'please_input_write_bytes',
        'write_bytes.integer'           => 'write_bytes_format_error',
        'write_bytes.between'           => 'write_bytes_format_error',
        'read_iops.require'             => 'please_input_read_iops',
        'read_iops.integer'             => 'read_iops_format_error',
        'read_iops.between'             => 'read_iops_format_error',
        'write_iops.require'            => 'please_input_write_iops',
        'write_iops.integer'            => 'write_iops_format_error',
        'write_iops.between'            => 'write_iops_format_error',
    ];

    protected $scene = [
        'create' => ['product_id','min_value','max_value','read_bytes','write_bytes','read_iops','write_iops'],
        'update' => ['id','min_value','max_value','read_bytes','write_bytes','read_iops','write_iops'],
    ];


}