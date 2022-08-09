<?php
namespace addon\idcsmart_ssh_key\validate;

use think\Validate;

/**
 * SSH密钥验证
 */
class IdcsmartSshKeyValidate extends Validate
{
    protected $rule = [
        'id'            => 'require|integer|gt:0',
        'name'          => 'require|max:10',
        'public_key'    => 'require|checkSshKey:thinkphp',
    ];

    protected $message = [
        'id.require'                => 'id_error',
        'id.integer'                => 'id_error',
        'id.gt'                     => 'id_error',
        'name.require'              => 'addon_idcsmart_ssh_key_name_require',
        'name.max'                  => 'addon_idcsmart_ssh_key_name_max',
        'public_key.require'        => 'addon_idcsmart_ssh_key_public_key_require',
        'public_key.checkSshKey'    => 'addon_idcsmart_ssh_key_public_key_error',
        
    ];

    protected $scene = [
        'create' => ['name', 'public_key'],
        'update' => ['id', 'name', 'public_key'],
    ];

    public function checkSshKey($value)
    {
        $startWith = [
            'ssh-rsa ',
            'ecdsa-sha2-nistp256 ',
            // 'ecdsa-sha2-nistp384 ',
            // 'ecdsa-sha2-nistp521 ',
            'ssh-ed25519 ',
            // 'sk-ecdsa-sha2-nistp256@openssh.com ',
            // 'sk-ssh-ed25519@openssh.com ',
        ];
        $res = false;
        foreach($startWith as $v){
            if(strpos($value, $v) === 0){
                $res = true;
                break;
            }
        }
        return $res;
    }
}