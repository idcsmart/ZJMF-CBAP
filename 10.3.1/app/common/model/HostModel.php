<?php
namespace app\common\model;

use app\common\logic\ResModuleLogic;
use think\db\Query;
use think\Model;
use think\Db;
use app\common\logic\ModuleLogic;
use app\common\model\NoticeSettingModel;
use app\admin\model\PluginModel;

/**
 * @title 产品模型
 * @desc 产品模型
 * @use app\common\model\HostModel
 */
class HostModel extends Model
{
	protected $name = 'host';

    // 设置字段信息
    protected $schema = [
        'id'                    => 'int',
        'client_id'             => 'int',
        'order_id'              => 'int',
        'product_id'            => 'int',
        'server_id'             => 'int',
        'name'                  => 'string',
        'status'                => 'string',
        'suspend_type'          => 'string',
        'suspend_reason'        => 'string',
        'suspend_time'          => 'int',
        'gateway'               => 'string',
        'gateway_name'          => 'string',
        'first_payment_amount'  => 'float',
        'renew_amount'          => 'float',
        'billing_cycle'         => 'string',
        'billing_cycle_name'    => 'string',
        'billing_cycle_time'    => 'int',
        'notes'                 => 'string',
        'client_notes'          => 'string',
        'active_time'           => 'int',
        'due_time'              => 'int',
        'termination_time'      => 'int',
        'create_time'           => 'int',
        'update_time'           => 'int',
        'downstream_info'       => 'string',
        'downstream_host_id'    => 'int',
    ];

    /**
     * 时间 2022-05-13
     * @title 产品列表
     * @desc 产品列表
     * @author theworld
     * @version v1
     * @param string param.keywords - 关键字,搜索范围:产品ID,商品名称,标识,用户名,邮箱,手机号
     * @param string param.billing_cycle - 付款周期
     * @param int param.client_id - 用户ID
     * @param string param.status - 状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败
     * @param int param.start_time - 开始时间
     * @param int param.end_time - 结束时间
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id,active_time,due_time
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - 产品
     * @return int list[].id - 产品ID 
     * @return int list[].client_id - 用户ID 
     * @return int list[].client_name - 用户名 
     * @return string list[].email - 邮箱 
     * @return string list[].phone_code - 国际电话区号 
     * @return string list[].phone - 手机号 
     * @return string list[].company - 公司 
     * @return int list[].product_id - 商品ID 
     * @return string list[].product_name - 商品名称 
     * @return string list[].name - 标识 
     * @return int list[].active_time - 开通时间 
     * @return int list[].due_time - 到期时间
     * @return string list[].first_payment_amount - 金额
     * @return string list[].billing_cycle - 周期
     * @return string list[].status - 状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败
     * @return int count - 产品总数
     */
    public function hostList($param)
    {
        // 获取当前应用
        $app = app('http')->getName();
        if($app=='home'){
            $param['client_id'] = get_client_id();
            if(empty($param['client_id'])){
                return ['list' => [], 'count' => 0];
            }
        }else{
            $param['client_id'] = isset($param['client_id']) ? intval($param['client_id']) : 0;
            $param['product_id'] = isset($param['product_id']) ? intval($param['product_id']) : 0;
        }

        $param['keywords'] = $param['keywords'] ?? '';
        $param['billing_cycle'] = $param['billing_cycle'] ?? '';
        $param['status'] = $param['status'] ?? '';
        $param['start_time'] = intval($param['start_time'] ?? 0);
        $param['end_time'] = intval($param['end_time'] ?? 0);
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id', 'client_id', 'product_name', 'name', 'active_time', 'due_time', 'first_payment_amount', 'status']) ? $param['orderby'] : 'id';
        if($param['orderby']=='product_name'){
            $param['orderby'] = 'p.name';
        }else{
            $param['orderby'] = 'h.'.$param['orderby'];  
        }

        $res = hook('get_client_host_id', ['client_id' => get_client_id(false)]);
        $res = array_values(array_filter($res ?? []));
        foreach ($res as $key => $value) {
            if(isset($value['status']) && $value['status']==200){
                $hostId = $value['data']['host'];
                if(empty($hostId)){
                    return ['list' => [], 'count' => 0];
                }
            }
        }
        $param['host_id'] = $hostId ?? [];

        $where = function (Query $query) use ($param, $app){
            if($app=='home'){
                $query->where('h.status', '<>', 'Cancelled');

                if (isset($param['scene']) && $param['scene']=='ticket' && class_exists('server\idcsmart_common_finance\model\IdcsmartCommonSonHost')){
                    $query->where('p.product_id','>',0);
                    $query->whereIn('s.module',['idcsmart_common_finance','idcsmart_common_dcim','idcsmart_common_cloud','idcsmart_common_business']);
                }
            }

            if(!empty($param['client_id'])){
                $query->where('h.client_id', (int)$param['client_id']);
            }
            if(!empty($param['product_id'])){
                $query->where('h.product_id', (int)$param['product_id']);
            }
            if(!empty($param['keywords'])){
                $query->where('h.id|p.name|h.name|c.username|c.email|c.phone|h.first_payment_amount', 'like', "%{$param['keywords']}%");
            }
            if(!empty($param['billing_cycle'])){
                $query->where('h.billing_cycle_name', 'like', "%{$param['billing_cycle']}%");
            }
            if(!empty($param['status'])){
                if($app=='home' && $param['status']=='Pending'){
                    $query->whereIn('h.status', ['Pending', 'Failed']);
                }else{
                    $query->where('h.status', $param['status']);
                }
            }
            if(!empty($param['host_id'])){
                $query->whereIn('h.id', (int)$param['host_id']);
            }
            if(!empty($param['start_time']) && !empty($param['end_time'])){
                $query->where('h.due_time', '>=', $param['start_time'])->where('h.due_time', '<=', $param['end_time']);
            }
        };

        $count = $this->alias('h')
            ->field('h.id')
            ->leftjoin('product p', 'p.id=h.product_id')
            ->leftjoin('client c', 'c.id=h.client_id')
            ->leftJoin('server s','s.id=h.server_id')
            ->where($where)
            ->count();
        $hosts = $this->alias('h')
            ->field('h.id,h.client_id,c.username client_name,c.email,c.phone_code,c.phone,c.company,h.product_id,p.name product_name,h.name,h.create_time,h.active_time,h.due_time,h.first_payment_amount,h.billing_cycle,h.billing_cycle_name,h.status,o.pay_time,h.renew_amount')
            ->leftjoin('product p', 'p.id=h.product_id')
            ->leftjoin('client c', 'c.id=h.client_id')
            ->leftjoin('order o', 'o.id=h.order_id')
            ->leftJoin('server s','s.id=h.server_id')
            ->where($where)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();
        foreach ($hosts as $key => $host) {
            $hosts[$key]['first_payment_amount'] = amount_format($host['first_payment_amount']); // 处理金额格式
            $hosts[$key]['billing_cycle'] = $host['billing_cycle']!='onetime' ? $host['billing_cycle_name'] : '';

            // 前台接口去除字段
            if($app=='home'){
                $hosts[$key]['status'] = $host['status']=='Failed' ? 'Pending' : $host['status'];
                unset($hosts[$key]['client_id'], $hosts[$key]['client_name'], $hosts[$key]['email'], $hosts[$key]['phone_code'], $hosts[$key]['phone'], $hosts[$key]['company']);
            }

            unset($hosts[$key]['billing_cycle_name'], $hosts[$key]['create_time'], $hosts[$key]['pay_time']);
        }

        return ['list' => $hosts, 'count' => $count];
    }

    /**
     * 时间 2022-10-13
     * @title 会员中心首页产品列表
     * @desc 会员中心首页产品列表
     * @author theworld
     * @version v1
     * @param int param.page - 页数
     * @return array list - 产品
     * @return int list[].id - 产品ID 
     * @return int list[].product_id - 商品ID 
     * @return string list[].product_name - 商品名称 
     * @return string list[].name - 标识 
     * @return int list[].due_time - 到期时间
     * @return string list[].status - 状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败
     * @return string list[].type - 类型 
     * @return int count - 产品总数
     */
    public function indexHostList($param)
    {
        $param['client_id'] = get_client_id();
        if(empty($param['client_id'])){
            return ['list' => [], 'count' => 0];
        }
        $res = hook('get_client_host_id', ['client_id' => get_client_id(false)]);
        $res = array_values(array_filter($res ?? []));
        foreach ($res as $key => $value) {
            if(isset($value['status']) && $value['status']==200){
                $hostId = $value['data']['host'];
                if(empty($hostId)){
                    return ['list' => [], 'count' => 0];
                }
            }
        }
        $param['host_id'] = $hostId ?? [];

        $count = $this->alias('h')
            ->field('h.id')
            ->leftjoin('product p', 'p.id=h.product_id')
            ->leftjoin('client c', 'c.id=h.client_id')
            ->where(function ($query) use($param) {
                $query->whereIn('h.status', ['Pending', 'Active', 'Suspended', 'Failed']);
                if(!empty($param['client_id'])){
                    $query->where('h.client_id', (int)$param['client_id']);
                }
                if(!empty($param['host_id'])){
                    $query->whereIn('h.id', $param['host_id']);
                }
            })
            ->count();
        $hosts = $this->alias('h')
            ->field('h.id,h.product_id,p.name product_name,h.name,h.due_time,h.status,s.module,ss.module module1')
            ->leftjoin('product p', 'p.id=h.product_id')
            ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id')
            ->leftjoin('server_group sg','p.type=\'server_group\' AND p.rel_id=sg.id')
            ->leftjoin('server ss','ss.server_group_id=sg.id')
            ->where(function ($query) use($param) {
                $query->whereIn('h.status', ['Pending', 'Active', 'Suspended', 'Failed']);
                if(!empty($param['client_id'])){
                    $query->where('h.client_id', (int)$param['client_id']);
                }
                if(!empty($param['host_id'])){
                    $query->whereIn('h.id', $param['host_id']);
                }
            })
            ->limit(10)
            ->page($param['page'])
            ->orderRaw('h.due_time>0 desc')
            ->order('h.due_time', 'asc')
            ->select()
            ->toArray();

        $ModuleLogic = new ModuleLogic();

        $moduleList = $ModuleLogic->getModuleList();
        $moduleList = array_column($moduleList, 'display_name', 'name');

        foreach ($hosts as $key => $host) {
            $hosts[$key]['status'] = $host['status']=='Failed' ? 'Pending' : $host['status'];
            $host['module'] = !empty($host['module']) ? $host['module'] : $host['module1'];
            $hosts[$key]['type'] = $moduleList[$host['module']] ?? $host['module'];
            unset($hosts[$key]['module'], $hosts[$key]['module1']);
        }

        return ['list' => $hosts, 'count' => $count];
    }

    /**
     * 时间 2022-05-13
     * @title 产品详情
     * @desc 产品详情
     * @author theworld
     * @version v1
     * @param int id - 产品ID required
     * @return int id - 产品ID 
     * @return int product_id - 商品ID 
     * @return int server_id - 接口ID 
     * @return string name - 标识 
     * @return string notes - 备注 
     * @return string first_payment_amount - 订购金额
     * @return string renew_amount - 续费金额
     * @return string billing_cycle - 计费周期
     * @return string billing_cycle_name - 模块计费周期名称
     * @return string billing_cycle_time - 模块计费周期时间,秒
     * @return int active_time - 开通时间 
     * @return int due_time - 到期时间
     * @return string status - 状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败
     * @return string suspend_type - 暂停类型,overdue到期暂停,overtraffic超流暂停,certification_not_complete实名未完成,other其他
     * @return string suspend_reason - 暂停原因
     * @return string product_name - 商品名称
     */
    public function indexHost($id)
    {
        // 获取当前应用
        $app = app('http')->getName();

        $host = $this->field('id,order_id,product_id,server_id,name,notes,first_payment_amount,renew_amount,billing_cycle,billing_cycle_name,billing_cycle_time,active_time,due_time,status,client_id,suspend_type,suspend_reason,client_notes')->find($id);
        if (empty($host)){
            return (object)[]; // 转换为对象
        }

        $product = ProductModel::find($host['product_id']);
        $upstreamHost = UpstreamHostModel::where('host_id', $host['id'])->find();

        // 产品的用户ID和前台用户不一致时返回空对象
        if($app=='home'){
            $client_id = get_client_id();
            if($host['client_id']!=$client_id || $host['status']=='Cancelled'){
                return (object)[]; // 转换为对象
            }
            $host['notes'] = $host['client_notes'];
            unset($host['server_id'], $host['client_notes']);

            $host['status'] = $host['status'] != 'Failed' ? $host['status'] : 'Pending';
        }

        $host['first_payment_amount'] = amount_format($host['first_payment_amount']); 
        $host['renew_amount'] = amount_format($host['renew_amount']);
        $host['product_name'] = $product['name'] ?? '';
        $host['agent'] = !empty($upstreamHost) ? 1 : 0;
        unset($host['client_id']);
        
        return $host;
    }

    /**
     * 时间 2022-07-22
     * @title 搜索产品
     * @desc 搜索产品
     * @author theworld
     * @version v1
     * @param string keywords - 关键字,搜索范围:产品ID,标识,商品名称
     * @return array list - 产品
     * @return int list[].id - 产品ID 
     * @return string list[].name - 标识
     * @return string list[].product_name - 商品名称
     * @return int list[].client_id - 用户ID
     */
    public function searchHost($keywords)
    {   
        // 获取当前应用
        $app = app('http')->getName();

        //全局搜索
        $hosts = $this->alias('h')
            ->field('h.id,h.name,p.name product_name,h.client_id')
            ->leftjoin('product p', 'p.id=h.product_id')
            ->where(function ($query) use($keywords, $app) {
                if($app=='home'){
                    $clientId = get_client_id();
                    $query->where('h.client_id', $clientId)->where('h.status', '<>', 'Cancelled');
                }
                if(!empty($keywords)){
                    $query->where('h.id|h.name|p.name', 'like', "%{$keywords}%");
                }
            })
            ->select()
            ->toArray();
        if($app=='home'){
            foreach ($hosts as $key => $value) {
                unset($hosts[$key]['client_id']);
            }
        }
        return ['list' => $hosts];
    }

    /**
     * 时间 2022-05-13
     * @title 修改产品
     * @desc 修改产品
     * @author theworld
     * @version v1
     * @param int param.id - 产品ID required
     * @param int param.product_id - 商品ID required
     * @param int param.server_id - 接口
     * @param string param.name - 标识
     * @param string param.notes - 备注
     * @param float param.first_payment_amount - 订购金额
     * @param float param.renew_amount - 续费金额
     * @param string param.billing_cycle - 计费周期 required
     * @param int param.active_time - 开通时间
     * @param int param.due_time - 到期时间
     * @param int param.status - 状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateHost($param)
    {
        // 验证产品ID
        $host = $this->find($param['id']);
        if (empty($host)){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }

        // 验证商品ID
        $product = ProductModel::find($param['product_id']);
        if (empty($product)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        $param['server_id'] = $param['server_id'] ?? 0;
        $param['name'] = $param['name'] ?? '';
        $param['notes'] = $param['notes'] ?? '';
        $param['first_payment_amount'] = $param['first_payment_amount'] ?? 0;
        $param['renew_amount'] = $param['renew_amount'] ?? 0;
        $param['active_time'] = isset($param['active_time']) ? strtotime($param['active_time']) : 0;
        $param['due_time'] = isset($param['due_time']) ? strtotime($param['due_time']) : 0;
        // 计费周期为一次性和免费的产品没有到期时间和续费金额,其他的使用传入的到期时间和续费金额
        if($param['billing_cycle']=='onetime'){
            $param['due_time'] = 0;
            $param['renew_amount'] = 0;
        }else if($param['billing_cycle']=='free'){
            $param['renew_amount'] = 0;
        }

        # 日志详情
        $description = [];
        if ($host['product_id'] != $param['product_id']){
            $oldProduct = ProductModel::find($host['product_id']);
            $oldProduct = $oldProduct['name'] ?? '';
            $newProduct = ProductModel::find($param['product_id']);
            $newProduct = $newProduct['name'] ?? '';

            $description[] = lang('old_to_new',['{old}'=>lang('host_product').$oldProduct, '{new}'=>$newProduct]);
        }
        if ($host['server_id'] != $param['server_id']){
            $oldServer = ServerModel::find($host['server_id']);
            $oldServer = $oldServer['name'] ?? '';
            $newServer = ServerModel::find($param['server_id']);
            $newServer = $newServer['name'] ?? '';

            $description[] = lang('old_to_new',['{old}'=>lang('host_server').$oldServer, '{new}'=>$newServer]);
        }
        if ($host['name'] != $param['name']){
            $description[] = lang('old_to_new',['{old}'=>lang('host_name').$host['name'], '{new}'=>$param['name']]);
        }
        if ($host['notes'] != $param['notes']){
            $description[] = lang('old_to_new',['{old}'=>lang('host_notes').$host['notes'], '{new}'=>$param['notes']]);
        }
        if ($host['first_payment_amount'] != $param['first_payment_amount']){
            $description[] = lang('old_to_new',['{old}'=>lang('host_first_payment_amount').$host['first_payment_amount'], '{new}'=>$param['first_payment_amount']]);
        }
        if ($host['renew_amount'] != $param['renew_amount']){
            $description[] = lang('old_to_new',['{old}'=>lang('host_renew_amount').$host['renew_amount'], '{new}'=>$param['renew_amount']]);
        }
        if ($host['billing_cycle'] != $param['billing_cycle']){
            $description[] = lang('old_to_new',['{old}'=>lang('host_billing_cycle').lang('host_billing_cycle_'.$host['billing_cycle']), '{new}'=>lang('host_billing_cycle_'.$param['billing_cycle'])]);
        }
        if ($host['active_time'] != $param['active_time']){
            $description[] = lang('old_to_new',['{old}'=>lang('host_active_time').date("Y-m-d H:i:s", $host['active_time']), '{new}'=>$param['active_time']]);
        }
        if ($host['due_time'] != $param['due_time']){
            $description[] = lang('old_to_new',['{old}'=>lang('host_due_time').date("Y-m-d H:i:s", $host['due_time']), '{new}'=>date("Y-m-d H:i:s", $param['due_time'])]);
        }
        if ($host['status'] != $param['status']){
            $description[] = lang('old_to_new',['{old}'=>lang('host_status').lang('host_status_'.$host['status']), '{new}'=>lang('host_status_'.$param['status'])]);
        }
        $description = implode(',', $description);

        $this->startTrans();
        try {
            
            $this->update([
                'product_id' => $param['product_id'],
                'server_id' => $param['server_id'],
                'name' => $param['name'],
                'notes' => $param['notes'],
                'first_payment_amount' => $param['first_payment_amount'],
                'renew_amount' => $param['renew_amount'],
                'billing_cycle' => $param['billing_cycle'],
                'active_time' => $param['active_time'],
                'due_time' => $param['due_time'],
                'status' => $param['status'],
                'update_time' => time()
            ], ['id' => $param['id']]);

            upstream_sync_host($param['id'], 'update_host');

            if(!empty($description)) active_log(lang('admin_modify_host', ['{admin}'=>request()->admin_name, '{host}'=>'host#'.$host->id.'#'.$param['name'].'#', '{description}'=>$description]), 'host', $host->id);

            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail').$e->getMessage()];
        }

        $upstreamProduct = UpstreamProductModel::where('product_id', $param['product_id'])->find();
        if($upstreamProduct){
            // $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            // $result = $ResModuleLogic->adminField($host);
        }else{
            $ModuleLogic = new ModuleLogic();
            $result = $ModuleLogic->hostUpdate($this->find($host->id), $param['customfield']['module_admin_field'] ?? []);
            if(isset($result['status']) && $result['status'] == 400){
                return $result;
            }
        }


        hook('after_host_edit',['id'=>$param['id'],'customfield'=>$param['customfield']??[]]);

        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2022-05-13
     * @title 删除产品
     * @desc 删除产品
     * @author theworld
     * @version v1
     * @param int id - 产品ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteHost($param)
    {
        $id = $param['id']??0;
        // 验证产品ID
        $host = $this->find($id);
        if (empty($host)){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        if($host['status']=='Pending'){
            return ['status'=>400, 'msg'=>lang('host_opening_cannot_delete')];
        }
        $this->startTrans();
        try {
            $client = ClientModel::find($host->client_id);
            if(empty($client)){
                $clientName = '#'.$host->client_id;
            }else{
                $clientName = 'client#'.$client->id.'#'.$client->username.'#';
            }
            # 记录日志
            active_log(lang('admin_delete_user_host', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{host}'=>$host['name']]), 'host', $host->id);

            $order = OrderModel::find($host['order_id']);
            if(!empty($order) && $order['status']=='Unpaid'){
                OrderItemModel::where('host_id', $host['id'])->delete();
                $count = OrderItemModel::where('order_id', $order['id'])->count();
                if($count==0){
                    OrderModel::destroy($host['order_id']);
                }else{
                    $amount = OrderItemModel::where('order_id', $order['id'])->sum('amount');
                    OrderModel::update(['amount'=>$amount],['id'=>$host['order_id']]);
                }
            }
            UpstreamHostModel::where('host_id', $id)->delete();

            upstream_sync_host($id, 'delete_host');

            $this->destroy($id);
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('delete_fail')];
        }

        hook('after_host_delete',['id'=>$id]);

        return ['status' => 200, 'msg' => lang('delete_success')];
    }

    /**
     * 时间 2023-01-30
     * @title 批量删除产品
     * @desc 批量删除产品
     * @author theworld
     * @version v1
     * @param array id - 产品ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function batchDeleteHost($param)
    {
        $id = $param['id']??[];
        // 验证产品ID
        $host = $this->whereIn('id', $id)->select()->toArray();
        if (empty($host)){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        if(count($host)!=count($id)){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }

        $client = ClientModel::whereIn('id', array_column($host, 'client_id'))->select()->toArray();
        $clientArr = [];
        foreach ($client as $key => $value) {
            $clientArr[$value['id']] = $value;
        }
        
        $this->startTrans();
        try {
            foreach ($host as $key => $value) {
                upstream_sync_host($value['id'], 'delete_host');

                UpstreamHostModel::where('host_id', $value['id'])->delete();

                $this->destroy($value['id']);

                hook('after_host_delete',['id'=>$value['id']]);

                $client = $clientArr[$value['client_id']] ?? [];
                if(empty($client)){
                    $clientName = '#'.$value['client_id'];
                }else{
                    $clientName = 'client#'.$client['id'].'#'.$client['username'].'#';
                }

                # 记录日志
                active_log(lang('admin_batch_delete_user_host', ['{admin}'=>request()->admin_name, '{client}'=>$clientName, '{host}'=>$value['name']]), 'host', $value['id']);
            }
            
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('delete_fail')];
        }

        return ['status' => 200, 'msg' => lang('delete_success')];
    }

    /**
     * 时间 2022-05-26
     * @title 获取通用模块参数
     * @desc 获取通用模块参数
     * @author hh
     * @version v1
     * @return  array
     */
    public function getModuleParams()
    {
        $result = [];
        $result['host'] = $this;
        $result['client'] = ClientModel::find($this->getAttr('client_id'));
        $result['product'] = ProductModel::find($this->getAttr('product_id'));
        $result['server'] = ServerModel::find($this->getAttr('server_id'));
        if(!empty($result['server'])){
            $result['server']['password'] = aes_password_decode($result['server']['password']);
        }
        // TODO 获取产品关联的config_option
        $result['config_option'] = [];
        return $result;
    }

    /**
     * 时间 2022-05-28
     * @title 获取当前产品关联模块类型(需要先实例化)
     * @desc 获取当前产品关联模块类型
     * @author hh
     * @version v1
     * @return  string
     */
    public function getModule()
    {
        $server = ServerModel::find($this->getAttr('server_id'));
        if(!empty($server)){
            $module = $server['module'];
        }else{
            // 获取商品的模块
            $ProductModel = ProductModel::findOrEmpty($this->getAttr('product_id'));
            $module = $ProductModel->getModule();
        }
        return $module;
    }

    /**
     * 时间 2022-05-28
     * @title 产品开通
     * @desc 产品开通
     * @author hh
     * @version v1
     * @param int id - 产品ID
     * @return int status - 状态码,200=成功,400=失败
     * @return string msg - 提示信息
     */
    public function createAccount($id)
    {
        $host = $this->find($id);
        if(empty($host)){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        if($host['status'] == 'Active'){
            return ['status'=>400, 'msg'=>lang('host_is_active')];
        }
        if($host['status'] == 'Suspended'){
            return ['status'=>400, 'msg'=>lang('host_is_suspended')];
        }


        hook('before_host_create',['id'=>$id]);

        if($host['billing_cycle']=='onetime'){
            $due_time = 0;
        }else if($host['billing_cycle']=='free' && $host['billing_cycle_time']==0){
            $due_time = 0;
        }else{
            $due_time = time() + $host['billing_cycle_time'];
        }
        $this->update([
            'active_time' => time(),
            'due_time' => $due_time,
            'update_time' => time(),
        ], ['id'=>$id]);

        $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();
        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $res = $ResModuleLogic->createAccount($host);
        }else{
            $ModuleLogic = new ModuleLogic();
            $res = $ModuleLogic->createAccount($host);
        }
        if($res['status'] == 200){

            hook('after_host_create_success',['id'=>$id]);

            /*if($host['billing_cycle']=='onetime'){
                $due_time = 0;
            }else if($host['billing_cycle']=='free' && $host['billing_cycle_time']==0){
                $due_time = 0;
            }else{
                $due_time = time() + $host['billing_cycle_time'];
            }*/
            $this->update([
                'status'      => 'Active',
                /*'active_time' => time(),
                'due_time' => $due_time,*/
                'update_time' => time(),
            ], ['id'=>$id]);

            $host_active = (new NoticeSettingModel())->indexSetting('host_active');
            if($host_active['sms_enable']==1){
                add_task([
                    'type' => 'email',
                    'description' => '产品开通成功,发送邮件',
                    'task_data' => [
                        'name'=>'host_active',//发送动作名称
                        'host_id'=>$id,//主机ID
                    ],      
                ]);
            }
            if($host_active['email_enable']==1){
               add_task([
                    'type' => 'sms',
                    'description' => '产品开通成功,发送短信',
                    'task_data' => [
                        'name'=>'host_active',//发送动作名称
                        'host_id'=>$id,//主机ID
                    ],      
                ]); 
            }

            $description = lang('log_module_create_account_success', [
                '{host}'=> 'host#'.$host->id.'#'.$host['name'].'#',
            ]);
        }else{
            hook('after_host_create_fail',['id'=>$id]);

            $this->update([
                'status'      => 'Failed',
                'update_time' => time(),
            ], ['id'=>$id]);

            $description = lang('log_module_create_account_failed', [
                '{host}'=>'host#'.$host->id.'#'.$host['name'].'#',
                '{reason}'=>$res['msg'] ?? '',
            ]);
        }

        upstream_sync_host($id, 'module_create');
        active_log($description, 'host', $host->id);
        return $res;
    }

    /**
     * 时间 2022-05-28
     * @title 产品暂停
     * @desc 产品暂停
     * @author hh
     * @version v1
     * @param int id - 产品ID require
     * @param string param.suspend_type overdue 暂停类型(overdue=到期暂停,overtraffic=超流暂停,certification_not_complete=实名未完成,other=其他,downstream下游暂停)
     * @param string param.suspend_reason - 暂停原因
     * @return int status - 状态码,200=成功,400=失败
     * @return string msg - 提示信息
     */
    public function suspendAccount($param)
    {
        $id = (int)$param['id'];
        $param['suspend_reason'] = $param['suspend_reason'] ?? '';

        $host = $this->find($id);
        if(empty($host)){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        if($host['status'] == 'Suspended'){
            // 状态先200,这样如果上下游不会失败
            return ['status'=>200, 'msg'=>lang('host_is_suspended')];
        }
        if($host['status'] != 'Active'){
            return ['status'=>400, 'msg'=>lang('host_is_not_active_cannot_suspend')];
        }
        $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();

        hook('before_host_suspend',['id'=>$id]);

        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $res = $ResModuleLogic->suspendAccount($host, $param);
        }else{
            $ModuleLogic = new ModuleLogic();
            $res = $ModuleLogic->suspendAccount($host, $param);
        }

        if($res['status'] == 200){

            hook('after_host_suspend_success',['id'=>$id]);

            $this->update([
                'status'         => 'Suspended',
                'suspend_type'   => $param['suspend_type'] ?? 'overdue',
                'suspend_reason' => $param['suspend_reason'],
                'suspend_time'   => time(),
                'update_time'    => time(),
            ], ['id'=>$id]);
			add_task([
				'type' => 'email',
				'description' => '产品暂停通知,发送邮件',
				'task_data' => [
					'name'=>'host_suspend',//发送动作名称
					'host_id'=>$id,//主机ID
				],		
			]);
			add_task([
				'type' => 'sms',
				'description' => '产品暂停通知,发送短信',
				'task_data' => [
					'name'=>'host_suspend',//发送动作名称
					'host_id'=>$id,//主机ID
				],		
			]);

            $suspendType = [
                'overdue'=>'到期暂停',
                'overtraffic'=>'超流暂停',
                'certification_not_complete'=>'实名未完成',
                'other'=>'其他',
            ];

            upstream_sync_host($id, 'module_suspend');

            $description = lang('log_module_suspend_account_success', [
                '{host}'=>'host#'.$host->id.'#'.$host['name'].'#',
                '{type}'=>$suspendType[ $param['suspend_type'] ] ?? $suspendType['overdue'],
                '{reason}'=>$param['suspend_reason'],
            ]);

        }else{
            hook('after_host_suspend_fail',['id'=>$id,'fail_reason'=>$res['msg']??'']);

            $description = lang('log_module_suspend_account_failed', [
                '{host}'=>'host#'.$host->id.'#'.$host['name'].'#',
                '{reason}'=>$res['msg'] ?? '',
            ]);
        }
        active_log($description, 'host', $host->id);
        return $res;
    }

    /**
     * 时间 2022-05-28
     * @title 产品解除暂停
     * @desc 产品解除暂停
     * @author hh
     * @version v1
     * @param int id - 产品ID
     * @return int status - 状态码,200=成功,400=失败
     * @return string msg - 提示信息
     */
    public function unsuspendAccount($id)
    {
        $host = $this->find($id);
        if(empty($host)){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        if($host['status'] == 'Active'){
            // 状态先200,这样如果上下游不会失败
            return ['status'=>200, 'msg'=>lang('host_is_already_unsuspend')];
        }
        if($host['status'] != 'Active' && $host['status'] != 'Suspended'){
            return ['status'=>400, 'msg'=>lang('host_status_not_need_unsuspend')];
        }
        if($host['suspend_type'] == 'upstream'){
            return ['status'=>400, 'msg'=>lang('不可解除上游发起的暂停')];
        }

        $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();

        hook('before_host_unsuspend',['id'=>$id]);

        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $res = $ResModuleLogic->unsuspendAccount($host);
        }else{
            $ModuleLogic = new ModuleLogic();
            $res = $ModuleLogic->unsuspendAccount($host);
        }
        if($res['status'] == 200){

            hook('after_host_unsuspend_success',['id'=>$id]);

            $this->update([
                'status'         => 'Active',
                'suspend_reason' => '',
                'suspend_time'   => 0,
                'update_time'    => time(),
            ], ['id'=>$id]);
			if(configuration('cron_due_unsuspend_swhitch')==1){
				add_task([
					'type' => 'email',
					'description' => '产品解除暂停通知,发送邮件',
					'task_data' => [
						'name'=>'host_unsuspend',//发送动作名称
						'host_id'=>$id,//主机ID
					],		
				]);
				add_task([
					'type' => 'sms',
					'description' => '产品解除暂停通知,发送短信',
					'task_data' => [
						'name'=>'host_unsuspend',//发送动作名称
						'host_id'=>$id,//主机ID
					],		
				]);
			}
            upstream_sync_host($id, 'module_unsuspend');

            $description = lang('log_module_unsuspend_account_success', [
                '{host}'=>'host#'.$host->id.'#'.$host['name'].'#',
            ]);
        }else{
            hook('after_host_unsuspend_fail',['id'=>$id,'fail_reason'=>$res['msg']??'']);

            $description = lang('log_module_unsuspend_account_failed', [
                '{host}'=>'host#'.$host->id.'#'.$host['name'].'#',
                '{reason}'=>$res['msg'] ?? '',
            ]);
        }
        active_log($description, 'host', $host->id);
        return $res;
    }

    /**
     * 时间 2022-05-28
     * @title 产品删除
     * @desc 产品删除
     * @author hh
     * @version v1
     * @param int id - 产品ID
     * @return int status - 状态码,200=成功,400=失败
     * @return string msg - 提示信息
     */
    public function terminateAccount($id)
    {
        $host = $this->find($id);
        if(empty($host)){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();

        hook('before_host_terminate',['id'=>$id]);

        // 暂不判断状态,所有状态应该都能删除
        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $res = $ResModuleLogic->terminateAccount($host);
        }else{
            $ModuleLogic = new ModuleLogic();
            $res = $ModuleLogic->terminateAccount($host);
        }
        if($res['status'] == 200){

            $this->update([
                'status'           => 'Deleted',
                'termination_time' => time(),
                'update_time'      => time(),
            ], ['id'=>$id]);

            hook('after_host_terminate_success',['id'=>$id]);

			add_task([
				'type' => 'email',
				'description' => '产品删除通知,发送邮件',
				'task_data' => [
					'name'=>'host_terminate',//发送动作名称
					'host_id'=>$id,//主机ID
				],		
			]);
			add_task([
				'type' => 'sms',
				'description' => '产品删除通知,发送短信',
				'task_data' => [
					'name'=>'host_terminate',//发送动作名称
					'host_id'=>$id,//主机ID
				],		
			]);

            upstream_sync_host($id, 'module_terminate');

            $description = lang('log_module_terminate_account_success', [
                '{host}'=>'host#'.$host->id.'#'.$host['name'].'#',
            ]);
        }else{
            hook('after_host_terminate_fail',['id'=>$id,'fail_reason'=>$res['msg']??'']);

            $description = lang('log_module_terminate_account_failed', [
                '{host}'=>'host#'.$host->id.'#'.$host['name'].'#',
                '{reason}'=>$res['msg'] ?? '',
            ]);
        }
        active_log($description, 'host', $host->id);
        return $res;
    }

    /**
     * 时间 2022-05-28
     * @title 后台产品内页模块输出
     * @desc 后台产品内页模块输出
     * @author hh
     * @version v1
     * @param int id - 产品ID
     * @return int status - 状态码,200=成功,400=失败
     * @return string msg - 提示信息
     * @return string data.content - 内页模块输出
     */
    public function adminArea($id)
    {
        $host = $this->find($id);
        if(empty($host)){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();

        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $content = $ResModuleLogic->adminArea($host);
        }else{
            $ModuleLogic = new ModuleLogic();
            $content = $ModuleLogic->adminArea($host);
        }
        
        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => [
                'content' => $content,
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-10-13
     * @title 自定义导航产品列表
     * @desc 自定义导航产品列表
     * @author hh
     * @version v1
     * @param int id - 导航ID
     * @return int status - 状态码,200=成功,400=失败
     * @return string msg - 提示信息
     * @return string data.content - 列表页模块输出
     */
    public function menuHostList($id)
    {
        $menu = MenuModel::find($id);
        if(empty($menu) || empty($menu['module'])){
            return ['status'=>400, 'msg'=>lang('fail_message')];
        }
        $param['product_id'] = json_decode($menu['product_id'], true);

        /*$upstreamProduct = UpstreamProductModel::where('product_id', $param['product_id'][0] ?? 0)->find();*/

        if($menu['menu_type']=='res_module'){
            $ResModuleLogic = new ResModuleLogic();
            $content = $ResModuleLogic->hostList($menu['module'], $param);
        }else{
            $ModuleLogic = new ModuleLogic();
            $content = $ModuleLogic->hostList($menu['module'], $param);
        }
        
        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => [
                'content' => $content,
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-05-28
     * @title 前台产品内页模块输出
     * @desc 前台产品内页模块输出
     * @author hh
     * @version v1
     * @param int id - 产品ID
     * @return int status - 状态码,200=成功,400=失败
     * @return string msg - 提示信息
     * @return string data.content - 内页模块输出
     */
    public function clientArea($id)
    {
        $host = $this->find($id);
        if(empty($host) || $host['client_id'] != get_client_id()){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        $res = hook('get_client_host_id', ['client_id' => get_client_id(true)]);
        $res = array_values(array_filter($res ?? []));
        foreach ($res as $key => $value) {
            if(isset($value['status']) && $value['status']==200){
                $hostId = $value['data']['host'];
            }
        }
        if(isset($hostId) && !in_array($id, $hostId)){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();
        
        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $content = $ResModuleLogic->clientArea($host);
        }else{
            $ModuleLogic = new ModuleLogic();
            $content = $ModuleLogic->clientArea($host);
        }
        
        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => [
                'content' => $content,
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-05-28
     * @title 后台产品升降级输出
     * @desc 后台产品升降级输出
     * @author hh
     * @version v1
     * @param int id - 产品ID
     * @return int status - 状态码,200=成功,400=失败
     * @return string msg - 提示信息
     * @return string data.content - 内页模块输出
     */
    public function adminChangeConfigOption($id)
    {
        $host = $this->find($id);
        if(empty($host)){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();

        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $content = $ResModuleLogic->adminChangeConfigOption($host);
        }else{
            $ModuleLogic = new ModuleLogic();
            $content = $ModuleLogic->adminChangeConfigOption($host);
        }
        
        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => [
                'content' => $content,
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-05-28
     * @title 前台产品升降级输出
     * @desc 前台产品升降级输出
     * @author hh
     * @version v1
     * @param int id - 产品ID
     * @return int status - 状态码,200=成功,400=失败
     * @return string msg - 提示信息
     * @return string data.content - 内页模块输出
     */
    public function clientChangeConfigOption($id)
    {
        $host = $this->find($id);
        if(empty($host) || get_client_id() != $host['client_id']){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        
        $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();

        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $content = $ResModuleLogic->clientChangeConfigOption($host);
        }else{
            $ModuleLogic = new ModuleLogic();
            $content = $ModuleLogic->clientChangeConfigOption($host);
        }
        
        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => [
                'content' => $content,
            ]
        ];
        return $result;
    }

    /**
     * 时间 2022-05-31
     * @title 升降级配置项计算价格 
     * @desc 升降级配置项计算价格
     * @author hh
     * @version v1
     * @param   int param.id - 产品ID required
     * @param   mixed param.config_options - 自定义配置项
     * @return  int status - 状态码,200=成功,400=失败
     * @return  array data - 计算后数据
     * @return  float data.price - 配置项金额
     * @return  string data.billing_cycle - 周期名称
     * @return  int data.duration - 周期时长(秒)
     * @return  string data.description - 子项描述
     */
    public function changeConfigOptionCalculatePrice($param){
        $host = $this->find((int)$param['id']);
        if(empty($host)){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        $param['config_options'] = $param['config_options'] ?? [];

        $app = app('http')->getName();
        if($app=='home'){
            $clientId = get_client_id();
            if(empty($clientId) || $clientId != $host['client_id']){
                return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
            }
        }
        $ModuleLogic = new ModuleLogic();
        $result = $ModuleLogic->changeConfigOptionCalculatePrice($host, $param['config_options']);
        return $result;
    }
    /**
     * 时间 2022-05-28
     * @title 升降级
     * @desc 升降级
     * @author hh
     * @version v1
     * @param int id - upgrade表ID
     * @return int status - 状态码,200=成功,400=失败
     * @return string msg - 提示信息
     */
    public function upgradeAccount($id)
    {
        $UpgradeModel = new UpgradeModel();
        $UpgradeModel->startTrans();
        $upgrade = $UpgradeModel->where('id',$id)->lock(true)->find();
        if (empty($upgrade) || $upgrade['status']=='Completed'){
            return false;
        }
        $host = $this->find($upgrade['host_id']);
        $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();
        # 升降级
        if($upgrade['type']=='product'){
            // 获取接口
            /*$product = ProductModel::find($upgrade['rel_id']);
            if($product['type']=='server_group'){
                $server = ServerModel::where('server_group_id', $product['rel_id'])->where('status', 1)->find();
                $serverId = $server['id'] ?? 0;
            }else{
                $serverId = $product['rel_id'];
            }

            $host = $this->find($upgrade['host_id']);
            // wyh 20210109 改 一次性/免费可升级后
            if($host['billing_cycle']=='onetime'){
                if ($product['pay_type']=='onetime'){
                    $hostDueTime = 0;
                }elseif ($product['pay_type']=='free' && $upgrade['billing_cycle_time']==0){
                    $hostDueTime = 0;
                }else{
                    $hostDueTime = time()+$upgrade['billing_cycle_time'];
                }
            }else if($host['billing_cycle']=='free' && $host['billing_cycle_time']==0){
                if ($product['pay_type']=='onetime'){
                    $hostDueTime = 0;
                }elseif ($product['pay_type']=='free' && $upgrade['billing_cycle_time']==0){
                    $hostDueTime = 0;
                }else{
                    $hostDueTime = time()+$upgrade['billing_cycle_time'];
                }
            }else{
                if ($product['pay_type']=='onetime'){
                    $hostDueTime = 0;
                }elseif ($product['pay_type']=='free' && $upgrade['billing_cycle_time']==0){
                    $hostDueTime = 0;
                }else{ # 周期到周期,不变更
                    $hostDueTime = $host['due_time'];
                }
            }

            $this->update([
                'product_id' => $upgrade['rel_id'],
                'server_id' => $serverId,
                'first_payment_amount' => $upgrade['price'],
                'renew_amount' => ($product['pay_type']=='recurring_postpaid' || $product['pay_type']=='recurring_prepayment') ? $upgrade['renew_price'] : 0,
                'billing_cycle' => $product['pay_type'],
                'billing_cycle_name' => $upgrade['billing_cycle_name'],
                'billing_cycle_time' => $upgrade['billing_cycle_time'],
                'due_time' => $hostDueTime,
            ],['id' => $upgrade['host_id']]);*/

            if($upstreamProduct){
                $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                $ResModuleLogic->changeProduct($host, json_decode($upgrade['data']));
            }else{
                $ModuleLogic = new ModuleLogic();
                $ModuleLogic->changeProduct($host, json_decode($upgrade['data'], true));
            }
        }else if($upgrade['type']=='config_option'){
            /*$host = $this->find($upgrade['host_id']);
            $this->update([
                'first_payment_amount' => $upgrade['price'],
                'renew_amount' => ($host['billing_cycle']=='recurring_postpaid' || $host['billing_cycle']=='recurring_prepayment') ? $upgrade['renew_price'] : 0,
            ],['id' => $upgrade['host_id']]);*/
            if($upstreamProduct){
                $ResModuleLogic = new ResModuleLogic($upstreamProduct);
                $ResModuleLogic->changePackage($host, json_decode($upgrade['data'], true));
            }else{
                $ModuleLogic = new ModuleLogic();
                $ModuleLogic->changePackage($host, json_decode($upgrade['data'], true));
            }
        }

        $ProductModel = new ProductModel();
        $product = $ProductModel->find($host['product_id']);

        # 发送邮件短信
		add_task([
			'type' => 'email',
			'description' => '产品升降级,发送邮件',
			'task_data' => [
				'name'=>'host_upgrad',//发送动作名称
				'host_id'=>$upgrade['host_id'],//主机ID
                'template_param'=>[
                    'product_name' => $product['name']??''
                ],
			],		
		]);
		add_task([
			'type' => 'sms',
			'description' => '产品升降级,发送短信',
			'task_data' => [
				'name'=>'host_upgrad',//发送动作名称
				'host_id'=>$upgrade['host_id'],//主机ID
                'template_param'=>[
                    'product_name' => $product['name']??''
                ],
			],		
		]);
        $upgrade->save([
            'status' => 'Completed',
            'update_time' => time()
        ]);
        $UpgradeModel->commit();
        return ['status'=>200, 'msg'=>lang('success_message')];
    }

    /**
     * 时间 2022-08-11
     * @title 修改产品备注
     * @desc 修改产品
     * @author theworld
     * @version v1
     * @param int param.id - 产品ID required
     * @param string param.notes - 备注
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateHostNotes($param)
    {
        $clientId = get_client_id();
        // 验证产品ID
        $host = $this->find($param['id']);
        if (empty($host)){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }

        if($clientId!=$host['client_id']){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }


        $this->startTrans();
        try {
            $this->update([
                'client_notes' => $param['notes'] ?? '',
                'update_time' => time()
            ], ['id' => $param['id']]);
            
            $this->commit();
        } catch (\Exception $e) {
            // 回滚事务
            $this->rollback();
            return ['status' => 400, 'msg' => lang('update_fail')];
        }
        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2022-10-26
     * @title 获取用户所有产品
     * @desc 获取用户所有产品
     * @author theworld
     * @version v1
     * @return array list - 产品
     * @return int list[].id - 产品ID 
     * @return int list[].product_id - 商品ID 
     * @return string list[].product_name - 商品名称 
     * @return string list[].name - 标识 
     * @return int count - 产品总数
     */
    public function clientHost($param)
    {
        // 获取当前应用
        $app = app('http')->getName();
        if($app=='home'){
            $param['client_id'] = get_client_id();
        }else{
            $param['client_id'] = isset($param['id']) ? intval($param['id']) : 0;
        }
        if(empty($param['client_id'])){
            return ['list' => [], 'count' => 0];
        }

        $count = $this->alias('h')
            ->field('h.id')
            ->leftjoin('product p', 'p.id=h.product_id')
            ->where(function ($query) use($param) {
                $query->where('h.status', '<>', 'Cancelled');
                if(!empty($param['client_id'])){
                    $query->where('h.client_id', (int)$param['client_id']);
                }
            })
            ->count();
        $hosts = $this->alias('h')
            ->field('h.id,h.product_id,p.name product_name,h.name')
            ->leftjoin('product p', 'p.id=h.product_id')
            ->where(function ($query) use($param) {
                $query->where('h.status', '<>', 'Cancelled');
                if(!empty($param['client_id'])){
                    $query->where('h.client_id', (int)$param['client_id']);
                }
            })
            ->select()
            ->toArray();

        return ['list' => $hosts, 'count' => $count];
    }

    /**
     * 时间 2023-01-31
     * @title 模块按钮输出
     * @desc 模块按钮输出
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     * @return  string button[].type - 按钮类型(暂时都是default)
     * @return  string button[].func - 按钮功能(create=开通,suspend=暂停,unsuspend=解除暂停,terminate=删除,renew=续费)
     * @return  string button[].name - 名称
     */
    public function moduleAdminButton($param){
        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => [
                'button' => [],
            ],
        ];
        $host = $this->find($param['id']);
        if(empty($host)){
            return $result;
        }
        $button = [];
        if(in_array($host['status'], ['Unpaid','Pending','Active','Suspended','Deleted','Failed'])){
            $button[] = [
                'type' => 'default',
                'func' => 'create',
                'name' => '开通',
            ];
        }
        if(in_array($host['status'], ['Pending','Active'])){
            $button[] = [
                'type' => 'default',
                'func' => 'suspend',
                'name' => '暂停',
            ];
        }
        if(in_array($host['status'], ['Suspended'])){
            $button[] = [
                'type' => 'default',
                'func' => 'unsuspend',
                'name' => '解除暂停',
            ];
        }
        if(in_array($host['status'], ['Pending','Active','Suspended','Failed'])){
            $button[] = [
                'type' => 'default',
                'func' => 'terminate',
                'name' => '删除',
            ];
        }
        if(in_array($host['status'], ['Active'])){
            // 判断下续费插件
            $renew = PluginModel::where('name', 'IdcsmartRenew')->where('status', 1)->value('id');
            if($renew){
                $button[] = [
                    'type' => 'default',
                    'func' => 'renew',
                    'name' => '续费',
                ];
            }
        }
        $result['data']['button'] = $button;
        return $result;
    }

    public function upstreamSyncHost($id, $action = '')
    {
        $host = $this->find($id);
        if(empty($host)){
            return false;
        }
        if(empty($host['downstream_host_id'])){
            return false;
        }
        $downstreamInfo = json_decode($host['downstream_info'], true) ?? [];
        if(empty($downstreamInfo)){
            return false;
        }
        $api = ApiModel::find($downstreamInfo['api'] ?? 0);
        if(empty($api)){
            return false;
        }
        $api['public_key'] = aes_password_decode($api['public_key']);

        $data = json_encode(['action' => $action, 'host' => $host]);

        $crypto = '';
 
        foreach (str_split($data, 117) as $chunk) {
 
            openssl_public_encrypt($chunk, $encryptData, $api['public_key']);
 
            $crypto .= $encryptData;
        }
 
        $data = base64_encode($crypto);

        $res = curl(rtrim($downstreamInfo['url'],'/').'/console/v1/upstream/sync', ['host_id' => $host['downstream_host_id'], 'data' => $data], 30, 'POST');

        return true;
    }


    /**
     * 时间 2023-04-14
     * @title 产品内页模块输入框输出
     * @desc 产品内页模块输入框输出
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     * @return  string data[].name - 名称
     * @return  string data[].key -  标识
     * @return  string data[].value - 当前值
     */
    public function moduleField($id)
    {
        $host = $this->find($id);
        if(empty($host)){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        $upstreamProduct = UpstreamProductModel::where('product_id', $host['product_id'])->find();

        if($upstreamProduct){
            $ResModuleLogic = new ResModuleLogic($upstreamProduct);
            $result = $ResModuleLogic->adminField($host);
        }else{
            $ModuleLogic = new ModuleLogic();
            $result = $ModuleLogic->adminField($host);
        }

        return $result;
    }


}
