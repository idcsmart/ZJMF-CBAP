<?php 
namespace server\idcsmart_cloud\model;

use think\Model;
use think\db\Query;
use app\common\model\ProductModel;

class ConfigModel extends Model{

	protected $name = 'module_idcsmart_cloud_config';

    // 设置字段信息
    protected $schema = [
        'id'             => 'int',
        'backup_enable'  => 'int',
        'backup_price'   => 'float',
        'backup_param'   => 'string',
        'panel_enable'   => 'int',
        'panel_price'    => 'float',
        'panel_param'    => 'string',
        'snap_enable'    => 'int',
        'snap_free_num'  => 'int',
        'snap_price'     => 'float',
        'hostname_rule'  => 'int',
        'product_id'     => 'int',
        'create_time'    => 'int',
        'update_time'    => 'int',
    ];
	
    /**
     * 时间 2022-06-20
     * @title 获取其他设置
     * @desc 获取其他设置
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  object data - 设置数据
     * @return  int data.backup_enable - 是否启用备份价格(0=不启用,1=启用)
     * @return  string data.backup_price - 备份价格
     * @return  string data.backup_param - 备份参数
     * @return  int data.panel_enable - 是否启用独立面板价格(0=不启用,1=启用)
     * @return  string data.panel_price - 独立面板价格
     * @return  string data.panel_param - 独立面板参数
     * @return  int data.snap_enable - 是否启用快照价格(0=不启用,1=启用)
     * @return  int data.snap_free_num - 免费快照数量
     * @return  string data.snap_price - 快照价格
     * @return  int data.hostname_rule - 主机名规则(1=日期+4位随机,2=8位随机,3=月日+4位随机)
     */
    public function indexConfig($param){
        $ProductModel = ProductModel::find($param['product_id'] ?? 0);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'idcsmart_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }

        $where = [];
        $where[] = ['product_id', '=', $param['product_id']];

        $config = $this
                ->field('backup_enable,backup_price,backup_param,panel_enable,panel_price,panel_param,snap_enable,snap_free_num,snap_price,hostname_rule')
                ->where($where)
                ->find();
        if(empty($config)){
            $config = $this->getDefaultConfig();

            $insert = $config;
            $insert['product_id'] = $param['product_id'];
            $this->insert($insert);
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
     * @param  int param.product_id - 商品ID require
     * @param  int param.backup_enable - 是否启用备份价格(0=不启用,1=启用) require
     * @param  string param.backup_price - 备份价格 require
     * @param  string param.backup_param - 备份参数 require
     * @param  int param.panel_enable - 是否启用独立面板价格(0=不启用,1=启用) require
     * @param  string param.panel_price - 独立面板价格 require
     * @param  string param.panel_param - 独立面板参数 require
     * @param  int param.snap_enable - 是否启用快照价格(0=不启用,1=启用) require
     * @param  int param.snap_free_num - 免费快照数量 require
     * @param  string param.snap_price - 快照价格 require
     * @param  int param.hostname_rule - 主机名规则(1=日期+4位随机,2=8位随机,3=月日+4位随机) require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function saveConfig($param){
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'idcsmart_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        $config = $this->where('product_id', $param['product_id'])->find();
        if(!empty($config)){
            $param['update_time'] = time();
            $this->update($param, ['id'=>$config['id']], ['backup_enable','backup_price','backup_param','panel_enable','panel_price','panel_param','snap_enable','snap_free_num','snap_price','hostname_rule','product_id','update_time']);
        }else{
            $param['create_time'] = time();
            $this->create($param, ['backup_enable','backup_price','backup_param','panel_enable','panel_price','panel_param','snap_enable','snap_free_num','snap_price','hostname_rule','product_id','update_time']);
        }
        return ['status'=>200, 'msg'=>lang_plugins('update_success')];
    }


    /**
     * 时间 2022-06-22
     * @title 前台获取其他设置
     * @desc 前台获取其他设置
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  object data - 设置数据
     * @return  int data.backup_enable - 是否启用备份价格(0=不启用,1=启用)
     * @return  string data.backup_price - 备份价格
     * @return  int data.panel_enable - 是否启用独立面板价格(0=不启用,1=启用)
     * @return  string data.panel_price - 独立面板价格
     * @return  int data.snap_enable - 是否启用快照价格(0=不启用,1=启用)
     * @return  int data.snap_free_num - 免费快照数量
     * @return  string data.snap_price - 快照价格
     * @return  int data.hostname_rule - 主机名规则(1=日期+4位随机,2=8位随机,3=月日+4位随机)
     */
    public function homeConfig($param){
        $where = [];
        $where[] = ['product_id', '=', $param['product_id']];

        $config = $this
                ->field('backup_enable,backup_price,panel_enable,panel_price,snap_enable,snap_free_num,snap_price,hostname_rule')
                ->where($where)
                ->find();

        if(empty($config)){
            $config = $this->getDefaultConfig();
            unset($config['backup_param'], $config['panel_param']);
        }
        // 是否支持vpc/安全组
        // $config['support_vpc'] = class_exists('addon\idcsmart_vpc\model\IdcsmartVpcModel');
        // $config['support_security_group'] = class_exists('addon\idcsmart_vpc\model\IdcsmartVpcModel');

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => $config,
        ];
        return $result;
    }

    /**
     * 时间 2022-06-20
     * @title 获取默认其他设置
     * @desc 获取默认其他设置
     * @author hh
     * @version v1
     * @return  int backup_enable - 是否启用备份价格(0=不启用,1=启用)
     * @return  string backup_price - 备份价格
     * @return  string backup_param - 备份参数
     * @return  int panel_enable - 是否启用独立面板价格(0=不启用,1=启用)
     * @return  string panel_price - 独立面板价格
     * @return  string panel_param - 独立面板参数
     * @return  int snap_enable - 是否启用快照价格(0=不启用,1=启用)
     * @return  int snap_free_num - 免费快照数量
     * @return  string snap_price - 快照价格
     * @return  int hostname_rule - 主机名规则(1=日期+4位随机,2=8位随机,3=月日+4位随机)
     * @return  int product_id - 商品ID
     */
    public function getDefaultConfig(){
        $defaultConfig = [
            'backup_enable'=>0,
            'backup_price'=>'0.00',
            'backup_param'=>'',
            'panel_enable'=>0,
            'panel_price'=>'0.00',
            'panel_param'=>'',
            'snap_enable'=>0,
            'snap_free_num'=>0,
            'snap_price'=>'0.00',
            'hostname_rule'=>1,
        ];
        return $defaultConfig;
    }



}

