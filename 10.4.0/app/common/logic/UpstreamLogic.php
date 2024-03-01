<?php
namespace app\common\logic;

use app\common\model\UpstreamProductModel;
use app\common\model\UpstreamHostModel;
use app\common\model\SupplierModel;
use app\common\model\HostModel;
use app\common\model\HostIpModel;

/**
 * @title 上游管理公共类
 * @desc 上游管理公共类
 * @use app\common\logic\UpstreamLogic
 */
class UpstreamLogic
{
    // 官方地址
    private $officialUrl = 'https://my.idcsmart.com';

    /**
     * 时间 2023-02-13
     * @title 推荐代理商品列表
     * @desc 推荐代理商品列表
     * @author theworld
     * @version v1
     * @param string param.keywords - 关键字,搜索范围:商品名称
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id
     * @param string param.sort - 升/降序 asc,desc
     * @return array list -  推荐商品
     * @return int list[].id - 推荐商品ID
     * @return int list[].upstream_product_id - 上游商品ID
     * @return string list[].name - 商品名称
     * @return string list[].type - 供应商类型default默认业务系统whmcs财务系统finance魔方财务
     * @return string list[].supplier_name - 供应商名称
     * @return string list[].login_url - 前台网站地址
     * @return string list[].url - 接口地址
     * @return string list[].pay_type - 付款类型,免费free,一次onetime,周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return string list[].price - 商品最低价格
     * @return string list[].cycle - 商品最低周期
     * @return int list[].cpu_min - CPU(核)最小值
     * @return int list[].cpu_max - CPU(核)最大值
     * @return int list[].memory_min - 内存(GB)最小值
     * @return int list[].memory_max - 内存(GB)最大值
     * @return int list[].disk_min - 硬盘(GB)最小值
     * @return int list[].disk_max - 硬盘(GB)最大值
     * @return int list[].bandwidth_min - 带宽(Mbps)最小值
     * @return int list[].bandwidth_max - 带宽(Mbps)最大值
     * @return int list[].flow_min - 流量(G)最小值 
     * @return int list[].flow_max - 流量(G)最大值 
     * @return string list[].description - 简介
     * @return int list[].agent - 是否已代理0否1是
     * @return object list[].supplier - 供应商,已添加时有数据
     * @return object list[].supplier.id - 供应商ID
     * @return object list[].supplier.username - 上游账户名
     * @return object list[].supplier.token - API密钥
     * @return object list[].supplier.secret - API私钥
     * @return int count -  推荐商品总数
     */
    public function recommendProductList($param)
    {
        $param['keywords'] = $param['keywords'] ?? '';
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? 'a.'.$param['orderby'] : 'a.id';

        $res = $this->upstreamRequest($this->officialUrl.'/console/v1/recommend/product', $param, 30, 'GET');
        $list = $res['data']['list'] ?? [];
        $upstreamProduct = UpstreamProductModel::column('upstream_product_id');
        $supplier = SupplierModel::select()->toArray();
        $supplierArr = [];
        foreach ($supplier as $key => $value) {
            $supplierArr[$value['url']] = ['id' => $value['id'], 'username' => $value['username'], 'token' => aes_password_decode($value['token']), 'secret' => aes_password_decode($value['secret'])];
        }
        foreach ($list as $key => $value) {
            if(in_array($value['upstream_product_id'], $upstreamProduct)){
                $list[$key]['agent'] = 1;
            }else{
                $list[$key]['agent'] = 0;
            }
            $list[$key]['supplier'] = $supplierArr[$value['url']] ?? (object)[];
        }

        return ['list' => $list, 'count' => $res['data']['count'] ?? 0];
    }

    /**
     * 时间 2023-02-13
     * @title 推荐代理商品详情
     * @desc 推荐代理商品详情
     * @author theworld
     * @version v1
     * @param int id - 商品ID required
     * @return object data - 商品详情
     * @return int data.id - 推荐商品ID
     * @return int data.upstream_product_id - 上游商品ID
     * @return string data.name - 商品名称
     * @return string data.supplier_name - 供应商名称
     * @return string data.login_url - 前台网站地址
     * @return string data.url - 接口地址
     * @return string data.pay_type - 付款类型免费free,一次onetime,周期先付recurring_prepayment,周期后付recurring_postpaid
     * @return string data.price - 商品最低价格
     * @return string data.cycle - 商品最低周期
     * @return int data.cpu_min - CPU(核)最小值
     * @return int data.cpu_max - CPU(核)最大值
     * @return int data.memory_min - 内存(GB)最小值
     * @return int data.memory_max - 内存(GB)最大值
     * @return int data.disk_min - 硬盘(GB)最小值
     * @return int data.disk_max - 硬盘(GB)最大值
     * @return int data.bandwidth_min - 带宽(Mbps)最小值
     * @return int data.bandwidth_max - 带宽(Mbps)最大值
     * @return int data.flow_min - 流量(G)最小值 
     * @return int data.flow_max - 流量(G)最大值 
     * @return string data.description - 简介
     */
    public function recommendProductDetail($param)
    {
        $param['id'] = $param['id'] ?? 0;

        $res = $this->upstreamRequest($this->officialUrl.'/console/v1/recommend/product/'.$param['id'], [], 30, 'GET');
        
        return ['data' => $res['data']['product'] ?? []];
    }

    /**
     * 时间 2023-02-13
     * @title 上游商品列表
     * @desc 上游商品列表
     * @author theworld
     * @version v1
     * @param string url - 上游地址 required
     * @param string type - 供应商类型default默认业务系统whmcs财务系统finance魔方财务 required
     * @return array list - 商品列表
     * @return int list[].id - 商品ID 
     * @return string list[].name - 商品名
     * @return string list[].description - 描述
     * @return string list[].price - 商品最低价格
     * @return string list[].cycle - 商品最低周期
     */
    public function upstreamProductList($param)
    {
        if($param['type']=='whmcs'){
            $res = $this->upstreamRequest(rtrim($param['url'],'/').'/modules/addons/idcsmart_reseller/logic/index.php?action=product_listing', [], 30, 'POST');
        }elseif ($param['type']=='finance'){
            $res = $this->upstreamRequest(rtrim($param['url'],'/').'/api/product/list', [], 30, 'GET');
        }
        else{
            $res = $this->upstreamRequest(rtrim($param['url'],'/').'/api/v1/product', [], 30, 'GET');
        }

        return ['list' => $res['data']['list'] ?? []];
    }

    /**
     * 时间 2023-02-13
     * @title 上游商品详情
     * @desc 上游商品详情
     * @author theworld
     * @version v1
     * @param int id - 商品ID required
     * @param int supplier_id - 供应商ID
     * @param string url - 上游地址 required
     * @param string type - 供应商类型default默认业务系统whmcs财务系统finance魔方财务 required
     * @return object data - 商品详情
     * @return object self_defined_field - 自定义字段 
     */
    public function upstreamProductDetail($param)
    {
        $param['id'] = $param['id'] ?? 0;

        $supplierId = $param['supplier_id']??0;

        $selfDefinedField = NULL;
        if($param['type']=='whmcs'){
            //$res = idcsmart_api_curl($supplierId,'modules/addons/idcsmart_reseller/logic/index.php?action=product_detail',['productid' => $param['id']],30,'POST');
            $res = $this->upstreamRequest(rtrim($param['url'],'/').'/modules/addons/idcsmart_reseller/logic/index.php?action=product_detail', ['productid' => $param['id']], 30, 'POST');

            if(isset($res['data']['customfields'])){
                $selfDefinedField = $res['data']['customfields'];
            }
        }elseif ($param['type']=='finance'){
            if(!empty($supplierId)){
                $res = idcsmart_api_curl($supplierId,'api/product/'.$param['id'],[],30,'GET');
            }else{
                $res = $this->upstreamRequest(rtrim($param['url'],'/').'/api/product/'.$param['id'], [], 30, 'GET');
            }
            if ($res['status']==200){
                //$res['data']['product']['price'] = $res['data']['product']['sale_price']??($res['data']['product']['price']??0);
                $res['data']['product']['pay_type'] = ($res['data']['product']['pay_type']=='recurring')?'recurring_prepayment':$res['data']['product']['pay_type'];
            }
            if(isset($res['data']['product']['customfields'])){
                $selfDefinedField = $res['data']['product']['customfields'];
            }
        }
        else{
            if(!empty($supplierId)){
                $res = idcsmart_api_curl($supplierId,'api/v1/product/'.$param['id'],[],30,'GET');
            }else{
                $res = $this->upstreamRequest(rtrim($param['url'],'/').'/api/v1/product/'.$param['id'], [], 30, 'GET'); 
            }
            if(isset($res['data']['self_defined_field'])){
                $selfDefinedField = $res['data']['self_defined_field'];
            }
        }
        return ['data' => $res['data']['product'] ?? [], 'self_defined_field'=>$selfDefinedField];
    }

    /**
     * 时间 2023-02-13
     * @title 上游商品详情
     * @desc 上游商品详情
     * @author theworld
     * @version v1
     * @param int id - 商品ID required
     * @param string url - 上游地址 required
     * @param string type - 供应商类型default默认业务系统whmcs财务系统finance魔方财务 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return string data.module - 代理模块名称
     * @return string data.version - 代理模块版本 
     */
    public function upstreamProductDownloadResource($param)
    {
        $param['id'] = $param['id'] ?? 0;

        if($param['type']=='whmcs'){
            $res = $this->upstreamRequest(rtrim($param['url'],'/').'/modules/addons/idcsmart_reseller/logic/index.php?action=product_detail', ['productid' => $param['id']], 30, 'POST');
            $res['data'] = $res['data']['product'] ?? '';
            if(!empty($res['data'])){
                if(file_exists(WEB_ROOT."plugins/reserver/".$res['data']['module'].'/'.$res['data']['module'].'_version.txt')){
                    $version = file_get_contents(WEB_ROOT."plugins/reserver/".$res['data']['module'].'/'.$res['data']['module'].'_version.txt');
                    if(!version_compare($res['data']['version'], $version, '>')){
                        return ['status' => 200, 'msg' => lang('success_message'), 'data' => $res['data']];
                    }
                }

                $dir = WEB_ROOT.'plugins/reserver/'.$res['data']['module'].'.zip';
                $content = $this->curl_download(rtrim($param['url'],'/').'/modules/addons/idcsmart_reseller/'.$res['data']['module'].'.zip', $dir);
                if($content){
                    $file = WEB_ROOT."plugins/reserver/".$res['data']['module'];
                    $uuid = $res['data']['module'];
                    $type = 'reserver';
                    $result = $this->unzip($dir,$file);

                    if ($result['status'] == 200){
                        file_put_contents($file.'/'.$res['data']['module'].'_version.txt', $res['data']['version']);
                        unlink($dir);
                        return ['status' => 200, 'msg' => lang('success_message'), 'data' => $res['data']];
                    }else{
                        return ['status' => 400 , 'msg' => lang('file_unzip_failed', ['{code}' =>$result['msg'], '{file}' => $dir])];
                    }
                }else{
                    return ['status' => 400, 'msg' => lang('resource_download_failed')];
                }
            }else{
                return ['status' => 400, 'msg' => lang('upstream_product_resource_get_failed')];
            }
        }elseif ($param['type']=='finance'){
            $res = $this->upstreamRequest(rtrim($param['url'],'/').'/api/product/'.$param['id'].'/resource', [], 30, 'GET');
            $res['data'] = $res['data'] ?? [];
            if(!empty($res['data'])){
                if(file_exists(WEB_ROOT."plugins/reserver/".$res['data']['module'].'/'.$res['data']['module'].'_version.txt')){
                    $version = file_get_contents(WEB_ROOT."plugins/reserver/".$res['data']['module'].'/'.$res['data']['module'].'_version.txt');
                    if(!version_compare($res['data']['version'], $version, '>')){
                        return ['status' => 200, 'msg' => lang('success_message'), 'data' => $res['data']];
                    }
                }

                $dir = WEB_ROOT.'plugins/reserver/'.$res['data']['module'].'.zip';
                $content = $this->curl_download($res['data']['url'], $dir);
                if($content){
                    $file = WEB_ROOT."plugins/reserver/".$res['data']['module'];
                    $uuid = $res['data']['module'];
                    $type = 'reserver';
                    $result = $this->unzip($dir,$file);

                    if ($result['status'] == 200){
                        file_put_contents($file.'/'.$res['data']['module'].'_version.txt', $res['data']['version']);
                        unlink($dir);
                        return ['status' => 200, 'msg' => lang('success_message'), 'data' => $res['data']];
                    }else{
                        return ['status' => 400 , 'msg' => lang('file_unzip_failed', ['{code}' =>$result['msg'], '{file}' => $dir])];
                    }
                }else{
                    return ['status' => 400, 'msg' => lang('resource_download_failed')];
                }
            }else{
                return ['status' => 400, 'msg' => lang('upstream_product_resource_get_failed')];
            }
        }
        else{
            $res = $this->upstreamRequest(rtrim($param['url'],'/').'/api/v1/product/'.$param['id'].'/resource', [], 30, 'GET');
            $res['data'] = $res['data'] ?? [];
            if(!empty($res['data'])){
                if(file_exists(WEB_ROOT."plugins/reserver/".$res['data']['module'].'/'.$res['data']['module'].'_version.txt')){
                    $version = file_get_contents(WEB_ROOT."plugins/reserver/".$res['data']['module'].'/'.$res['data']['module'].'_version.txt');
                    if(!version_compare($res['data']['version'], $version, '>')){
                        return ['status' => 200, 'msg' => lang('success_message'), 'data' => $res['data']];
                    }
                }

                $dir = WEB_ROOT.'plugins/reserver/'.$res['data']['module'].'.zip';
                $content = $this->curl_download($res['data']['url'], $dir);
                if($content){
                    $file = WEB_ROOT."plugins/reserver/".$res['data']['module'];
                    $uuid = $res['data']['module'];
                    $type = 'reserver';
                    $result = $this->unzip($dir,$file);

                    if ($result['status'] == 200){
                        file_put_contents($file.'/'.$res['data']['module'].'_version.txt', $res['data']['version']);
                        unlink($dir);
                        return ['status' => 200, 'msg' => lang('success_message'), 'data' => $res['data']];
                    }else{
                        return ['status' => 400 , 'msg' => lang('file_unzip_failed', ['{code}' =>$result['msg'], '{file}' => $dir])];
                    }
                }else{
                    return ['status' => 400, 'msg' => lang('resource_download_failed')];
                }
            }else{
                return ['status' => 400, 'msg' => lang('upstream_product_resource_get_failed')];
            }
        }
    }

    /**
     * 时间 2023-02-13
     * @title 检查供应商接口连接状态
     * @desc 检查供应商接口连接状态
     * @author theworld
     * @version v1
     * @param string username - 上游api账号 required
     * @param string password - 上游api密钥 required
     * @param string url - 上游地址 required
     * @param string type - 供应商类型default默认业务系统whmcs财务系统finance魔方财务 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function upstreamApiAuth($param)
    {
        if($param['type']=='whmcs'){
            $res = $this->upstreamRequest(rtrim($param['url'],'/').'/modules/addons/idcsmart_reseller/logic/index.php?action=product_listing', [], 30, 'POST');
        } elseif ($param['type']=='finance'){
            $res = $this->upstreamRequest(rtrim($param['url'],'/').'/zjmf_api_login', $param, 30, 'POST');
        } else{
            $res = $this->upstreamRequest(rtrim($param['url'],'/').'/api/v1/auth', $param, 30, 'POST');
        }

        return $res;
    }

    /**
     * 时间 2023-02-15
     * @title 上游同步数据
     * @desc 上游同步数据
     * @author theworld
     * @version v1
     * @param int param.host_id - 产品ID
     * @param string param.data - 推送数据
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function syncHost($param)
    {
        $param['host_id'] = $param['host_id'] ?? 0;
        if(empty($param['host_id'])){
            return ['status' => 400, 'msg' => lang('fail_message')];
        }
        $HostModel = new HostModel();
        $host = $HostModel->find($param['host_id']);
        if(empty($host)){
            return ['status' => 400, 'msg' => lang('fail_message')];
        }

        $UpstreamHostModel = new UpstreamHostModel();
        $upstreamHost = $UpstreamHostModel->where('host_id', $param['host_id'])->find();
        if(empty($upstreamHost)){
            return ['status' => 400, 'msg' => lang('fail_message')];
        }

        $UpstreamProductModel = new UpstreamProductModel();
        $product = $UpstreamProductModel->where('product_id', $host['product_id'])->find();
        if(empty($product)){
            return ['status' => 400, 'msg' => lang('fail_message')];
        }

        $SupplierModel = new SupplierModel();
        $supplier = $SupplierModel->where('id', $product['supplier_id'])->find();
        $param['data'] = $this->rsaDecrypt($param['data'], aes_password_decode($supplier['secret']));
        $param['data'] = json_decode($param['data'], true);
        if(empty($param['data'])){
            return ['status' => 400, 'msg' => lang('fail_message')];
        }
        if($param['data']['action']=='module_create'){
            if($param['data']['host']['status']=='Active'){
                $HostModel->update([
                    'status'        => 'Active', 
                    'update_time'   => time()
                ], ['id' => $param['host_id']]);

                $UpstreamHostModel->update([
                    'upstream_host_id' => $param['data']['host']['id'],
                ], ['host_id' => $param['host_id']]);
                
            }else if($param['data']['host']['status']=='Failed'){
                $HostModel->update([
                    'status' => 'Failed', 
                    'update_time' => time()
                ], ['id' => $param['host_id']]);

                $UpstreamHostModel->update([
                    'upstream_host_id' => $param['data']['host']['id'],
                ], ['host_id' => $param['host_id']]);
                
            }

            // 同步IP信息
            if(isset($param['data']['host_ip'])){
                $HostIpModel = new HostIpModel();
                $HostIpModel->hostIpSave([
                    'host_id'       => $param['host_id'],
                    'dedicate_ip'   => $param['data']['host_ip']['dedicate_ip'],
                    'assign_ip'     => $param['data']['host_ip']['assign_ip'],
                ]);

                // 同步到标识
                if(!empty($param['data']['host_ip']['dedicate_ip']) && filter_var($param['data']['host_ip']['dedicate_ip'], FILTER_VALIDATE_IP)){
                    HostModel::where('id', $param['host_id'])->update(['name'=>$param['data']['host_ip']['dedicate_ip'] ]);
                }
            }
            upstream_sync_host($param['host_id'], 'module_create');
        }else if($param['data']['action']=='module_suspend'){
            if($param['data']['host']['status']=='Suspended'){
                $HostModel->update([
                    'status' => 'Suspended', 
                    'suspend_type' => 'upstream',
                    'suspend_reason' => '上游暂停',
                    'update_time' => time()
                ], ['id' => $param['host_id']]);
            }
            upstream_sync_host($param['host_id'], 'module_suspend');
        }else if($param['data']['action']=='module_unsuspend'){
            if($param['data']['host']['status']=='Active'){
                $HostModel->update([
                    'status' => 'Active', 
                    'suspend_type' => '',
                    'suspend_reason' => '',
                    'update_time' => time()
                ], ['id' => $param['host_id']]);
            }
            upstream_sync_host($param['host_id'], 'module_unsuspend');
        }else if($param['data']['action']=='module_terminate'){
            if($param['data']['host']['status']=='Deleted'){
                $HostModel->update([
                    'status' => 'Deleted', 
                    'update_time' => time()
                ], ['id' => $param['host_id']]);
            }
            upstream_sync_host($param['host_id'], 'module_terminate');
        }else if($param['data']['action']=='update_host'){
            if(in_array($param['data']['host']['status'], ['Active', 'Suspended', 'Deleted'])){
                $HostModel->update([
                    'status' => $param['data']['host']['status'], 
                    'update_time' => time()
                ], ['id' => $param['host_id']]);
            }
            // 同步IP信息
            if(isset($param['data']['host_ip'])){
                $HostIpModel = new HostIpModel();
                $HostIpModel->hostIpSave([
                    'host_id'       => $param['host_id'],
                    'dedicate_ip'   => $param['data']['host_ip']['dedicate_ip'],
                    'assign_ip'     => $param['data']['host_ip']['assign_ip'],
                ]);

                // 同步到标识
                if(!empty($param['data']['host_ip']['dedicate_ip']) && filter_var($param['data']['host_ip']['dedicate_ip'], FILTER_VALIDATE_IP)){
                    HostModel::where('id', $param['host_id'])->update(['name'=>$param['data']['host_ip']['dedicate_ip'] ]);
                }
            }
            upstream_sync_host($param['host_id'], 'update_host');
        }else if($param['data']['action']=='delete_host'){
            $HostModel->update([
                'status' => 'Deleted', 
                'update_time' => time()
            ], ['id' => $param['host_id']]);
            upstream_sync_host($param['host_id'], 'delete_host');
        }else if($param['data']['action']=='host_renew'){
            $HostModel->update([
                'status' => $param['data']['host']['status'], 
                'due_time' => $param['data']['host']['due_time'], 
                'update_time' => time()
            ], ['id' => $param['host_id']]);
            upstream_sync_host($param['host_id'], 'host_renew');
        }
        return ['status' => 200, 'msg' => lang('success_message')];
    }

    /**
     * 时间 2023-02-15
     * @title 接口请求
     * @desc 接口请求
     * @author theworld
     * @version v1
     * @param string url - 接口地址 required
     * @param object data - 请求数据 required
     * @param int timeout 30 超时时间
     * @param string request POST 请求方式GET,POST,PUT,DELETE
     * @param array header [] 头部参数
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function upstreamRequest($url, $data, $timeout = 30, $request = 'POST', $header = [])
    {
        $res = curl($url, $data, $timeout, $request, $header);

        if($res['http_code'] == 200){
            $result = json_decode($res['content'], true);
        }else{
            $result['status'] = 400;
            $result['msg'] = '请求失败,HTTP状态码:'.$res['http_code'];
        }
        return $result;
    }

    /**
     * 时间 2023-02-15
     * @title rsa解密
     * @desc rsa解密
     * @author theworld
     * @version v1
     * @param string encryptData - 加密数据 required
     * @param string rsaPrivateKey - 私钥 required
     * @return string - - 解密后数据
     */
    private function rsaDecrypt($encryptData, $rsaPrivateKey){
 
        $crypto = '';
 
        foreach (str_split(base64_decode($encryptData), 512) as $chunk) {
 
            openssl_private_decrypt($chunk, $decryptData, $rsaPrivateKey);
 
            $crypto .= $decryptData;
        }
 
        return $crypto;
    }

    /**
     * 时间 2023-02-15
     * @title zip解压
     * @desc zip解压
     * @author theworld
     * @version v1
     * @param string filepath - 文件路径 required
     * @param string path - 解压路径 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    private function unzip($filepath,$path)
    {
        $zip = new \ZipArchive();

        $res = $zip->open($filepath);
        if ( $res === true) {
            //解压文件到获得的路径a文件夹下
            if (!file_exists($path)){
                mkdir($path,0777,true);
            }
            $zip->extractTo($path);
            //关闭
            $zip->close();
            return ['status' => 200 , 'msg' => lang('success_message')];
        } else {
            return ['status' => 400 , 'msg' => $res];
        }
    }

    /**
     * 时间 2023-02-15
     * @title curl下载解压包到指定路径
     * @desc curl下载解压包到指定路径
     * @author theworld
     * @version v1
     * @param string url - 下载链接地址
     * @param string file_name - 目标路径
     * @return mixed
     */
    private function curl_download($url, $file_name)
    {
        $ch = curl_init($url);
        //设置抓取的url
        $dir = $file_name;
        $fp = fopen($dir, "wb");
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $res=curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return $res;
    }

    /**
     * 时间 2023-02-15
     * @title 验证签名
     * @desc 验证签名
     * @author theworld
     * @version v1
     * @param int params.id - 产品ID
     * @param string params.token - token
     * @param string params.rand_str - 随机字符串
     * @param string sign - 签名
     * @return bool
     */
    public function validateSign($params, $sign){
        // 用这几个参数生成签名
        $data = [
            'id'=>$params['id'],
            'token'=>$params['token'],
            'rand_str'=>$params['rand_str'],
        ];
        ksort($data, SORT_STRING);
        $str = json_encode($data);
        $signature = md5($str);
        return strtoupper($signature) === $sign;
    }
}