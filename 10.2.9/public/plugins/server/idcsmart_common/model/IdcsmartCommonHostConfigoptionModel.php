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

        /*$param['config_option'] = $param['config_option'] ?? [];
        $host = HostModel::find($hostId);

        if(empty($host)){
            return ['status'=>400, 'msg'=>lang_plugins('param_error')];
        }

        $server = ServerModel::find($host['server_id']);
        if($server['module']!='idcsmart_common'){
            return ['status'=>400, 'msg'=>lang_plugins('param_error')];
        }

        $configoptions = IdcsmartCommonProductConfigoptionModel::field('id,option_name,option_type,qty_min,qty_max')
            ->where('product_id',$host['product_id'])
            ->whereIn('option_type', ['radio', 'select', 'quantity', 'quantity_range'])
            ->select()
            ->toArray();
        $optionArr = [];
        foreach ($configoptions as $key => $value) {
            $optionArr[$value['id']] = $value;
        }
        $configoptionsubs = IdcsmartCommonProductConfigoptionSubModel::field('id,product_configoption_id,option_name')
            ->whereIn('product_configoption_id',array_column($configoptions, 'id'))
            ->select()
            ->toArray();
        $subArr = [];
        foreach ($configoptionsubs as $key => $value) {
            $subArr[$value['product_configoption_id']][] = ['name' => $value['option_name'], 'value' => $value['id']];
        }
        $configoptionsubs = array_column($configoptionsubs, 'option_name', 'id');
        foreach ($param['config_option'] as $key => $value) {
            if(!in_array($value['id'], array_column($configoptions, 'id'))){
                return ['status'=>400, 'msg'=>lang_plugins('param_error')];
            }
            if(in_array($optionArr[$value['id']]['option_type'], ['select', 'radio'])){
                if(!in_array($value['value'], array_column($subArr[$value['id']], 'value'))){
                    return ['status'=>400, 'msg'=>lang_plugins('param_error')];
                }
            }else{
                if($value['value']>$optionArr[$value['id']]['qty_max'] || $value['value']<$optionArr[$value['id']]['qty_min']){
                    return ['status'=>400, 'msg'=>lang_plugins('param_error')];
                }
            }
        }

        $hostConfigoptions = $this->field('id,configoption_id,configoption_sub_id,qty')
            ->where('host_id',$host['id'])
            ->select()
            ->toArray();
        $oldArr = [];
        foreach ($hostConfigoptions as $key => $value) {
            $oldArr[$value['configoption_id']] = $value;
        }
        $description = [];
        foreach ($param['config_option'] as $key => $value) {
            if (in_array($optionArr[$value['id']]['option_type'], ['select', 'radio'])){
                $old = $oldArr[$value['id']]['configoption_sub_id'] ?? '';
                $new = $value['value'];
                if($old!=$new){
                    $description[] = lang_plugins('idcsmart_common_old_to_new',['{old}'=>lang_plugins('idcsmart_common_config_option').$optionArr[$value['id']]['option_name'].($configoptionsubs[$old]??''), '{new}'=>($configoptionsubs[$new]??'')]);
                }
            }else{
                $old = $oldArr[$value['id']]['qty'] ?? 0;
                $new = $value['value'];
                if($old!=$new){
                    $description[] = lang_plugins('idcsmart_common_old_to_new',['{old}'=>lang_plugins('idcsmart_common_config_option').$optionArr[$value['id']]['option_name'].$old, '{new}'=>$new]);
                }
            }
        }

        $HostModel = HostModel::find($hostId);
        $params = $HostModel->getModuleParams();

        $IdcsmartCommon = new IdcsmartCommon();

        $result = $IdcsmartCommon->changePackage($params);
        if($result['status'] == 400){
            return $result;
        }

        $description = implode(',', $description);
        if(!empty($description)) active_log(lang_plugins('idcsmart_common_admin_modify_config_option', ['{admin}'=>request()->admin_name, '{host}'=>'host#'.$host->id.'#'.$host['name'].'#', '{description}'=>$description]), 'host', $host->id);


        return $result;*/

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