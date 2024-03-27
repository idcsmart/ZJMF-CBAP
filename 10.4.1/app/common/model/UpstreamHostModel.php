<?php
namespace app\common\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title 上游产品模型
 * @desc 上游产品模型
 * @use app\common\model\UpstreamHostModel
 */
class UpstreamHostModel extends Model
{
	protected $name = 'upstream_host';

	// 设置字段信息
    protected $schema = [
        'id'                    => 'int',
        'supplier_id'           => 'int',
        'host_id'               => 'int',
        'upstream_host_id'      => 'int',
        'upstream_info'         => 'string',
        'upstream_configoption' => 'string',
        'create_time'           => 'int',
        'update_time'           => 'int',
    ];

	/**
     * 时间 2023-02-13
     * @title 产品列表
     * @desc 产品列表
     * @author theworld
     * @version v1
     * @param string param.keywords - 关键字,搜索范围:ID,用户名称,邮箱,手机号,商品名称,产品标识
     * @param int param.supplier_id - 供应商ID
     * @param string param.billing_cycle - 付款周期
     * @param string param.status - 状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败
     * @param int param.start_time - 开始时间
     * @param int param.end_time - 结束时间
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - 产品
     * @return int list[].id - 产品ID 
     * @return string list[].name - 产品标识 
     * @return string list[].product_name - 商品名称 
     * @return string list[].status - 状态Unpaid未付款Pending开通中Active已开通Suspended已暂停Deleted已删除Failed开通失败 
     * @return string list[].first_payment_amount - 金额 
     * @return string list[].renew_amount - 续费金额 
     * @return string list[].billing_cycle - 周期
     * @return string list[].due_time - 到期时间 
     * @return string list[].client_id - 用户ID 
     * @return string list[].username - 用户名 
     * @return string list[].company - 公司 
     * @return string list[].email - 邮箱  
     * @return string list[].phone_code - 国际电话区号 
     * @return string list[].phone - 手机号
     * @return int list[].ip_num - IP数量
     * @return int count - 产品总数
     */
    public function hostList($param)
    {
    	if (!isset($param['orderby']) || !in_array($param['orderby'],['id'])){
            $param['orderby'] = 'h.id';
        }else{
            $param['orderby'] = 'h.'.$param['orderby'];
        }
        $param['keywords'] = $param['keywords'] ?? '';
        $param['supplier_id'] = intval($param['supplier_id'] ?? 0);
        $param['billing_cycle'] = $param['billing_cycle'] ?? '';
        $param['status'] = $param['status'] ?? '';
        $param['start_time'] = intval($param['start_time'] ?? 0);
        $param['end_time'] = intval($param['end_time'] ?? 0);

        $where = function (Query $query) use($param) {
        	$query->where('h.id', '>', 0);
            if(!empty($param['keywords'])){
                $query->where('h.id|h.name|c.username|c.email|c.phone|p.name', 'like', "%{$param['keywords']}%");
            }
            if(!empty($param['supplier_id'])){
                $query->where('a.supplier_id', $param['supplier_id']);
            }
            if(!empty($param['billing_cycle'])){
                $query->where('h.billing_cycle_name', 'like', "%{$param['billing_cycle']}%");
            }
            if(!empty($param['status'])){
                $query->where('h.status', $param['status']);
            }
            if(!empty($param['start_time']) && !empty($param['end_time'])){
                $query->where('h.create_time', '>=', $param['start_time'])->where('h.create_time', '<=', $param['end_time']);
            }
            $query->where('h.is_delete', 0);
        };

        $count = $this->alias('a')
            ->field('h.id')
            ->leftJoin('host h','h.id=a.host_id')
            ->leftjoin('client c','h.client_id=c.id')
            ->leftjoin('product p','p.id=h.product_id')
            ->where($where)
            ->count();

        $hosts = $this->alias('a')
            ->field('h.id,h.name,p.name product_name,h.status,h.first_payment_amount,h.renew_amount,h.billing_cycle_name,h.billing_cycle,h.due_time,h.client_id,c.username,c.company,c.email,c.phone_code,c.phone,hi.ip_num')
            ->leftJoin('host h','h.id=a.host_id')
            ->leftjoin('client c','h.client_id=c.id')
            ->leftjoin('product p','p.id=h.product_id')
            ->leftjoin('host_ip hi', 'h.id=hi.host_id')
            ->where($where)
            ->withAttr('ip_num', function($val){
                return $val ?? 0;
            })
            ->limit($param['limit'])
    		->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();

        foreach ($hosts as $key => $value) {
            $hosts[$key]['first_payment_amount'] = amount_format($value['first_payment_amount']);
            $hosts[$key]['billing_cycle'] = $value['billing_cycle']!='onetime' ? $value['billing_cycle_name'] : '';
            unset($hosts[$key]['billing_cycle_name']);
        }

        return ['list'=>$hosts,'count'=>$count];
        
    }

    # 产品详情
    public function indexHost($id)
    {
    	$host = $this->where('host_id', $id)->find();
        if(empty($host)){
        	return (object)[];
        }

        $HostModel = new HostModel();
        $hostInit = $HostModel->find($id);
        if(empty($hostInit) || $hostInit['is_delete']){
            return (object)[];
        }

        $UpstreamProductModel = new UpstreamProductModel();
        $upstreamProduct = $UpstreamProductModel->where('supplier_id',$host['supplier_id'])
            ->where('product_id',$hostInit['product_id'])
            ->find();
        if (isset($upstreamProduct['res_module']) && in_array($upstreamProduct['res_module'],['mf_finance','mf_finance_dcim'])){
            $UpstreamHostModel = new UpstreamHostModel();
            $upstreamHost = $UpstreamHostModel->where('host_id', $id)->find();
            $upstreamHostUpstream = $UpstreamHostModel->where('host_id',$upstreamHost['upstream_host_id'])->find();
            $SupplierModel = new SupplierModel();
            $supplier = $SupplierModel->find($host['supplier_id']);
            if (!empty($supplier) && $supplier['type']=='default'){
                // 产品的上游产品依然是代理产品
                $res = idcsmart_api_curl($host['supplier_id'], 'console/v1/host/'.$host['upstream_host_id'], [], 30, 'GET');
            }else{
                $res = idcsmart_api_curl($host['supplier_id'], 'host/header', ['host_id'=>$host['upstream_host_id']], 30, 'GET');
                if ($res['status']==200){
                    $hostData = $res['data']['host_data']??[];
                    $res['data']['host'] = [
                        'active_time' => $hostData['regdate'],
                        'agent' => 1,
                        'billing_cycle'=>$hostData['billingcycle'],
                        'billing_cycle_name'=>$hostData['billingcycle_desc'],
                        'billing_cycle_time'=>0,
                        'client_notes'=>$hostData['remark'],
                        'due_time'=>$hostData['nextduedate'],
                        'first_payment_amount'=>$hostData['firstpaymentamount'],
                        'id'=>$hostData['id'],
                        'name'=>$hostData['domain'],
                        'notes'=>$hostData['remark'],
                        'product_id'=>$hostData['productid'],
                        'product_name'=>$hostData['productname'],
                        'renew_amount'=>$hostData['amount'],
                        'server_id'=>$hostData['dcimid'],
                        'status'=>$hostData['domainstatus'],
                        'suspend_reason'=>$hostData['suspendreason'],
                        'suspend_type'=>$hostData['suspendreason_type'],
                    ];
                    $res['data']['status'] = [
                        'Unpaid',
                        'Pending',
                        'Active',
                        'Suspended',
                        'Deleted',
                        'Failed',
                        'Cancelled'
                    ];
                }
            }
        }else if (isset($upstreamProduct['res_module']) && in_array($upstreamProduct['res_module'], ['whmcs_cloud', 'whmcs_dcim'])){
            $result = idcsmart_api_curl($host['supplier_id'], 'host_detail', ['hosting_id' => $host['upstream_host_id']], 30, 'POST');
            $res = [];
            $res['data']['host'] = $result['data'] ?? [];
        }else{
            $res = idcsmart_api_curl($host['supplier_id'], 'console/v1/host/'.$host['upstream_host_id'], [], 30, 'GET');
        }

        if(!isset($res['data'])){
            return (object)[];
        }
        $host = $res['data'];

        return $host;
    }
}