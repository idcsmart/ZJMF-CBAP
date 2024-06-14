<?php
namespace reserver\mf_finance_common\controller\home;

use app\admin\model\PluginModel;
use app\common\model\HostModel;
use app\common\model\MenuModel;
use app\common\model\OrderModel;
use app\common\model\ProductUpgradeProductModel;
use app\common\model\SupplierModel;
use app\common\model\UpstreamHostModel;
use app\common\model\UpstreamOrderModel;
use app\common\model\UpstreamProductModel;
use reserver\mf_finance_common\logic\RouteLogic;
use reserver\mf_finance_common\model\SystemLogModel;
use app\common\model\SelfDefinedFieldModel;
use reserver\mf_finance_common\validate\HostValidate;

/**
 * @title V10代理魔方财务通用商品-前台
 * @desc V10代理魔方财务通用商品-前台
 * @use reserver\mf_finance_common\controller\home\CloudController
 * @time 2024-05-16
 */
class CloudController
{
    /**
     * 时间 2024-05-16
     * @title 获取订购页面配置
     * @desc 获取订购页面配置
     * @url /console/v1/product/:id/remf_finance_common/order_page
     * @method  GET
     * @author wyh
     * @version v1
     * @param   int id - 商品ID require
     */
    public function orderPage(){
        $param = request()->param();
        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByProduct($param['id']);
            // 当前商品的上游商品也是代理商品时，继续调上游reserver
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id', $param['id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_common'){
                // 上游商品
                $result = $RouteLogic->curl( "console/v1/product/{$upstreamProduct['upstream_product_id']}/remf_finance_common/order_page", [], 'GET');
            }else{
                $postData = [
                    'pid' => $RouteLogic->upstream_product_id,
                    'billingcycle' => $param['billingcycle']??''
                ];
                $result = $RouteLogic->curl( 'cart/set_config', $postData, 'GET');
                if ($result['status']==200){
                    $cycles = [];
                    foreach ($result['product']['cycle'] as $item){
                        if ($item['billingcycle']!='ontrial'){
                            unset($item['product_price'],$item['setup_fee']);
                            $cycles[] = $item;
                        }
                    }
                    $result['product']['cycle'] = $cycles;
                }
            }
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_act_exception') . $e->getMessage()]);
        }
        return json($result);
    }

    /**
     * 时间 2024-05-16
     * @title 获取订购页面配置
     * @desc 获取订购页面配置(层级联动)
     * @url /console/v1/product/:id/remf_finance_common/link
     * @method  GET
     * @author wyh
     * @version v1
     * @param   int id - 商品ID require
     * @param   int cid - 配置项ID require
     * @param   int sub_id - 子项ID require
     */
    public function link(){
        $param = request()->param();
        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByProduct($param['id']);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id', $param['id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_common'){
                // 上游商品
                $result = $RouteLogic->curl( "console/v1/product/{$upstreamProduct['upstream_product_id']}/remf_finance_common/link", [
                    'cid' => $param['cid'],
                    'sub_id' => $param['sub_id'],
                    'billingcycle' => $param['billingcycle']??''
                ], 'GET');
            }else{
                $postData = [
                    'pid' => $RouteLogic->upstream_product_id,
                    'cid' => $param['cid'],
                    'sub_id' => $param['sub_id'],
                    'billingcycle' => $param['billingcycle']??''
                ];
                $result = $RouteLogic->curl( 'link_list', $postData, 'GET');
            }
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_act_exception') . $e->getMessage()]);
        }
        return json($result);
    }

    /**
     * 时间 2024-05-16
     * @title 获取商品配置所有周期价格
     * @desc 获取商品配置所有周期价格
     * @url /console/v1/product/:id/remf_finance_common/duration
     * @method  GET
     * @author wyh
     * @version v1
     * @param   int id - 商品ID require
     * @return object duration - 周期
     * @return float duration.product_price - 价格
     * @return float duration.setup_fee - 初装费
     * @return string duration.billingcycle - 周期
     * @return string duration.billingcycle_zh - 周期
     * @return string duration.pay_ontrial_cycle - 试用
     */
    public function cartConfigoption()
    {
        $param = request()->param();

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByProduct($param['id']);
            unset($param['id']);
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $HostModel = HostModel::find($param['id']);
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_common'){
                // 上游商品
                $result = $RouteLogic->curl( "console/v1/product/{$upstreamHost['upstream_host_id']}/remf_finance_common/duration", [
                    'billingcycle' => $param['billingcycle']??''
                ], 'GET');
            }else{
                $postData = [
                    'pid' => $RouteLogic->upstream_product_id,
                    'billingcycle' => $param['billingcycle']??''
                ];
                $result = $RouteLogic->curl( 'cart/set_config', $postData, 'GET');
            }

            if($result['status'] == 200){
                // 计算价格倍率
                foreach($result['product']['cycle'] as $k=>$v){
                    // 计算汇率
                    $v['product_price'] = $v['product_price'] * $supplier['rate'];
                    $v['setup_fee'] = $v['setup_fee'] * $supplier['rate'];

                    if($v['product_price'] > 0){
                        # 固定利润
                        if ($RouteLogic->profit_type==1){
                            $result['product']['cycle'][$k]['product_price'] = bcadd($v['product_price'], $RouteLogic->profit_percent*100);
                        }else{
                            $result['product']['cycle'][$k]['product_price'] = bcmul($v['product_price'], $RouteLogic->price_multiple);
                        }

                    }
                    if($v['setup_fee'] > 0){
                        # 固定利润
                        if ($RouteLogic->profit_type==1){
                            $result['product']['cycle'][$k]['setup_fee'] = bcadd($v['setup_fee'], 0);
                        }else{
                            $result['product']['cycle'][$k]['setup_fee'] = bcmul($v['setup_fee'], $RouteLogic->price_multiple);
                        }
                    }
                }
                $cycles = [];
                foreach ($result['product']['cycle'] as $item){
                    if ($item['billingcycle']!='ontrial'){
                        $cycles[] = $item;
                    }
                }
                $result['product']['cycle'] = $cycles;
                $res = [
                    'status' => 200,
                    'msg' => $result['msg'],
                    'data' => [
                        'duration' => $result['product']['cycle']??[]
                    ]
                ];
                return json($res);
            }else{
                return json($result);
            }
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_act_exception')]);
        }
    }

    /**
     * 时间 2024-05-16
     * @title 获取控制台地址
     * @desc 获取控制台地址
     * @url /console/v1/remf_finance_common/:id/vnc
     * @method  POST
     * @author wyh
     * @version v1
     * @return  string data.url - 控制台地址
     */
    public function vnc()
    {
        $param = request()->param();
        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete'] ){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_host_not_found')]);
        }
        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_common'){
                $result = $RouteLogic->curl( "console/v1/remf_finance_common/{$upstreamHost['upstream_host_id']}/vnc", [], 'POST');
            }else{
                $postData = [
                    'id'        => $RouteLogic->upstream_host_id,
                    'func'        => 'vnc',
                    'is_api'    => true
                ];
                $result = $RouteLogic->curl( 'provision/default', $postData, 'POST');
            }
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_act_exception')]);
        }
        if ($result['status']!=200){
            $result['status'] = 400;
        }
        return json($result);
    }

    /**
     * 时间 2024-05-16
     * @title 获取实例详情(具体参数参考魔方财务)
     * @desc 获取实例详情(具体参数参考魔方财务)
     * @url /console/v1/remf_finance_common/:id
     * @method  GET
     * @author wyh
     * @version v1
     * @param   int $id - 产品ID
     */
    public function detail(){
        $param = request()->param();
        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete']){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_host_not_found')]);
        }
        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_common'){
                $result = $RouteLogic->curl( "console/v1/remf_finance_common/{$upstreamHost['upstream_host_id']}", [], 'GET');
            }else{
                $result = $RouteLogic->curl( 'host/header', ['host_id'=>$RouteLogic->upstream_host_id], 'GET');
                if ($result['status']==200){
                    if (isset($result['data']['host_data'])){
                        if (isset($result['data']['host_data']['port']) && $result['data']['host_data']['port']==0){
                            $result['data']['host_data']['port'] = lang_plugins("res_mf_finance_common_default");
                        }
                        unset(
                            $result['data']['host_data']['amount'],
                            $result['data']['host_data']['amount_desc'],
                            $result['data']['host_data']['firstpaymentamount'],
                            $result['data']['host_data']['firstpaymentamount_desc'],
                            $result['data']['host_data']['order_amount'],
                            $result['data']['host_data']['upstream_price_value'],
                            $result['data']['system_config'],
                            $result['data']['second'],
                        );
                    }
                    // 操作系统数据
                    $result['data']['os'] = [];
                    $osSubs = [];
                    foreach ($result['data']['cloud_os_group'] as $item){
                        $version = [];
                        foreach ($result['data']['cloud_os'] as $item2){
                            if ($item2['group']==$item['id']){
                                $version[] = [
                                    'id' => strtolower($item2['id']),
                                    'option_name' => $item2['name']
                                ];
                            }
                        }
                        $osSubs[] = [
                            'os' => strtolower($item['id']),
                            'version' => $version
                        ];
                    }
                    if (!empty($osSubs)){
                        $result['data']['os'] = [
                            'id' => 0,
                            'option_name' => '',
                            'option_type' => 'os',
                            'subs' => $osSubs
                        ];
                    }
                    // 隐藏安全组，NAT建站，NAT转发
                    if (isset($result['data']['module_client_area']) && is_array($result['data']['module_client_area'])){
                        $filter = [];
                        foreach ($result['data']['module_client_area'] as $item){
                            if (!in_array($item['key'],['security_group','nat_acl','nat_web'])){
                                $filter[] = $item;
                            }
                        }
                        $result['data']['module_client_area'] = $filter;
                    }
                }
            }
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_act_exception')]);
        }
        if ($result['status']!=200){
            $result['status'] = 400;
        }
        return json($result);
    }

    /**
     * 时间 2024-05-16
     * @title 产品列表
     * @desc 产品列表
     * @url /console/v1/remf_finance_common
     * @method  GET
     * @author wyh
     * @version v1
     * @param   int page 1 页数
     * @param   int limit - 每页条数
     * @param   string orderby - 排序(id,due_time,status)
     * @param   string sort - 升/降序
     * @param   string keywords - 关键字搜索,搜索套餐名称/主机名/IP
     * @param   string status - 产品状态(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除)
     * @param   string tab - 状态using使用中expiring即将到期overdue已逾期deleted已删除
     * @param   int m - 菜单ID
     * @return  array data.list - 列表数据
     * @return  int data.list[].id - 产品ID
     * @return  string data.list[].name - 产品标识
     * @return  string data.list[].status - 产品状态(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除)
     * @return  int data.list[].due_time - 到期时间
     * @return  int data.list[].active_time - 开通时间
     * @return  string data.list[].product_name - 商品名称
     * @return  int count - 总条数
     * @return  int expiring_count - 即将到期产品数量
     * @return  int self_defined_field[].id - 自定义字段ID
     * @return  string self_defined_field[].field_name - 自定义字段名称
     * @return  string self_defined_field[].field_type - 字段类型(text=文本框,link=链接,password=密码,dropdown=下拉,tickbox=勾选框,textarea=文本区)
     */
    public function hostList(){
        $param = request()->param();

        $result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => [
                'list'  => [],
                'count' => [],
            ]
        ];

        $clientId = get_client_id();
        if(empty($clientId)){
            return json($result);
        }

        $where = [];
        if(isset($param['m']) && !empty($param['m'])){
            // 菜单,菜单里面必须是下游商品
            $MenuModel = MenuModel::where('menu_type', 'res_module')
                ->where('module', 'mf_finance_common')
                ->where('id', $param['m'])
                ->find();
            if(!empty($MenuModel) && !empty($MenuModel['product_id'])){
                $MenuModel['product_id'] = json_decode($MenuModel['product_id'], true);
                if(!empty($MenuModel['product_id'])){
                    $upstreamProduct = UpstreamProductModel::whereIn('product_id', $MenuModel['product_id'])->where('res_module', 'mf_finance_common')->find();
                    if(!empty($upstreamProduct)){
                        $where[] = ['h.product_id', 'IN', $MenuModel['product_id'] ];
                    }
                }
            }
        }else{
            //return json($result);
        }

        $param['page'] = isset($param['page']) ? ($param['page'] ? (int)$param['page'] : 1) : 1;
        $param['limit'] = isset($param['limit']) ? ($param['limit'] ? (int)$param['limit'] : config('idcsmart.limit')) : config('idcsmart.limit');
        $param['sort'] = isset($param['sort']) ? ($param['sort'] ?: config('idcsmart.sort')) : config('idcsmart.sort');
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id','due_time','status']) ? $param['orderby'] : 'id';
        $param['orderby'] = 'h.'.$param['orderby'];

        $where[] = ['h.client_id', '=', $clientId];
        $where[] = ['h.status', '<>', 'Cancelled'];

        // 前台是否展示已删除产品
        $homeShowDeletedHost = configuration('home_show_deleted_host');
        if($homeShowDeletedHost!=1){
            $where[] = ['h.status', '<>', 'Deleted'];
        }

        // 获取子账户可见产品
        $res = hook('get_client_host_id', ['client_id' => get_client_id(false)]);
        $res = array_values(array_filter($res ?? []));
        foreach ($res as $key => $value) {
            if(isset($value['status']) && $value['status']==200){
                $hostId = $value['data']['host'];
            }
        }
        if(isset($hostId) && !empty($hostId)){
            $where[] = ['h.id', 'IN', $hostId];
        }

        // hh 20240319 先做兼容处理,后续稳定后不用判断
        $supportOrderRecycleBin = is_numeric(configuration('order_recycle_bin'));
        if($supportOrderRecycleBin){
            $where[] = ['h.is_delete', '=', 0];
        }

        // theworld 20240401 获取即将到期数量
        $expiringCount = HostModel::alias('h')
            ->field('h.id')
            ->leftJoin('product p', 'h.product_id=p.id')
            ->join('upstream_product up', 'p.id=up.product_id AND up.res_module="mf_finance_common"')
            ->where($where)
            ->where(function($query){
                $time = time();
                $renewalFirstDay = configuration('cron_due_renewal_first_day');
                $timeRenewalFirst = strtotime(date('Y-m-d 23:59:59', $time+$renewalFirstDay*24*3600));
                $query->whereIn('h.status', ['Pending', 'Active'])->where('h.due_time', '>', $time)->where('h.due_time', '<=', $timeRenewalFirst)->where('billing_cycle', '<>', 'free')->where('billing_cycle', '<>', 'onetime');
            })
            ->count();

        // theworld 20240401 列表过滤条件移动
        if(isset($param['status']) && !empty($param['status'])){
            if($param['status'] == 'Pending'){
                $where[] = ['h.status', 'IN', ['Pending','Failed']];
            }else if(in_array($param['status'], ['Unpaid','Active','Suspended','Deleted'])){
                $where[] = ['h.status', '=', $param['status']];
            }
        }
        if(isset($param['tab']) && !empty($param['tab'])){
            if($param['tab']=='using'){
                $where[] = ['h.status', 'IN', ['Pending','Active']];
            }else if($param['tab']=='expiring'){
                $time = time();
                $renewalFirstDay = configuration('cron_due_renewal_first_day');
                $timeRenewalFirst = strtotime(date('Y-m-d 23:59:59', $time+$renewalFirstDay*24*3600));

                $where[] = ['h.status', 'IN', ['Pending','Active']];
                $where[] = ['h.due_time', '>', $time];
                $where[] = ['h.due_time', '<=', $timeRenewalFirst];
                $where[] = ['h.billing_cycle', '<>', 'free'];
                $where[] = ['h.billing_cycle', '<>', 'onetime'];
            }else if($param['tab']=='overdue'){
                $time = time();

                $where[] = ['h.status', 'IN', ['Pending', 'Active', 'Suspended', 'Failed']];
                $where[] = ['h.due_time', '<=', $time];
                $where[] = ['h.billing_cycle', '<>', 'free'];
                $where[] = ['h.billing_cycle', '<>', 'onetime'];
            }else if($param['tab']=='deleted'){
                $time = time();
                $where[] = ['h.status', '=', 'Deleted'];
            }
        }
        if(isset($param['keywords']) && $param['keywords'] !== ''){
            $where[] = ['h.name', 'LIKE', '%'.$param['keywords'].'%'];
        }

        $count = HostModel::alias('h')
            ->leftJoin('product p', 'h.product_id=p.id')
            ->join('upstream_product up', 'p.id=up.product_id AND up.res_module="mf_finance_common"')
            ->where($where)
            ->count();

        $host = HostModel::alias('h')
            ->field('h.id,h.product_id,h.name,h.status,h.active_time,h.due_time,p.name product_name,h.client_notes')
            ->leftJoin('product p', 'h.product_id=p.id')
            ->join('upstream_product up', 'p.id=up.product_id AND up.res_module="mf_finance_common"')
            ->where($where)
            ->withAttr('status', function($val){
                return $val == 'Failed' ? 'Pending' : $val;
            })
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->group('h.id')
            ->select()
            ->toArray();
        if(!empty($host) && class_exists('app\common\model\SelfDefinedFieldModel')){
            $hostId = array_column($host, 'id');
            $productId = array_column($host, 'product_id');

            $SelfDefinedFieldModel = new SelfDefinedFieldModel();
            $selfDefinedField = $SelfDefinedFieldModel->getHostListSelfDefinedFieldValue([
                'product_id' => $productId,
                'host_id'    => $hostId,
            ]);
        }
        foreach($host as $k=>$v){
            $host[$k]['self_defined_field'] = $selfDefinedField['self_defined_field_value'][ $v['id'] ] ?? (object)[];
        }

        $result['data']['list']  = $host;
        $result['data']['count'] = $count;
        $result['data']['expiring_count'] = $expiringCount;
        $result['data']['self_defined_field'] = $selfDefinedField['self_defined_field'] ?? [];
        return json($result);
    }

    /**
     * 时间 2024-05-16
     * @title 获取自定义tab内容
     * @desc 获取自定义tab内容
     * @url /console/v1/remf_finance_common/:id/custom/content
     * @method  GET
     * @author wyh
     * @version v1
     * @param   int id - 产品ID require
     * @param   string key - 关键字（/console/v1/remf_finance_common/:id接口字段module_client_area的键key） require
     * @param   int date - 时间戳1713172205000 require
     */
    public function content()
    {
        $param = request()->param();
        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete']){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_host_not_found')]);
        }
        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_common'){
                // 上游商品
                $result = $RouteLogic->curl( "console/v1/remf_finance_common/{$upstreamHost['upstream_host_id']}/custom/content", [
                    'key'        => $param['key']??"",
                    'date'        => $param['date']??"",
                    'api_url'        => $param['api_url']??"",
                ], 'GET');
            }else{
                $data = [
                    'id'        => $RouteLogic->upstream_host_id,
                    'key'       => $param['key']??"",
                    'date'      => $param['date']??"",
                    'api_url'   => request()->domain().request()->rootUrl()."/console/v1/remf_finance_common/host/{$param['id']}/custom/provision",
                    'is_api'    => true
                ];
                $login = idcsmart_api_login($RouteLogic->supplier_id, true);
                $html = $RouteLogic->curl('provision/custom/content?jwt='.$login['data']['jwt'], $data, 'GET','html');
                $html .= "<link rel=\"stylesheet\" href=\"/plugins/server/idcsmart_common/module/nokvm/templates/nokvm/css/htools.select.skin.css\">
  <link rel=\"stylesheet\" href=\"/plugins/server/idcsmart_common/module/nokvm/templates/nokvm/css/loading.css\">
  <script src=\"/plugins/server/idcsmart_common/module/nokvm/templates/nokvm/js/sweetalert2.all.min.js\"></script>";
                $html .= "<style>
  .swal2-popup {
    top: 30%;
    font-size: 16px;
  }
</style>";
//                $content = file_get_contents($supplier['url']."/vendor/nokvm/js/sweetalert2.all.min.js");
//                $content .= file_get_contents($supplier['url']."/vendor/nokvm/js/jquery.htools.select.js");
//                $content .= file_get_contents($supplier['url']."/vendor/nokvm/js/jquery-min.js");
//                $content .= file_get_contents($supplier['url']."/vendor/nokvm/js/selectFilter.js");
                //$html .= $html."<script>{$content}</script>";
                // 将JWT token替换为Bearer v10_token
                $pattern = '/JWT\s[a-zA-Z0-9\._-]+\.[a-zA-Z0-9\._-]+\.[a-zA-Z0-9\._-]+/';
                $html = preg_replace($pattern,"Bearer ".get_header_jwt(),$html);
                return json([
                    'status' => 200,
                    'msg' => lang_plugins('message_message'),
                    'data' => [
                        'content' => $html
                    ]
                ]);
            }
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_act_exception')]);
        }
        return json($result);
    }

    /**
     * 时间 2024-05-16
     * @title 开机
     * @desc 开机
     * @url /console/v1/remf_finance_common/:id/on
     * @method  POST
     * @author wyh
     * @version v1
     * @param   int id - 产品ID require
     */
    public function on()
    {
        $param = request()->param();
        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete']){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_host_not_found')]);
        }
        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_common'){
                // 上游商品
                $result = $RouteLogic->curl( "console/v1/remf_finance_common/{$upstreamHost['upstream_host_id']}/on", [
                    'os' => $param['os']??'',
                    'code' => $param['code']??'',
                ], 'POST');
            }else{
                $postData = [
                    'id'        => $RouteLogic->upstream_host_id,
                    'func'        => 'on',
                    'is_api'    => true
                ];
                $result = $RouteLogic->curl('provision/default', $postData, 'POST');
            }
            if($result['status'] == 200){
                $description = lang_plugins('res_mf_finance_common_log_host_start_boot_success', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }else{
                $description = lang_plugins('res_mf_finance_common_log_host_start_boot_fail', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }
            active_log($description, 'host', $HostModel['id']);
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_act_exception')]);
        }

        if ($result['status']!=200){
            $result['status'] = 400;
        }

        return json($result);
    }

    /**
     * 时间 2024-05-16
     * @title 关机
     * @desc 关机
     * @url /console/v1/remf_finance_common/:id/off
     * @method  POST
     * @author wyh
     * @version v1
     * @param   int id - 产品ID require
     */
    public function off()
    {
        $param = request()->param();
        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete'] ){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_host_not_found')]);
        }
        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_common'){
                $result = $RouteLogic->curl( "console/v1/remf_finance_common/{$upstreamHost['upstream_host_id']}/off", [], 'POST');
            }else{
                $postData = [
                    'id'        => $RouteLogic->upstream_host_id,
                    'func'        => 'off',
                    'is_api'    => true
                ];
                $result = $RouteLogic->curl( 'provision/default', $postData, 'POST');
            }
            if($result['status'] == 200){
                $description = lang_plugins('res_mf_finance_common_log_host_start_off_success', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }else{
                $description = lang_plugins('res_mf_finance_common_log_host_start_off_fail', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }
            active_log($description, 'host', $HostModel['id']);
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_act_exception')]);
        }
        if ($result['status']!=200){
            $result['status'] = 400;
        }
        return json($result);
    }

    /**
     * 时间 2024-05-16
     * @title 重启
     * @desc 重启
     * @url /console/v1/remf_finance_common/:id/reboot
     * @method  POST
     * @author wyh
     * @version v1
     * @param   int id - 产品ID require
     */
    public function reboot()
    {
        $param = request()->param();
        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete'] ){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_host_not_found')]);
        }
        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_common'){
                $result = $RouteLogic->curl( "console/v1/remf_finance_common/{$upstreamHost['upstream_host_id']}/reboot", [], 'POST');
            }else{
                $postData = [
                    'id'        => $RouteLogic->upstream_host_id,
                    'func'        => 'reboot',
                    'is_api'    => true
                ];
                $result = $RouteLogic->curl( 'provision/default', $postData, 'POST');
            }
            if($result['status'] == 200){
                $description = lang_plugins('res_mf_finance_common_log_host_start_reboot_success', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }else{
                $description = lang_plugins('res_mf_finance_common_log_host_start_reboot_fail', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }
            active_log($description, 'host', $HostModel['id']);
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_act_exception')]);
        }
        if ($result['status']!=200){
            $result['status'] = 400;
        }
        return json($result);
    }

    /**
     * 时间 2024-05-16
     * @title 硬关机
     * @desc 硬关机
     * @url /console/v1/remf_finance_common/:id/hard_off
     * @method  POST
     * @author wyh
     * @version v1
     * @param   int id - 产品ID require
     */
    public function hardOff()
    {
        $param = request()->param();
        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete'] ){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_host_not_found')]);
        }
        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_common'){
                // 上游商品
                $result = $RouteLogic->curl( "console/v1/remf_finance_common/{$upstreamHost['upstream_host_id']}/hard_off", [], 'POST');
            }else{
                $postData = [
                    'id'        => $RouteLogic->upstream_host_id,
                    'func'        => 'hard_off',
                    'is_api'    => true
                ];
                $result = $RouteLogic->curl( 'provision/default', $postData, 'POST');
            }
            if($result['status'] == 200){
                $description = lang_plugins('res_mf_finance_common_log_host_hard_off_success', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }else{
                $description = lang_plugins('res_mf_finance_common_log_host_hard_off_fail', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }
            active_log($description, 'host', $HostModel['id']);
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_act_exception')]);
        }
        if ($result['status']!=200){
            $result['status'] = 400;
        }
        return json($result);
    }

    /**
     * 时间 2024-05-16
     * @title 硬重启
     * @desc 硬重启
     * @url /console/v1/remf_finance_common/:id/hard_reboot
     * @method  POST
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     */
    public function hardReboot()
    {
        $param = request()->param();
        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete'] ){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_host_not_found')]);
        }
        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_common'){
                $result = $RouteLogic->curl( "console/v1/remf_finance_common/{$upstreamHost['upstream_host_id']}/hard_reboot", [], 'POST');
            }else{
                $postData = [
                    'id'        => $RouteLogic->upstream_host_id,
                    'func'        => 'hard_reboot',
                    'is_api'    => true
                ];
                $result = $RouteLogic->curl( 'provision/default', $postData, 'POST');
            }
            if($result['status'] == 200){
                $description = lang_plugins('res_mf_finance_common_log_host_hard_reboot_success', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }else{
                $description = lang_plugins('res_mf_finance_common_log_host_hard_reboot_fail', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }
            active_log($description, 'host', $HostModel['id']);
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_act_exception')]);
        }
        if ($result['status']!=200){
            $result['status'] = 400;
        }
        return json($result);
    }

    /**
     * 时间 2024-05-16
     * @title 重装系统
     * @desc 重装系统
     * @url /console/v1/remf_finance_common/:id/reinstall
     * @method  POST
     * @author wyh
     * @version v1
     * @param   int id - 产品ID require
     * @param   int os - 重装系统的操作系统id require
     * @param   string os_group - 操作系统分组 require
     * @param   string code - 二次验证码（这里可以不传，走api不做验证）
     */
    public function reinstall()
    {
        $param = request()->param();
        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete'] ){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_host_not_found')]);
        }
        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_common'){
                $result = $RouteLogic->curl( "console/v1/remf_finance_common/{$upstreamHost['upstream_host_id']}/reinstall", [
                    'os' => $param['os']??'',
                    'os_group' => $param['os_group']??'',
                    'code' => $param['code']??'',
                ], 'POST');
            }else{
                $postData = [
                    'id'        => $RouteLogic->upstream_host_id,
                    'os'        => $param['os'] ?? '',
                    'os_group'  => $param['os_group'] ?? '',
                    'code'  => $param['code'] ?? '',
                    'func'      => 'reinstall',
                    'is_api'    => true
                ];
                $result = $RouteLogic->curl( 'provision/default', $postData, 'POST');
            }
            if($result['status'] == 200){
                $description = lang_plugins('res_mf_finance_common_log_host_start_reinstall_success', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }else{
                $description = lang_plugins('res_mf_finance_common_log_host_start_reinstall_fail', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }
            active_log($description, 'host', $HostModel['id']);
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_act_exception')]);
        }
        if ($result['status']!=200){
            $result['status'] = 400;
        }
        return json($result);
    }

    /**
     * 时间 2024-05-16
     * @title 重置密码
     * @desc 重置密码
     * @url /console/v1/remf_finance_common/:id/crack_pass
     * @method  POST
     * @author wyh
     * @version v1
     * @param   int id - 产品ID require
     * @param   string password - 新密码 require
     * @param   string force - 是否强制关机，on是off否 require
     * @param   string code - 二次验证码（非必传）
     */
    public function crackPass()
    {
        $param = request()->param();
        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete'] ){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_host_not_found')]);
        }
        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_common'){
                $result = $RouteLogic->curl( "console/v1/remf_finance_common/{$upstreamHost['upstream_host_id']}/reset_password", [
                    'password' => $param['password']??'',
                    'force'      => $param['force'] ?? '',
                    'code'      => $param['code'] ?? '',
                ], 'POST');
            }else{
                $postData = [
                    'id'            => $RouteLogic->upstream_host_id,
                    'password'      => $param['password'] ?? '',
                    'force'      => $param['force'] ?? '',
                    'func'      => 'crack_pass',
                    'code'      => $param['code'] ?? '',
                    'is_api' => true
                ];
                $result = $RouteLogic->curl( 'provision/default', $postData, 'POST');
            }
            if($result['status'] == 200){
                $description = lang_plugins('res_mf_finance_common_log_host_start_reset_password_success', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }else{
                $description = lang_plugins('res_mf_finance_common_log_host_start_reset_password_success', [
                    '{hostname}' => $HostModel['name'],
                ]);
            }
            active_log($description, 'host', $HostModel['id']);
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_act_exception')]);
        }
        if ($result['status']!=200){
            $result['status'] = 400;
        }
        return json($result);
    }

    /**
     * 时间 2024-05-16
     * @title 获取实例状态
     * @desc 获取实例状态
     * @url /console/v1/remf_finance_common/:id/status
     * @method  GET
     * @author wyh
     * @version v1
     * @param   int id - 产品ID require
     * @return  string data.status - 实例状态(on=开机,off=关机,operating=操作中,fault=故障)
     * @return  string data.desc - 实例状态描述
     */
    public function status()
    {
        $param = request()->param();
        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete'] ){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_host_not_found')]);
        }
        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_common'){
                $result = $RouteLogic->curl( "console/v1/remf_finance_common/{$upstreamHost['upstream_host_id']}/status", [], 'GET');
            }else{
                $postData = [
                    'id'        => $RouteLogic->upstream_host_id,
                    'func' => 'status',
                    'is_api'    => true
                ];
                $res = $RouteLogic->curl( 'provision/default', $postData, 'POST');
                if($res['status'] == 200 && isset($res['data']['status'])){
                    if($res['data']['status'] == 'not_support'){
                        $status = [
                            'status' => 'fault',
                            'desc'   => lang_plugins('res_mf_finance_common_fault'),
                        ];
                    }else if($res['data']['status'] == 'on'){
                        $status = [
                            'status' => 'on',
                            'desc'   => lang_plugins('res_mf_finance_common_on'),
                        ];
                    }else if($res['data']['status'] == 'off'){
                        $status = [
                            'status' => 'off',
                            'desc'   => lang_plugins('res_mf_finance_common_off')
                        ];
                    }else if($res['data']['status'] == 'suspend'){
                        $status = [
                            'status' => 'suspend',
                            'desc'   => lang_plugins('res_mf_finance_common_suspend')
                        ];
                    }else{
                        $status = [
                            'status' => 'fault',
                            'desc'   => lang_plugins('res_mf_finance_common_fault'),
                        ];
                    }
                }else{
                    $status = [
                        'status' => 'fault',
                        'desc'   => lang_plugins('res_mf_finance_common_fault'),
                    ];
                }
                $result = [
                    'status' => 200,
                    'msg'    => lang_plugins('success_message'),
                    'data'   => $status,
                ];
            }
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_act_exception')]);
        }
        if ($result['status']!=200){
            $result['status'] = 400;
        }
        return json($result);
    }

    /**
     * 时间 2024-05-08
     * @title 批量操作
     * @desc 批量操作
     * @url /console/v1/remf_finance_common/batch_operate
     * @method  POST
     * @author theworld
     * @version v1
     * @param   array id - 产品ID require
     * @param   string action - 动作on开机off关机reboot重启 require
     */
    public function batchOperate()
    {
        $param = request()->param();
        $HostValidate = new HostValidate();
        if (!$HostValidate->scene('batch')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($HostValidate->getError())]);
        }
        $id = $param['id'] ?? [];
        $id = array_unique(array_filter($id, function ($x) {
            return is_numeric($x) && $x > 0;
        }));
        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [],
        ];
        $action = [
            'on' => 'on',
            'off' => 'off',
            'reboot' => 'reboot',
        ];
        foreach ($id as $v) {
            $res = reserver_api('MfFinanceCommon', 'cloud', $action[$param['action']], ['id' => (int)$v]);
            $result['data'][] = ['id' => (int)$v, 'status' => $res['status'], 'msg' => $res['msg']];
        }
        if ($result['status']!=200){
            $result['status'] = 400;
        }
        return json($result);
    }

    /**
     * 时间 2024-05-16
     * @title 获取模块图表数据
     * @desc 获取模块图表数据
     * @url /console/v1/remf_finance_common/:id/chart
     * @method  GET
     * @author wyh
     * @version v1
     * @param   int id - 产品ID require
     * @param   int start - 开始秒级时间
     * @param   int end - 结束秒级时间
     * @param   string type - module_chart里面的type(console/v1/remf_finance_common/:id接口返回)
     * @param   string select - module_chart里面的select的value(console/v1/remf_finance_common/:id接口返回)
     * @return  array list - 图表数据
     * @return  int list[].time - 时间(秒级时间戳)
     * @return  float list[].in_bw - 进带宽
     * @return  float list[].out_bw - 出带宽
     * @return  string unit - 当前单位
     */
    public function chart()
    {
        $param = request()->param();
        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete'] ){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_host_not_found')]);
        }
        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_common'){
                $res = $RouteLogic->curl( "console/v1/remf_finance_common/{$upstreamHost['upstream_host_id']}/chart", [
                    'start'    => isset($param['start_time']) ? $param['start_time'].'000' : '',
                    'end'    => isset($param['end_time']) ? $param['end_time'].'000' : '',
                    'type'          => $param['type']??"",
                    'select'          => $param['select']??"",
                ], 'POST');
            }else{
                $postData = [
                    'id'            => $RouteLogic->upstream_host_id,
                    'start'    => isset($param['start']) ? $param['start'].'000' : '',
                    'end'    => isset($param['end']) ? $param['end'].'000' : '',
                    'type'          => $param['type']??"",
                    'select'          => $param['select']??"",
                    'is_api'        => true
                ];
                $res = $RouteLogic->curl( 'provision/chart/'.$RouteLogic->upstream_host_id, $postData, 'GET');
            }
            $result = [
                'status' => 200,
                'msg'    => lang_plugins('success_message'),
                'data'   => $res['data']??[],
            ];
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_act_exception')]);
        }
        return json($result);
    }

    /**
     * 时间 2024-05-16
     * @title 日志
     * @desc 日志
     * @url /console/v1/remf_finance_common/:id/log
     * @method  GET
     * @author wyh
     * @version v1
     * @param int id - 产品ID
     * @param string keywords - 关键字
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id,description,create_time,ip
     * @param string sort - 升/降序 asc,desc
     * @return array list - 系统日志
     * @return int list[].id - 系统日志ID
     * @return string list[].description - 描述
     * @return string list[].create_time - 时间
     * @return int list[].ip - IP
     * @return int count - 系统日志总数
     */
    public function log(){
        $param = request()->param();
        $SystemLogModel = new SystemLogModel();
        $result = $SystemLogModel->systemLogList($param);
        return json($result);
    }

    /**
     * 时间 2024-05-16
     * @title 自定义模块动作
     * @desc 自定义模块动作
     * @url /console/v1/remf_finance_common/host/:id/custom/provision
     * @method  POST
     * @author wyh
     * @version v1
     * @param int id - 产品ID
     */
    public function customProvision(){
        $param = request()->param();
        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete'] ){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_host_not_found')]);
        }
        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_common'){
                $postData = [
                    'func'        => $param['func']??"",
                ];
                $postData = array_merge($postData,$param);
                $result = $RouteLogic->curl( "console/v1/remf_finance_common/host/{$upstreamHost['upstream_host_id']}/custom/provision", $postData, 'POST');
            }else{
                $postData = [
                    'id'        => $RouteLogic->upstream_host_id,
                    'func'        => $param['func']??"",
                    'is_api'    => true
                ];
                $postData = array_merge($postData,$param);
                $result = $RouteLogic->curl( 'provision/custom/'.$RouteLogic->upstream_host_id, $postData, 'POST');
            }
        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_act_exception')]);
        }
        if ($result['status']!=200){
            $result['status'] = 400;
        }
        return json($result);
    }

    /**
     * 时间 2024-05-16
     * @title 升降级配置页面
     * @desc 升降级配置页面
     * @url /console/v1/remf_finance_common/:id/upgrade_config
     * @method  GET
     * @author wyh
     * @version v1
     * @param int id - 产品ID require
     * @return array host - 配置数据
     */
    public function upgradeConfig(){
        $param = request()->param();
        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete'] ){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_host_not_found')]);
        }
        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $HostModel = HostModel::find($param['id']);
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$HostModel['product_id'])->find();
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_common'){
                // 上游商品
                $result = $RouteLogic->curl( "console/v1/remf_finance_common/{$upstreamHost['upstream_host_id']}/upgrade_config", [
                ], 'GET');
            }else{
                $postData = [
                    'hid' => $RouteLogic->upstream_host_id,
                ];
                $result = $RouteLogic->curl( 'upgrade/index/'.$RouteLogic->upstream_host_id, $postData,'GET');
            }

        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_act_exception')]);
        }
        return json($result);
    }

    /**
     * 时间 2023-02-06
     * @title 升降级配置计算价格
     * @desc 升降级配置计算价格
     * @url /console/v1/remf_finance_common/:id/sync_upgrade_config_price
     * @method  POST
     * @author wyh
     * @version v1
     * @param int id - 产品ID require
     * @param array configoption - 配置信息{"配置ID":"子项ID"} require
     * @return float price - 价格
     */
    public function syncUpgradeConfigPrice($return=0,$local=0){
        $param = request()->param();
        $host = HostModel::find($param['id']);
        if(empty($host) || $host['client_id'] != get_client_id() || $host['is_delete']){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_host_not_found')]);
        }
        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $param['id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($RouteLogic->supplier_id);
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProduct = $UpstreamProductModel->where('product_id',$host['product_id'])->find();
            // 多级代理
            if (!empty($supplier) && $supplier['type']=='default' && $upstreamProduct['res_module']=='mf_finance_common'){
                // 上游商品
                // 提交给上游时，判断是否提交配置数据
                $postData = [
                    'configoption' => $param['configoption']??[],
                    'is_downstream' => 1,
                    'return' => $param['return']??$return
                ];
                $result = $RouteLogic->curl( "console/v1/remf_finance_common/{$upstreamHost['upstream_host_id']}/sync_upgrade_config_price", $postData, 'POST');
                $param['RouteLogic'] = $RouteLogic;
                $param['supplier'] = $supplier;
                $param['host'] = $host;
                $result = upstream_upgrade_result_deal($param,$result);
            }else{
                // 接口传了return参数(涉及到多级代理时，方法传参方式行不通，需要接口传参)
                if (isset($param['return'])){
                    $return = intval($param['return']);
                }
                // 前端调接口时，才提交配置到老财务
                if ($return==0){
                    $postData = [
                        'hid' => $RouteLogic->upstream_host_id,
                        'configoption' => $param['configoption']??[]
                    ];
                    $result = $RouteLogic->curl( 'upgrade/upgrade_config_post', $postData,'POST');
                }else{
                    // 后端直接调用时，只需要返回价格数据
                    $result['status'] = 200;
                }
                if ($result['status']==200){
                    $result = $RouteLogic->curl( 'upgrade/upgrade_config_page', ['hid' => $RouteLogic->upstream_host_id],'GET');

                    if ($result['status']==200){
                        $formatZero = bcsub(0,0,2);
                        $renewPriceDifference = $formatZero;
                        $preview = [];
                        if (isset($result['data']['alloption']) && is_array($result['data']['alloption'])){
                            foreach ($result['data']['alloption'] as $item){
                                $renewPriceDifference = bcadd($renewPriceDifference,$item['upgrade_item']['recurring_change']??$formatZero,2);
                                $optionType = $item['option_type'];
                                if (in_array($optionType,[4,7,9,11,14,15,16,17,18,19])){
                                    $value = ($item['old_qty']??$formatZero) . '=>' . ($item['qty']??$formatZero) . ($item['unit']??'');
                                }else{
                                    $value = ($item['old_suboption_name']??'') . '=>' . ($item['suboption_name']??'');
                                }
                                $preview[] = [
                                    'name' => $item['option_name'],
                                    'value' => $value,
                                    'price' => $item['upgrade_item']['recurring_change']??$formatZero,
                                ];
                            }
                        }
                        if ($renewPriceDifference>0 && isset($result['data']['recurring_change_discount'])) { // 升级
                            $renewPriceDifference = bcsub($renewPriceDifference,$result['data']['recurring_change_discount'],2)>0?bcsub($renewPriceDifference,$result['data']['recurring_change_discount'],2):$formatZero;
                        }elseif ($renewPriceDifference<0 && isset($result['data']['recurring_change_discount'])) { // 降级
                            $renewPriceDifference = bcsub($renewPriceDifference,$result['data']['recurring_change_discount'],2);
                        }
                        $data = [
                            'price_difference' => bcsub($result['data']['subtotal']??0,$result['data']['saleproducts']??0,2), // 减去折扣后的升降级差价
                            'renew_price_difference' => $renewPriceDifference, // 减去折扣后的续费差价
                            'base_price' => 0, // 老财务无原价，直接返回0，下游并不需要此字段
                            'preview' => $preview,
                            'configoptions' => $result['data']['configoptions']??[],
                        ];
                        $result = [
                            'status' => 200,
                            'msg' => lang_plugins('success_message'),
                            'data' => $data
                        ];
                        $param['RouteLogic'] = $RouteLogic;
                        $param['supplier'] = $supplier;
                        $param['host'] = $host;
                        $result = upstream_upgrade_result_deal($param,$result);
                    }
                }
            }
        }catch(\Exception $e){
            $result = ['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_act_exception').$e->getMessage()];
        }
        if ($return==1 && $local==1){
            return $result;
        }
        return json($result);
    }

    /**
     * 时间 2023-02-06
     * @title 升降级配置结算
     * @desc 升降级配置结算
     * @url /console/v1/remf_finance_common/:id/upgrade_config
     * @method  POST
     * @author wyh
     * @version v1
     * @param int id - 产品ID require
     * @param object customfield - 自定义字段{"promo_code":"zkj143df","voucher_id":1}
     * @return int id - 订单ID
     */
    public function upgradeConfigPost()
    {
        $param = request()->param();
        $host = HostModel::find($param['id']);
        if(empty($host) || $host['client_id'] != get_client_id() || $host['is_delete']){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_host_not_found')]);
        }
        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);

            $result = $this->syncUpgradeConfigPrice(1,1);

            if ($result['status']!=200){
                throw new \Exception($result['msg']??lang_plugins('res_mf_finance_act_exception'));
            }

            $profit = $result['data']['profit']??0;

            $OrderModel = new OrderModel();

            $data = [
                'host_id'     => $host['id'],
                'client_id'   => get_client_id(),
                'type'        => 'upgrade_config',
                'amount'      => $result['data']['price'],
                'description' => $result['data']['description'],
                'price_difference' => $result['data']['price_difference'],
                'renew_price_difference' => $result['data']['renew_price_difference'],
                'base_price' => $result['data']['base_price'],
                'upgrade_refund' => 0,
                'config_options' => [
                    'type'          => 'remf_finance_upgrade_config',
                    'configoption'  => $result['data']['configoptions']??[],
                ],
                'customfield' => $param['customfield'] ?? [],
            ];

            $result = $OrderModel->createOrder($data);

            if($result['status'] == 200){
                UpstreamOrderModel::create([
                    'supplier_id'   => $RouteLogic->supplier_id,
                    'order_id'      => $result['data']['id'],
                    'host_id'       => $host['id'],
                    'amount'        => $data['amount'],
                    'profit'        => $profit,
                    'create_time'   => time(),
                ]);
            }
        }catch (\Exception $e){
            $result = ['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_act_exception').$e->getMessage()];
        }
        return json($result);
    }

    /**
     * 时间 2024-05-16
     * @title 升降级商品
     * @desc 升降级商品
     * @url /console/v1/remf_finance_common/:id/upgrade_product
     * @method  GET
     * @author wyh
     * @version v1
     * @param int id - 产品ID require
     * @return object old_host - 原产品数据
     * @return array host - 可升降级的商品数组
     * @return int host[].pid - 商品ID
     * @return string host[].host - 商品名称
     * @return array host[].cycle - 周期
     * @return float host[].cycle[].price - 价格
     * @return string host[].cycle[].billingcycle - 周期
     * @return string host[].cycle[].billingcycle_zh - 周期
     */
    public function upgradeProduct(){
        $param = request()->param();

        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete'] ){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_host_not_found')]);
        }

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);

            $productId = $HostModel['product_id'];

            // 可升降级商品
            $ProductUpgradeProductModel = new ProductUpgradeProductModel();
            $upgradeProductIds = $ProductUpgradeProductModel->where('product_id',$productId)->column('upgrade_product_id');
            // 对应的上游商品ID
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProductIds = $UpstreamProductModel->whereIn('product_id',$upgradeProductIds)->column('upstream_product_id');

            $postData = [
                'need_pids' => $upstreamProductIds??[], // [4,5]
            ];

            $result = $RouteLogic->curl( 'upgrade/upgrade_product/'.$RouteLogic->upstream_host_id, $postData,'GET');

        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_act_exception')]);
        }
        return json($result);
    }

    /**
     * 时间 2024-05-16
     * @title 升降级商品计算价格
     * @desc 升降级商品计算价格
     * @url /console/v1/remf_finance_common/:id/sync_upgrade_product_price
     * @method  POST
     * @author wyh
     * @version v1
     * @param int id - 产品ID require
     * @param int product_id - 新商品ID require
     * @param string cycle - 周期,传billingcycle的值 require
     * @return float price - 价格
     */
    public function syncUpgradeProductPrice(){
        $param = request()->param();

        $HostModel = HostModel::find($param['id']);
        if(empty($HostModel) || $HostModel['client_id'] != get_client_id() || $HostModel['is_delete'] ){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_common_host_not_found')]);
        }

        try{
            $RouteLogic = new RouteLogic();
            $RouteLogic->routeByHost($param['id']);

            // 可升降级商品
            $productId = $HostModel['product_id'];
            $ProductUpgradeProductModel = new ProductUpgradeProductModel();
            $upgradeProductIds = $ProductUpgradeProductModel->where('product_id',$productId)->column('upgrade_product_id');
            // 对应的上游商品ID
            $UpstreamProductModel = new UpstreamProductModel();
            $upstreamProductIds = $UpstreamProductModel->whereIn('product_id',$upgradeProductIds)->column('upstream_product_id');
            if (!in_array($param['product_id']??0,$upstreamProductIds)){
                throw new \Exception("商品不可升降级");
            }

            $postData = [
                'hid' => $RouteLogic->upstream_host_id,
                'pid' => $param['product_id']??0,
                'billingcycle' => $param['cycle']??""
            ];

            $result = $RouteLogic->curl( 'upgrade/upgrade_product_post', $postData,'POST');
            if ($result['status']==200){
                $result = $RouteLogic->curl( 'upgrade/upgrade_product_page', ['hid' => $RouteLogic->upstream_host_id],'GET');
                if ($result['status']==200){
                    $SupplierModel = new SupplierModel();
                    $supplier = $SupplierModel->find($RouteLogic->supplier_id);

                    // 计算汇率
                    $result['data']['amount_total'] = $result['data']['amount_total'] * $supplier['rate'];

                    $res['status'] = 200;
                    $res['msg'] = $result['msg'];
                    $upstream = $UpstreamProductModel->where('upstream_product_id',$param['product_id']??0)
                        ->where('supplier_id',$RouteLogic->supplier_id)
                        ->find();
                    if ($RouteLogic->upgrade_profit_type==1){
                        $res['data']['price'] = bcadd($result['data']['amount_total']??0, $upstream['upgrade_profit_percent'],2);
                    }else{
                        $res['data']['price'] = bcmul($result['data']['amount_total']??0, (1+$upstream['upgrade_profit_percent']/100),2);
                    }
                    if ($res['data']['price']<0){
                        $res['data']['price'] = bcsub(0,0,2);
                    }
                    return json($res);
                }
            }

        }catch(\Exception $e){
            return json(['status'=>400, 'msg'=>$e->getMessage()/*lang_plugins('res_mf_finance_common_act_exception')*/]);
        }
        return json($result);
    }

    /**
     * 时间 2024-05-16
     * @title 升降级商品结算
     * @desc 升降级商品结算
     * @url /console/v1/remf_finance_common/:id/upgrade_product
     * @method  POST
     * @author wyh
     * @version v1
     * @param int id - 产品ID require
     * @return int id - 订单ID
     */
    public function upgradeProductPost(){
        $param = request()->param();

        $host = HostModel::find($param['id']);
        if(empty($host) || $host['client_id'] != get_client_id() || $host['is_delete'] ){
            return json(['status'=>400, 'msg'=>lang_plugins('res_mf_finance_host_not_found')]);
        }
        $RouteLogic = new RouteLogic();
        $RouteLogic->routeByHost($param['id']);

        $res = $RouteLogic->curl( 'upgrade/upgrade_product_page', ['hid' => $RouteLogic->upstream_host_id],'GET');
        if ($res['status']!=200){
            return json($res);
        }

        // 上游商品ID
        $upstreamProductId = $res['data']['new_pid'];
        // 对应的本地商品ID
        $UpstreamProductModel = new UpstreamProductModel();
        $upstream = $UpstreamProductModel->where('upstream_product_id',$upstreamProductId)
            ->where('supplier_id',$RouteLogic->supplier_id)
            ->find();
        if (empty($upstream)){
            return json(['status'=>400,'msg'=>"商品不可升降级"]);
        }

        $SupplierModel = new SupplierModel();
        $supplier = $SupplierModel->find($RouteLogic->supplier_id);

        // 计算汇率
        $res['data']['amount_total'] = $res['data']['amount_total'] * $supplier['rate'];

        // 以新商品利润计算
        if ($RouteLogic->upgrade_profit_type==1){
            $amount = bcadd($res['data']['amount_total']??0, $upstream['upgrade_profit_percent'],2);
        }else{
            $amount = bcmul($res['data']['amount_total']??0, (1+$upstream['upgrade_profit_percent']/100),2);
        }
        $amount = $amount>0?$amount:bcsub(0,0,2);

        // 自定义升降级产品订单逻辑
        $OrderModel = new OrderModel();
        $result = $OrderModel->createUpgradeOrder([
            'host_id' => $param['id'],
            'client_id' => get_client_id(),
            'upgrade_refund' => 0, # 不支持退款
            'product' => [
                'product_id' => $upstream['product_id'],
                'price' =>  $amount,
                'config_options' => [
                    'new_pid' => $upstreamProductId,//上游商品
                    'cycle' => $res['data']['billingcycle']??"",
                    'configoption' => [] //使用默认配置
                ]
            ]
        ]);

        return json($result);
    }
}
