<?php 
namespace server\mf_dcim\model;

use think\Model;
use think\db\Query;
use app\common\model\ServerModel;
use app\common\model\HostModel;
use app\common\model\ProductModel;
use app\common\model\CountryModel;
use app\common\model\OrderModel;
use server\mf_dcim\logic\ToolLogic;

class ConfigModel extends Model{

	protected $name = 'module_mf_dcim_config';

    // 设置字段信息
    protected $schema = [
        'id'                        => 'int',
        'product_id'                => 'int',
        'rand_ssh_port'             => 'int',
        'reinstall_sms_verify'      => 'int',
        'reset_password_sms_verify' => 'int',
        'manual_resource'           => 'int',
    ];

    /**
     * 时间 2022-06-20
     * @title 获取设置
     * @desc 获取设置
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @return  int rand_ssh_port - 随机SSH端口(0=关闭,1=开启)
     * @return  int reinstall_sms_verify - 重装短信验证(0=不启用,1=启用)
     * @return  int reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用)
     * @param  int manual_resource - 手动资源(0=不启用,1=启用)
     */
    public function indexConfig($param){
        $ProductModel = ProductModel::find($param['product_id'] ?? 0);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_dcim'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_mf_dcim_module')];
        }

        $where = [];
        $where[] = ['product_id', '=', $param['product_id']];

        $config = $this
                ->where($where)
                ->find();
        if(empty($config)){
            $config = $this->getDefaultConfig();

            $insert = $config;
            $insert['product_id'] = $ProductModel->id;
            $this->insert($insert);
        }else{
            unset($config['id'], $config['product_id']);
        }

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => $config,
        ];
        return $result;
    }

    /**
     * 时间 2022-06-20
     * @title 保存其他设置
     * @desc 保存其他设置
     * @author hh
     * @version v1
     * @param   int product_id - 商品ID require
     * @param  int rand_ssh_port - 随机SSH端口(0=关闭,1=开启)
     * @param  int reinstall_sms_verify - 重装短信验证(0=不启用,1=启用)
     * @param  int reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用)
     * @param  int manual_resource - 手动资源(0=不启用,1=启用)
     */
    public function saveConfig($param){
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'mf_dcim'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $productId = $ProductModel->id;
        
        $config = $this->where('product_id', $param['product_id'])->find();
        if(empty($config)){
            $config = $this->getDefaultConfig();

            $insert = $config;
            $insert['product_id'] = $param['product_id'];
            $this->insert($insert);
        }
        $this->update($param, ['product_id'=>$param['product_id']], ['rand_ssh_port','reinstall_sms_verify','reset_password_sms_verify','manual_resource']);

        $switch = [lang_plugins('mf_dcim_switch_off'), lang_plugins('mf_dcim_switch_on')];

        $des = [
            'rand_ssh_port'             => lang_plugins('mf_dcim_rand_ssh_port'),
            'reinstall_sms_verify'      => lang_plugins('mf_dcim_reinstall_sms_verify'),
            'reset_password_sms_verify' => lang_plugins('mf_dcim_reset_password_sms_verify'),
            'manual_resource'           => lang_plugins('mf_dcim_manual_resource'),
        ];

        $config['rand_ssh_port'] = $switch[ $config['rand_ssh_port'] ];
        $config['reinstall_sms_verify'] = $switch[ $config['reinstall_sms_verify'] ];
        $config['reset_password_sms_verify'] = $switch[ $config['reset_password_sms_verify'] ];
        $config['manual_resource'] = $switch[ $config['manual_resource'] ];
        if(isset($param['rand_ssh_port']))  $param['rand_ssh_port'] = $switch[ $param['rand_ssh_port'] ];
        if(isset($param['reinstall_sms_verify']))  $param['reinstall_sms_verify'] = $switch[ $param['reinstall_sms_verify'] ];
        if(isset($param['reset_password_sms_verify']))  $param['reset_password_sms_verify'] = $switch[ $param['reset_password_sms_verify'] ];
        if(isset($param['manual_resource']))  $param['manual_resource'] = $switch[ $param['manual_resource'] ];
        
        $description = ToolLogic::createEditLog($config, $param, $des);
        if(!empty($description)){
            $description = lang_plugins('mf_dcim_log_modify_config_success', [
                '{detail}'=>$description,
            ]);
            active_log($description, 'product', $param['product_id']);
        }
        return ['status'=>200, 'msg'=>lang_plugins('update_success')];
    }

    /**
     * 时间 2023-02-01
     * @title 获取默认其他设置
     * @desc 获取默认其他设置
     * @author hh
     * @version v1
     * @return  string rand_ssh_port - 随机SSH端口(0=关闭,1=开启)
     * @return  int reinstall_sms_verify - 重装短信验证(0=不启用,1=启用)
     * @return  int reset_password_sms_verify - 重置密码短信验证(0=不启用,1=启用)
     * @return  int manual_resource - 手动资源(0=不启用,1=启用)
     */
    public function getDefaultConfig(){
        $defaultConfig = [
            'rand_ssh_port'             => '0',
            'reinstall_sms_verify'      => '0',
            'reset_password_sms_verify' => '0',
            'manual_resource'           => '0',
        ];
        return $defaultConfig;
    }

}