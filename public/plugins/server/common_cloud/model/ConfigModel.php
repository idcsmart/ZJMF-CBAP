<?php 
namespace server\common_cloud\model;

use think\Model;
use think\db\Query;
use app\common\model\ServerModel;
use app\common\model\HostModel;
use app\common\model\ProductModel;
use app\common\model\CountryModel;
use app\common\model\OrderModel;
use server\common_cloud\idcsmart_cloud\IdcsmartCloud;
use server\common_cloud\logic\ToolLogic;

class ConfigModel extends Model{

	protected $name = 'module_common_cloud_config';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'product_type'      => 'int', // 暂时不用
        'support_ssh_key'   => 'int',
        'buy_data_disk'     => 'int',
        'price'             => 'float',
        'disk_min_size'     => 'int',
        'disk_max_size'     => 'int',
        'disk_max_num'      => 'int',
        'disk_store_id'     => 'string',
        'backup_enable'     => 'int',
        'snap_enable'       => 'int',
        'product_id'        => 'int',
    ];

    /**
     * 时间 2022-06-20
     * @title 获取设置
     * @desc 获取设置
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @return  int data.product_type - 产品模式(0=固定配置,1=自定义配置)
     * @return  int data.support_ssh_key - 是否支持SSH密钥(0=不支持,1=支持)
     * @return  int data.buy_data_disk - 是否支持独立订购(0=不支持,1=支持)
     * @return  float data.price - 每10G价格
     * @return  string data.disk_min_size - 最小容量
     * @return  string data.disk_max_size - 最大容量
     * @return  int data.disk_max_num - 最大附加数量
     * @return  string data.disk_store_id - 储存ID
     * @return  int data.backup_enable - 是否启用备份(0=不启用,1=启用)
     * @return  int data.snap_enable - 是否启用快照(0=不启用,1=启用)
     */
    public function indexConfig($param){
        $ProductModel = ProductModel::find($param['product_id'] ?? 0);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'common_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }

        $where = [];
        $where[] = ['product_id', '=', $param['product_id']];

        $config = $this
                ->where($where)
                ->find();
        if(empty($config)){
            $config = $this->getDefaultConfig();

            $insert = $config;
            $insert['product_id'] = $param['product_id'];
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
     * @param  int param.product_id - 商品ID require
     * @param  int data.product_type - 产品模式(0=固定配置,1=自定义配置)
     * @param  int data.support_ssh_key - 是否支持SSH密钥(0=不支持,1=支持)
     * @param  int data.buy_data_disk - 是否支持独立订购(0=不支持,1=支持)
     * @param  float data.price - 每10G价格 buy_data_disk=1时require生效
     * @param  string data.disk_min_size - 最小容量 buy_data_disk=1时require生效
     * @param  string data.disk_max_size - 最大容量 buy_data_disk=1时require生效
     * @param  int data.disk_max_num - 最大附加数量 buy_data_disk=1时require生效
     * @param  string data.disk_store_id - 储存ID buy_data_disk=1时生效
     * @param  int data.backup_enable - 是否启用备份(0=不启用,1=启用)
     * @param  int data.snap_enable - 是否启用快照(0=不启用,1=启用)
     */
    public function saveConfig($param){
        $ProductModel = ProductModel::find($param['product_id']);
        if(empty($ProductModel)){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_found')];
        }
        if($ProductModel->getModule() != 'common_cloud'){
            return ['status'=>400, 'msg'=>lang_plugins('product_not_link_idcsmart_cloud_module')];
        }
        // 要更新的数据
        $data = [];
        if(isset($param['product_type']) && is_numeric($param['product_type'])){
            $data['product_type'] = $param['product_type'];
        }
        if(isset($param['support_ssh_key']) && is_numeric($param['support_ssh_key'])){
            $data['support_ssh_key'] = $param['support_ssh_key'];
        }
        if(isset($param['buy_data_disk']) && is_numeric($param['buy_data_disk'])){
            $data['buy_data_disk'] = $param['buy_data_disk'];

            if($param['buy_data_disk'] == 1){
                $data['price'] = $param['price'];
                $data['disk_min_size'] = $param['disk_min_size'];
                $data['disk_max_size'] = $param['disk_max_size'];
                $data['disk_max_num'] = $param['disk_max_num'];

                if(isset($param['disk_store_id'])){
                    $data['disk_store_id'] = $param['disk_store_id'];
                }
            }
        }
        if(isset($param['backup_enable']) && is_numeric($param['backup_enable'])){
            $data['backup_enable'] = $param['backup_enable'];
        }
        if(isset($param['snap_enable']) && is_numeric($param['snap_enable'])){
            $data['snap_enable'] = $param['snap_enable'];
        }
        if(empty($data)){
            return ['status'=>400, 'msg'=>lang_plugins('param_error')];
        }

        $config = $this->where('product_id', $param['product_id'])->find();
        if(empty($config)){
            $config = $this->getDefaultConfig();

            $insert = $config;
            $insert['product_id'] = $param['product_id'];
            $this->insert($insert);
        }
        $this->update($data, ['product_id'=>$param['product_id']]);

        $switch = [lang_plugins('switch_off'), lang_plugins('switch_on')];

        $desc = [
            'support_ssh_key'=>lang_plugins('support_ssh_key'),
            'buy_data_disk'=>lang_plugins('buy_data_disk'),
            'price'=>lang_plugins('per_10_price'),
            'disk_min_size'=>lang_plugins('disk_min_size'),
            'disk_max_size'=>lang_plugins('disk_max_size'),
            'disk_max_num'=>lang_plugins('max_add_disk_num'),
            'disk_store_id'=>lang_plugins('store_id'),
            'backup_enable'=>lang_plugins('backup_enable'),
            'snap_enable'=>lang_plugins('snap_enable'),
        ];

        $config['support_ssh_key'] = $switch[ $config['support_ssh_key'] ];
        $config['buy_data_disk'] = $switch[ $config['buy_data_disk'] ];
        $config['backup_enable'] = $switch[ $config['backup_enable'] ];
        $config['snap_enable'] = $switch[ $config['snap_enable'] ];

        if(isset($data['support_ssh_key']))  $data['support_ssh_key'] = $switch[ $data['support_ssh_key'] ];
        if(isset($data['buy_data_disk'])) $data['buy_data_disk'] = $switch[ $data['buy_data_disk'] ];
        if(isset($data['backup_enable'])) $data['backup_enable'] = $switch[ $data['backup_enable'] ];
        if(isset($data['snap_enable'])) $data['snap_enable'] = $switch[ $data['snap_enable'] ];

        $description = ToolLogic::createEditLog($config, $data, $desc);
        if(!empty($description)){
            $description = lang_plugins('log_modify_config_success', [
                '{detail}'=>$description,
            ]);
            active_log($description, 'product', $param['product_id']);
        }
        return ['status'=>200, 'msg'=>lang_plugins('update_success')];
    }


    /**
     * 时间 2022-06-22
     * @title 前台获取所有设置
     * @desc 前台获取所有设置
     * @author hh
     * @version v1
     * @param   int param.product_id - 商品ID require
     * @return  int data.product_type - 产品模式(0=固定配置,1=自定义配置)
     * @return  int data.support_ssh_key - 是否支持SSH密钥(0=不支持,1=支持)
     * @return  int data.buy_data_disk - 是否支持独立订购(0=不支持,1=支持)
     * @return  float data.price - 每10G价格
     * @return  string data.disk_min_size - 最小容量
     * @return  string data.disk_max_size - 最大容量
     * @return  int data.disk_max_num - 最大附加数量
     * @return  int data.backup_enable - 是否启用备份(0=不启用,1=启用)
     * @return  int data.snap_enable - 是否启用快照(0=不启用,1=启用)
     * @return  int data.backup_option[].num - 备份数量
     * @return  string data.backup_option[].price - 价格
     * @return  int data.snap_option[].num - 快照数量
     * @return  string data.snap_option[].price - 价格
     */
    public function homeConfig($param){
        $where = [];
        $where[] = ['product_id', '=', $param['product_id']];

        $config = $this
                ->where($where)
                ->find();

        if(empty($config)){
            $config = $this->getDefaultConfig();
        }
        unset($config['disk_store_id']);

        $config['backup_option'] = [];
        $config['snap_option'] = [];

        $BackupConfigModel = new BackupConfigModel();
        if($config['backup_enable']){
            $res = $BackupConfigModel->backupConfigList(['product_id'=>$param['product_id'], 'type'=>'backup']);

            $config['backup_option'] = $res['data']['list'];
        }
        if($config['snap_enable']){
            $res = $BackupConfigModel->backupConfigList(['product_id'=>$param['product_id'], 'type'=>'snap']);

            $config['snap_option'] = $res['data']['list'];
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
     * @title 获取默认其他设置
     * @desc 获取默认其他设置
     * @author hh
     * @version v1
     * @return  int product_type - 产品模式(0=固定配置,1=自定义配置)
     * @return  int support_ssh_key - 是否支持SSH密钥(0=不支持,1=支持)
     * @return  int buy_data_disk - 是否支持独立订购(0=不支持,1=支持)
     * @return  float price - 每10G价格
     * @return  string disk_min_size - 最小容量
     * @return  string disk_max_size - 最大容量
     * @return  int disk_max_num - 最大附加数量
     * @return  string disk_store_id - 储存ID
     * @return  int backup_enable - 是否启用备份(0=不启用,1=启用)
     * @return  int snap_enable - 是否启用快照(0=不启用,1=启用)
     */
    public function getDefaultConfig(){
        $defaultConfig = [
            'product_type'=>0,
            'support_ssh_key'=>0,
            'buy_data_disk'=>0,
            'price'=>0,
            'disk_min_size'=>'',
            'disk_max_size'=>'',
            'disk_max_num'=>1,
            'disk_store_id'=>'',
            'backup_enable'=>0,
            'snap_enable'=>0,
        ];
        return $defaultConfig;
    }

    /**
     * 时间 2022-09-27
     * @title 验证磁盘大小数量
     * @desc 
     * @author hh
     * @version v1
     * @param   [type] $data        [description]
     * @param   [type] $ConfigModel [description]
     * @return  [type]              [description]
     */
    public function checkDiskArr($data, $ConfigModel = null){
        $ConfigModel = $ConfigModel ?? $this;
        if(count($data) > $ConfigModel['disk_max_num']){
            return ['status'=>400, 'msg'=>'最多只可购买'.$ConfigModel['disk_max_num'].'个额外磁盘'];
        }
        $size = 0;
        foreach($data as $v){
            $check = $this->checkDisk($v);
            if($check['status'] == 400){
                return $check;
            }
            $size += $v;
        }
        return ['status'=>200, 'data'=>['size'=>$size]];
    }

    public function checkDisk($size, $ConfigModel = null){
        $ConfigModel = $ConfigModel ?? $this;

        if(!is_numeric($size) || !preg_match('/^[\d]+$/', $size)){
            return ['status'=>400, 'msg'=>'额外数据盘大小只能是数字'];
        }
        if(!empty($ConfigModel['disk_min_size']) && $size < $ConfigModel['disk_min_size']){
            return ['status'=>400, 'msg'=>'额外数据盘大小错误'];
        }
        if(!empty($ConfigModel['disk_max_size']) && $size > $ConfigModel['disk_max_size']){
            return ['status'=>400, 'msg'=>'额外数据盘大小错误'];
        }
        if($size%10 != 0){
            return ['status'=>400, 'msg'=>'磁盘大小只能是10的倍数'];
        }
        return ['status'=>200, 'msg'=>'成功'];
    }


    /**
     * 时间 2022-09-25
     * @title 
     * @desc 
     * @author hh
     * @version v1
     * @param   string x             - x
     * @return  [type] [description]
     */
    public function calConfigPrice($param){
        // 验证产品和用户
        $host = HostModel::find($param['id']);
        if(empty($host) || $host['status'] != 'Active'){
            return ['status'=>400, 'msg'=>lang_plugins('产品未开通')];
        }
        // 前台判断
        $app = app('http')->getName();
        if($app == 'home'){
            if($host['client_id'] != get_client_id()){
                return ['status'=>400, 'msg'=>lang_plugins('host_is_not_exist')];
            }
        }    
        $hostLink = HostLinkModel::where('host_id', $param['id'])->find();
        if(empty($hostLink)){
            return ['status'=>400, 'msg'=>lang_plugins('产品未开通')];
        }
        if( $hostLink[ $param['type'].'_num' ] == $param['num']){
            return ['status'=>400, 'msg'=>'数量没有变动'];
        }
        $ConfigModel = ConfigModel::where('product_id', $host['product_id'])->find();

        $type = ['backup'=>'备份','snap'=>'快照'];

        $ServerModel = ServerModel::find($host['server_id']);
        $IdcsmartCloud = new IdcsmartCloud($ServerModel);
        // 当前已用数量
        $res = $IdcsmartCloud->cloudSnapshot($hostLink['rel_id'], ['per_page'=>999, 'type'=>$param['type']]);
        if($res['status'] != 200){
            return ['status'=>400, 'msg'=>'产品状态异常,请联系管理员'];
        }
        if($param['num'] < ($res['data']['meta']['total'] ?? 0 )){
            return ['status'=>400, 'msg'=>'当前已用数量超过'.$param['num'].',请删除后再降级'];
        }
        if(!isset($ConfigModel[$param['type'].'_enable']) || $ConfigModel[$param['type'].'_enable'] == 0){
            return ['status'=>400, 'msg'=>'不能购买'.$type[$param['type']]];
        }
        $arr = BackupConfigModel::where('product_id', $host['product_id'])
            ->where('type', $param['type'])
            ->select()
            ->toArray();
        $arr = array_column($arr, 'price', 'num');
        if(!isset($arr[ $param['num'] ])){
            return ['status'=>400, 'msg'=>'数量错误'];
        }

        $diffTime = $this->hostModel['due_time'] - time();
        // 获取之前的周期
        $duration = HostLinkModel::getDuration($this->hostModel);

        $price = 0;
        $priceDifference = 0;

        // 原价,找不到数量就当成0
        $oldPrice = $arr[ $hostLink[ $param['type'].'_num' ] ] ?? 0;
        
        if($this->hostModel['billing_cycle'] == 'onetime' || $diffTime<=0 || $this->hostModel['billing_cycle_time'] == 0){
            // 不允许白嫖
            $price = $arr[ $param['num'] ] - $oldPrice;
        }else{
            $price = ( $arr[ $param['num'] ] - $oldPrice ) * $diffTime/$this->hostModel['billing_cycle_time'];
        }
        $price = bcmul($price, $duration['num']);

        $priceDifference = $arr[ $param['num'] ] - $oldPrice;

        $description = $type[$param['type']].'数量：'.$hostLink[ $param['type'].'_num' ].' => '.$param['num'];

        $price = max(0, $price);
        $price = amount_format($price);
        
        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'price' => $price,
                'description' => $description,
                'price_difference' => $priceDifference,
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-07-29
     * @title 生成磁盘扩容订单
     * @desc 生成磁盘扩容订单
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID require
     * @param   array remove_disk_id - 要取消订购的磁盘ID
     * @param   array add_disk - 新增磁盘大小
     * @return  int status - 状态码(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  string data.id - 订单ID
     */
    public function createBackupConfigOrder($param){
        $res = $this->calConfigPrice($param);
        if($res['status'] == 400){
            return $res;
        }

        $OrderModel = new OrderModel();

        $data = [
            'host_id'     => $param['id'],
            'client_id'   => get_client_id(),
            'type'        => 'upgrade_config',
            'amount'      => $res['data']['price'],
            'description' => $res['data']['description'],
            'price_difference' => $res['data']['price_difference'],
            'config_options' => [
                'type'       => 'modify_backup',
                'backup_type' => $param['type'],
                'num' => $param['num'],
            ]
        ];
        return $OrderModel->createOrder($data);
    }



}