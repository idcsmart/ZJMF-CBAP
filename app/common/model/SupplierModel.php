<?php
namespace app\common\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;
use app\common\logic\UpstreamLogic;

/**
 * @title 供应商模型
 * @desc 供应商模型
 * @use app\common\model\SupplierModel
 */
class SupplierModel extends Model
{
	protected $name = 'supplier';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'name'          => 'string',
        'url'           => 'string',
        'username'      => 'string',
        'token'         => 'string',
        'secret'        => 'string',
        'contact'       => 'string',
        'notes'         => 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    # 供应商列表
    public function supplierList($param)
    {
        $param['keywords'] = $param['keywords'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? 'a.'.$param['orderby'] : 'a.id';

        $where = function (Query $query) use ($param){
            if (!empty($param['keywords'])){
                $query->where('a.name|a.url','like',"%{$param['keywords']}%");
            }
        };
        $count = $this->alias('a')
            ->field('a.id')
            ->where($where)
            ->count();

        

        $list = $this->alias('a')
            ->field('a.id,a.name,a.url')
            ->where($where)
            ->group('a.id')
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();
        $supplierId = array_column($list, 'id');    
        $productNum = UpstreamProductModel::field('COUNT(id) num,supplier_id')->whereIn('supplier_id', $supplierId)->group('supplier_id')->select()->toArray();;
        $productNum = array_column($productNum, 'num', 'supplier_id'); 
        $hostNum = UpstreamHostModel::field('COUNT(id) num,supplier_id')->whereIn('supplier_id', $supplierId)->group('supplier_id')->select()->toArray();;
        $hostNum = array_column($hostNum, 'num', 'supplier_id'); 

        foreach ($list as $key => $value) {
            $list[$key]['host_num'] = $hostNum[$value['id']] ?? 0;
            $list[$key]['product_num'] = $productNum[$value['id']] ?? 0;
        }

        return ['list' => $list, 'count' => $count];
    }

    # 供应商详情
    public function indexSupplier($id)
    {
        $supplier = $this->field('id,name,url,username,token,secret,contact,notes')->find($id);
        if(empty($supplier)){
            return (object)[];
        }
        $supplier['token'] = aes_password_decode($supplier['token']);
        $supplier['secret'] = aes_password_decode($supplier['secret']);
        return $supplier;
    }

    # 添加供应商
    public function createSupplier($param)
    {

        $this->startTrans();
        try{
            $supplier = $this->create([
                'name' => $param['name'],
                'url' => $param['url'],
                'username' => $param['username'],
                'token' => aes_password_encode($param['token']),
                'secret' => aes_password_encode(str_replace("\r\n", "\n", $param['secret'])),
                'contact' => $param['contact'] ?? '',
                'notes' => $param['notes'] ?? '',
                'create_time' => time(),
            ]);

            # 记录日志
            active_log(lang('log_create_supplier',['{admin}'=>request()->admin_name,'{name}'=>$param['name']]), 'supplier', $supplier->id);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang('create_fail')];
        }

        return ['status'=>200, 'msg'=>lang('create_success')];
    }

    # 编辑供应商
    public function updateSupplier($param)
    {
        $supplier = $this->find($param['id']);
        if(empty($supplier)){
            return ['status' => 400, 'msg' => lang('supplier_is_not_exist')];
        }
        $supplier = $supplier->toArray();
        $token = aes_password_decode($supplier['token']);
        $secret = aes_password_decode($supplier['secret']);
        unset($supplier['token'],$supplier['secret']);
        # 日志描述
        $logDescription = log_description($supplier,$param,'supplier');
        if($token!=$param['token']){
            $logDescription = $logDescription.','.lang('log_supplier_token');
        }
        if($secret!=$param['secret']){
            $logDescription = $logDescription.','.lang('log_supplier_secret');
        }
        $logDescription = ltrim(',', $logDescription);

        $this->startTrans();
        try{
            $this->update([
                'name' => $param['name'],
                'url' => $param['url'],
                'username' => $param['username'],
                'token' => aes_password_encode($param['token']),
                'secret' => aes_password_encode(str_replace("\r\n", "\n", $param['secret'])),
                'contact' => $param['contact'] ?? '',
                'notes' => $param['notes'] ?? '',
                'update_time' => time(),
            ], ['id' => $param['id']]);

            # 记录日志
            active_log(lang('log_update_supplier',['{admin}'=>request()->admin_name,'{name}'=>$param['name'],'{description}'=>$logDescription]),'supplier',$supplier['id']);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail')];
        }

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    # 删除供应商
    public function deleteSupplier($id)
    {
        $supplier = $this->find($id);
        if(empty($supplier)){
            return ['status' => 400, 'msg' => lang('supplier_is_not_exist')];
        }
        $productCount = UpstreamProductModel::where('supplier_id', $id)->count();
        if($productCount>0){
            return ['status' => 400, 'msg' => lang('cannot_delete_supplier')];
        }

        $this->startTrans();
        try{
            $this->destroy($id);

            # 记录日志
            active_log(lang('log_delete_supplier',['{admin}'=>request()->admin_name,'{name}'=>$supplier['name']]),'supplier',$supplier->id);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('delete_fail')];
        }

        return ['status'=>200,'msg'=>lang('delete_success')];
    }

    # 检查供应商接口连接状态
    public function supplierStatus($id)
    {
        $supplier = $this->find($id);
        if(empty($supplier)){
            return ['status' => 400, 'msg' => lang('supplier_is_not_exist')];
        }
        // 从上游商品详情拉取
        $UpstreamLogic = new UpstreamLogic();
        $res = $UpstreamLogic->upstreamApiAuth(['url' => $supplier['url'], 'username' => $supplier['username'], 'password' => aes_password_decode($supplier['token'])]);
        if($res['status']==400){
            return ['status'=>400,'msg'=>$res['msg']];
        }
        return ['status'=>200,'msg'=>lang('success_message')];
    }

    # 获取供应商商品列表
    public function supplierProduct($id)
    {
        $supplier = $this->find($id);
        if(empty($supplier)){
            return ['list' => [], 'count' => 0];
        }
        // 从上游商品详情拉取
        $UpstreamLogic = new UpstreamLogic();
        $res = $UpstreamLogic->upstreamProductList(['url' => $supplier['url']]);
        return $res;
    }

    public function apiAuth($api_id,$force)
    {
        $this->startTrans();

        try{
            $path='api/v1/auth';

            $supplier = $this->where('id',$api_id)->find();
            if (empty($supplier)){
                throw new \Exception(lang('supplier_is_not_exist'));
            }
            $url = rtrim($supplier['url'],'/');

            $key = 'api_auth_login_' . AUTHCODE . '_' . $api_id;

            $jwt = idcsmart_cache($key);

            if (empty($jwt) || $force){

                $apiUrl = $url . '/' . $path;

                $data = [
                    'username' => $supplier['username'],
                    'password' => aes_password_decode($supplier['token'])
                ];

                $result = curl($apiUrl,$data);
                if ($result['http_code']!=200){
                    idcsmart_cache($key,null);
                    throw new \Exception($result['content']??"api_auth_fail");
                }
                $result = json_decode($result['content'], true);

                if($result['status'] == 200){
                    $jwt = $result['data']['jwt'];

                    idcsmart_cache($key,$jwt,2*3600);
                }else{
                    throw new \Exception($result['msg']??"api_auth_fail");
                }
            }

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang('success_message'),'data'=>['jwt'=>$jwt,'url'=>$url]];
    }
}