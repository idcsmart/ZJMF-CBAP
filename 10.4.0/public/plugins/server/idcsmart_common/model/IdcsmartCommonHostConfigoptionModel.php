<?php
namespace server\idcsmart_common\model;

use think\Model;
use addon\idcsmart_app_market\model\AuthorizeModel;
use app\common\model\HostModel;
use app\common\model\ProductModel;
use app\common\model\ServerModel;
use server\idcsmart_common\IdcsmartCommon;

class IdcsmartCommonHostConfigoptionModel extends Model
{
    protected $name = 'module_idcsmart_common_host_configoption';

    // 设置字段信息
    protected $schema = [
        'id'                     => 'int',
        'host_id'                => 'int',
        'configoption_id'        => 'int',
        'configoption_sub_id'    => 'int',
        'qty'                    => 'int',
        'repeat'                 => 'int',
    ];

    public function hostConfigoptionList($param)
    {
        $hostId = $param['id'] ?? [];
        $hostConfigoptions = $this->field('host_id,configoption_id,configoption_sub_id,qty')
            ->whereIn('host_id',$hostId)
            ->select()
            ->toArray();
        $valueArr = [];
        foreach ($hostConfigoptions as $key => $value) {
            $valueArr[$value['host_id']][$value['configoption_id']] = $value;
        }
        $configoptions = IdcsmartCommonProductConfigoptionModel::field('id,option_name,option_type,qty_min,qty_max')
            ->whereIn('id',array_column($hostConfigoptions, 'configoption_id'))
            ->whereIn('option_type', ['radio', 'select', 'quantity', 'quantity_range'])
            ->select()
            ->toArray();
        $configoptionsubs = IdcsmartCommonProductConfigoptionSubModel::field('id,product_configoption_id,option_name')
            ->whereIn('id',array_column($hostConfigoptions, 'configoption_sub_id'))
            ->select()
            ->toArray();
        $configoptionsubs = array_column($configoptionsubs, 'option_name', 'id');

        $options = [];
        foreach ($hostId as $k => $v) {
            $options[$v] = [];
            if(isset($valueArr[$v])){
                foreach ($configoptions as $key => $value) {
                    if(in_array($value['id'], $valueArr[$v])){
                        $configoption = [];
                        $configoption['name'] = $value['option_name'];
                        if(in_array($value['option_type'], ['quantity', 'quantity_range'])){
                            $configoption['value'] = $valueArr[$v][$value['id']]['qty'] ?? 0;
                        }else{
                            $configoptions['value'] = $configoptionsubs[$valueArr[$v][$value['id']]['configoption_sub_id']] ?? '';
                        }
                        $options[$v][] = $configoption;
                    }
                }
            }

        }

        $authorize = AuthorizeModel::where('host_id', $hostId)->find();
        $sonHost = HostModel::alias('h')
            ->field('h.id,h.create_time,h.due_time')
            ->leftJoin('idcsmart_module_idcsmart_common_son_host b','b.son_host_id=h.id')
            ->where('b.host_id',$hostId)
            ->find();
        $data = [
            'service_due_time' => $sonHost['due_time'] ?? 0,
            'ip' => $authorize['ip'] ?? '',
            'license' => $authorize['license'] ?? '',
            'authorize_id' => $authorize['authorize_id'] ?? 0,
            'domain' => $authorize['domain'] ?? '',
            'config_option' => $options
        ];

        return $options;
    }

    /**
     * 时间 2022-09-26
     * @title 产品配置信息
     * @desc 产品配置信息
     * @author wyh
     * @version v1
     * @param   int id - 产品ID require
     * @return   array config_option - 配置项
     * @return   int config_option.id - 配置项ID
     * @return   string config_option.option_name - 名称
     * @return   string config_option.option_type - 类型select单选radio单选quantity数量quantity_range数量拖动
     * @return   int config_option.qty_min - 数量最小值
     * @return   int config_option.qty_max - 数量最大值
     * @return   string config_option.value - 值
     * @return   array config_option.sub - 选项
     * @return   string config_option.sub.name - 选项名称
     * @return   int config_option.sub.value - 选项值
     * @return   array upgrade_configoptions - 可升降级配置项
     * @return   int upgrade_configoptions[].id - 配置项ID
     * @return   int upgrade_configoptions[].product_id - 商品ID
     * @return   string upgrade_configoptions[].option_name - 配置项名称
     * @return   string upgrade_configoptions[].option_type - 配置项类型：select下拉单选，multi_select下拉多选，radio点击单选，quantity数量输入，quantity_range数量拖动，yes_no是否，area区域
     * @return   string upgrade_configoptions[].option_param - 参数:请求接口
     * @return   int upgrade_configoptions[].qty_min - 最小值
     * @return   int upgrade_configoptions[].qty_max - 最大值
     * @return   int upgrade_configoptions[].order - 排序
     * @return   int upgrade_configoptions[].hidden - 是否隐藏:1是，0否
     * @return   string upgrade_configoptions[].unit - 单位
     * @return   int upgrade_configoptions[].allow_repeat - 是否允许重复:开启后,前台购买时，可通过点击添加按钮，自动创建一个新的配置项，取名如bw1
     * @return   int upgrade_configoptions[].max_repeat - 最大允许重复数量
     * @return   string upgrade_configoptions[].fee_type - 数量的类型的计费方式：stage阶梯计费，qty数量计费(当前区间价格*数量
     * @return   string upgrade_configoptions[].description - 说明
     * @return   int upgrade_configoptions[].configoption_id - 当前商品其他类型为数量拖动/数量输入的配置项ID
     * @return   int upgrade_configoptions[].son_product_id - 子商品ID
     * @return   int upgrade_configoptions[].free - 关联商品首周期是否免费:1是，0否
     * @return   array upgrade_configoptions[].subs - 子配置
     * @return   int upgrade_configoptions[].subs[].id - 子配置项ID
     * @return   int upgrade_configoptions[].subs[].product_configoption_id - 配置项ID
     * @return   string upgrade_configoptions[].subs[].option_name - 名称
     * @return   string upgrade_configoptions[].subs[].option_param - 参数
     * @return   int upgrade_configoptions[].subs[].qty_min - 最小值
     * @return   int upgrade_configoptions[].subs[].qty_max - 最大值
     * @return   int upgrade_configoptions[].subs[].order - 排序
     * @return   int upgrade_configoptions[].subs[].hidden - 是否隐藏:1是，0否默认
     * @return   string upgrade_configoptions[].subs[].country - 国家:类型为区域时选择
     * @return   int upgrade_configoptions[].subs[].qty_change - 数量变化最小值
     */
    public function indexHost($param)
    {
        $hostId = $param['id'] ?? 0;

        $HostModel = new HostModel();
        $host = $HostModel->alias('h')
            ->field('h.id,h.product_id,p.name,h.first_payment_amount,h.billing_cycle_name')
            ->leftJoin('product p','p.id=h.product_id')
            ->where('h.id',$hostId)
            ->find();
        if (empty($host)){
            return [
                'status' => 400,
                'msg' => lang_plugins('error_message')
            ];
        }
        $productId = $host['product_id'];

        $IdcsmartCommonHostConfigoptionModel = new IdcsmartCommonHostConfigoptionModel();
        $configoptions = $IdcsmartCommonHostConfigoptionModel->alias('hc')
            ->field('dpc.id,dpc.option_type,dpc.option_name,dpcs.option_name as sub_name,hc.qty,hc.configoption_sub_id')
            ->leftJoin('module_idcsmart_common_product_configoption dpc','dpc.id=hc.configoption_id')
            ->leftJoin('idcsmart_module_idcsmart_common_product_configoption_sub dpcs','dpcs.id=hc.configoption_sub_id')
            ->where('hc.host_id',$hostId)
            ->select()
            ->toArray();

        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
        $upgradeConfigoptions = $IdcsmartCommonProductConfigoptionModel->where('product_id',$productId)
            ->where('hidden',0)
            ->select()
            ->toArray();
        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
        $upgradeConfigoptionsFilter = [];
        foreach ($upgradeConfigoptions as $upgradeConfigoption){
            $subs = $IdcsmartCommonProductConfigoptionSubModel->where('product_configoption_id',$upgradeConfigoption['id'])
                ->where('hidden',0)
                ->select()
                ->toArray();
            $upgradeConfigoption['subs'] = $subs;
            $upgradeConfigoptionsFilter[] = $upgradeConfigoption;
        }

        return [
            //'host' => $host,
            'configoptions' => $configoptions,
            'upgrade_configoptions' => $upgradeConfigoptionsFilter
        ];


        $hostId = $param['id'] ?? 0;
        $host = HostModel::find($hostId);

        if(empty($host)){
            return [];
        }

        $server = ServerModel::find($host['server_id']);
        if($server['module']!='idcsmart_common'){
            return [];
        }


        $hostConfigoptions = $this->field('configoption_id,configoption_sub_id,qty')
            ->where('host_id',$host['id'])
            ->select()
            ->toArray();
        $valueArr = [];
        foreach ($hostConfigoptions as $key => $value) {
            $valueArr[$value['configoption_id']] = $value;
        }
        $configoptions = IdcsmartCommonProductConfigoptionModel::field('id,option_name,option_type,qty_min,qty_max')
            ->where('product_id',$host['product_id'])
            ->whereIn('option_type', ['radio', 'select', 'quantity', 'quantity_range'])
            ->select()
            ->toArray();
        $configoptionsubs = IdcsmartCommonProductConfigoptionSubModel::field('id,product_configoption_id,option_name')
            ->whereIn('product_configoption_id',array_column($configoptions, 'id'))
            ->select()
            ->toArray();
        $subArr = [];
        foreach ($configoptionsubs as $key => $value) {
            $subArr[$value['product_configoption_id']][] = ['name' => $value['option_name'], 'value' => $value['id']];
        }

        foreach ($configoptions as $key => $value) {
            if(in_array($value['option_type'], ['quantity', 'quantity_range'])){
                $configoptions[$key]['value'] = $valueArr[$value['id']]['qty'] ?? 0;
            }else{
                $configoptions[$key]['sub'] = $subArr[$value['id']] ?? [];
                $configoptions[$key]['value'] = $valueArr[$value['id']]['configoption_sub_id'] ?? 0;
            }
        }

        $data = [
            'config_option' => $configoptions,
            'status' => config('idcsmart.host_status'),
        ];

        return $data;
    }

    /**
     * 时间 2022-09-26
     * @title 保存产品配置信息
     * @desc 保存产品配置信息
     * @author wyh
     * @version v1
     * @param int id - 产品ID require
     * @param   array config_option - 配置项 require
     * @param   int config_option.id - 配置项ID require
     * @param   string config_option.value - 值 require
     */
    public function updateHost($param)
    {
        $hostId = $param['id'] ?? 0;
        $host = HostModel::find($hostId);
        $IdcsmartCommon = new IdcsmartCommon();
        $params = [
            'custom' => [
                'configoption' => $param['config_option']??[],
            ],
            'host' => $host
        ];
        $result = $IdcsmartCommon->changePackage($params);
        return $result;
    }

    # 删除产品时实现钩子
    public function deleteHost($param)
    {
        $hostId = $param['id']??0;

        $this->startTrans();

        try{

            $this->where('host_id',$hostId)->delete();

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return false;
        }

        return true;
    }
}