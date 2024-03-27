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
        'base_price'            => 'float',
        'ratio_renew'           => 'int',
        'is_delete'             => 'int',  // 逻辑删除
        'delete_time'           => 'int',
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
     * @param int param.product_id - 商品ID
     * @param string param.name - 标识
     * @param string param.username - 用户名
     * @param string param.email - 邮箱
     * @param string param.phone - 手机号
     * @param int param.server_id - 接口ID
     * @param string param.tab - 状态using使用中expiring即将到期overdue已逾期deleted已删除
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
     * @return string list[].renew_amount - 续费金额
     * @return string list[].client_notes - 用户备注
     * @return int list[].ip_num - IP数量
     * @return int count - 产品总数
     * @return int expiring_count 即将到期产品数量
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
        $param['host_id'] = intval($param['host_id'] ?? 0);
        $param['name'] = $param['name'] ?? '';
        $param['username'] = $param['username'] ?? '';
        $param['email'] = $param['email'] ?? '';
        $param['phone'] = $param['phone'] ?? '';
        $param['server_id'] = intval($param['server_id'] ?? 0);
        $param['tab'] = $param['tab'] ?? '';
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
        $param['client_host_id'] = $hostId ?? [];

        $where = function (Query $query) use ($param, $app){
            if($app=='home'){
                $query->where('h.status', '<>', 'Cancelled');

                // 自用判断
                if (isset($param['scene']) && $param['scene']=='ticket' && class_exists('server\idcsmart_common_finance\model\IdcsmartCommonSonHost')){
                    $query->where('p.product_id','>',0);
                    $query->whereIn('s.module',['idcsmart_common_finance','idcsmart_common_dcim','idcsmart_common_cloud','idcsmart_common_business']);
                    $query->whereOr("p.id=225 and h.client_id=".$param['client_id']);
                }
                if(isset($param['scene']) && $param['scene'] == 'security_group'){
                    $query->whereIn('s.module', ['mf_cloud','common_cloud','cloudpods']);
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
            if(!empty($param['tab'])){
                if($param['tab']=='using'){
                    $query->whereIn('h.status', ['Pending', 'Active']);
                }else if($param['tab']=='expiring'){
                    $time = time();
                    $renewalFirstDay = configuration('cron_due_renewal_first_day');
                    $timeRenewalFirst = strtotime(date('Y-m-d 23:59:59', $time+$renewalFirstDay*24*3600));
                    $query->whereIn('h.status', ['Pending', 'Active'])->where('h.due_time', '>', $time)->where('h.due_time', '<=', $timeRenewalFirst)->where('billing_cycle', '<>', 'free')->where('billing_cycle', '<>', 'onetime');
                }else if($param['tab']=='overdue'){
                    $time = time();
                    $query->whereIn('h.status', ['Pending', 'Active', 'Suspended', 'Failed'])->where('h.due_time', '<=', $time)->where('billing_cycle', '<>', 'free')->where('billing_cycle', '<>', 'onetime');
                }else if($param['tab']=='deleted'){
                    $time = time();
                    $query->where('h.status', 'Deleted');
                }
            }
            if(!empty($param['host_id'])){
                $query->where('h.id', (int)$param['host_id']);
            }
            if(!empty($param['client_host_id'])){
                $query->whereIn('h.id', $param['client_host_id']);
            }
            if(!empty($param['start_time']) && !empty($param['end_time'])){
                $query->where('h.due_time', '>=', strtotime(date('Y-m-d', $param['start_time'])))->where('h.due_time', '<=', strtotime(date('Y-m-d 23:59:59', $param['end_time'])));
            }
            // 右下角搜索
            if(!empty($param['name'])){
                $query->where('h.name', 'like', "%{$param['name']}%");
            }
            if(!empty($param['username'])){
                $query->where('c.username', 'like', "%{$param['username']}%");
            }
            if(!empty($param['email'])){
                $query->where('c.email', 'like', "%{$param['email']}%");
            }
            if(!empty($param['phone'])){
                $query->where('c.phone', 'like', "%{$param['phone']}%");
            }
            if(!empty($param['server_id'])){
                $query->where('h.server_id', $param['server_id']);
            }
            $query->where('h.is_delete', 0);
        };

        $count = $this->alias('h')
            ->field('h.id')
            ->leftjoin('product p', 'p.id=h.product_id')
            ->leftjoin('client c', 'c.id=h.client_id')
            ->leftJoin('server s','s.id=h.server_id')
            ->where($where)
            ->count();
        $hosts = $this->alias('h')
            ->field('h.id,h.client_id,c.username client_name,c.email,c.phone_code,c.phone,c.company,h.product_id,p.name product_name,h.name,h.create_time,h.active_time,h.due_time,h.first_payment_amount,h.billing_cycle,h.billing_cycle_name,h.status,o.pay_time,h.renew_amount,h.client_notes,hi.ip_num')
            ->leftjoin('product p', 'p.id=h.product_id')
            ->leftjoin('client c', 'c.id=h.client_id')
            ->leftjoin('order o', 'o.id=h.order_id')
            ->leftJoin('server s','s.id=h.server_id')
            ->leftjoin('host_ip hi', 'h.id=hi.host_id')
            ->withAttr('product_name', function($val) use ($app) {
                if($app == 'home'){
                    $multiLanguage = hook_one('multi_language', [
                        'replace' => [
                            'product_name' => $val,
                        ],
                    ]);
                    if(isset($multiLanguage['product_name'])){
                        $val = $multiLanguage['product_name'];
                    }
                }
                return $val;
            })
            ->withAttr('ip_num', function($val){
                return $val ?? 0;
            })
            ->where($where)
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->group('h.id')
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

        if($app=='home'){
            return ['list' => $hosts, 'count' => $count];
        }else{
            $where = function (Query $query) use ($param, $app){
                $time = time();
                $renewalFirstDay = configuration('cron_due_renewal_first_day');
                $timeRenewalFirst = strtotime(date('Y-m-d 23:59:59', $time+$renewalFirstDay*24*3600));
                $query->whereIn('h.status', ['Pending', 'Active'])->where('h.due_time', '>', $time)->where('h.due_time', '<=', $timeRenewalFirst)->where('billing_cycle', '<>', 'free')->where('billing_cycle', '<>', 'onetime');
                $query->where('h.is_delete', 0);
            };
            $expiringCount = $this->alias('h')
                ->field('h.id')
                ->leftjoin('product p', 'p.id=h.product_id')
                ->leftjoin('client c', 'c.id=h.client_id')
                ->leftJoin('server s','s.id=h.server_id')
                ->where($where)
                ->count();
            return ['list' => $hosts, 'count' => $count, 'expiring_count' => $expiringCount];
        }    
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
     * @return string list[].client_notes - 用户备注
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

        $where = function (Query $query) use($param) {
            $query->whereIn('h.status', ['Active']);
            if(!empty($param['client_id'])){
                $query->where('h.client_id', (int)$param['client_id']);
            }
            if(!empty($param['host_id'])){
                $query->whereIn('h.id', $param['host_id']);
            }
            $query->where('h.is_delete', 0);
        };

        $count = $this->alias('h')
            ->field('h.id')
            ->leftjoin('product p', 'p.id=h.product_id')
            ->leftjoin('client c', 'c.id=h.client_id')
            ->where($where)
            ->count();
        $hosts = $this->alias('h')
            ->field('h.id,h.product_id,p.name product_name,h.name,h.due_time,h.status,h.client_notes,s.module,ss.module module1')
            ->leftjoin('product p', 'p.id=h.product_id')
            ->leftjoin('server s','p.type=\'server\' AND p.rel_id=s.id')
            ->leftjoin('server_group sg','p.type=\'server_group\' AND p.rel_id=sg.id')
            ->leftjoin('server ss','ss.server_group_id=sg.id')
            ->where($where)
            ->withAttr('product_name', function($val){
                $multiLanguage = hook_one('multi_language', [
                    'replace' => [
                        'product_name' => $val,
                    ],
                ]);
                if(isset($multiLanguage['product_name'])){
                    $val = $multiLanguage['product_name'];
                }
                return $val;
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
     * @return int order_id - 订单ID 
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
     * @return string client_notes - 用户备注
     * @return int ratio_renew - 是否开启比例续费:0否,1是
     * @return string base_price - 购买周期原价
     * @return string product_name - 商品名称
     * @return int agent - 代理产品0否1是
     * @return string upstream_host_id - 上游产品ID
     */
    public function indexHost($id)
    {
        // 获取当前应用
        $app = app('http')->getName();

        $host = $this->field('id,order_id,product_id,server_id,name,notes,first_payment_amount,renew_amount,billing_cycle,billing_cycle_name,billing_cycle_time,active_time,due_time,status,client_id,suspend_type,suspend_reason,client_notes,ratio_renew,base_price')->where('is_delete', 0)->find($id);
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
        $host['upstream_host_id'] = $upstreamHost['upstream_host_id']??0;
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

        $resultHook = hook('before_search_host', ['keywords' => $keywords]);
        $resultHook = array_values(array_filter($resultHook ?? []));
        $hostIdArr = [];
        foreach ($resultHook as $key => $value) {
            if(isset($value['host_id']) && !empty($value['host_id']) && is_array($value['host_id'])){
                $hostIdArr = array_merge($hostIdArr, $value['host_id']);
            }
        }
        $hostIdArr = array_unique($hostIdArr);
        
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
                $query->where('h.is_delete', 0);
            })
            ->select()
            ->toArray();
        if(!empty($hostIdArr)){
            $hostIdArr = array_merge($hostIdArr, array_column($hosts, 'id'));
            $hostIdArr = array_unique($hostIdArr);
            $hosts = $this->alias('h')
                ->field('h.id,h.name,p.name product_name,h.client_id')
                ->leftjoin('product p', 'p.id=h.product_id')
                ->whereIn('h.id', $hostIdArr)
                ->where('h.is_delete', 0)
                ->select()
                ->toArray();
        }

        if($app=='home'){
            foreach ($hosts as $key => $value) {
                unset($hosts[$key]['client_id']);

                $multiLanguage = hook_one('multi_language', [
                    'replace' => [
                        'product_name' => $value['product_name'],
                    ],
                ]);
                if(isset($multiLanguage['product_name'])){
                    $hosts[$key]['product_name'] = $multiLanguage['product_name'];
                }
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
     * @param string param.upstream_host_id - 上游产品ID
     * @param float param.first_payment_amount - 订购金额 required
     * @param float param.renew_amount - 续费金额 required
     * @param string param.billing_cycle - 计费周期 required
     * @param string param.active_time - 开通时间
     * @param string param.due_time - 到期时间
     * @param string param.status - 状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败
     * @param object param.self_defined_field - 自定义字段({"5":"123"},5是自定义字段ID,123是填写的内容)
     * @param int param.host.ratio_renew - 是否开启比例续费:0否,1是
     * @param float param.host.base_price - 购买周期原价
     * @param object param.customfield - 自定义字段
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateHost($param)
    {
        // 验证产品ID
        $host = $this->find($param['id']);
        if (empty($host) || $host['is_delete']){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }

        // 验证商品ID
        $product = ProductModel::find($param['product_id']);
        if (empty($product)){
            return ['status'=>400, 'msg'=>lang('product_is_not_exist')];
        }
        $SelfDefinedFieldModel = new SelfDefinedFieldModel();
        if($host['product_id'] == $param['product_id'] && isset($param['self_defined_field'])){
            $selfDefinedFieldFormat = $SelfDefinedFieldModel->adminHostUpdateFormat([
                'host_id'           => $host->id,
                'self_defined_field'=> $param['self_defined_field'],
            ]);
            if($selfDefinedFieldFormat['status'] != 200){
                return $selfDefinedFieldFormat;
            }
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
        if(isset($selfDefinedFieldFormat)){
            $description = array_merge($description, array_column($selfDefinedFieldFormat['data'], 'log'));
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
                'update_time' => time(),
                'ratio_renew' => $param['ratio_renew']??0,
                'base_price' => $param['base_price']??0,
            ], ['id' => $param['id']]);

            if (isset($param['upstream_host_id']) && $param['upstream_host_id']){

                $UpstreamHostModel = new UpstreamHostModel();

                $upstreamHost = $UpstreamHostModel->where('host_id',$host['id'])->find();

                $UpstreamProductModel = new UpstreamProductModel();
                $upstreamProduct = $UpstreamProductModel->where('product_id',$host['product_id'])->find();

                if (isset($upstreamProduct['res_module']) && in_array($upstreamProduct['res_module'],['mf_finance','mf_finance_dcim'])){
                    $upstreamHostUpstream = $UpstreamHostModel->where('host_id',$upstreamHost['upstream_host_id'])->find();
                    $SupplierModel = new SupplierModel();
                    $supplier = $SupplierModel->find($upstreamProduct['supplier_id']);
                    if (!empty($supplier) && $supplier['type']=='default'){
                        $res = idcsmart_api_curl($upstreamHost['supplier_id'], 'console/v1/host/'.$param['upstream_host_id'], [], 30, 'GET');
                        if (!isset($res['data']['host']) || empty($res['data']['host'])){
                            return ['status'=>400, 'msg'=>lang('upstream_host_is_not_exist')];
                        }
                    }else{
                        $res = idcsmart_api_curl($upstreamHost['supplier_id'], 'host/header', ['host_id'=>$param['upstream_host_id']], 30, 'GET');
                        if (!isset($res['data']['host_data'])){
                            return ['status'=>400, 'msg'=>lang('upstream_host_is_not_exist')];
                        }
                    }
                }else if (isset($upstreamProduct['res_module']) && in_array($upstreamProduct['res_module'], ['whmcs_cloud', 'whmcs_dcim'])){
                    $result = idcsmart_api_curl($upstreamHost['supplier_id'], 'host_detail', ['hosting_id' => $param['upstream_host_id']], 30, 'POST');
                    if (!isset($result['data'])){
                        return ['status'=>400, 'msg'=>lang('upstream_host_is_not_exist')];
                    }
                }else{
                    $res = idcsmart_api_curl($upstreamHost['supplier_id'], 'console/v1/host/'.$param['upstream_host_id'], [], 30, 'GET');
                    if (!isset($res['data']['host']) || empty($res['data']['host'])){
                        return ['status'=>400, 'msg'=>lang('upstream_host_is_not_exist')];
                    }
                }

                $UpstreamHostModel->update([
                    'upstream_host_id' => $param['upstream_host_id']
                ],['host_id'=>$host['id']]);


            }

            if($host['product_id'] != $param['product_id']){
                $SelfDefinedFieldValueModel = new SelfDefinedFieldValueModel();
                $SelfDefinedFieldValueModel->withDelete([
                    'type'  => 'product',
                    'relid' => $host['id'],
                ]);
            }else{
                if(isset($selfDefinedFieldFormat)){
                    $SelfDefinedFieldModel->adminHostUpdateSave($selfDefinedFieldFormat);
                }
            }

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
     * @param int param.id - 产品ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteHost($param)
    {
        $id = $param['id']??0;
        // 验证产品ID
        $host = $this->find($id);
        if (empty($host) || $host['is_delete']){
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
            HostIpModel::where('host_id', $id)->delete();

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
     * @param array param.id - 产品ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function batchDeleteHost($param)
    {
        $id = $param['id']??[];
        // 验证产品ID
        $host = $this->whereIn('id', $id)->where('is_delete', 0)->select()->toArray();
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
     * @return  HostModel host - 当前产品类
     * @return  ClientModel client - 所属用户类
     * @return  ProductModel product - 所属商品类
     * @return  ServerModel server - 关联接口类
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
     * @param   int id - 产品ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function createAccount($id)
    {
        $host = $this->find($id);
        if(empty($host) || $host['is_delete']){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        if($host['status'] == 'Active'){
            return ['status'=>400, 'msg'=>lang('host_is_active')];
        }
        if($host['status'] == 'Suspended'){
            return ['status'=>400, 'msg'=>lang('host_is_suspended')];
        }
        $lock = $this->getCreateAccountLock($id);
        if($lock['status'] == 400){
            return $lock;
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
            if($host_active['email_enable']==1){
                add_task([
                    'type' => 'email',
                    'description' => lang('host_create_success_send_mail'),
                    'task_data' => [
                        'name'=>'host_active',//发送动作名称
                        'host_id'=>$id,//主机ID
                    ],      
                ]);
            }
            if($host_active['sms_enable']==1){
               add_task([
                    'type' => 'sms',
                    'description' => lang('host_create_success_send_sms'),
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
        $this->clearCreateAccountLock($id);

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
     * @param int param.id - 产品ID require
     * @param string param.suspend_type overdue 暂停类型(overdue=到期暂停,overtraffic=超流暂停,certification_not_complete=实名未完成,other=其他,downstream=下游暂停)
     * @param string param.suspend_reason - 暂停原因
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function suspendAccount($param)
    {
        $id = (int)$param['id'];
        $param['suspend_reason'] = $param['suspend_reason'] ?? '';

        $host = $this->find($id);
        if(empty($host) || $host['is_delete']){
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
				'description' => lang('host_suspend_send_mail'),
				'task_data' => [
					'name'=>'host_suspend',//发送动作名称
					'host_id'=>$id,//主机ID
				],		
			]);
			add_task([
				'type' => 'sms',
				'description' => lang('host_suspend_send_sms'),
				'task_data' => [
					'name'=>'host_suspend',//发送动作名称
					'host_id'=>$id,//主机ID
				],		
			]);

            $suspendType = [
                'overdue'=>lang('suspend_type_overdue'),
                'overtraffic'=>lang('suspend_type_overtraffic'),
                'certification_not_complete'=>lang('suspend_type_certification_not_complete'),
                'other'=>lang('suspend_type_other'),
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
     * @param   int id - 产品ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function unsuspendAccount($id)
    {
        $host = $this->find($id);
        if(empty($host) || $host['is_delete']){
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
            return ['status'=>400, 'msg'=>lang('cannot_unsuspend_from_upstream')];
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
					'description' => lang('host_unsuspend_send_mail'),
					'task_data' => [
						'name'=>'host_unsuspend',//发送动作名称
						'host_id'=>$id,//主机ID
					],		
				]);
				add_task([
					'type' => 'sms',
					'description' => lang('host_unsuspend_send_sms'),
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
     * @param int id - 产品ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function terminateAccount($id)
    {
        $host = $this->find($id);
        if(empty($host) || $host['is_delete']){
            return ['status'=>400, 'msg'=>lang('host_is_not_exist')];
        }
        $client = ClientModel::find($host['client_id']);

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
				'description' => lang('host_delete_send_mail'),
				'task_data' => [
					'name'=>'host_terminate',//发送动作名称
					'host_id'=>$id,//主机ID
				],		
			]);
			add_task([
				'type' => 'sms',
				'description' => lang('host_delete_send_sms'),
				'task_data' => [
					'name'=>'host_terminate',//发送动作名称
					'host_id'=>$id,//主机ID
				],		
			]);

            upstream_sync_host($id, 'module_terminate');

            $description = lang('log_module_terminate_account_success', [
                '{host}'    => 'host#'.$host->id.'#'.$host['name'].'#',
                '{client}'  => 'client#'.$client->id.'#'.$client['username'].'#',
            ]);
        }else{
            hook('after_host_terminate_fail',['id'=>$id,'fail_reason'=>$res['msg']??'']);

            $description = lang('log_module_terminate_account_failed', [
                '{host}'    => 'host#'.$host->id.'#'.$host['name'].'#',
                '{reason}'  => $res['msg'] ?? '',
                '{client}'  => 'client#'.$client->id.'#'.$client['username'].'#',
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
     * @param int id - 产品ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return string data.content - 内页模块输出
     */
    public function adminArea($id)
    {
        $host = $this->find($id);
        if(empty($host) || $host['is_delete']){
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
     * @param int id - 导航ID require
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
     * @param int id - 产品ID require
     * @return int status - 状态码,200=成功,400=失败
     * @return string msg - 提示信息
     * @return string data.content - 内页模块输出
     */
    public function clientArea($id)
    {
        $host = $this->find($id);
        if(empty($host) || $host['client_id'] != get_client_id() || $host['is_delete']){
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
     * @title 升降级
     * @desc 升降级
     * @author hh
     * @version v1
     * @param int id - upgrade表ID require
     * @return int status - 状态码,200=成功,400=失败
     * @return string msg - 提示信息
     */
    public function upgradeAccount($id)
    {
        $UpgradeModel = new UpgradeModel();
        $UpgradeModel->startTrans();
        $upgrade = $UpgradeModel->where('id',$id)->lock(true)->find();
        if (empty($upgrade) || $upgrade['status']=='Completed'){
            return ['status'=>200, 'msg'=>lang('success_message')];
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

            // 删除原来的自定义字段
            $SelfDefinedFieldValueModel = new SelfDefinedFieldValueModel();
            $SelfDefinedFieldValueModel->withDelete([
                'type'  => 'product',
                'relid' => $host['id'],
            ]);

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
			'description' => lang('host_upgrade_send_mail'),
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
			'description' => lang('host_upgrade_send_sms'),
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

        upstream_sync_host($host['id'], 'update_host');
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
        if (empty($host) || $host['is_delete']){
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

        $where = function (Query $query) use($param) {
            $query->where('h.status', '<>', 'Cancelled');
            if(!empty($param['client_id'])){
                $query->where('h.client_id', (int)$param['client_id']);
            }
            $query->where('h.is_delete', 0);
        };

        $count = $this->alias('h')
            ->field('h.id')
            ->leftjoin('product p', 'p.id=h.product_id')
            ->where($where)
            ->count();
        $hosts = $this->alias('h')
            ->field('h.id,h.product_id,p.name product_name,h.name')
            ->leftjoin('product p', 'p.id=h.product_id')
            ->where($where)
            ->withAttr('product_name', function($val) use ($app) {
                if($app == 'home'){
                    $multiLanguage = hook_one('multi_language', [
                        'replace' => [
                            'product_name' => $val,
                        ],
                    ]);
                    if(isset($multiLanguage['product_name'])){
                        $val = $multiLanguage['product_name'];
                    }
                }
                return $val;
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
     * @param   int param.id - 产品ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.button[].type - 按钮类型(暂时都是default)
     * @return  string data.button[].func - 按钮功能(create=开通,suspend=暂停,unsuspend=解除暂停,terminate=删除,renew=续费)
     * @return  string data.button[].name - 名称
     */
    public function moduleAdminButton($param)
	{
        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => [
                'button' => [],
            ],
        ];
        $host = $this->find($param['id']);
        if(empty($host) || $host['is_delete']){
            return $result;
        }
        $button = [];
        if(in_array($host['status'], ['Unpaid','Pending','Active','Suspended','Deleted','Failed'])){
            $button[] = [
                'type' => 'default',
                'func' => 'create',
                'name' => lang('module_button_create'),
            ];
        }
        if(in_array($host['status'], ['Pending','Active'])){
            $button[] = [
                'type' => 'default',
                'func' => 'suspend',
                'name' => lang('module_button_suspend'),
            ];
        }
        if(in_array($host['status'], ['Suspended'])){
            $button[] = [
                'type' => 'default',
                'func' => 'unsuspend',
                'name' => lang('module_button_unsuspend'),
            ];
        }
        if(in_array($host['status'], ['Pending','Active','Suspended','Failed'])){
            $button[] = [
                'type' => 'default',
                'func' => 'terminate',
                'name' => lang('module_button_terminate'),
            ];
        }
        if(in_array($host['status'], ['Active'])){
            // 判断下续费插件
            $renew = PluginModel::where('name', 'IdcsmartRenew')->where('status', 1)->value('id');
            if($renew){
                $button[] = [
                    'type' => 'default',
                    'func' => 'renew',
                    'name' => lang('module_button_renew'),
                ];
            }
        }
        $result['data']['button'] = $button;
        return $result;
    }

    /**
     * @title 上游同步产品信息到下游
     * @desc  上游同步产品信息到下游
     * @author theworld
     * @version v1
     * @param   int    id 财务产品ID
     * @param   string action  动作module_create模块开通module_suspend模块暂停module_unsuspend模块解除暂停module_terminate模块删除update_host修改产品delete_host删除产品host_renew产品续费
     */
    public function upstreamSyncHost($id, $action = '')
    {
        $host = $this->find($id);
        if(empty($host) || $host['is_delete']){
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

        // 魔方财务
        if (isset($downstreamInfo['type']) && $downstreamInfo['type']=='finance'){

            $data = $this->syncDownStreamHost($host);

            $sign = create_sign(['id'=>$host['downstream_host_id']],$downstreamInfo['token']);

            $data = array_merge($data,$sign);

            $res = curl(rtrim($downstreamInfo['url'],'/').'/api/host/sync', $data, 30, 'POST');
        }else{
            // 获取IP信息
            $HostIpModel = new HostIpModel();
            $hostIp = $HostIpModel->getHostIp(['host_id'=>$id]);

            $api['public_key'] = aes_password_decode($api['public_key']);

            $data = json_encode(['action' => $action, 'host' => $host, 'host_ip'=>$hostIp]);

            $crypto = '';

            foreach (str_split($data, 117) as $chunk) {

                openssl_public_encrypt($chunk, $encryptData, $api['public_key']);

                $crypto .= $encryptData;
            }

            $data = base64_encode($crypto);

            $res = curl(rtrim($downstreamInfo['url'],'/').'/console/v1/upstream/sync', ['host_id' => $host['downstream_host_id'], 'data' => $data], 30, 'POST');

        }

        active_log(lang("upstream_host_downstream_update",['{id}'=>$id,"{downstream_id}"=>$host['downstream_host_id'],"{result}"=>json_encode($res)]),"host",$id);

        return true;
    }


    /**
     * 时间 2023-04-14
     * @title 产品内页模块输入框输出
     * @desc 产品内页模块输入框输出
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data[].name - 配置小标题
     * @return  string data[].field[].name - 名称
     * @return  string data[].field[].key - 标识(不要重复)
     * @return  string data[].field[].value - 当前值
     * @return  bool   data[].field[].disable - 状态(false=可修改,true=不可修改)
     */
    public function moduleField($id)
    {
        $host = $this->find($id);
        if(empty($host) || $host['is_delete']){
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

    // 同步数据至下游（魔方财务）
    public function syncDownStreamHost(HostModel $host)
    {
        $id = $host['id'];
        // 状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败Cancelled已取消
        if ($host['status']=='Unpaid' || $host['status']=='Pending'){
            $domainstatus = 'Pending';
        }elseif ($host['status']=='Suspended'){
            $domainstatus = 'Suspended';
        }elseif ($host['status']=='Deleted'){
            $domainstatus = 'Deleted';
        }elseif ($host['status']=='Failed'){
            $domainstatus = 'Failed';
        }elseif ($host['status']=='Cancelled'){
            $domainstatus = 'Cancelled';
        }elseif ($host['status']=='Active'){
            $domainstatus = 'Active';
        }else{
            $domainstatus = 'Pending';
        }

        $ProductModel = new ProductModel();
        $product = $ProductModel->where('id',$host['product_id'])->find();
        if ($product['type']=='server'){
            $ServerModel = new ServerModel();
            $server = $ServerModel->where('id',$product['rel_id'])->find();
        }else{
            $ServerGroupModel = new ServerGroupModel();
            $serverGroup = $ServerGroupModel->where('id',$product['rel_id'])->find();
            $ServerModel = new ServerModel();
            $server = $ServerModel->where('server_group_id',$serverGroup['id'])->find();
        }
        if ($server['module']=="mf_cloud"){
            // TODO 这几个同步操作容易超时
            if (class_exists('server\mf_cloud\logic\CloudLogic')){
                try{
                    $CloudLogic = new \server\mf_cloud\logic\CloudLogic($id);
                    $result = $CloudLogic->detail();
                    $password = $result['data']['password']??"";
                    $username = $result['data']['username']??"";
                    $port = $result['data']['port']??"";
                }catch (\Exception $e){

                }
            }
            if (class_exists('server\mf_cloud\model\HostLinkModel')){
                $HostLinkModel = new \server\mf_cloud\model\HostLinkModel();
                $hostlink = $HostLinkModel->detail($id);
                if (!empty($hostlink['data'])){
                    $ip = $hostlink['data']['ip']??"";
                    $image = $hostlink['data']['image']['name']??"";
                }
            }
        }elseif ($server['module']=="mf_dcim"){
            // TODO 这几个同步操作容易超时
            if (class_exists('server\mf_dcim\logic\CloudLogic')){
                try{
                    $CloudLogic = new \server\mf_dcim\logic\CloudLogic($id);
                    $result = $CloudLogic->detail();
                    $password = $result['data']['password']??"";
                    $username = $result['data']['username']??"";
                    $port = $result['data']['port']??"";
                }catch (\Exception $e){

                }
            }
            if (class_exists('server\mf_dcim\model\HostLinkModel')){
                $HostLinkModel = new \server\mf_dcim\model\HostLinkModel();
                $hostlink = $HostLinkModel->detail($id);
                if (!empty($hostlink['data'])){
                    $ip = $hostlink['data']['ip']??"";
                    $image = $hostlink['data']['image']['name']??"";
                }
            }
        }

        // 获取IP信息
        $HostIpModel = new HostIpModel();
        $hostIp = $HostIpModel->getHostIp(['host_id'=>$id]);

        $data = [
            'id' => $host['downstream_host_id'],
            'domain' => $host['name']??"", // wyh 20240308 修改bug,同步主机名至魔方财务
            'username' => $username??"",
            'password' => $password??"",
            'os' => $image??"",
            'os_url' => "",
            'dedicatedip' => $hostIp['dedicate_ip'] ?: ($ip ?? ''),
            'assignedips' => $hostIp['assign_ip'],
            'port' => $port??"",
            'suspendreason' => $host['suspend_reason'],
            'nextduedate' => $host['due_time'],
            'domainstatus' => $domainstatus,
        ];

        return $data;
    }

    /**
     * 时间 2024-01-19
     * @title 获取产品开通锁
     * @desc  获取产品开通锁,防止重复开通
     * @author hh
     * @version v1
     * @param   int $id - 产品ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function getCreateAccountLock($id)
    {
        $cacheKey = 'HOST_DEFAULT_ACTION_CREATE_' . $id;
        if(!empty(cache($cacheKey))){
            return ['status'=>400, 'msg'=>lang('host_is_creating_please_wait_and_retry')];
        }
        cache($cacheKey, 1, 180);
        return ['status'=>200, 'msg'=>lang('success_message')];
    }

    /**
     * 时间 2024-01-19
     * @title 清除产品开通锁
     * @desc  清除产品开通锁
     * @author hh
     * @version v1
     * @param   int id - 产品ID require
     */
    public function clearCreateAccountLock($id)
    {
        $cacheKey = 'HOST_DEFAULT_ACTION_CREATE_' . $id;
        cache($cacheKey, NULL);
    }



}
