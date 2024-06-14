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
	// 商店地址
	public $market_url = 'https://my.idcsmart.com';

	/**
     * 时间 2022-10-25
     * @title 生成token
     * @desc 生成token
     * @author theworld
     * @version v1
     * @url /admin/v1/app_market/set_token
     * @method  POST
     * @return string market_url - 商店自动登录url
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
     * @param string license - 授权码
     */
	public function checkToken(){
		$param = $this->request->param();
		$token = $param['token'] ?? '';
		$license = $param['license'] ?? '';
		if($token==cache('market_token')){
			if(!empty($license)){
				updateConfiguration('system_license', $license);
			}
			$result = ['status'=>200, 'msg'=>lang('success_message'), 'data' => ['version' => configuration('system_version')]];
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
     * @return string list[].support_version - 支持的系统版本 
     * @return string list[].type - 应用分类addon插件,captcha验证码接口,certification实名接口,gateway支付接口,mail邮件接口,sms短信接口,server模块,template主题,oauth第三方登录,sub_server子模块,widget首页挂件,oss对象存储 
     * @return string list[].error_msg - 错误信息，该信息不为空代表不可下载和升级插件
     * @return int upgrade - 是否存在可升级应用0否1是 
     */
	public function getNewVersion(){
		$res = curl($this->market_url."/console/v1/app_market/market/version", ['request_time'=>time(), 'domain'=>request()->domain()], 30, 'GET');
		if($res['http_code'] == 200){
	        $result = json_decode($res['content'], true);
	    }else{
	        return json(['status'=>400, 'msg'=>lang('request_fail_http_code', ['{code}' =>$res['content']])]);
	    }
		if(isset($result['status']) && $result['status']==200){
			$upgrade = 0;
			$version = configuration('system_version');
			foreach ($result['data']['list'] as $key => $value) {
				if($value['type']=='template'){
					$file = WEB_ROOT;
				}else if($value['type']=='sub_server'){
					$file = WEB_ROOT."plugins/server/idcsmart_common/module";
				}else{
					$file = WEB_ROOT."plugins/".$value['type'];
				}
				if(in_array($value['type'], ['sub_server', 'template'])){
					if(file_exists($file.'/'.$value['uuid'].'_version.txt')){
						$old_version = file_get_contents($file.'/'.$value['uuid'].'_version.txt');
						$old_version = $old_version == '1.0' ? '1.0.0' : $old_version;
					}else{
						unset($result['data']['list'][$key]);
						continue;
					}
					//$old_version = file_exists($file.'/'.$value['uuid'].'_version.txt') ? file_get_contents($file.'/'.$value['uuid'].'_version.txt') : '1.0.0';
				}else{
					$PluginModel = new PluginModel();
					$old_version = $PluginModel->where('name', $value['uuid'])->value('version');
					$old_version = $old_version == '1.0' ? '1.0.0' : $old_version;
					if(empty($old_version)){
						unset($result['data']['list'][$key]);
						continue;
					}
				}
				$result['data']['list'][$key]['version'] = !empty($value['version']) ? str_replace('V', '', $value['version'])  : '1.0.0';
				$result['data']['list'][$key]['old_version'] = !empty($old_version) ? $old_version : '1.0.0';
				if(version_compare($result['data']['list'][$key]['version'],$old_version,'>')){
					$upgrade = 1;
				}
				$result['data']['list'][$key]['support_version'] = $value['support_version'] ?? '';
				$result['data']['list'][$key]['error_msg'] = '';
                if(isset($value['support_version']) && !empty($value['support_version'])){
                    if(version_compare($value['support_version'], $version, '>')){
                        $result['data']['list'][$key]['error_msg'] = lang('plugin_version_not_support_please_upgrade_system');
                    }
                }
			}
			$result['data']['list'] = array_values($result['data']['list']);
			$result['data']['upgrade'] = $upgrade;
		}else{
			$result = ['status'=>200, 'msg'=>lang('success_message'), 'data' => ['list' => [], 'upgrade' => 0]];
		}
		return json($result);
	}

	/**
     * 时间 2024-05-22
     * @title 获取主题最新版本
     * @desc 获取主题最新版本
     * @author theworld
     * @version v1
     * @url /admin/v1/app_market/template/:theme/version
     * @method  GET
     * @param string theme - 主题标识 required
     * @return string old_version - 主题当前版本 
     * @return string version - 主题最新版本 
     * @return string support_version - 支持的系统版本 
     * @return string description - 版本描述
     * @return int upgrade - 是否可升级0否1是 
     * @return string error_msg - 错误信息，该信息不为空代表不可下载和升级插件
     */
	public function getTemplateVersion(){
		$param = $this->request->param();

		$theme = $param['theme'];
		$uuid = parse_name($theme, 1);

		$res = curl($this->market_url."/console/v1/app_market/market/version", ['request_time'=>time(), 'domain'=>request()->domain()], 30, 'GET');
		if($res['http_code'] == 200){
	        $result = json_decode($res['content'], true);
	    }else{
	        return json(['status'=>400, 'msg'=>lang('request_fail_http_code', ['{code}' =>$res['content']])]);
	    }

	    $PluginModel = new PluginModel();
		$plugin = $PluginModel->where('module', 'template')->where('name', $uuid)->find();
		if(!empty($plugin)){
			$oldVersion = $plugin['version'];
			$oldVersion = $oldVersion == '1.0' ? '1.0.0' : $oldVersion;
		}else{
			if(file_exists(WEB_ROOT.'/'.$uuid.'_version.txt')){
				$oldVersion = file_get_contents(WEB_ROOT.'/'.$uuid.'_version.txt');
				$oldVersion = $oldVersion == '1.0' ? '1.0.0' : $oldVersion;
			}else{
				$oldVersion = '1.0.0';
			}
		}
		$version = '';
		$description = '';
		$upgrade = 0;
		$errorMsg = '';
		$supportVersion = '';

		if(isset($result['status']) && $result['status']==200){
			$systemVersion = configuration('system_version');
			foreach ($result['data']['list'] as $key => $value) {
				if($value['type']=='template' && $value['uuid']==$uuid){
					$version = !empty($value['version']) ? str_replace('V', '', $value['version'])  : '1.0.0';
					if(version_compare($version,$oldVersion,'>')){
						$upgrade = 1;
					}
					
					$supportVersion = $value['support_version'] ?? '';
					if(!empty($supportVersion)){
	                    if(version_compare($supportVersion, $systemVersion, '>')){
	                        $errorMsg = lang('plugin_version_not_support_please_upgrade_system');
	                    }
	                }

	                break;
				}
			}
		}

		$result = ['status'=>200, 'msg'=>lang('success_message'), 'data' => ['old_version' => $oldVersion, 'version' => $version, 'support_version' => $supportVersion, 'description' => $description, 'upgrade' => $upgrade, 'error_msg' => $errorMsg]];

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
     * @return string type - 应用分类addon插件,captcha验证码接口,certification实名接口,gateway支付接口,mail邮件接口,sms短信接口,server模块,template主题,oauth第三方登录,sub_server子模块,widget首页挂件,oss对象存储 
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

		$res = curl($this->market_url."/console/v1/app_market/market/download?id={$id}&from=".request()->domain().'/'.DIR_ADMIN.'&token='.$token.'&time='.time(), [], 30, 'GET');
		if($res['http_code'] == 200){
	        $res = json_decode($res['content'], true);
	    }else{
	        return json(['status'=>400, 'msg'=>lang('request_fail_http_code', ['{code}' =>$res['content']])]);
	    }
	    if(isset($res['status']) && $res['status']==200){
	    	$url = $res['data']['url'];
	    }else{
	    	return json(['status'=>400, 'msg'=>$res['msg'] ?? lang('app_download_fail')]);
	    }

    	if($result['data']['app']['type']=='template'){
    		
		    $dir = WEB_ROOT.$result['data']['app']['uuid'].'.zip';
		    $content = $this->curl_download($url, $dir);

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
    	}else if($result['data']['app']['type']=='sub_server'){
    		$dir = WEB_ROOT."plugins/server/idcsmart_common/module/".$result['data']['app']['uuid'].'.zip';
    		$content = $this->curl_download($url, $dir);
    		if($content){
    			
    			$file = WEB_ROOT."plugins/server/idcsmart_common/module";

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
    	}else{
    		$dir = WEB_ROOT."plugins/".$result['data']['app']['type'].'/'.$result['data']['app']['uuid'].'.zip';
    		$content = $this->curl_download($url, $dir);
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

	/**
     * 时间 2022-5-25
     * @title 解压压缩包
     * @desc 解压压缩包
     * @author theworld
     * @version v1
     * @param string filepath - 文件路径
     * @param string path - 解压目标路径
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
     * 时间 2022-5-25
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
}