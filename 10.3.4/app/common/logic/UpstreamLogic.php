<?php
namespace app\common\logic;

use app\common\model\UpstreamProductModel;
use app\common\model\UpstreamHostModel;
use app\common\model\SupplierModel;
use app\common\model\HostModel;

/**
 * @title 上游管理公共类
 * @desc 上游管理公共类
 * @use app\common\logic\UpstreamLogic
 */
class UpstreamLogic
{
    private $officialUrl = 'https://my.idcsmart.com';

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

    public function recommendProductDetail($param)
    {
        $param['id'] = $param['id'] ?? 0;

        $res = $this->upstreamRequest($this->officialUrl.'/console/v1/recommend/product/'.$param['id'], [], 30, 'GET');
        
        return ['data' => $res['data']['product'] ?? []];
    }

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

    public function upstreamProductDetail($param)
    {
        $param['id'] = $param['id'] ?? 0;

        if($param['type']=='whmcs'){
            $res = $this->upstreamRequest(rtrim($param['url'],'/').'/modules/addons/idcsmart_reseller/logic/index.php?action=product_detail', ['productid' => $param['id']], 30, 'POST');
        }elseif ($param['type']=='finance'){
            $res = $this->upstreamRequest(rtrim($param['url'],'/').'/api/product/'.$param['id'], [], 30, 'GET');
            if ($res['status']==200){
                $res['data']['product']['pay_type'] = ($res['data']['product']['pay_type']=='recurring')?'recurring_prepayment':$res['data']['product']['pay_type'];
            }
        }
        else{
            $res = $this->upstreamRequest(rtrim($param['url'],'/').'/api/v1/product/'.$param['id'], [], 30, 'GET');
        }
        

        return ['data' => $res['data']['product'] ?? []];
    }

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

    # 同步产品信息
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
                    'status' => 'Active', 
                    'update_time' => time()
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

    # 接口请求
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

    private function rsaDecrypt($encryptData, $rsaPrivateKey){
 
        $crypto = '';
 
        foreach (str_split(base64_decode($encryptData), 512) as $chunk) {
 
            openssl_private_decrypt($chunk, $decryptData, $rsaPrivateKey);
 
            $crypto .= $decryptData;
        }
 
        return $crypto;
    }

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

    /*
     * curl下载解压包到指定路径
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

    // 魔方财务签名
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