<?php
namespace app\common\model;

use think\Model;

/**
 * @title 用户登录模型
 * @desc 用户登录模型
 * @use app\common\model\ClientLoginModel
 */
class ClientLoginModel extends Model
{
    protected $name = 'client_login';

    // 设置字段信息
    protected $schema = [
        'client_id'       => 'int',
        'last_login_ip'   => 'string',
        'last_action_time'=> 'int',
        'create_time'     => 'int',
        'jwt_key'         => 'string',
        'login_times'     => 'int',
    ];

    /**
     * 时间 2022-05-23
     * @title 用户登录记录
     * @desc 用户登录记录
     * @author wyh
     * @version v1
     * @param int client_id - 用户ID
     * @return bool
     */
    public function clientLogin($client_id)
    {
        $ip = get_client_ip();
        $time = time();
        #$key = rand_str(30);

        $clientLogin = $this->where('last_login_ip',$ip)
            ->where('client_id',$client_id)
            ->find();
        if (!empty($clientLogin)){
            $result = $clientLogin->save([
                'last_action_time' => $time,
                'login_times'      => $clientLogin['login_times'] + 1,
            ]);
        }else{
            $result = $this->create([
                'client_id'         => $client_id,
                'last_login_ip'     => $ip,
                'last_action_time'  => $time,
                'create_time'       => $time,
                'login_times'       => 1,
            ]);
        }

        return $result?true:false;
    }

    # 获取jwt的签发密钥(预留在这)
    public function getJwtKey($client_id)
    {
        $key = $this->where('client_id',$client_id)
            ->where('last_login_ip',get_client_ip())
            ->value('jwt_key');
        return $key?:'';
    }

    /**
     * 时间 2024-05-21
     * @title 是否常用IP登录
     * @desc  是否常用IP登录
     * @author hh
     * @version v1
     * @param   int param.client_id - 用户ID require
     * @return  boolean
     */
    public function isCommonIpLogin($param)
    {
        $homeLoginCheckCommonIp = configuration(['home_login_check_common_ip']);
        if($homeLoginCheckCommonIp){
            $ip = get_client_ip();
            $clientId = $param['client_id'];

            // 获取常用登录IP,登录3次以上的IP视为常用IP
            $commonIp = $this
                        ->where('client_id', $clientId)
                        ->where('login_times', '>=', 3)
                        ->column('last_login_ip');

            // 如果有常用IP
            if(!empty($commonIp)){
                if(!in_array($ip, $commonIp)){
                    // 该IP是否首次登录
                    $first = $this
                            ->where('client_id', $clientId)
                            ->where('last_login_ip', $ip)
                            ->find();
                    if(empty($first)){
                        return false;
                    }
                }
            }
        }
        return true;
    }


}
