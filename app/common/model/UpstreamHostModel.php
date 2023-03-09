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

	# 产品列表
    public function hostList($param)
    {
    	if (!isset($param['orderby']) || !in_array($param['orderby'],['id'])){
            $param['orderby'] = 'h.id';
        }else{
            $param['orderby'] = 'h.'.$param['orderby'];
        }
        $param['keywords'] = $param['keywords'] ?? '';
        $param['supplier_id'] = intval($param['supplier_id'] ?? 0);

        $where = function (Query $query) use($param) {
        	$query->where('h.id', '>', 0);
            if(!empty($param['keywords'])){
                $query->where('h.id|h.name|c.username|c.email|c.phone|p.name', 'like', "%{$param['keywords']}%");
            }
            if(!empty($param['supplier_id'])){
                $query->where('a.supplier_id', $param['supplier_id']);
            }
        };

        $count = $this->alias('a')
            ->field('h.id')
            ->leftJoin('host h','h.id=a.host_id')
            ->leftjoin('client c','h.client_id=c.id')
            ->leftjoin('product p','p.id=h.product_id')
            ->where($where)
            ->count();

        $hosts = $this->alias('a')
            ->field('h.id,h.name,p.name product_name,h.status,h.first_payment_amount,h.renew_amount,h.billing_cycle_name,h.billing_cycle,h.due_time,h.client_id,c.username,c.company,c.email,c.phone_code,c.phone')
            ->leftJoin('host h','h.id=a.host_id')
            ->leftjoin('client c','h.client_id=c.id')
            ->leftjoin('product p','p.id=h.product_id')
            ->where($where)
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

        $res = idcsmart_api_curl($host['supplier_id'], 'console/v1/host/'.$host['upstream_host_id'], [], 30, 'GET');
        if(!isset($res['data'])){
            return (object)[];
        }
        $host = $res['data'];

        return $host;
    }
}