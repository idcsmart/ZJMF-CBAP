<?php
namespace app\admin\controller;
use app\admin\model\PluginModel;

/**
 * @title 应用商店自动化接口
 * @desc 应用商店自动化接口
 * @use app\admin\controller\AppMarketController
 */
class AppMarketController extends AdminBaseController
{

	public $market_url = 'https://my.idcsmart.com';

	/**
     * 时间 2022-10-25
     * @title 生成token
     * @desc 生成token
     * @author theworld
     * @version v1
     * @url /admin/v1/app_market/set_token
     * @method  POST
     * @return int bind - 是否绑定用户0否1是
     * @return string jwt - JWT
     */
	public function setToken(){
		if(cache('?market_token')){
			$token = cache('market_token');
		}else{
			$token = rand_str(12);
			cache('market_token', $token , 3600);
		}
		$result = ['status'=>200, 'market_url'=>$this->market_url.'/console/v1/app_market/market/token_login?from='.request()->domain().'/'.DIR_ADMIN.'&token='.$token.'&time='.time()];
		return json($result);
	}

	/**
     * 时间 2022-10-25
     * @title 校验token
     * @desc 校验token
     * @author theworld
     * @version v1
     * @url /admin/v1/app_market/check_token
     * @method  GET
     * @param string token - 验证token required
     */
	public function checkToken(){
		$param = $this->request->param();
		$token = $param['token'] ?? '';
		$license = $param['license'] ?? '';
		if($token==cache('market_token')){
			if(!empty($license)){
				updateConfiguration('system_license', $license);
			}
			$result = ['status'=>200, 'msg'=>lang('success_message')];
		}else{
			$result = ['status'=>400, 'msg'=>lang('fail_message')];
		}
		return json($result);
	}

	/**
     * 时间 2022-10-25
     * @title 获取已购买应用最新版本
     * @desc 获取已购买应用最新版本
     * @author theworld
     * @version v1
     * @url /admin/v1/app_market/app/version
     * @method  GET
     * @return array list - 应用列表
     * @return int list[].id - 应用ID 
     * @return string list[].uuid - 应用标识 
     * @return string list[].old_version - 应用当前版本 
     * @return string list[].version - 应用最新版本 
     * @return string list[].type - 应用分类addon插件,captcha验证码接口,certification实名接口,gateway支付接口,mail邮件接口,sms短信接口,server模块,template主题,oauth第三方登录 
     */
	public function getNewVersion(){
		$res = curl($this->market_url."/console/v1/app_market/market/version", ['request_time'=>time(), 'domain'=>request()->domain()], 30, 'GET');
		if($res['http_code'] == 200){
	        $result = json_decode($res['content'], true);
	    }else{
	        return json(['status'=>400, 'msg'=>lang('request_fail_http_code', ['{code}' =>$res['content']])]);
	    }
		if(isset($result['status']) && $result['status']==200){
			foreach ($result['data']['list'] as $key => $value) {
				if($value['type']=='template'){
					$file = WEB_ROOT;
				}else{
					$file = WEB_ROOT."plugins/".$value['type'];
				}
				if(in_array($value['type'], ['server', 'template'])){
					$old_version = file_exists($file.'/'.$value['uuid'].'_version.txt') ? file_get_contents($file.'/'.$value['uuid'].'_version.txt') : '1.0.0';
				}else{
					$PluginModel = new PluginModel();
					$old_version = $PluginModel->where('name', $value['uuid'])->value('version');
					$old_version = !empty($old_version) ? $old_version : '1.0.0';
				}
				$result['data']['list'][$key]['old_version'] = !empty($old_version) ? $old_version : '1.0.0';
				$result['data']['list'][$key]['version'] = !empty($value['version']) ? str_replace('V', '', $value['version'])  : '1.0.0';
			}
		}else{
			$result = ['status'=>400, 'msg'=>lang('fail_message')];
		}
		return json($result);
	}

	/**
     * 时间 2022-10-25
     * @title 安装应用
     * @desc 安装应用
     * @author theworld
     * @version v1
     * @url /admin/v1/app_market/app/:id/install
     * @method  GET
     * @param int id - 应用ID required
     */
	public function install(){
		$param = $this->request->param();
		$id = $param['id'];
		if(!extension_loaded('ionCube Loader')){
			return json(['status'=>400, 'msg'=>lang('not_install_ioncube')]);
		}

        $res = curl($this->market_url."/console/v1/app_market/market/app/".$id, ['request_time'=>time()], 30, 'GET');
        if($res['http_code'] == 200){
	        $result = json_decode($res['content'], true);
	    }else{
	        return json(['status'=>400, 'msg'=>lang('request_fail_http_code', ['{code}' =>$res['content']])]);
	    }
	    if(cache('?market_token')){
			$token = cache('market_token');
		}else{
			$token = rand_str(12);
			cache('market_token', $token , 3600);
		}
    	if($result['data']['app']['type']=='template'){

			$dir = WEB_ROOT.$result['data']['app']['uuid'].'.zip';

    		$content = $this->curl_download($this->market_url."/console/v1/app_market/market/download?id={$id}&from=".request()->domain().'/'.DIR_ADMIN.'&token='.$token.'&time='.time(), $dir);

    		if($content){

    			$file = WEB_ROOT;

    			$version = str_replace('V', '', $result['data']['app']['last_version']);
    			$uuid = $result['data']['app']['uuid'];
    			$type = $result['data']['app']['type'];
    			$res = $this->unzip($dir,$file);

	            if ($res['status'] == 200){
	            	file_put_contents($file.'/'.$uuid.'_version.txt', $version);
	            	unlink($dir);

	                return json(['status' => 200 , 'msg' => lang('app_install_success'), 'data' => ['type' => $type]]);
	            }else{

	                return json(['status' => 400 , 'msg' => lang('app_unzip_fail', ['{code}' =>$res['msg'], '{file}' => $dir])]);
	            }
    		}else{

    			return json(['status' => 400, 'msg' => lang('app_download_fail')]);
    		}
    	}else{
    		$dir = WEB_ROOT."plugins/".$result['data']['app']['type'].'/'.$result['data']['app']['uuid'].'.zip';
    		$content = $this->curl_download($this->market_url."/console/v1/app_market/market/download?id={$id}&from=".request()->domain().'/'.DIR_ADMIN.'&token='.$token.'&time='.time(), $dir);
    		if($content){

    			$file = WEB_ROOT."plugins/".$result['data']['app']['type'];

    			$version = str_replace('V', '', $result['data']['app']['last_version']);
    			$uuid = $result['data']['app']['uuid'];
    			$type = $result['data']['app']['type'];
    			$res = $this->unzip($dir,$file);

	            if ($res['status'] == 200){
	            	if(!file_exists($file.'/'.$uuid.'_version.txt')) file_put_contents($file.'/'.$uuid.'_version.txt', $version);
	            	unlink($dir);
	                return json(['status' => 200 , 'msg' => lang('app_install_success'), 'data' => ['type' => $type]]);
	            }else{
	                return json(['status' => 400 , 'msg' => lang('app_unzip_fail', ['{code}' =>$res['msg'], '{file}' => $dir])]);
	            }
    		}else{
    			return json(['status' => 400, 'msg' => lang('app_download_fail')]);
    		}
    	}
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
}