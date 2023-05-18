<?php
namespace app\admin\model;

use think\Model;

/**
 * @title 管理员登录模型
 * @desc 管理员登录模型
 * @use app\admin\model\AdminLoginModel
 */
class AdminLoginModel extends Model
{
    protected $name = 'admin_login';

    // 设置字段信息
    protected $schema = [
        'admin_id'        => 'int',
        'last_login_ip'   => 'string',
        'last_action_time'=> 'int',
        'create_time'     => 'int',
        'jwt_key'         => 'string',
    ];

    /**
     * 时间 2022-05-23
     * @title 管理员登录记录
     * @desc 管理员登录记录
     * @author wyh
     * @version v1
     * @param int admin_id - 管理员ID
     * @return bool
     */
    public function adminLogin($admin_id)
    {
        $ip = get_client_ip();
        $time = time();
        #$key = rand_str(30);

        $adminLogin = $this->where('last_login_ip',$ip)
            ->where('admin_id',$admin_id)
            ->find();
        if (!empty($adminLogin)){
            $result = $adminLogin->save([
                'last_action_time' => $time,
                #'jwt_key' => $key
            ]);
        }else{
            $result = $this->create([
                'admin_id' => $admin_id,
                'last_login_ip' => $ip,
                'last_action_time' => $time,
                'create_time' => $time,
                #'jwt_key' => $key
            ]);
        }

        return $result?true:false;
    }

    # 获取jwt的签发密钥
    public function getJwtKey($admin_id)
    {
        $key = $this->where('admin_id',$admin_id)
            ->where('last_login_ip',get_client_ip())
            ->value('jwt_key');
        return $key?:'';
    }

}