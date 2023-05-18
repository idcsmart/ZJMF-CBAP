<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 上下游供应商验证
 */
class SupplierValidate extends Validate
{
    protected $rule = [
        'id'            => 'require|integer|gt:0',
        'type'          => 'require|in:default,whmcs',
        'name'          => 'require|max:50',
        'url'           => 'require|max:255|url',
        'username'      => 'require|max:100',
        'token'         => 'require|max:200',
        'secret'        => 'require',
        'contact'       => 'max:1000',
        'notes'         => 'max:1000',
    ];

    protected $message = [
        'id.require'        => 'id_error',
        'id.integer'        => 'id_error',
        'id.gt'             => 'id_error',
        'type.require'      => 'param_error',
        'type.in'           => 'param_error',
        'name.require'      => 'please_enter_supplier_name',
        'name.max'          => 'supplier_name_cannot_exceed_50_chars',
        'url.require'       => 'please_enter_supplier_url',
        'url.max'           => 'supplier_url_cannot_exceed_255_chars',
        'url.url'           => 'supplier_url_error',
        'username.require'  => 'please_enter_supplier_username',
        'username.max'      => 'supplier_username_cannot_exceed_100_chars',
        'token.require'     => 'please_enter_supplier_token',
        'token.max'         => 'supplier_token_cannot_exceed_200_chars',
        'secret.require'    => 'please_enter_supplier_secret',
        'contact.max'       => 'supplier_contact_cannot_exceed_1000_chars',
        'notes.max'         => 'supplier_notes_cannot_exceed_1000_chars',
    ];

    protected $scene = [
        'create' => ['type', 'name', 'url', 'username', 'token', 'secret', 'contact', 'notes'],
        'update' => ['id', 'type', 'name', 'url', 'username', 'token', 'secret', 'contact', 'notes'],
    ];
}