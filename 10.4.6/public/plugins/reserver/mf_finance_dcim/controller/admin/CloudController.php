<?php
namespace reserver\mf_finance_dcim\controller\admin;

use app\common\model\HostModel;
use app\common\model\SupplierModel;
use app\common\model\UpstreamHostModel;
use app\common\model\UpstreamProductModel;
use reserver\mf_finance_dcim\logic\RouteLogic;
use think\facade\Cache;
use think\facade\View;

/**
 * @title V10代理魔方财务DCIM-后台内页操作
 * @desc  V10代理魔方财务DCIM-后台内页操作
 * @use reserver\mf_finance_dcim\controller\admin\CloudController
 */
class CloudController
{
    /**
     * 时间 2024-05-22
     * @title 获取实例详情
     * @desc 获取实例详情
     * @url /admin/v1/remf_finance_dcim/:id
     * @method  GET
     * @author hh
     * @version v1
     * @param   int $id - 产品ID
     * @return host_data:基础数据@
     * @host_data  ordernum:订单id
     * @host_data  productid:产品id
     * @host_data  serverid:服务器id
     * @host_data  regdate:产品开通时间
     * @host_data  domain:主机名
     * @host_data  payment:支付方式
     * @host_data  firstpaymentamount:首付金额
     * @host_data  firstpaymentamount_desc:首付金额
     * @host_data  amount:续费金额
     * @host_data  amount_desc:续费金额
     * @host_data  billingcycle:付款周期
     * @host_data  billingcycle_desc:付款周期
     * @host_data  nextduedate:到期时间
     * @host_data  nextinvoicedate:下次帐单时间
     * @host_data  dedicatedip:独立ip
     * @host_data  assignedips:附加ip
     * @host_data  ip_num:IP数量
     * @host_data  domainstatus:产品状态
     * @host_data  domainstatus_desc:产品状态
     * @host_data  username:服务器用户名
     * @host_data  password:服务器密码
     * @host_data  suspendreason:暂停原因
     * @host_data  auto_terminate_end_cycle:是否到期取消
     * @host_data  auto_terminate_reason:取消原因
     * @host_data  productname:产品名
     * @host_data  groupname:产品组名
     * @host_data  bwusage:当前使用流量
     * @host_data  bwlimit:当前使用流量上限(0表示不限)
     * @host_data  os:操作系统
     * @host_data  port:端口
     * @host_data  remark:备注
     * @return config_options:可配置选项@
     * @config_options  name:配置名
     * @config_options  sub_name:配置项值
     * @return custom_field_data:自定义字段@
     * @custom_field_data  fieldname:字段名
     * @custom_field_data  value:字段值
     * @return download_data:可下载数据@
     * @download_data  id:文件id
     * @title  id:文件标题
     * @down_link  id:下载链接
     * @location  id:文件名
     * @return module_button:模块按钮@
     * @module_button  type:default:默认,custom:自定义
     * @module_button  type:func:函数名
     * @module_button  type:name:名称
     * @return module_client_area:模块页面输出
     * @return hook_output:钩子在本页面的输出，数组，循环显示的html
     * @return dcim.flowpacket:当前产品可购买的流量包@
     * @dcim.flowpacket  id:流量包ID
     * @dcim.flowpacket  name:流量包名称
     * @dcim.flowpacket  price:价格
     * @dcim.flowpacket  sale_times:销售次数
     * @dcim.flowpacket  stock:库存(0不限)
     * @return dcim.auth:服务器各种操作权限控制(on有权限off没权限)
     * @return dcim.area_code:区域代码
     * @return dcim.area_name:区域名称
     * @return dcim.os_group:操作系统分组@
     * @dcim.os_group  id:分组ID
     * @dcim.os_group  name:分组名称
     * @dcim.os_group  svg:分组svg号
     * @return dcim.os:操作系统数据@
     * @dcim.os  id:操作系统ID
     * @dcim.os  name:操作系统名称
     * @dcim.os  ostype:操作系统类型(1windows0linux)
     * @dcim.os  os_name:操作系统真实名称(用来判断具体的版本和操作系统)
     * @dcim.os  group_id:所属分组ID
     * @return  flow_packet_use_list:流量包使用情况@
     * @flow_packet_use_list  name:流量包名称
     * @flow_packet_use_list  capacity:流量包大小
     * @flow_packet_use_list  price:价格
     * @flow_packet_use_list  pay_time:支付时间
     * @flow_packet_use_list  used:已用流量
     * @flow_packet_use_list  used:已用流量
     * @return  host_cancel: 取消请求数据,空对象
     */
    public function detail(){
        $param = request()->param();

        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['is_delete']){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_dcim_host_not_found')]);
        }

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);

            // TODO WYH 20240306 当前产品的上游产品也是代理产品时，继续调上游reserver
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $upstreamHostUpstream = $UpstreamHostModel->where('host_id',$upstreamHost['upstream_host_id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_dcim'){
                // 上游商品
                $result = $RouteLogic->curl( "console/v1/remf_finance_dcim/{$upstreamHost['upstream_host_id']}", [], 'GET');
            }else{
                $result = $RouteLogic->curl( 'host/header', ['host_id'=>$RouteLogic->upstream_host_id], 'GET');
                if ($result['status']==200){
                    if (isset($result['data']['host_data'])){
                        if (isset($result['data']['host_data']['port']) && $result['data']['host_data']['port']==0){
                            $result['data']['host_data']['port'] = lang_plugins("res_mf_finance_dcim_default");
                        }
                        unset(
                            $result['data']['host_data']['amount'],
                            $result['data']['host_data']['amount_desc'],
                            $result['data']['host_data']['firstpaymentamount'],
                            $result['data']['host_data']['firstpaymentamount_desc'],
                            $result['data']['host_data']['order_amount'],
                            $result['data']['host_data']['upstream_price_value']
                        );
                    }
                }
            }
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_dcim_act_exception')]);
        }
        if ($result['status']!=200){
            $result['status'] = 400;
        }
        return json($result);
    }

    /**
     * 时间 2024-05-22
     * @title 开机
     * @desc  开机
     * @url /admin/v1/remf_finance_dcim/:id/on
     * @method  POST
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     * @param   string admin_operate_password - 操作密码,需要验证时传
     */
    public function on()
    {
        $param = request()->param();

        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['is_delete']){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_dcim_host_not_found')]);
        }

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);

            // TODO WYH 20240306 当前产品的上游产品也是代理产品时，继续调上游reserver
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $upstreamHostUpstream = $UpstreamHostModel->where('host_id',$upstreamHost['upstream_host_id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_dcim'){
                // 上游商品
                $result = $RouteLogic->curl( "console/v1/remf_finance_dcim/{$upstreamHost['upstream_host_id']}/on", [
                    'os' => $param['os']??'',
                    'code' => $param['code']??'',
                ], 'POST');
            }else{
                $postData = [
                    'id'        => $RouteLogic->upstream_host_id,
                    'is_api'    => true
                ];

                $result = $RouteLogic->curl('dcim/on', $postData, 'POST');
            }

            if($result['status'] == 200){
                $description = lang_plugins('res_mf_finance_dcim_log_host_start_boot_success', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }else{
                $description = lang_plugins('res_mf_finance_dcim_log_host_start_boot_fail', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }
            active_log($description, 'host', $HostModel['id']);
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_dcim_act_exception')]);
        }
        return json($result);
    }

    /**
     * 时间 2024-05-22
     * @title 关机
     * @desc 关机
     * @url /admin/v1/remf_finance_dcim/:id/off
     * @method  POST
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     * @param   string admin_operate_password - 操作密码,需要验证时传
     */
    public function off()
    {
        $param = request()->param();

        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['is_delete'] ){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_dcim_host_not_found')]);
        }

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);

            // TODO WYH 20240306 当前产品的上游产品也是代理产品时，继续调上游reserver
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $upstreamHostUpstream = $UpstreamHostModel->where('host_id',$upstreamHost['upstream_host_id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_dcim'){
                // 上游商品
                $result = $RouteLogic->curl( "console/v1/remf_finance_dcim/{$upstreamHost['upstream_host_id']}/off", [
                    'os' => $param['os']??'',
                    'code' => $param['code']??'',
                ], 'POST');
            }else{
                $postData = [
                    'id'        => $RouteLogic->upstream_host_id,
                    'is_api'    => true
                ];

                $result = $RouteLogic->curl( 'dcim/off', $postData, 'POST');
            }

            if($result['status'] == 200){
                $description = lang_plugins('res_mf_finance_dcim_log_host_start_off_success', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }else{
                $description = lang_plugins('res_mf_finance_dcim_log_host_start_off_fail', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }
            active_log($description, 'host', $HostModel['id']);
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_dcim_act_exception')]);
        }
        return json($result);
    }

    /**
     * 时间 2024-05-22
     * @title 重启
     * @desc 重启
     * @url /admin/v1/remf_finance_dcim/:id/reboot
     * @method  POST
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     * @param   string admin_operate_password - 操作密码,需要验证时传
     */
    public function reboot()
    {
        $param = request()->param();

        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['is_delete'] ){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_dcim_host_not_found')]);
        }

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);

            // TODO WYH 20240306 当前产品的上游产品也是代理产品时，继续调上游reserver
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $upstreamHostUpstream = $UpstreamHostModel->where('host_id',$upstreamHost['upstream_host_id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_dcim'){
                // 上游商品
                $result = $RouteLogic->curl( "console/v1/remf_finance_dcim/{$upstreamHost['upstream_host_id']}/reboot", [
                    'os' => $param['os']??'',
                    'code' => $param['code']??'',
                ], 'POST');
            }else{
                $postData = [
                    'id'        => $RouteLogic->upstream_host_id,
                    'is_api'    => true
                ];

                $result = $RouteLogic->curl( 'dcim/reboot', $postData, 'POST');
            }
            if($result['status'] == 200){
                $description = lang_plugins('res_mf_finance_dcim_log_host_start_reboot_success', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }else{
                $description = lang_plugins('res_mf_finance_dcim_log_host_start_reboot_fail', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }
            active_log($description, 'host', $HostModel['id']);
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_dcim_act_exception')]);
        }
        return json($result);
    }

    /**
     * 时间 2024-05-22
     * @title 重置BMC
     * @desc 重置BMC
     * @url /admin/v1/remf_finance_dcim/:id/reset_bmc
     * @method  POST
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     * @param   string admin_operate_password - 操作密码,需要验证时传
     */
    public function resetBmc()
    {
        $param = request()->param();

        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['is_delete'] ){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_dcim_host_not_found')]);
        }

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);

            // TODO WYH 20240306 当前产品的上游产品也是代理产品时，继续调上游reserver
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $upstreamHostUpstream = $UpstreamHostModel->where('host_id',$upstreamHost['upstream_host_id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_dcim'){
                // 上游商品
                $result = $RouteLogic->curl( "console/v1/remf_finance_dcim/{$upstreamHost['upstream_host_id']}/reset_bmc", [
                ], 'POST');
            }else{
                $postData = [
                    'id'        => $RouteLogic->upstream_host_id,
                    'is_api'    => true
                ];

                $result = $RouteLogic->curl( 'dcim/bmc', $postData, 'POST');
            }

            if($result['status'] == 200){
                $description = lang_plugins('res_mf_finance_dcim_log_host_start_reset_bmc_success', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }else{
                $description = lang_plugins('res_mf_finance_dcim_log_host_start_reset_bmc_fail', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }
            active_log($description, 'host', $HostModel['id']);
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_dcim_act_exception')]);
        }
        return json($result);
    }

    /**
     * 时间 2024-05-22
     * @title 获取控制台地址(TODO)
     * @desc 获取控制台地址
     * @url /admin/v1/remf_finance_dcim/:id/vnc
     * @method  POST
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     * @param   string admin_operate_password - 操作密码,需要验证时传
     * @return  string data.url - 控制台地址
     */
    public function vnc()
    {
        $param = request()->param();

        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['is_delete'] ){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_dcim_host_not_found')]);
        }

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);

            // TODO WYH 20240306 当前产品的上游产品也是代理产品时，继续调上游reserver
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $upstreamHostUpstream = $UpstreamHostModel->where('host_id',$upstreamHost['upstream_host_id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_dcim'){
                // 上游商品
                $result = $RouteLogic->curl( "console/v1/remf_finance_dcim/{$upstreamHost['upstream_host_id']}/vnc", [
                ], 'POST');
            }else{
                $postData = [
                    'id'        => $RouteLogic->upstream_host_id,
                    'is_api'    => true
                ];

                $result = $RouteLogic->curl( 'dcim/novnc', $postData, 'POST');
            }

            if($result['status'] == 200){

                // 获取的东西放入缓存
                $cache = [
                    'vnc_url' => base64_decode(urldecode($result['data']['url'])),
                    'vnc_pass'=> $result['data']['password'],
                    'password'=> aes_password_decode(''),
                ];

                if (request()->scheme()=='https'){
                    $ws = 'wss';
                }else{
                    $ws = 'ws';
                }

                $parseUrl = parse_url($cache['vnc_url']);

                $cache['vnc_url'] = $ws . '://' . $parseUrl['host'] . ':' . $parseUrl['port'] . $parseUrl['path'].'?'.$parseUrl['query'];

                $result['data']['url'] = request()->domain().'/console/v1/remf_finance_dcim/'.$param['id'].'/vnc';

                // 生成一个临时token
                $token = md5(rand_str(16));
                $cache['token'] = $token;

                Cache::set('remf_finance_dcim_vnc_'.$param['id'], $cache, 30*60);
                if(strpos($result['data']['url'], '?') !== false){
                    $result['data']['url'] .= '&tmp_token='.$token;
                }else{
                    $result['data']['url'] .= '?tmp_token='.$token;
                }
            }
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_dcim_act_exception')]);
        }
        return json($result);
    }

    /**
     * 时间 2024-05-22
     * @title 控制台页面
     * @desc 控制台页面
     * @url /admin/v1/remf_finance_dcim/:id/vnc
     * @method  GET
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     */
    public function vncPage(){
        $param = request()->param();

        $cache = Cache::get('remf_finance_dcim_vnc_'.$param['id']);
        if(!empty($cache) && isset($param['tmp_token']) && $param['tmp_token'] === $cache['token']){
            View::assign($cache);
        }else{
            return lang_plugins('res_remf_finance_dcim_vnc_token_expired_please_reopen');
        }
        return View::fetch(WEB_ROOT . 'plugins/reserver/mf_dcim/view/vnc_page.html');
    }

    /**
     * 时间 2024-05-22
     * @title 获取实例状态
     * @desc 获取实例状态
     * @url /admin/v1/remf_finance_dcim/:id/status
     * @method  GET
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     * @return  string data.status - 实例状态(on=开机,off=关机,operating=操作中,fault=故障)
     * @return  string data.desc - 实例状态描述
     */
    public function status()
    {
        $param = request()->param();

        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['is_delete'] ){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_dcim_host_not_found')]);
        }

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);

            // TODO WYH 20240306 当前产品的上游产品也是代理产品时，继续调上游reserver
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $upstreamHostUpstream = $UpstreamHostModel->where('host_id',$upstreamHost['upstream_host_id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_dcim'){
                // 上游商品
                $result = $RouteLogic->curl( "console/v1/remf_finance_dcim/{$upstreamHost['upstream_host_id']}/status", [
                ], 'GET');
            }else{
                $postData = [
                    'id'        => $RouteLogic->upstream_host_id,
                    'is_api'    => true
                ];

                $res = $RouteLogic->curl( 'dcim/refresh_all_power_status', $postData, 'POST');

                if($res['status'] == 200 && isset($res['data'][0]['status'])){
                    if($res['data'][0]['status'] == 'not_support'){
                        $status = [
                            'status' => 'fault',
                            'desc'   => lang_plugins('res_mf_finance_dcim_fault'),
                        ];
                    }else if($res['data'][0]['status'] == 'on'){
                        $status = [
                            'status' => 'on',
                            'desc'   => lang_plugins('res_mf_finance_dcim_on'),
                        ];
                    }else if($res['data'][0]['status'] == 'off'){
                        $status = [
                            'status' => 'off',
                            'desc'   => lang_plugins('res_mf_finance_dcim_off')
                        ];
                    }else if($res['data'][0]['status'] == 'error'){
                        $status = [
                            'status' => 'off',
                            'desc'   => lang_plugins('res_mf_finance_dcim_fault')
                        ];
                    }else{
                        $status = [
                            'status' => 'fault',
                            'desc'   => lang_plugins('res_mf_finance_dcim_fault'),
                        ];
                    }
                }else{
                    $status = [
                        'status' => 'fault',
                        'desc'   => lang_plugins('res_mf_finance_dcim_fault'),
                    ];
                }
                $result = [
                    'status' => 200,
                    'msg'    => lang_plugins('success_message'),
                    'data'   => $status,
                ];
            }

        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_dcim_act_exception')]);
        }
        return json($result);
    }

    /**
     * 时间 2024-05-22
     * @title 重置密码
     * @desc 重置密码
     * @url /admin/v1/remf_finance_dcim/:id/reset_password
     * @method  POST
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     * @param   string password - 新密码 require
     * @param   string admin_operate_password - 操作密码,需要验证时传
     */
    public function resetPassword()
    {
        $param = request()->param();

        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['is_delete'] ){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_dcim_host_not_found')]);
        }

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);

            // TODO WYH 20240306 当前产品的上游产品也是代理产品时，继续调上游reserver
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $upstreamHostUpstream = $UpstreamHostModel->where('host_id',$upstreamHost['upstream_host_id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_dcim'){
                // 上游商品
                $result = $RouteLogic->curl( "console/v1/remf_finance_dcim/{$upstreamHost['upstream_host_id']}/reset_password", [
                    'password' => $param['password']??'',
                ], 'POST');
            }else{
                $postData = [
                    'id'            => $RouteLogic->upstream_host_id,
                    'password'      => $param['password'] ?? '',
                    'is_api' => true
                ];

                $result = $RouteLogic->curl( 'dcim/crack_pass', $postData, 'POST');
            }

            if($result['status'] == 200){
                $description = lang_plugins('res_mf_finance_dcim_log_host_start_reset_password_success', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }else{
                $description = lang_plugins('res_mf_finance_dcim_log_host_start_reset_password_success', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }
            active_log($description, 'host', $HostModel['id']);
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_dcim_act_exception')]);
        }
        return json($result);
    }

    /**
     * 时间 2024-05-22
     * @title 救援模式
     * @desc 救援模式
     * @url /admin/v1/remf_finance_dcim/:id/rescue
     * @method  POST
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     * @param   int type - 指定救援系统类型(1=windows,2=linux) require
     * @param   string admin_operate_password - 操作密码,需要验证时传
     */
    public function rescue()
    {
        $param = request()->param();

        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['is_delete'] ){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_dcim_host_not_found')]);
        }

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);

            // TODO WYH 20240306 当前产品的上游产品也是代理产品时，继续调上游reserver
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $upstreamHostUpstream = $UpstreamHostModel->where('host_id',$upstreamHost['upstream_host_id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_dcim'){
                // 上游商品
                $result = $RouteLogic->curl( "console/v1/remf_finance_dcim/{$upstreamHost['upstream_host_id']}/rescue", [
                    'type' => $param['type']??'',
                ], 'POST');
            }else{
                $postData = [
                    'id'        => $RouteLogic->upstream_host_id,
                    'system'    => $param['type'] == 2 ? 1 : 2,
                    'is_api'    => true
                ];

                $result = $RouteLogic->curl( 'dcim/rescue', $postData, 'POST');
            }

            if($result['status'] == 200){
                $description = lang_plugins('res_mf_finance_dcim_log_host_start_rescue_success', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }else{
                $description = lang_plugins('res_mf_finance_dcim_log_host_start_rescue_fail', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }
            active_log($description, 'host', $HostModel['id']);
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_dcim_act_exception')]);
        }
        return json($result);
    }

    /**
     * 时间 2024-05-22
     * @title 取消救援
     * @desc 取消救援
     * @url /admin/v1/remf_finance_dcim/:id/cancel_task
     * @method  POST
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     * @param   string admin_operate_password - 操作密码,需要验证时传
     */
    public function cancelTask()
    {
        $param = request()->param();

        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['is_delete'] ){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_dcim_host_not_found')]);
        }

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);

            // TODO WYH 20240306 当前产品的上游产品也是代理产品时，继续调上游reserver
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $upstreamHostUpstream = $UpstreamHostModel->where('host_id',$upstreamHost['upstream_host_id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_dcim'){
                // 上游商品
                $result = $RouteLogic->curl( "console/v1/remf_finance_dcim/{$upstreamHost['upstream_host_id']}/cancel_task", [
                ], 'POST');
            }else{
                $postData = [
                    'id'        => $RouteLogic->upstream_host_id,
                    'is_api'    => true
                ];

                $result = $RouteLogic->curl( 'dcim/cancel_task', $postData, 'POST');
            }

            if($result['status'] == 200){
                $description = lang_plugins('res_mf_finance_dcim_log_host_cancel_rescue_success', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }else{
                $description = lang_plugins('res_mf_finance_dcim_log_host_cancel_rescue_fail', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }
            active_log($description, 'host', $HostModel['id']);
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_dcim_act_exception')]);
        }
        return json($result);
    }

    /**
     * 时间 2024-05-22
     * @title 重装系统
     * @desc 重装系统
     * @url /admin/v1/remf_finance_dcim/:id/reinstall
     * @method  POST
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     * @param   int os - 重装系统的操作系统id require
     * @param   string password - 密码 require
     * @param   int port - 端口 require
     * @param   string admin_operate_password - 操作密码,需要验证时传
     */
    public function reinstall()
    {
        $param = request()->param();

        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['is_delete'] ){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_dcim_host_not_found')]);
        }

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);

            // TODO WYH 20240306 当前产品的上游产品也是代理产品时，继续调上游reserver
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $upstreamHostUpstream = $UpstreamHostModel->where('host_id',$upstreamHost['upstream_host_id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_dcim'){
                // 上游商品
                $result = $RouteLogic->curl( "console/v1/remf_finance_dcim/{$upstreamHost['upstream_host_id']}/reinstall", [
                    'os' => $param['os']??'',
                    'port' => $param['port']??'',
                    'password' => $param['password']??'',
                    'part_type' => $param['part_type']??0,
                ], 'POST');
            }else{
                $postData = [
                    'id'        => $RouteLogic->upstream_host_id,
                    'os'        => $param['os'] ?? '',
                    'password'  => $param['password'] ?? '',
                    'port'      => $param['port'] ?? '',
                    'part_type' => $param['part_type']??0,
                    'is_api'    => true
                ];

                $result = $RouteLogic->curl( 'dcim/reinstall', $postData, 'POST');
            }

            if($result['status'] == 200){
                $description = lang_plugins('res_mf_finance_dcim_log_host_start_reinstall_success', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }else{
                $description = lang_plugins('res_mf_finance_dcim_log_host_start_reinstall_fail', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }
            active_log($description, 'host', $HostModel['id']);
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_dcim_act_exception')]);
        }
        return json($result);
    }

}
