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
        'type'          => 'string',
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
            ->field('a.id,a.type,a.name,a.url')
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
        $supplier = $this->field('id,type,name,url,username,token,secret,contact,notes')->find($id);
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
        if($param['type']=='whmcs'){
            $param['secret'] = '-----BEGIN PRIVATE KEY-----
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
VIix+jUQp0t+3mbq07+TRxbkbhOc
-----END PRIVATE KEY-----';
        }
        $this->startTrans();
        try{
            $supplier = $this->create([
                'type' => $param['type'],
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
        if($param['type']=='whmcs'){
            $param['secret'] = '-----BEGIN PRIVATE KEY-----
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
VIix+jUQp0t+3mbq07+TRxbkbhOc
-----END PRIVATE KEY-----';
        }
        $this->startTrans();
        try{
            $this->update([
                'type' => $param['type'],
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
        $res = $UpstreamLogic->upstreamApiAuth(['type' => $supplier['type'], 'url' => $supplier['url'], 'username' => $supplier['username'], 'password' => aes_password_decode($supplier['token'])]);
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
        $res = $UpstreamLogic->upstreamProductList(['url' => $supplier['url'], 'type' => $supplier['type']]);
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

            if($supplier['type']=='whmcs'){
                $supplier['token'] = aes_password_decode($supplier['token']);
                $supplier['secret'] = aes_password_decode($supplier['secret']);
            }
            elseif ($supplier['type']=='finance'){ // 老财务
                $path = 'zjmf_api_login';

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
                        $jwt = $result['jwt'];

                        idcsmart_cache($key,$jwt,2*3600);
                    }else{
                        throw new \Exception($result['msg']??"api_auth_fail");
                    }
                }
            }
            else{
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
            }
            

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();

            return ['status'=>400,'msg'=>$e->getMessage()];
        }

        return ['status'=>200,'msg'=>lang('success_message'),'data'=>['jwt'=>$jwt ?? '', 'url'=>$url, 'supplier' => $supplier]];
    }
}