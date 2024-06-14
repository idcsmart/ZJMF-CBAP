<?php

use app\common\model\ConfigurationModel;
use think\facade\Cache;
use app\admin\model\PluginModel;
use app\common\model\SystemLogModel;
use app\common\model\OrderTmpModel;
use app\common\model\ClientCreditModel;
use app\common\model\ClientModel;
use think\facade\Event;
use app\common\model\TaskWaitModel;
use app\common\model\NoticeSettingModel;
use app\common\model\FileLogModel;
use app\admin\model\AdminModel;
use app\home\model\OauthModel;

/**
 * 时间 2024-02-07
 * @title 读取目录下所有文件(除.和..)并放入files数组
 * @desc  读取目录下所有文件(除.和..)并放入files数组
 * @author wyh
 * @version v1
 * @param string dir - 目录 require
 * @param array files - 文件数组
 * @return array files - 文件
 */
function read_dir($dir = '', $files = []){
    if(!is_dir($dir)){
        return $files;
    }
    $handle = opendir($dir);
    if ($handle) {//目录打开正常
        while(($file = readdir($handle)) !== false){
            if($file != "." && $file != ".."){
                if(!is_dir("$dir/$file")){
                    $files[]="$dir/$file";
                }else{
                    $files = read_dir("$dir/$file", $files);
                }
            }
        }
        closedir($handle);
    }
    return $files;
}

/**
 * 时间 2024-02-07
 * @title 格式化打印
 * @desc  格式化打印
 * @author wyh
 * @version v1
 * @param array input - 需要打印的字符串数组 require
 */
function format_print(...$input)
{
    echo "<pre>";
    var_dump($input);die;
}

/**
 * @title 后台获取当前登录管理员ID
 * @desc 后台获取当前登录管理员ID
 * @author wyh
 * @version v1
 * @return int
 */
function get_admin_id()
{
    return intval(request()->admin_id);
}

/**
 * @title 前台获取当前登录用户ID
 * @desc 前台获取当前登录用户ID
 * @author wyh
 * @version v1
 * @return int
 */
function get_client_id($origin = true)
{
    if($origin===true){
        $result = hook('get_client_parent_id',['client_id'=>request()->client_id]);

        foreach ($result as $value){
            if ($value){
                return (int)$value;
            }
        }
        
        return intval(request()->client_id);
    }else{
        return intval(request()->client_id);
    }
    
}

/**
 * @title 获取请求头的jwt
 * @desc 获取请求头的jwt
 * @author wyh
 * @version v1
 * @return string
 */
function get_header_jwt()
{
    $header = request()->header();
    $jwt = '';
    if(isset($header['authorization'])){
        $jwt = count(explode(' ',$header['authorization']))>1?explode(' ',$header['authorization'])[1]:'';
    }
    return $jwt;
}

/**
 * @title 生成jwt
 * @desc 生成jwt
 * @author wyh
 * @version v1
 * @param array info - 基础信息,如['id'=>1,'name'=>'wyh']
 * @param int expire - 过期时间,单位秒(s),默认7200s
 * @param bool is_admin - 是否后台创建
 * @return string
 */
function create_jwt($info, $expire = 7200, $is_admin=false)
{
    # jwt的签发密钥，验证token的时候需要用到,此密钥通用,未采用存数据库方式动态生成!有一定的非安全性
    if ($is_admin){
        $key = config('idcsmart.jwt_key_admin') . AUTHCODE;
    }else{
        $key = config('idcsmart.jwt_key_client') . AUTHCODE;
    }
    # jwt的签发密钥存数据库,因ip以及客户端问题,以及后台以用户登录产生问题,此方法搁置
    /*if ($is_admin){
        $AdminLoginModel = new AdminLoginModel();
        $key = $AdminLoginModel->getJwtKey($info['id']);
    }else{
        $ClientLoginModel = new ClientLoginModel();
        $key = $ClientLoginModel->getJwtKey($info['id']);
    }*/

    $time = time();

    $token = array(
        "info" => $info,
        "iss" => "www.idcsmart.com", # 签发组织
        "aud" => "www.idcsmart.com", # 接收该JWT的一方
        "ip" => get_client_ip(),
        "iat" => $time, # 签发时间
        "nbf" => $time, # not before，如果当前时间在nbf里的时间之前，则Token不被接受；一般都会留一些余地，比如几分钟。
        "exp" => $time + $expire, # expire 指定token的生命周期
    );

    $jwt = Firebase\JWT\JWT::encode($token, $key, 'HS256');

    $key = 'login_token_'.$jwt;
    Cache::set($key,$info['id'],$expire);

    return $jwt;
}

/**
 * @title 添加钩子
 * @desc 添加钩子
 * @author wyh
 * @version v1
 * @param string hook - 钩子名称
 * @param mixed  params - 传入参数
 * @return mixed
 */
function hook($hook,$params=null)
{
    return Event::trigger($hook ,$params);
}

/**
 * @title 添加钩子,只执行一个
 * @desc 添加钩子,只执行一个
 * @author wyh
 * @version v1
 * @param string hook - 钩子名称
 * @param mixed  params - 传入参数
 * @return mixed
 */
function hook_one($hook,$params=null)
{
    return Event::trigger($hook ,$params,true);
}

/**
 * @title 监听钩子
 * @desc 监听钩子
 * @author wyh
 * @version v1
 * @param string hook - 钩子名称
 * @param mixed  fun - 执行方法
 * @return mixed
 */
function add_hook($hook,$fun)
{
    return Event::listen($hook,$fun);
}

/**
* @title 内部调用API
* @desc 内部调用API
* @author xiong
* @version v1
* @param string $cmd - 调用API名称 require
* @param array $data - 传入的参数
* @return array
*/
function local_api($cmd,$data=[]){
	list($project,$module,$action) = explode("_",$cmd);
	$http_app = app('http')->getName();
	if($http_app==$project && strtolower(request()->controller())==strtolower($module) && strtolower(request()->action())==strtolower($action)){
		return ['status' => 400, 'msg' => lang('fail_message')];
	}
	request()->page = isset($data['page']) ? intval($data['page']):config('idcsmart.page');
    request()->limit = isset($data['limit']) ? intval($data['limit']):config('idcsmart.limit');
    request()->sort = isset($data['sort']) ? intval($data['sort']):config('idcsmart.sort');
    $class = "\app\\{$project}\\controller\\{$module}Controller";
	if (!class_exists($class)) {
		return ['status' => 400, 'msg' => lang('fail_message')];
	}
	request()->local_api_data = $data;
	$cls = new $class( app() );
	$cls_methods = get_class_methods($cls);
	if(!in_array($action,$cls_methods)){
		return ['status' => 400, 'msg' => lang('fail_message')];
	}
	$result = $cls->$action()->getData();
	return $result;
}

/**
 * @title 调用插件API
 * @desc 代码内部调用插件API
 * @author wyh
 * @version v1
 * @param string addon - 插件 require
 * @param string controller - 控制器前缀 require
 * @param string action - 方法 require
 * @param array param - 传入的参数
 * @param boolean admin - 是否后台
 * @return array
 */
function plugin_api($addon,$controller,$action,$param=[],$admin=false)
{
    $addon = parse_name($addon);

    $controller = ucwords($controller);
    if ($admin){
        $class = "addon\\{$addon}\\controller\\{$controller}Controller";
    }else{
        $class = "addon\\{$addon}\\controller\\clientarea\\{$controller}Controller";
    }

    if (!class_exists($class)){
        return [];
    }

    # 追加默认参数
    $request = request();
    $request->local_api_data = $param;

    $request->page = isset($param['page']) ? intval($param['page']):config('idcsmart.page');
    $request->limit = isset($param['limit']) ? intval($param['limit']):config('idcsmart.limit');
    $request->sort = isset($param['sort']) ? intval($param['sort']):config('idcsmart.sort');

    $result = app('app')->invoke([$class,$action],[$param]);

    return $result->getData();
}

/**
 * @title 调用代理模块API
 * @desc 代码内部调用代理模块API
 * @author theworld
 * @version v1
 * @param string reserver - 代理模块 require
 * @param string controller - 控制器前缀 require
 * @param string action - 方法 require
 * @param array param - 传入的参数
 * @param boolean admin - 是否后台
 * @return array
 */
function reserver_api($reserver,$controller,$action,$param=[],$admin=false)
{
    $reserver = parse_name($reserver);

    $controller = ucwords($controller);
    if ($admin){
        $class = "reserver\\{$reserver}\\controller\\admin\\{$controller}Controller";
    }else{
        $class = "reserver\\{$reserver}\\controller\\home\\{$controller}Controller";
    }

    if (!class_exists($class)){
        return [];
    }

    # 追加默认参数
    $request = request();
    $request->local_api_data = $param;

    $request->page = isset($param['page']) ? intval($param['page']):config('idcsmart.page');
    $request->limit = isset($param['limit']) ? intval($param['limit']):config('idcsmart.limit');
    $request->sort = isset($param['sort']) ? intval($param['sort']):config('idcsmart.sort');

    $result = app('app')->invoke([$class,$action],[$param]);

    return $result->getData();
}

/**
* @title 获取语言列表
* @desc 获取语言列表
* @author xiong
* @version v1
* @param string app admin 应用名称,只有admin和home这两个值
* @return array
* @return string [].display_name - 语言名称
* @return string [].display_flag - 国家代码
* @return string [].display_lang - 语言标识
*/
function lang_list($app = 'admin')
{
	if($app == 'admin') $app = DIR_ADMIN;
	if($app == 'home') $app = 'clientarea';
	$path= public_path() .'/'. $app .'/language';
	if(!file_exists($path))	return [];
	$handler = opendir($path);//当前目录中的文件夹下的文件夹
	$lang_data_now_all = [];
	while (($filename = readdir($handler)) !== false) {
	   if ($filename != "." && $filename != ".." ) {
			if(strpos($filename,".php")===false) continue;
			$_LANG=include $path."/".$filename;
			if(empty($_LANG['display_name'])) continue;
			$lang_data_now['display_name'] = $_LANG['display_name'];
			$lang_data_now['display_flag'] = $_LANG['display_flag'];
			$lang_data_now['display_img'] = '/upload/common/country/'.$_LANG['display_flag'].'.png';
			$lang_data_now['display_lang'] = str_replace(".php","",$filename);
			$lang_data_now_all[] = $lang_data_now;
			unset($_LANG);
		}
	}
	closedir($handler);
    return $lang_data_now_all;
}
/**
* @title 获取语言
* @desc 获取语言
* @author xiong
* @version v1
* @param string name - 名称
* @param array param - 要替换语言中的参数
* @return string
*/
function lang($name = '', $param = [])
{
	$defaultLang = config('lang.default_lang');
    if(!empty(get_client_id())){
        $defaultLang = get_client_lang();
    }else{
        $defaultLang = get_system_lang(true);
    }
    
	$langAdmin = include WEB_ROOT.'/'.DIR_ADMIN.'/language/'. $defaultLang .'.php';
	$langHome = include WEB_ROOT.'/clientarea/language/'. $defaultLang .'.php';
	$lang = array_merge($langAdmin, $langHome);
	if(empty($name)){
		return $lang;
	}else if(empty($lang[$name])){
		return $name;
	}else{
		$language = $lang[$name];
		foreach($param as $k => $v){
			$language = str_replace($k, $v , $language);
		}
		return $language;
	}
}

/**
 * @title 获取插件语言
 * @desc 获取插件语言
 * @author xiong
 * @version v1
 * @param string name - 名称
 * @param array param - 要替换语言中的参数
 * @return string
 */
function lang_plugins($name = '', $param = [], $reload = false)
{
    #$currentAddon = request()->param('_plugin')??'';
    #$name = $currentAddon?$currentAddon . '_' . $name:$name;
    $defaultLang = config('lang.default_lang');
    if(!empty(get_client_id())){
        $defaultLang = get_client_lang();
    }else{
        $app = app('http')->getName();
        if ($app=='home'){
            $defaultLang = get_client_lang();
        }else{
            $defaultLang = get_system_lang(true);
        }
    }
    $cacheName = 'pluginLang_'.$defaultLang;
    $lang = Cache::get($cacheName);
    if(!empty($lang) && $reload===false){
        $lang = json_decode($lang, true);
    }else{
        $lang = [];
        # 加载插件多语言(wyh 20220616 改:涉及到一个插件需要调另一个插件以及系统调插件钩子的情况,所以只有加载所有已安装使用插件的多语言)
        $addonDir = WEB_ROOT . 'plugins/addon/';
        $addons = array_map('basename', glob($addonDir . '*', GLOB_ONLYDIR));
        $PluginModel = new PluginModel();
        foreach ($addons as $addon){
            $parseName = parse_name($addon,1);
            # 说明:存在一定的安全性,判断是否安装且启用的插件
            $plugin = $PluginModel->where('name',$parseName)
                //->where('status',1)
                ->find();
            if (!empty($plugin) && is_file($addonDir . $addon . "/lang/{$defaultLang}.php")){
                $pluginLang = include $addonDir . $addon . "/lang/{$defaultLang}.php";
                $lang = array_merge($lang,$pluginLang);
            }
        }
        # 加载模块多语言
        $serverDir = WEB_ROOT . 'plugins/server/';
        $servers = array_map('basename', glob($serverDir . '*', GLOB_ONLYDIR));
        foreach ($servers as $server){
            if (is_file($serverDir . $server . "/lang/{$defaultLang}.php")){
                $pluginLang = include $serverDir . $server . "/lang/{$defaultLang}.php";
                $lang = array_merge($lang,$pluginLang);
            }
        }

        # 加载模块多语言
        $reserverDir = WEB_ROOT . 'plugins/reserver/';
        $servers = array_map('basename', glob($reserverDir . '*', GLOB_ONLYDIR));
        foreach ($servers as $server){
            if (is_file($reserverDir . $server . "/lang/{$defaultLang}.php")){
                $pluginLang = include $reserverDir . $server . "/lang/{$defaultLang}.php";
                $lang = array_merge($lang,$pluginLang);
            }
        }

        # 加载模板控制器多语言
        $templateDir = WEB_ROOT . 'web/';
        $templates = array_map('basename', glob($templateDir . '*', GLOB_ONLYDIR));
        foreach ($templates as $template){
            if (is_file($templateDir . $template . "/controller/lang/{$defaultLang}.php")){
                $pluginLang = include $templateDir . $template . "/controller/lang/{$defaultLang}.php";
                $lang = array_merge($lang,$pluginLang);
            }
        }
        Cache::set($cacheName, json_encode($lang), 24*3600);
    }

    if(empty($name)){
        return $lang;
    }else if(!isset($lang[$name])){
        return $name;
    }else{
        $language = $lang[$name];
        foreach($param as $k => $v){
            $language = str_replace($k, $v , $language);
        }
        return $language;
    }
}

/**
 * @title 获取系统使用语言
 * @desc 获取系统使用语言,分前后台
 * @author wyh
 * @version v1
 * @param string is_admin - 是否后台:true是
 * @return array
 */
function get_system_lang($is_admin=true)
{
    $header = request()->header();
    $langAdmin = lang_list('admin');
    $langHome = lang_list('home');
    $lang = array_merge(array_column($langAdmin, 'display_lang'), array_column($langHome, 'display_lang'));
    if(isset($header['language']) && !empty($header['language']) && in_array($header['language'], $lang)){
        $lang = $header['language'];
    }else if ($is_admin){
        $lang = configuration('lang_admin');
    }else{
        $lang = configuration('lang_home');
    }
    return $lang;
}

/**
 * @title 获取客户使用语言
 * @desc 获取客户使用语言
 * @author wyh
 * @version v1
 * @return string
 */
function get_client_lang()
{
    $ClientModel = new ClientModel();
    $client_id = get_client_id();
    $client = $ClientModel->find($client_id);
    if(!empty($client)){
        $language = !empty($client['language']) ? $client['language'] : get_system_lang(false);
    }else{
        $language = cookie("web_language")??get_system_lang(false);
    }
    return $language;
}

/**
* @title CURL
* @desc 公共curl
* @author xiong
* @version v1
* @param string url - url地址 require
* @param array data [] 传递的参数
* @param string timeout 30 超时时间
* @param string request POST 请求类型
* @param array header [] 头部参数
* @return int http_code - http状态码
* @return string error - 错误信息
* @return string content - 内容
*/
function curl($url, $data = [], $timeout = 30, $request = 'POST', $header = [])
{
    $curl = curl_init();
    $request = strtoupper($request);

    if($request == 'GET'){
        $s = '';
        if(!empty($data)){
            foreach($data as $k=>$v){
                if($v === ''){
                    $data[$k] = '';
                }
            }
            $s = http_build_query($data);
        }
        if(strpos($url, '?') !== false){
            if($s){
                $s = '&'.$s;
            }
        }else{
            if($s){
                $s = '?'.$s;
            }
        }
        curl_setopt($curl, CURLOPT_URL, $url.$s);
    }else{
        curl_setopt($curl, CURLOPT_URL, $url);
    }
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_REFERER, request() ->host());
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    if($request == 'GET'){
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPGET, 1);
    }
    if($request == 'POST'){
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        if(is_array($data)){
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        }else{
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
    }
    if($request == 'PUT' || $request == 'DELETE'){
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $request);
        if(is_array($data)){
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        }else{
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
    }
    if(!empty($header)){
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    }
    $content = curl_exec($curl);
    $error = curl_error($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
	return ['http_code'=>$http_code, 'error'=>$error , 'content' => $content];
}

/**
 * @title 密码加密
 * @desc 前后台登录密码加密方式
 * @author wyh
 * @version v1
 * @param string pw - 密码 require
 * @param string authCode - 系统唯一身份验证字符
 * @return string
 */
function idcsmart_password($pw, $authCode = '')
{
    error_reporting(0);
    if (defined('IS_ZKEYS') && IS_ZKEYS){ # 兼容zkeys迁移密码
        $result = md5($authCode . $pw);
        //$result = md5(htmlspecialchars($pw));
    }else{
        if (is_null($pw)){
            return '';
        }

        if (empty($authCode)) {
            $authCode = AUTHCODE;
        }

        $result = "###" . md5(md5($authCode . $pw));
    }

    return $result;
}

// 这个不开放出去
function idcsmart_password_zkeys($pw,$authCode="")
{
    $result = md5($authCode . $pw);
    return $result;
}

/**
 * @title 密码比较
 * @desc 密码比较,正确返回true
 * @author wyh
 * @version v1
 * @param string password - 密码 require
 * @param string passwordInDb - 密码 require
 * @return bool
 */
function idcsmart_password_compare($password, $passwordInDb)
{
    // zkeys加密方式更改，兼容zkeys最新的两种加密方式，以及V10加密 20231110
    if (defined('IS_ZKEYS') && IS_ZKEYS){
        return (idcsmart_password($password,"http://www.niaoyun.com/") == $passwordInDb) ||
            (idcsmart_password(htmlspecialchars($password)) == $passwordInDb) ||
            (idcsmart_password_zkeys($password,'http://www.niaoyun.com/') == $passwordInDb) ||
            (idcsmart_password_zkeys($password) == $passwordInDb);
    }

    return idcsmart_password($password) == $passwordInDb;
}


/**
 * @title 对称加密
 * @desc 对称加密
 * @author wyh
 * @version v1
 * @param string data - 加密数据 required
 * @return string
 */
function aes_password_encode($data){
    $key = md5('idcsmart');
    $v = substr($key,0,8);
    $result = openssl_encrypt($data, 'DES-CBC', $key, OPENSSL_RAW_DATA, $v);
    return base64_encode($result);
}

/**
 * @title 解密
 * @desc 解密:aes_password_encode方法解密
 * @author wyh
 * @version v1
 * @param string data - 加密数据 required
 * @return string
 */
function aes_password_decode($data){
    $data = base64_decode($data);
    $key = md5('idcsmart');
    $v = substr($key,0,8);
    $result = openssl_decrypt($data, 'DES-CBC', $key, OPENSSL_RAW_DATA, $v);
    return $result;
}

/**
 * @title 金额格式化
 * @desc 金额格式化,返回保留两位小数的金额
 * @author theworld
 * @version v1
 * @param float amount - 金额 require
 * @return string
 */
function amount_format($amount){
    $amount = (float)bcdiv($amount, 1, 2);
    return number_format($amount, 2, ".", "");
}

/**
 * @title 获取客户端IP地址
 * @desc 获取客户端IP地址
 * @author wyh
 * @version v1
 * @param int type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param bool adv  是否进行高级模式获取（有可能被伪装）
 * @return string
 */
function get_client_ip($type = 0, $adv = true)
{
    if(getenv('HTTP_X_FORWARDED_FOR')){
        return getenv('HTTP_X_FORWARDED_FOR');
    }else{
        return request()->ip($type, $adv);
    }
}

/**
 * @title 获取插件类名
 * @desc 获取插件类名
 * @author wyh
 * @version v1
 * @param string name 插件名
 * @param string module 模块目录
 * @return string
 */
function get_plugin_class($name, $module)
{
    $name = ucwords($name);
    $pluginDir = parse_name($name);
    if($module=='template'){
        $class = "{$module}\\{$pluginDir}\\controller\\{$name}";
    }else{
        $class = "{$module}\\{$pluginDir}\\{$name}";
    }
    
    return $class;
}

/**
 * @title 编码图片base64格式
 * @desc 编码图片base64格式
 * @author wyh
 * @version v1
 * @param string image_file 图片地址
 * @return string
 */
function base64_encode_image($image_file)
{
    $base64_image = null;
    $image_info = getimagesize($image_file);
    $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
    if (!isset($image_data[0])){
        return '';
    }
    $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
    return $base64_image;
}

/**
 * @title base64_decode_image($base64_image_content,$path):base64格式编码转换为图片并保存对应文件夹
 * @desc base64_decode_image($base64_image_content,$path):base64格式编码转换为图片并保存对应文件夹
 * @author wyh
 * @version v1
 * @param string base64_image_content base64
 * @param string path 保存路径
 * @return string
 */
function base64_decode_image($base64_image_content,$path)
{
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
        $type = $result[2];
        $new_file = $path;
        if(!file_exists($new_file)){
            mkdir($new_file, 0700);
        }
        $image = md5(uniqid()).time().".{$type}";
        $new_file = $new_file.$image;
        if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
            return $image;
        }else{
            return false;
        }
    }else{
        return false;
    }
}

/**
 * @title 支付接口
 * @desc 支付接口
 * @author wyh
 * @version v1
 * @return array list - 支付接口
 * @return int list[].id - ID
 * @return string list[].title - 名称
 * @return string list[].name - 标识
 * @return string list[].url - 图片:base64格式
 * @return int count - 总数
 */
function gateway_list()
{
    $PluginModel = new PluginModel();

    $gateways = $PluginModel->plugins('gateway');

    return $gateways;
}

/**
 * @title 验证支付接口
 * @desc 验证支付接口
 * @author wyh
 * @version v1
 * @param string WxPay 支付插件标识
 * @return bool
 */
function check_gateway($gateway)
{
    $PluginModel = new PluginModel();

    return $PluginModel->checkPlugin($gateway,'gateway');
}

/**
 * @title 获取系统配置
 * @desc 获取系统配置
 * @author wyh
 * @version v1
 * @param string|array setting 配置项键
 * @return mixed|array
 */
function configuration($setting)
{
    if (!is_array($setting)){
        $setting = [$setting];
    }

    $array = [];

    $ConfigurationModel = new ConfigurationModel();
    $configurations = $ConfigurationModel->index();
    foreach ($configurations as $configuration){
        foreach ($setting as $v){
            if ($v == $configuration['setting']){
                $array[$v] = $configuration['value'];
            }
            if (!isset($array[$v])){
                $array[$v] = '';
            }
        }
    }

    return count($setting)==1?$array[$setting[0]]:$array;
}

/**
 * @title 保存系统配置
 * @desc 保存系统配置
 * @author wyh
 * @version v1
 * @param string setting 配置项键
 * @param string value 值
 * @return boolean
 */
function updateConfiguration($setting,$value)
{
    $ConfigurationModel = new ConfigurationModel();
    $ConfigurationModel->saveConfiguration(['setting' => $setting, 'value' => $value]);
    return true;
}

/**
 * @title 检查手机格式
 * @desc 检查手机格式,中国手机不带国际电话区号,国际手机号格式为:国际电话区号-手机号
 * @author theworld
 * @version v1
 * @param string mobile 手机号
 * @return boolean
 */
function check_mobile($mobile)
{
    if (preg_match('/(^(13\d|14\d|15\d|16\d|17\d|18\d|19\d)\d{8})$/', $mobile)) {
        return true;
    } else {
        if (preg_match('/^\d{1,4}-\d{1,11}$/', $mobile)) {
            if (preg_match('/^\d{1,4}-0+/', $mobile)) {
                //不能以0开头
                return false;
            }

            return true;
        }

        return false;
    }
}

/**
 * @title 获取图形验证码
 * @desc 获取图形验证码
 * @author wyh
 * @version v1
 * @param boolean is_admin false 是否后台
 * @return string
 */
function get_captcha($is_admin=false)
{
    $captchaPlugin = configuration('captcha_plugin')??'TpCaptcha';

    $html = plugin_reflection($captchaPlugin,[],'captcha',$is_admin?'describe_admin':'describe');

    return $html;
}

/**
 * @title 验证图形验证码
 * @desc 验证图形验证码
 * @author wyh
 * @version v1
 * @param string captcha 12345 验证码
 * @param string token d7e57706218451cbb23c19cfce583fef 验证码唯一识别码
 * @return boolean
 */
function check_captcha($captcha,$token,$base=false)
{
    $data = [
        'captcha' => $captcha,
        'token' => $token,
        'base' => $base
    ];

    $captchaPlugin = configuration('captcha_plugin')??'TpCaptcha';

    $result = plugin_reflection($captchaPlugin,$data,'captcha','verify');

    if ($result['status']==200){
        return true;
    }else{
        return false;
    }
}

/**
 * @title 生成随机字符
 * @desc 生成随机字符
 * @author wyh
 * @version v1
 * @param int len 8 长度
 * @param string format ALL 格式,ALL大小写字母加数字,CHAR大小写字母,NUMBER数字
 * @return string
 */
function rand_str($len=8,$format='ALL'){
    $is_abc = $is_numer = 0;
    $password = $tmp ='';
    switch($format){
        case 'ALL':
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            break;
        case 'CHAR':
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            break;
        case 'NUMBER':
            $chars='0123456789';
            break;
        default :
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            break;
    }
    //mt_srand((double)microtime()*1000000*getmypid());
    while(strlen($password)<$len){
        $tmp =substr($chars,(mt_rand()%strlen($chars)),1);
        if(($is_numer <> 1 && is_numeric($tmp) && $tmp > 0 )|| $format == 'CHAR'){
            $is_numer = 1;
        }
        if(($is_abc <> 1 && preg_match('/[a-zA-Z]/',$tmp)) || $format == 'NUMBER'){
            $is_abc = 1;
        }
        $password.= $tmp;
    }
    if($is_numer <> 1 || $is_abc <> 1 || empty($password) ){
        $password = rand_str($len,$format);
    }

    return $password;
}

/**
 * @title 隐藏部分字符串
 * @desc 隐藏部分字符串
 * @author theworld
 * @version v1
 * @param string str - 需要隐藏的字符串
 * @param string replacement * 隐藏后显示的字符
 * @param int start 1 起始位置
 * @param int length 3 隐藏长度
 * @return string
 */
function hide_str($str, $replacement = '*', $start = 1, $length = 3)
{
    $len = mb_strlen($str,'utf-8');
    if ($len > intval($start+$length)) {
        $str1 = mb_substr($str, 0, $start, 'utf-8');
        $str2 = mb_substr($str, intval($start+$length), NULL, 'utf-8');
    } else {
        $str1 = mb_substr($str, 0, 1, 'utf-8');
        $str2 = mb_substr($str, $len-1, 1, 'utf-8');
        $length = $len - 2;
    }
    $newStr = $str1;
    for ($i = 0; $i < $length; $i++) {
        $newStr .= $replacement;
    }
    $newStr .= $str2;

    return $newStr;
}

/**
 * @title 临时订单ID生成
 * @desc 临时订单ID生成
 * @author wyh
 * @version v1
 * @param int rule 1 生成规则,1:毫秒时间戳+8位随机数,2:时间戳+8位随机数,3:10位随机数
 * @return int
 */
function idcsmart_tmp_order_id($rule=1)
{
    if ($rule == 1){
        $microtime = implode('',explode('.',microtime(true)));
        $tmp =  $microtime. rand_str(8,'NUMBER');
    }elseif ($rule == 2){
        $tmp = time() . rand_str(8,'NUMBER');
    }else{
        $tmp = rand_str(10,'NUMBER');
    }

    return $tmp;
}

/**
 * @title 添加系统日志
 * @desc 添加系统日志
 * @author theworld
 * @version v1
 * @param string description - 描述
 * @param string type - 关联类型
 * @param int relId - 关联ID
 * @param int relId - 关联用户ID
 * @return boolean
 */
function active_log($description, $type = '', $relId = 0, $clientId = 0)
{
    // 实例化模型类
    $SystemLogModel = new SystemLogModel();

    $description = htmlspecialchars($description);
    
    $param = [
        'description' => $description,
        'type' => $type,
        'rel_id' => $relId,
        'client_id' => $clientId,
    ];
    // 添加日志
    $result = $SystemLogModel->createSystemLog($param);

    return true;
}

/**
 * @title 更新操作的日志描述记录
 * @desc 更新操作的日志描述记录
 * @author wyh
 * @version v1
 * @param array old - 旧数据
 * @param array new - 新数据
 * @param string type - 类型
 * @param boolean plugin - 是否插件
 * @return string
 */
function log_description($old=[],$new=[],$type='product',$plugin=false)
{
    $description = '';
    foreach ($old as $key=>$value){
        if (isset($new[$key]) && ($value != $new[$key])){
            if ($plugin){
                $description .= lang('log_admin_update_description',['{field}'=>lang_plugins('field_'.$type.'_'.$key),'{old}'=>$value,'{new}'=>$new[$key]]) .',';
            }else{
                $description .= lang('log_admin_update_description',['{field}'=>lang('field_'.$type.'_'.$key),'{old}'=>$value,'{new}'=>$new[$key]]) .',';
            }
        }
    }

    return rtrim($description,',');
}

/**
 * 时间 2022-05-24
 * @title 订单支付回调系统处理
 * @desc 订单支付回调系统处理
 * @author wyh
 * @version v1
 * @param string param.tmp_order_id 1653364762428172693291 临时订单ID required
 * @param float param.amount 1.00 金额 required
 * @param string param.trans_id qwery134151786 交易流水ID required
 * @param string param.currency CNY 货币 required
 * @param string param.paid_time 2022-05-24 时间 required
 * @param string param.gateway AliPay 支付方式 required
 * @return bool
 */
function order_pay_handle($param)
{
    $OrderTmpModel = new OrderTmpModel();

    return $OrderTmpModel->orderPayHandle($param);
}

/**
 * @title 修改用户余额
 * @desc 修改用户余额
 * @author theworld
 * @version v1
 * @param string param.type - 类型:人工Artificial 充值Recharge 应用至订单Applied 超付Overpayment 少付Underpayment 退款Refund
 * @param float param.amount - 金额 required
 * @param string param.notes - 备注 required
 * @param int param.client_id - 用户ID required
 * @param int param.order_id - 订单ID
 * @param int param.host_id - 产品ID
 * @return boolean
 */
function update_credit($param){
    // 实例化模型类
    $ClientCreditModel = new ClientCreditModel();

    $param = [
        'type' => $param['type'] ?? '',
        'amount' => $param['amount'] ?? 0,
        'notes' => $param['notes'] ?? '',
        'id' => $param['client_id'] ?? 0,
        'order_id' => $param['order_id'] ?? 0,
        'host_id' => $param['host_id'] ?? 0,
    ];
    // 修改用户余额
    $result = $ClientCreditModel->updateClientCredit($param);
    return $result['status']==200 ?? false;
}

/**
 * @title 生成产品标识
 * @desc 修改用户余额
 * @author theworld
 * @version v1
 * @return string
 */
function generate_host_name()
{
    $prefix = 'ser';
    $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lower = strtolower($upper);
    $num = '0123456789';

    $randstr = $str = '';
    $str .= $num;

    $len = strlen($str)-1;
    $length = 12;

    if ($len<$length){
        $n = ceil($length/$len);
        for ($j=0; $j<$n; $j++){
            $str .= $str;
        }
        $len = strlen($str) - 1;
    }
    for($i=0; $i<$len; $i++){
        $num = mt_rand(0, $len);
        $randstr .= $str[$num];
    }
    return $prefix . substr($randstr, 0, $length);
}

/**
 * @title 获取系统钩子
 * @desc 获取系统钩子
 * @author wyh
 * @version v1
 * @return array
 */
function get_system_hooks()
{
    $class = new \ReflectionClass('app\\home\\controller\\HooksController');
    $methods = $class->getMethods();
    $methodsFilter = [];
    foreach ($methods as $method){
        $methodsFilter[] = $method->name;
    }

    $methodsFilter = array_merge($methodsFilter,config('idcsmart.template_hooks'));

    return $methodsFilter;
}

/**
 * @title 映射插件方法
 * @desc 映射插件方法
 * @author wyh
 * @version v1
 * @param string plugin - 插件标识 required
 * @param string param  - 参数 required
 * @param string module - 模块
 * @param string action - 方法
 * @return mixed
 */
function plugin_reflection($plugin,$param,$module='gateway',$action='handle')
{
    $class = get_plugin_class($plugin,$module);

    if (!class_exists($class)){
        return '';
    }

    # 实现默认方法:插件标识+Handle
    $action = parse_name(parse_name($plugin) . '_' . $action,1);

    $methods = get_class_methods($class);

    if (!in_array($action,$methods)){
        return '';
    }

    return app('app')->invoke([$class,$action],[$param]);
}

/**
 * @title 验证插件方法是否存在
 * @desc 验证插件方法是否存在
 * @author wyh
 * @version v1
 * @param string plugin - 插件标识 required
 * @param string module - 模块
 * @param string action - 方法
 * @return boolean
 */
function plugin_method_exist($plugin,$module='gateway',$action='handle')
{
    $class = get_plugin_class($plugin,$module);

    if (!class_exists($class)){
        return false;
    }

    # 实现默认方法:插件标识+Handle
    $action = parse_name(parse_name($plugin) . '_' . $action,1);

    $methods = get_class_methods($class);

    if (!in_array($action,$methods)){
        return false;
    }

    return true;
}

/**
 * @title 生成访问插件addon的url
 * @desc 生成访问插件addon的url
 * @author wyh
 * @version v1
 * @param string url - url格式：插件名://控制器名/方法 required
 * @param array vars  - 参数
 * @param bool is_admin - 是否后台
 * @return string
 */
function idcsmart_addon_url($url, $vars = [], $is_admin = false)
{
    $url              = parse_url($url);
    $caseInsensitive = true;
    $plugin           = $caseInsensitive ? parse_name($url['scheme']) : $url['scheme'];
    $controller       = $caseInsensitive ? parse_name($url['host']) : $url['host'];
    $action           = trim($caseInsensitive ? strtolower($url['path']) : $url['path'], '/');
    /* 解析URL带的参数 */
    if (isset($url['query'])) {
        parse_str($url['query'], $query);
        $vars = array_merge($query, $vars);
    }
    /* 基础参数 */
    $params = [
        '_plugin'     => $plugin,
        '_controller' => $controller,
        '_action'     => $action,
    ];
    $params = array_merge($params,$vars);

    if ($is_admin){
        $new = '/'. DIR_ADMIN . '/addon?' . http_build_query($params);
    }else{
        $plugin = parse_name($plugin,1);
        $PluginModel = new PluginModel();
        $plugin = $PluginModel->where('name',$plugin)->find();
        $params['_plugin'] = $plugin->id;
        $new = 'console/addon?' . http_build_query($params);
    }

    return $new;
}

/**
 * @title 是否新客户
 * @desc 是否新客户,新客户判断标准:无产品购买记录或历史已支付订单金额为0(标记支付金额为0，用户第三方支付为0，余额支付为0，同时满足这三个条件就算新用户)
 * @author wyh
 * @version v1
 * @param int client_id - 客户ID required
 * @return bool
 */
function new_client($client_id)
{
    $ClientModel = new ClientModel();

    return $ClientModel->newClient($client_id);
}

/**
 * @title 是否旧客户
 * @desc 是否旧客户,新客户判断标准:无产品购买记录或历史已支付订单金额为0
 * @author wyh
 * @version v1
 * @param int client_id - 客户ID required
 * @return bool
 */
function old_client($client_id)
{
    if (empty($client_id)){
        return false;
    }

    $ClientModel = new ClientModel();
    $client = $ClientModel->find($client_id);
    if (empty($client)){
        return false;
    }

    return !new_client($client_id);
}

/**
 * @title 判断文件是否是图片
 * @desc 判断文件是否是图片
 * @author wyh
 * @version v1
 * @param string filename - 文件名 required
 * @return bool
 */
function is_image($filename)
{
    if(file_exists($filename)) {
        if (!($info = @getimagesize($filename))){
            return false;
        }
        $ext = image_type_to_extension($info['2']);
        $types = '.gif|.jpeg|.png|.bmp|.ico|.svg'; # 定义检查的图片类型
        return stripos($types,$ext);
    } elseif (filter_var($filename,FILTER_VALIDATE_URL)){
        // 简单方式判断，url必须是授信的
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, $imageExtensions);
    } else {
        return false;
    }
}

/**
 * @title 判断文件是否是PDF文件
 * @desc 判断文件是否是PDF文件
 * @author wyh
 * @version v1
 * @param string filename - 文件名 required
 * @return bool
 */
function is_pdf($filename)
{
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filename);
    finfo_close($finfo);
    return $mimeType === 'application/pdf';
}

/**
 * 时间 2022-05-19
 * @title 添加到任务队列
 * @desc 添加到任务队列
 * @author xiong
 * @version v1
 * @param string param.type - 名称,sms短信发送,email邮件发送,host_create开通主机,host_suspend暂停主机,host_unsuspend解除暂停主机,host_terminate删除主机,执行在插件中的任务 required
 * @param int param.rel_id - 相关id
 * @param string param.description - 描述 required
 * @param array param.task_data - 任务要执行的数据 required
 */
function add_task($param)
{
	return (new TaskWaitModel())->createTaskWait($param);

}
/**
 * @title 创建动作
 * @desc 创建动作
 * @author xiong
 * @version v1
 * @param string param.name - 动作英文标识 required
 * @param string param.name_lang  - 动作名称（在页面显示的名称） required
 * @param string param.sms_name  - 短信接口标识名（可以为空，默认智简魔方短信接口）
 * @param string param.sms_template[].title  - 短信模板标题 required
 * @param string param.sms_template[].content  - 短信模板内容 required
 * @param string param.sms_global_name  - 国际短信接口标识名（可以为空，默认智简魔方短信接口）
 * @param string param.sms_global_template[].title  - 国际短信模板标题 required
 * @param string param.sms_global_template[].content  - 国际短信模板内容 required
 * @param string param.email_name  - 邮件接口名称（可以为空，默认SMTP接口）
 * @param string param.email_template[].name  - 邮件模板名称 required
 * @param string param.email_template[].title  - 邮件模板标题 required
 * @param string param.email_template[].content  - 邮件模板内容 required
 * @return mixed
 */
function notice_action_create($param)
{
	return (new NoticeSettingModel())->noticeActionCreate($param);
}

/**
 * @title 删除动作
 * @desc 删除动作,短信邮件模板
 * @author xiong
 * @version v1
 * @param string name - 动作英文标识 required
 */
function notice_action_delete($name)
{
	return (new NoticeSettingModel())->noticeActionDelete($name);
}

/**
 * @title 加密
 * @desc 加密
 * @author wyh
 * @version v1
 * @param string password - 密码 required
 * @return string
 */
function password_encrypt($password)
{
    $key = config('idcsmart.aes.key');

    $iv = config('idcsmart.aes.iv');

    $data = openssl_encrypt($password, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);

    $data = base64_encode($data);

    return $data;
}

/**
 * @title 密码解密
 * @desc 前端CryptoJs加密,php解密
 * @author wyh
 * @version v1
 * @param string password - 加密密码 required
 * @return string
 */
function password_decrypt($password)
{
    $key = config('idcsmart.aes.key');

    $iv = config('idcsmart.aes.iv');

    $encrypted = base64_decode($password);

    $plainText = openssl_decrypt($encrypted,'AES-128-CBC',$key,OPENSSL_RAW_DATA,$iv);

    return $plainText;
}

/**
 * @title 获取目录下文件夹
 * @desc 获取目录下文件夹
 * @author theworld
 * @version v1
 * @param string path - 目录路径 required
 * @return array
 */
function get_files($path)
{
    $arr = [];//存放文件名
    $handler = opendir($path);//当前目录中的文件夹下的文件夹
    while (($filename = readdir($handler)) !== false) {
        if ($filename != "." && $filename != ".." &&  strpos($filename,'.') ===false) {
            //$arr[]=$filename;
            array_push($arr, $filename);
        }
    }
    closedir($handler);
    return $arr;
}

/**
 * @title 实名认证接口
 * @desc 实名认证接口
 * @author wyh
 * @version v1
 * @return array list - 支付接口
 * @return int list[].id - ID
 * @return string list[].title - 名称
 * @return string list[].name - 标识
 * @return string list[].url - 图片:base64格式
 * @return int count - 总数
 */
function certification_list()
{
    $PluginModel = new PluginModel();

    $certification = $PluginModel->plugins('certification');

    return $certification;
}

/**
 * @title 检查客户是否实名认证
 * @desc 检查客户是否实名认证
 * @author wyh
 * @version v1
 * @param int client_id - 客户ID required
 * @return boolean
 */
function check_certification($client_id)
{
    $result = hook('check_certification',['client_id'=>$client_id]);

    foreach ($result as $value){
        if ($value){
            return true;
        }
    }

    return false;
}

/**
 * @title 是否开启未认证无法充值功能
 * @desc 是否开启未认证无法充值功能
 * @author wyh
 * @version v1
 * @param int client_id - 客户ID required
 * @return boolean
 */
function check_certification_recharge()
{
    $result = hook('check_certification_recharge');

    foreach ($result as $value){
        if ($value){
            return true;
        }
    }

    return false;
}

/**
 * @title 导出EXCEL
 * @desc 导出EXCEL
 * @author theworld
 * @version v1
 * @param string filename - 文件名称
 * @param array field - 导出字段,参数名对应显示名称
 * @param array data - 导出数据,二维数组
 */
function export_excel(string $filename = '', array $field = [], array $data = [])
{
    require(IDCSMART_ROOT . 'vendor/excel/vendor/phpoffice/phpexcel/Classes/PHPExcel.php');
    $enToCn = $field;
    $cnToEn = array_flip($enToCn);
    $intToCn = array_keys($cnToEn);
    $intToEn = array_keys($enToCn);

    $name = $filename;
    $excel = new \PHPExcel();
    iconv('UTF-8', 'gb2312', $name); //针对中文名转码
    $excel->setActiveSheetIndex(0);
    $sheel = $excel->getActiveSheet();
    $sheel->setTitle($name); //设置表名
    $sheel->getDefaultRowDimension()->setRowHeight(14.25);//设置默认行高
    $sheel->getDefaultColumnDimension()->setWidth(18);//设置默认列宽
    $letterArr = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK'];
    foreach ($intToEn as $k => $v) {
        $sheel->setCellValue($letterArr[$k] . 1, $enToCn[$v]);
    }
    $nn = count($intToEn);
    // 写入内容
    for($i=0; $i<count($data); $i++){
        $j = $i+2;
        foreach ($intToEn as $k => $v) {
            $sheel->setCellValue($letterArr[$k] . $j, $data[$i][$v]."\t");
        }
    }
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename='.$name.'.xlsx');
    header('Cache-Control: max-age=0');
    $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');

    $objWriter->save('php://output');
    exit;
}

/**
 * @title 获取授权信息
 * @desc 获取授权信息
 * @author theworld
 * @version v1
 * @return boolean
 */
function get_idcsamrt_auth()
{
    $license = configuration('system_license');//系统授权码
    if(empty($license)){
        return false;
    }
    if(!empty($_SERVER) && isset($_SERVER['SERVER_ADDR']) && !empty($_SERVER['SERVER_ADDR']) && isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])){
        
    }else{
        return false;
    }
    $ip = $_SERVER['SERVER_ADDR'];//服务器地址
    $arr = parse_url($_SERVER['HTTP_HOST']);
    $domain = isset($arr['host'])? ($arr['host'].(isset($arr['port']) ? (':'.$arr['port']) : '')) :$arr['path'];
    $type = 'finance';
    
    $version = configuration('system_version');//系统当前版本
    $data = [
        'ip' => $ip,
        'domain' => $domain,
        'type' => $type,
        'license' => $license,
        'install_version' => $version,
        'request_time' => time(),
    ];
    
    $url = "https://license.soft13.idcsmart.com/app/api/auth_rc";
    $res = curl($url,$data,20,'POST');
    if($res['http_code'] == 200){
        $result = json_decode($res['content'], true);
    }else{
        return false;
    }
    if(isset($result['status']) && $result['status']==200){
        $ConfigurationModel = new ConfigurationModel();
        $ConfigurationModel->saveConfiguration(['setting' => 'idcsmartauthinfo', 'value' => $result['data']]);
        $ConfigurationModel->saveConfiguration(['setting' => 'idcsmart_service_due_time', 'value' => $result['due_time']]);
        $ConfigurationModel->saveConfiguration(['setting' => 'idcsmart_due_time', 'value' => $result['auth_due_time']]);
        return true;
    }else{
        return false;
    }
}

/**
 * @title 魔方缓存
 * @desc 魔方缓存
 * @author wyh
 * @version v1
 * @param string key - 键
 * @param string value - 值:为null表示删除，’‘表示获取，其他设置
 * @param int timeout - 过期时间
 * @return mixed
 */
function idcsmart_cache($key,$value='',$timeout=null)
{
    return \app\common\lib\IdcsmartCache::cache($key,$value,$timeout);
}

/**
 * @title API鉴权登录
 * @desc API鉴权登录
 * @author wyh
 * @version v1
 * @param int api_id - 供应商ID
 * @param boolean force - 是否强制登录
 * @return array
 */
function idcsmart_api_login($api_id,$force=false)
{
    $SupplierModel = new \app\common\model\SupplierModel();

    return $SupplierModel->apiAuth($api_id,$force);
}

/**
 * @title 代理商请求供应商接口通用方法
 * @desc  代理商请求供应商接口通用方法
 * @author wyh
 * @version v1
 * @param   int    api_id  财务APIid
 * @param   string path    接口路径
 * @param   array  data    请求数据
 * @param   int    timeout 超时时间
 * @param   string request 请求方式(GET,POST,PUT,DELETE)
 */
function idcsmart_api_curl($api_id,$path,$data=[],$timeout=30,$request='POST',$response_type='json')
{
    //idcsmart_cache('api_auth_login_' . AUTHCODE . '_' . $api_id,null);
    $login = idcsmart_api_login($api_id);
    if ($login['status']!=200){
        return $login;
    }
    if($login['data']['supplier']['type']=='whmcs'){
        $header = [
            'Email: '.$login['data']['supplier']['username'],
            'Password: '.$login['data']['supplier']['token'],
        ];

        $apiUrl = $login['data']['url'] . '/modules/addons/idcsmart_reseller/logic/index.php?action='. $path;

        $result = curl($apiUrl,$data,$timeout,$request,$header);
        if($result['http_code'] != 200){
            return ['status'=>400, 'msg'=>lang('network_desertion'), 'content'=>$result['content']];
        }
        $result = json_decode($result['content'], true);
        if(isset($result['status'])){
            if($result['status']=='success' || $result['status']==200){
                $result['status'] = 200;
            }else{
                $result['status'] = 400;
            }
        }
    }else{
        $header = [
            'Authorization: Bearer '.$login['data']['jwt']
        ];

        $apiUrl = $login['data']['url'] . '/' .$path;

        $result = curl($apiUrl,$data,$timeout,$request,$header);
        if($result['http_code'] != 200){
            return ['status'=>400, 'msg'=>lang('network_desertion'), 'content'=>$result['content']];
        }
        // 直接返回html
        if ($response_type=='html'){
            return $result['content'];
        }
        $result = json_decode($result['content'], true);
        if(empty($result)){
            $result = ['status'=>400, 'msg'=>lang('network_desertion'), 'content'=>$result['content']];
        }
        if ($result['status']==401 || $result['status']==405){
            $login = idcsmart_api_login($api_id, true);

            if ($login['status']!=200){
                return $login;
            }

            $header = [
                'Authorization: Bearer '.$login['data']['jwt']
            ];
            $result = curl($apiUrl,$data,$timeout,$request,$header);
            
            if($result['http_code'] != 200){
                return ['status'=>400, 'msg'=>lang('network_desertion'), 'content'=>$result['content']];
            }
            $result = json_decode($result['content'], true);
            if ($result['status']==401){
                $result['status']=400;
                $result['msg'] = lang('api_account_or_password_error');
            }
        } 
    }

    

    return $result;
}

/**
 * @title 魔方生成RSA公私钥
 * @desc 魔方生成RSA公私钥
 * @author theworld
 * @version v1
 * @return string public_key - 公钥
 * @return string private_key - 私钥
 */
function idcsmart_openssl_rsa_key_create()
{
    $config = array(
        "digest_alg" => "sha512",
        "private_key_bits" => 4096,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    );

    $res = openssl_pkey_new($config);

    openssl_pkey_export($res, $privateKey);

    $publicKey = openssl_pkey_get_details($res);
    $publicKey = $publicKey["key"];

    return ['public_key' => $publicKey, 'private_key' => $privateKey];
}

/**
 * @title 上游同步产品信息到下游
 * @desc  上游同步产品信息到下游
 * @author theworld
 * @version v1
 * @param   int    host_id 财务产品ID
 * @param   string action  动作module_create模块开通module_suspend模块暂停module_unsuspend模块解除暂停module_terminate模块删除update_host修改产品delete_host删除产品host_renew产品续费
 */
function upstream_sync_host($host_id, $action = '')
{
    $HostModel = new \app\common\model\HostModel();

    return $HostModel->upstreamSyncHost($host_id, $action);
}

/**
 * @title 更新上游订单利润
 * @desc  更新上游订单利润
 * @author theworld
 * @version v1
 * @param   int    order_id 财务订单ID
 */
function update_upstream_order_profit($order_id)
{
    $OrderModel = new \app\common\model\OrderModel();

    return $OrderModel->updateUpstreamOrderProfit($order_id);
}

/**
 * @title debug加密
 * @desc debug加密
 * @author wyh
 * @version v1
 * @param string originalData - 源数据
 * @param string private_key - 私钥
 * @return string
 */
function zjmf_private_encrypt($originalData,$private_key){
    $crypted = '';
    foreach (str_split($originalData, 117) as $chunk) {
        openssl_private_encrypt($chunk, $encryptData, $private_key);
        $crypted .= $encryptData;
    }
    return base64_encode($crypted);
}

/**
 * @title 生成签名
 * @desc 生成签名
 * @author wyh
 * @version v1
 * @param array params - 参数
 * @param string token - 密钥
 * @return array
 */
function create_sign($params, $token){
    $rand_str = rand_str(6);
    $params['token'] = $token;
    $params['rand_str'] = $rand_str;
    ksort($params, SORT_STRING);
    $str = json_encode($params);
    $sign = md5($str);
    $sign = strtoupper($sign);
    $res['signature'] = $sign;
    $res['rand_str'] = $rand_str;
    return $res;
}

/**
 * @title 是否使用手机端
 * @desc 是否使用手机端
 * @author wyh
 * @version v1
 * @return boolean
 */
function use_mobile()
{
    return configuration("clientarea_theme_mobile_switch") && request()->isMobile();
}

/**
 * @title 文件资源生成签名
 * @desc 文件资源生成签名
 * @author wyh
 * @version v1
 * @param array params - 参数
 * @param string token - 密钥
 * @param string rand_str - 随机字符串
 * @return array
 */
function generate_signature($params,$token,$rand_str=""){
    $rand_str = $rand_str?:rand_str(6);
    unset($params['sign']);
    $params['token'] = $token;
    $params['rand_str'] = $rand_str;
    ksort($params, SORT_STRING);
    $str = json_encode($params);
    $sign = md5($str);
    $sign = strtoupper($sign);
    $res['signature'] = $sign;
    $res['rand_str'] = $rand_str;
    return $res;
}

/**
 * @title 文件资源验证签名
 * @desc 文件资源验证签名
 * @author wyh
 * @version v1
 * @param array params - 参数
 * @param string token - 密钥
 * @param string rand_str - 随机字符串
 * @return boolean
 */
function validate_signature($params,$token,$rand_str=""){
    return $params['sign']==generate_signature($params,$token,$rand_str)['signature'];
}

/**
 * @title 获取同步回调地址
 * @desc 获取同步回调地址
 * @author wyh
 * @version v1
 * @param string tmp_order_id - 订单ID
 * @return string
 */
function get_gateway_return_url($tmp_order_id){
    $OrderTmpModel = new OrderTmpModel();
    return $OrderTmpModel->getGatewayReturnUrl($tmp_order_id);
}

/**
 * @title 上传文件到对象存储
 * @desc 上传文件到对象存储
 * @author theworld
 * @version v1
 * @param string file_path - 文件路径
 * @param string file_name - 文件名
 * @param string type - 文件类型：defautl系统默认、ticket工单、app应用等
 */
function idcsmart_oss_upload($file_path, $file_name, $type = 'default')
{
    $ossMethod = configuration("oss_method");
    $result = plugin_reflection($ossMethod,[
        'file_path' => $file_path,
        'file_name' => $file_name
    ],'oss','upload');
    if (empty($result)){
        return ['status'=>400,'msg'=>lang("non_existent_storage_method")];
    }
    
    if (isset($result['status']) && $result['status']==200){
        unlink(rtrim($file_path,'/') . "/" . $file_name);
        // 保存数据至数据库
        $FileLogModel = new FileLogModel();
        $uuid = explode("^",$file_name)[0]??explode(".",$file_name)[0];
        $file = $FileLogModel->where('uuid', $uuid)->find();
        if(!empty($file)){
            $FileLogModel->update([
                'save_name' => $file_name,
                'name' => explode("^",$file_name)[1]??"",
                'type' => $type,
                'oss_method' => $ossMethod,
                'create_time' => time(),
                'client_id' => get_client_id(),
                'admin_id' => get_admin_id(),
                'source' => get_client_id()>0?"client":"admin",
                'url' => $result['data']['url'] ?? ''
            ], ['uuid' => $uuid]);
        }else{
            $FileLogModel->insert([
                'uuid' => $uuid,
                'save_name' => $file_name,
                'name' => explode("^",$file_name)[1]??"",
                'type' => $type,
                'oss_method' => $ossMethod,
                'create_time' => time(),
                'client_id' => get_client_id(),
                'admin_id' => get_admin_id(),
                'source' => get_client_id()>0?"client":"admin",
                'url' => $result['data']['url'] ?? ''
            ]);
        }
        return ['status'=>200,'msg'=>lang('success_message'),'data' => ['url' => $result['data']['url'] ?? '']];
    }else{
        return ['status'=>400,'msg'=>$result['msg']??lang('move_fail')];
    }
}

/**
 * 模板数据标签替换
 * @param $content 模板内容
 * @param $tag 替换标签
 * @param $templateName $tag是debug时的debug所在标签模板名称
 * @return string 返回替换后的字符串
 */
function view_tpl_replace($content,$data,$templateName="")
{   
    $debug = '{debug}';
    if(stripos($content, $debug)!==false){
        //debug替换输出
        $debugData = '';
        foreach($data as $dataKey => $dataVal){
            $td = '<td>$' . $dataKey . '</td>';
            if(is_array($dataVal)){
                $td .= '<td><b>Value</b><br>';
                $td .= view_tpl_array_out($dataVal, false);
                $td .= '</td>';
            }else{
                $td .= '<td><b>Value</b><br>"'.$dataVal.'"</td>';
            }
            $debugData .= '<tr>'.$td.'</tr>';
        }

        $hn = '<h1>当前调试模板debug标签所在文件地址:"'.$templateName.'" </h1>';
        $debugHtml = '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>当前模板输出的数据</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style type="text/css">           
            body, h1, h2, h3, td, th, p {
                font-family: sans-serif;
                font-weight: normal;
                font-size: 0.9em;
                margin: 1px;
                padding: 0;
            }
            h1 {
                margin: 0;
                text-align: left;
                padding: 2px;
                background-color: green;
                color: #fff;
                font-weight: bold;
                font-size: 1.2em;
            }
            h2 {
                background-color: #333;
                color: white;
                text-align: left;
                font-weight: bold;
                padding: 2px;
                border-top: 1px solid black;
            }
            table {
                width: 100%;
            }
            tr, td {
                font-family: monospace;
                vertical-align: top;
                text-align: left;
            }
            td {
                color: green;           
                padding:15px 10px;
            }
            tr:nth-of-type(odd) {
                background-color: #eeeeee;
            }
            tr:nth-of-type(even) {
                background-color: #fafafa;
            }            
        </style>
</head>
<body>
'.$hn.'
<h2>当前模板输出的数据:<br> </h2>
<table>
<tbody>
'.$debugData.'
</tbody>
</table>
</body>
</html>
';

        $debugHtml = ltrim(rtrim(preg_replace(array("/> *([^ ]*) *</","//","'/\*[^*]*\*/'","/\r\n/","/\n/","/\t/",'/>[ ]+</'),array(">\\1<",'','','','','','><'),$debugHtml)));
                
        //echo $debugHtml;exit;
        $streplace='
        <script type="text/javascript">
        idcsmart_debug_console = window.open("", "'.md5(time()).'", "width=1024,height=600,left=50,top=50,resizable,scrollbars=yes");
        idcsmart_debug_console.document.write("'.addslashes($debugHtml).'");
        </script>
        ';
                        
        $strreplace = "_smarty_console.document.close();";
        $content1 = substr($content,0,stripos($content,$debug)+strlen($debug));                   
        $content1 = str_replace($debug,$streplace,$content1);                
        $content = $content1.substr($content,stripos($content,$debug));
    }            
    
    // 多余的debug 和tagdata标签替换成空
    $content =str_replace($debug,"",$content);

    return $content;
}

function view_tpl_array_out($dataVal, $nbsp = ""){
    if($nbsp!==false) $nbsp.="&nbsp;&nbsp;";
    $nbsp_br = '';
    $td = '';
    foreach($dataVal as $key=>$val){
        if(is_array($val)){
            if($nbsp && $nbsp!="") $nbsp_br = $nbsp;
            if(!$nbsp)$nbsp="";
            $td.=''.$nbsp_br.$key.'=><br>'.view_tpl_array_out($val,$nbsp);
        }else{
            $td.=$nbsp.$key.'=>"'.$val.'"<br>';
        }
    }
    return $td;
}

/**
 * 获取币种汇率
 * @return object 返回USD(美元)对不同货币的汇率,{"USD": 1,"AED": 3.67,...}
 */
function getRate()
{
    $res = curl(config('app.getRateUrl'), [], 15, 'GET');
    if($res['http_code'] == 200){
        $result = json_decode($res['content'], true);
        $exchangeRates = $result['rates'] ?? [];
    }else{
        $exchangeRates = [];
    }
    return $exchangeRates;
}

/**
 * @title 获取对象存储文件访问地址和原文件名
 * @desc 获取对象存储文件访问地址和原文件名
 * @author wyh
 * @version v1
 * @param string param.file_path - 文件保存路径
 * @param string param.file_name - 文件名
 * @return array
 * @return string url - 访问地址
 * @return string name - 原文件名
 * @return string save_name - 附件名
 */
function getOssUrl($param)
{
    // 需要兼容老数据，没有文件日志记录的情况
    /*$FileLogModel = new FileLogModel();
    $fileLog = $FileLogModel->where('save_name',$param['file_name'])->find();
    if (!empty($fileLog)){
        $name = $fileLog['name']??"";
    }else{
        $name = explode('^',$param['file_name'])[1]??$param['file_name'];
    }*/

    $name = explode('^',$param['file_name'])[1]??$param['file_name'];

    if ($ossMethod = \cache('oss_method')){

    }else{
        $ossMethod = configuration("oss_method");
        \cache('oss_method',$ossMethod);
    }
    $result = plugin_reflection($ossMethod,$param,'oss','download');
    $url = $result['data']['url']??"";
    return ['url'=>$url,'name'=>$name,'save_name'=>$param['file_name']];
}

/**
 * @title 处理上下游升降级配置结果
 * @desc 处理上下游升降级配置结果
 * @author wyh
 * @version v1
 * @time 2024-05-26
 * @param object param.RouteLogic - 代理模块路由逻辑类对象 require
 * @param object param.supplier - 供应商对象 require
 * @param object param.host - 产品对象 require
 * @param int param.is_downstream - 是否下游，多级代理情况下
 * @param array result - 上游返回的结果数组
 * @param string result.data.price_difference - 升降级差价
 * @param string result.data.renew_price_difference - 续费差价
 * @param string result.data.base_price - 升降级后整个产品的基础价格
 * @return array  - 返回处理结果
 * @return int status - 状态，200或400
 * @return string msg - 描述
 * @return array data - 返回数据，当status==200时，才返回此字段
 * @return string data.base_price - 升降级后整个产品的基础价格
 * @return string data.base_price_client_level_discount - 升降级后整个产品的基础价格折扣
 * @return string data.description - 描述，保存到订单子项描述里
 * @return string data.new_first_payment_amount - 新首付金额
 * @return string data.new_first_payment_amount_client_level_discount - 新首付金额折扣
 * @return string data.price - 购买价格，必须>=0的
 * @return string data.price_difference - 价格差价，可为负数
 * @return string data.price_difference_client_level_discount - 价格差价折扣，可为负数
 * @return string data.profit - 利润，可为负数
 * @return string data.renew_price_difference - 续费差价，可为负数
 * @return string data.renew_price_difference_client_level_discount - 续费差价折扣，可为负数
 */
function upstream_upgrade_result_deal($param,$result)
{
    $UpstreamLogic = new \app\common\logic\UpstreamLogic();

    return $UpstreamLogic->upstreamUpgradeResultDeal($param,$result);
}

/**
 * 时间 2024-05-27
 * @title 验证前台强制安全选项
 * @desc  验证前台强制安全选项
 * @author hh
 * @version v1
 * @return  bool redirect - 是否重定向
 * @return  string url - 重定向地址
 */
function check_home_enforce_safe_method_redirect()
{
    // 当不在账户详情时,判断是否开启了
    $clientId = get_client_id();
    $redirect = false;
    if(!empty($clientId)){
        $client = ClientModel::find($clientId);

        $homeEnforceSafeMethod = configuration(['home_enforce_safe_method']);
        $homeEnforceSafeMethod = !empty($homeEnforceSafeMethod) ? explode(',', $homeEnforceSafeMethod) : [];
        if(!$redirect && in_array('phone', $homeEnforceSafeMethod)){
            $redirect = empty($client['phone']);
        }
        if(!$redirect && in_array('email', $homeEnforceSafeMethod)){
            $redirect = empty($client['email']);
        }
        if(!$redirect && in_array('operate_password', $homeEnforceSafeMethod)){
            $redirect = empty($client['operate_password']);
        }
        if(!$redirect && in_array('certification', $homeEnforceSafeMethod)){
            $certification = hook_one('certification_detail', ['client_id'=>$clientId]);
            if(isset($certification['person']) && isset($certification['company'])){
                $redirect = empty($certification['person']) && empty($certification['company']);
            }
        }
        if(!$redirect && in_array('oauth', $homeEnforceSafeMethod)){
            $oauth = OauthModel::where('client_id', $clientId)->find();
            $redirect = empty($oauth);
        }
    }
    return ['redirect'=>$redirect, 'url'=>request()->domain() . '/account.htm'];
}

/**
 * 时间 2024-05-27
 * @title 验证前台强制安全选项
 * @desc  验证前台强制安全选项
 * @author hh
 * @version v1
 * @return  bool redirect - 是否重定向
 * @return  string url - 重定向地址
 */
function check_admin_enforce_safe_method_redirect()
{
    $adminId = get_admin_id();
    $redirect = false;
    if(!empty($adminId)){
        $adminEnforceSafeMethod = configuration(['admin_enforce_safe_method']);
        $adminEnforceSafeMethod = !empty($adminEnforceSafeMethod) ? explode(',', $adminEnforceSafeMethod) : [];
        if(in_array('operate_password', $adminEnforceSafeMethod)){
            // 当前管理员是否设置了操作密码
            $operatePassword = AdminModel::where('id', $adminId)->value('operate_password');
            $redirect = empty($operatePassword);
        }
    }
    return ['redirect'=>$redirect, 'url'=>request()->domain() . '/' . DIR_ADMIN . '/security_center.htm'];
}

/**
 * 时间 2024-06-05
 * @title 是否通知
 * @desc  是否通知
 * @author wyh
 * @version v1
 * @param string param.type - 方式：email邮件，sms短信，
 * @param int param.client_id - 客户ID
 * @return bool -
 */
function client_notice($param)
{
    $ClientModel = new ClientModel();

    return $ClientModel->clientNotice($param);
}