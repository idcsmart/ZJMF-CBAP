<?php 
namespace server\idcsmart_common\model;

use app\common\model\ClientModel;
use server\idcsmart_common\logic\IdcsmartCommonLogic;
use think\Model;

class IdcsmartCommonServerHostLinkModel extends Model
{
    protected $name = 'module_idcsmart_common_server_host_link';

    // 设置字段信息
    protected $schema = [
        'id'                     => 'int',
        'host_id'                => 'int',
        'server_id'              => 'int',
        'dedicatedip'              => 'string',
        'assignedips'              => 'string',
        'username'              => 'string',
        'password'              => 'string',
        'bwlimit'              => 'int',
        'os'              => 'string',
        'bwusage'              => 'float',
        'vserverid'              => 'int',
    ];

    public function getProvisionParams($hostid)
    {
        $where = [];
        $where[] = ['hl.host_id', '=', $hostid];
        $where[] = ['h.is_delete', '=', 0];
        
        $data = $this->field('cp.*,hl.host_id hostid,hl.server_id serverid,hl.password host_password,hl.vserverid,cs.secure,cs.password server_password,h.client_id uid,cs.type module_type,
        cs.ip_address server_ip,cs.accesshash,cs.port')
            ->alias('hl')
            ->leftJoin('module_idcsmart_common_server cs','cs.id=hl.server_id')
            ->leftJoin('host h','h.id=hl.host_id')
            ->leftJoin('module_idcsmart_common_product cp','cp.product_id=h.product_id')
            ->where($where)
            ->find();
        if (is_null($data)){
            return ['module_type'=>""];
        }else{
            $data = $data->toArray();
        }

        // 增加https/http前缀
        if($data['secure'] == 1){
            $data['server_http_prefix'] = 'https';
        }else{
            $data['server_http_prefix'] = 'http';
        }
        $data['server_password'] = aes_password_decode($data['server_password']);
        //$temp = $data['host_password'];
        //$data['host_password'] = $data['password'];
        $data['password'] = password_decrypt($data['host_password']);
        $data['user_info'] = (new ClientModel())->find($data['uid']);

        $IdcsmartCommonHostConfigoptionModel = new IdcsmartCommonHostConfigoptionModel();
        $configoptions = $IdcsmartCommonHostConfigoptionModel->alias('hc')
            ->field('hc.host_id,hc.qty,cpc.option_name,cpc.option_param,cpc.option_type,cpcs.option_name sub_option,cpcs.option_param sub_option_param')
            ->leftJoin('module_idcsmart_common_product_configoption cpc','hc.configoption_id=cpc.id')
            ->leftJoin('idcsmart_module_idcsmart_common_product_configoption_sub cpcs','cpcs.id=hc.configoption_sub_id')
            ->where('hc.host_id',$hostid)
            ->select()
            ->toArray();
        $IdcsmartCommonLogic = new IdcsmartCommonLogic();
        foreach ($configoptions as $k=>$v){

            // 过滤否
            if ($IdcsmartCommonLogic->checkYesNo($v['option_type']) && $v['sub_option']=='否'){
                continue;
            }

            // 配置项格式：os|操作系统
            if(strpos($v['option_name'], '|') !== false){
                $key = explode('|', $v['option_name'])[0];
            }else{
                $key = $v['option_param']; //参数：键
            }
            if ($IdcsmartCommonLogic->checkQuantity($v['option_type'])){
                $value = $v['qty'];
            } elseif ($IdcsmartCommonLogic->checkYesNo($v['option_type'])){

            } else{
                if(strpos($v['sub_option'], '|') !== false){
                    $value = explode('|', $v['sub_option'])[0];
                }else{
                    $value = $v['sub_option_param']; // 使用新版本参数
                }
            }

            $data['configoptions'][$key] = $value;

            $data['configoptions_upgrade'][$key] = $value;
        }

        return $data;
    }
}