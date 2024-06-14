<?php 
namespace app\common\logic;

use think\captcha\Captcha;
use think\facade\Cache;

/**
 * @title 系统升级逻辑类
 * @desc 系统升级逻辑类
 * @use app\common\logic\UpgradeSystemLogic
 */
class UpgradeSystemLogic
{
    public $upgrade_log = "https://license.soft13.idcsmart.com/upgrade/rc/upgrade.php";//更新服务器地址,存放升级版本记录

    public $upload_dir = IDCSMART_ROOT.'public/upgrade/';//客户升级包目录

    public $root_dir = IDCSMART_ROOT; //站点代码的根目录

    public $progress_log = IDCSMART_ROOT . "public/upgrade/progress_log.log"; //记录进度

    private $auth_url = "https://license.soft13.idcsmart.com";//授权地址

    private $upgrade_path = "rc_stable";

    public function __construct(){
        $systemVersionType = configuration('system_version_type'); 

        $this->upgrade_path = $systemVersionType=='beta' ? 'rc' : $this->upgrade_path;

        $this->upgrade_log = "https://license.soft13.idcsmart.com/upgrade/{$this->upgrade_path}/upgrade.php";
    }

    /**
     * 时间 2022-07-21
     * @title 获取系统版本
     * @desc 获取系统版本
     * @author theworld
     * @version v1
     * @return string version - 当前系统版本 
     * @return string last_version - 最新系统版本 
     * @return string last_version_check - 最新系统版本检测结果 
     * @return int is_download - 更新包是否下载完毕:0否1是 
     * @return string license - 授权码
     * @return string service_due_time - 服务到期时间
     * @return string due_time - 授权到期时间
     * @return string system_version_type - 系统升级版本beta内测版stable正式版
     * @return string system_version_type_last - 最后一次系统升级版本beta内测版stable正式版
     */
    public function getSystemVersion()
    {

        $lastVersion = $this->getLastVersion();
        if (isset($lastVersion['status']) && $lastVersion['status'] == 400){
            $lastVersion = configuration('system_version');
            $lastVersionCheck = 'no_response';
        }else{
            if(!is_dir($this->upload_dir)){
                mkdir($this->upload_dir);
            }
            $handler = opendir($this->upload_dir);
            while( ($filename = readdir($handler)) !== false ) {
                if ($filename == "." && $filename == "..")continue;
                if (preg_match('/'.$lastVersion.'\.zip$/i', $filename) && file_exists($this->upload_dir.$filename.'.md5'))$isDownload = 1;
            }
        }
        $data = [
            'version' => configuration('system_version'),
            'last_version' => $lastVersion,
            'last_version_check' => $lastVersionCheck ?? '',
            'is_download' => $isDownload ?? 0,
            'license' => !empty(configuration('system_license')) ? configuration('system_license') : '',
            'service_due_time' => configuration('idcsmart_service_due_time'),
            'due_time' => configuration('idcsmart_due_time'),
            'system_version_type' => configuration('system_version_type'),
            'system_version_type_last' => configuration('system_version_type_last'),
        ];
        return ['status' => 200, 'data' => $data];
    }

    /**
     * 时间 2024-06-05
     * @title 更改系统升级版本
     * @desc 更改系统升级版本
     * @author theworld
     * @version v1
     * @param string param.system_version_type - 系统升级版本beta内测版stable正式版 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateSystemVersionType($param)
    {
        if(!isset($param['system_version_type']) || !in_array($param['system_version_type'], ['beta', 'stable'])){
            return ['status' => 400, 'msg' => lang('param_error')];
        }

        updateConfiguration('system_version_type', $param['system_version_type']);

        return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2022-07-21
     * @title 获取更新内容
     * @desc 获取更新内容
     * @author theworld
     * @version v1
     * @return string warning - 必读内容 
     * @return string content - 更新内容 
     */
    public function getUpgradeContent()
    {
        $version = configuration('system_version');
        $last_version = $this->getHistoryVersion();
        $str = $warning = '';
        if (version_compare($last_version['last'],$version,'>=')){ //2.1.0 1.2.6
            $arr = $this->diffVersion($last_version['last'],$version);
            $arr = array_reverse($arr);
            /**
             * 增加历史更新
             */
            array_shift($arr);
            $str = file_get_contents($this->auth_url . "/upgrade/{$this->upgrade_path}/{$last_version['last']}.php");
            $warning = file_get_contents($this->auth_url . "/upgrade/{$this->upgrade_path}/{$last_version['last']}_warning.php");
            if($arr)
            {
                $str .= '<h1>历史更新</h1>';
                foreach ($arr as $v){
                    if(in_array($v, $last_version['all_version'])){
                        $str .= file_get_contents($this->auth_url . "/upgrade/{$this->upgrade_path}/{$v}.php");
                    }
                }
            }
        }
        return ['status' => 200, 'data'=>['warning' => mb_convert_encoding(iconv('utf-8', 'gbk//IGNORE', $warning), 'utf-8', 'GBK'), 'content' => mb_convert_encoding(iconv('utf-8', 'gbk//IGNORE', $str), 'utf-8', 'GBK')]];
    }

    /**
     * 时间 2022-07-21
     * @title 更新下载
     * @desc 更新下载
     * @author theworld
     * @version v1
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function upgradeDownload()
    {
        //获取最新记录,且非空
        $res = $this->getUpgradeLog();
        if (isset($res['status']) && $res['status'] == 400){
            return ['status' => 400, 'msg' => $res['msg']];
        }
        $arr = explode(',',$res['last']);
        if (empty($arr)){
            return ['status' => 400, 'msg' => lang('get_new_version_failed')];
        }
        $lastVersion = $arr[1]; // 最新版本号
        $url = $arr[3]; // 升级包地址 .zip
        if(!version_compare($lastVersion, configuration('system_version'), '>')) {
            return ['status' => 400, 'msg' => lang('version_is_last')];
        }

        $is_download = 0;
        $handler = opendir($this->upload_dir);
        while( ($filename = readdir($handler)) !== false ) {
            if ($filename == "." && $filename == "..")continue;
            if (preg_match('/'.$lastVersion.'\.zip$/i', $filename) && file_exists($this->upload_dir.$filename.'.md5'))$is_download = 1;
        }
        if ($is_download)return ['status'=>200, 'msg'=>lang('package_has_downloaded')];

        ini_set('max_execution_time', 3600);
        cache('upgrade_system_start',time(),3600);
        session_write_close();

        $progress_log = [];

        // 检测根目录权限
        if (!is_readable($this->root_dir) || !$this->newIsWriteable($this->root_dir)){
            return ['status' => 400, 'msg' => lang('root_cannot_read_write')];
        }
        // 检测升级目录权限
        if (!is_readable($this->upload_dir) || !$this->newIsWriteable($this->upload_dir)){
            return['status' => 400, 'msg' => lang('upgrade_cannot_read_write')];
        }

        //2、下载更新包
        ini_set('max_execution_time', 3600);
        $downloadResult = $this->downloadZip($url);
        if ($downloadResult['status'] != 200){
            die;
        }
        $filename = $downloadResult['data'];
        //获取解压后的更新包名称
        $url = trim($url);
        $package_name = str_replace('.zip',"",basename($url));

        return ['status'=>200, 'msg'=>lang('download_sucesss')];
    }

    /**
     * 时间 2022-07-21
     * @title 获取系统最新版本
     * @desc 获取系统最新版本
     * @author theworld
     * @version v1
     * @return string - - 最新版本
     */
    private function getLastVersion()
    {
        $data = $this->getUpgradeLog();
        if (isset($data['status']) && $data['status'] == 400){
            return $data;
        }
        $arr = explode(',',$data['last']);
        return $arr[1];
    }

    /**
     * 时间 2022-07-21
     * @title 获取历史版本
     * @desc 获取历史版本
     * @author theworld
     * @version v1
     * @return string last - 最新版本
     * @return array all_version - 全部版本
     */
    private function getHistoryVersion()
    {
        $data = $this->getUpgradeLog();
        if (isset($data['status']) && $data['status'] == 400){
            return $data;
        }
        $arr = explode(',',$data['last']);
        foreach ($data['arr'] as $key => $value) {
            $value = explode(',', $value);
            $data['arr'][$key] = $value[1];
        }
        return ['last' => $arr[1], 'all_version' => $data['arr']];
    }

    /**
     * 时间 2022-07-21
     * @title 获取远程升级版本记录,换行记录,且获取最新的非空记录
     * @desc 获取远程升级版本记录,换行记录,且获取最新的非空记录
     * @author theworld
     * @version v1
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     * @return string last - 最新版本
     * @return array arr - 全部版本
     */
    private function getUpgradeLog()
    {

        // 日志记录格式：时间,版本号,备注,更新包地址
        // 注意：换行存储
        // 20200601,1.0.0,20200601更新,http://license.soft13.idcsmart.com/upgrade/1.0.0.zip
        // 20200602,1.0.1,20200602更新,http://license.soft13.idcsmart.com/upgrade/1.0.1.zip

        //设置超时时间10s
        $timeout = [
            'http' => [
                'timeout' => 10
            ]
        ];
        $ctx = stream_context_create($timeout);
        //获取升级版本记录信息
        $handle = fopen($this->upgrade_log, 'r',false,$ctx);
        if (!$handle){
            return ['status' => 400 , 'msg' => lang('open_remote_file_failed')];
        }
        $content = '';
        while(!feof($handle)){
            $content .= fread($handle, 80800);
        }
        fclose($handle);
        $arr = explode("\n",$content);
        //过滤空值
        $fun = function ($value){
            if (empty($value)){
                return false;
            }else{
                return true;
            }
        };
        $arr = array_filter($arr,$fun);
        $this->recurseGetLastVersion($last,$arr);
        return ['last' => $last, 'arr' => $arr];
    }

    /**
     * 时间 2022-07-21
     * @title 获取更新下载进度
     * @desc 获取更新下载进度
     * @author theworld
     * @version v1
     * @return string data.progress - 下载百分比 
     * @return string data.moment_size - 已下载大小,MB
     * @return string data.origin_size - 文件总大小,MB
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function getUpgradeDownloadProgress()
    {
        $file_name = Cache::get('file_name');
        if(empty($file_name)){
            return ['status'=>400, 'msg'=>lang('upgrade_download_not_exist')];
        }
        $origin_size = Cache::get('origin_size');
        if(!file_exists($this->upload_dir . $file_name)){
            return ['status'=>400, 'msg'=>lang('upgrade_download_not_exist')];
        }
        $moment_size = filesize($this->upload_dir . $file_name);
        $moment_size = bcdiv($moment_size,1024*1024,2);

        $data['progress'] = bcmul(bcdiv($moment_size,$origin_size,4),100,2) . "%";
        $data['moment_size'] = $moment_size;
        $data['origin_size'] = $origin_size;

        return ['status'=>200, 'data'=>$data];
    }

    /**
     * 时间 2022-07-21
     * @title 获取授权信息
     * @desc 获取授权信息
     * @author theworld
     * @version v1
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function getAuth()
    {
        $res = get_idcsamrt_auth();
        if($res){
            return ['status' => 200, 'msg' => lang('get_idcsamrt_auth_success')];
        }else{
            return ['status' => 400, 'msg' => lang('get_idcsamrt_auth_failed')];
        }
    }

    /**
     * 时间 2022-07-21
     * @title 更换授权码
     * @desc 更换授权码
     * @author theworld
     * @version v1
     * @param string param.license - 授权码 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateLicense($param)
    {
        $oldLicense = configuration('system_license');
        updateConfiguration('system_license', $param['license']);
        $res = get_idcsamrt_auth();
        if($res){
            return ['status' => 200, 'msg' => lang('replace_idcsamrt_auth_success')];
        }else{
            updateConfiguration('system_license', $oldLicense);
            return ['status' => 400, 'msg' => lang('replace_idcsamrt_auth_failed')];
        }
    }

    /**
     * 时间 2022-07-21
     * @title 下载zip包
     * @desc 下载zip包
     * @author theworld
     * @version v1
     * @param string url - 下载地址
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    private function downloadZip($url)
    {
        $url = urldecode($url);
        $fname = basename($url);
        $str_name = pathinfo($fname);
        $time = date("Ymd",time());
        $file_name = $time . rand(1000,9999) . '^' . $str_name['filename'] . '.zip';
        $dir = $this->upload_dir . $file_name;
        if (!file_exists($this->upload_dir)){
            mkdir($this->upload_dir,0777,true);
        }
        if(file_exists($dir)){
            chmod($dir,0777);
        }
        
        //下载更新包
        $url = dirname($url) . '/' .$str_name['filename'] . '.zip';
        $url2 = dirname($url) . '/' .$str_name['filename'] . '.zip.md5';
        $origin_size = get_headers($url,1);
        $origin_size = $origin_size['Content-Length']??0;
        $origin_size = bcdiv($origin_size,1024*1024,2)??number_format(0,2);

        Cache::set('file_name', $file_name, 3600*24);
        Cache::set('origin_size', $origin_size, 3600*24);


        $ch = curl_init($url);
        //设置抓取的url
        $dir = $this->upload_dir . $file_name;
        $fp = fopen($dir, "wb");
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $content = curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        $url = trim($url);
        $package_name = str_replace('.zip',"",basename($url));

        $md5 = file_get_contents($url2);
        file_put_contents($dir.'.md5', $md5);
        #保存校验版本号
        session('upgrade.system_version',$package_name);
        if ($content){
            if (file_exists($this->upload_dir . $str_name['filename'])){
                $this->deleteDir($this->upload_dir . $str_name['filename'] );
            }
            return ['status' => 200 , 'msg' => lang('download_sucesss')];
        }else{
            #清理失败文件
            $this->deleteUpgrdeFile($file_name,$package_name);
            return ['status' => 400 , 'msg' => lang('download_failed')];
        }
    }

    /**
     * 时间 2022-07-21
     * @title 清理异常文件
     * @desc 清理异常文件
     * @author theworld
     * @version v1
     * @param string file_name - 压缩包
     * @param string package_name - 解压目录
     */
    private function deleteUpgrdeFile($file_name,$package_name)
    {
        $check_version = session('upgrade.system_version');
        #无验证数据不执行，防止服务器上面数据被错误删除
        if (empty($check_version))return false;
        #删除压缩包
        if (!empty($file_name) && file_exists($this->upload_dir . $file_name) && strpos($file_name,$check_version) !== false) {
            @unlink($this->upload_dir . $file_name);
        }
        #删除解压目录
        if (!empty($package_name) && file_exists($this->upload_dir . $package_name) && strpos($package_name,$check_version) !== false) {
            chmod($this->upload_dir . $package_name, 0777);
            $this->deleteDir($this->upload_dir . $package_name);
        }
    }

    /**
     * 时间 2022-07-21
     * @title 递归删除目录
     * @desc 递归删除目录
     * @author theworld
     * @version v1
     * @param string path - 目标目录
     * @param array out - 排除目录
     */
    private function deleteDir($path,$out=[]) {

        if (is_dir($path)) {
            //扫描一个目录内的所有目录和文件并返回数组
            $dirs = scandir($path);

            foreach ($dirs as $dir) {
                if (!in_array($dir,$out)){
                    //排除目录中的当前目录(.)和上一级目录(..)
                    if ($dir != '.' && $dir != '..') {
                        //如果是目录则递归子目录，继续操作
                        $sonDir = $path.'/'.$dir;
                        if (is_dir($sonDir)) {
                            //递归删除
                            $this->deleteDir($sonDir);

                            //目录内的子目录和文件删除后删除空目录
                            @rmdir($sonDir);
                        } else {

                            //如果是文件直接删除
                            @unlink($sonDir);
                        }
                    }
                }
            }
            @rmdir($path);
        }
    }

    /**
     * 时间 2022-07-21
     * @title 递归获取最后版本
     * @desc 递归获取最后版本
     * @author theworld
     * @version v1
     * @param string last - 最新版本
     * @param array arr - 全部版本
     */
    private function recurseGetLastVersion(&$last,$arr=[])
    {
        $last = array_pop($arr);
        if (explode(',',$last)[2] == 'beta_test'){
            recurseGetLastVersion($last,$arr);
        }
        return ;
    }

    /**
     * 时间 2022-07-21
     * @title 版本对比
     * @desc 版本对比
     * @author theworld
     * @version v1
     * @param string last_version - 最新版本
     * @param array version - 全部版本
     * @return array - - 低于最新版本的版本
     */
    private function diffVersion($last_version,$version)
    {
        $a = explode('.',$last_version);
        $b = explode('.',$version);
        $arr = [];
        $num1 = $a[0] * 100 + $a[1] * 10 + $a[2];
        $num2 = $b[0] * 100 + $b[1] * 10 + $b[2];
        while ($num1 >= $num2){
            $hundred = floor($num2/100);
            $ten = floor(($num2-100*$hundred)/10);
            $unit = floor($num2-100*$hundred-10 * $ten);
            $version = $hundred . '.' . $ten . '.' . $unit;
            $arr[] = $version;
            $num2++;
        }
        return $arr;
    }

    /**
     * 时间 2022-07-21
     * @title 检查目录/文件是否可写
     * @desc 检查目录/文件是否可写
     * @author theworld
     * @version v1
     * @param string file - 目录/文件
     * @return bool
     */
    private function newIsWriteable($file) {
        if (is_dir($file)){
            $dir = $file;
            if ($fp = @fopen("$dir/test.txt", 'w')) {
                @fclose($fp);
                @unlink("$dir/test.txt");
                $writeable = true;
            } else {
                $writeable = false;
            }
        } else {
            if ($fp = @fopen($file, 'a+')) {
                @fclose($fp);
                $writeable = true;
            } else {
                $writeable = false;
            }
        }

        return $writeable;
    }

    public function upgradeData()
    {
        $systemVersion = configuration('system_version');

        $file_name = IDCSMART_ROOT . "/public/upgrade/upgrade.log";
        $content = file_get_contents(IDCSMART_ROOT . '/public/upgrade/upgrade.log');
        $upgradeVersion = explode("\n", $content);
        foreach ($upgradeVersion as $key => $value) {
            $value = explode(',', $value);
            $upgradeVersion[$key] = $value[1];
        }
        foreach ($upgradeVersion as $value) {
            if(version_compare($systemVersion, $value, '<') && file_exists(IDCSMART_ROOT.'app/upgrade/'.$value.'.php')){
                require_once IDCSMART_ROOT.'app/upgrade/'.$value.'.php';
            }
        }
    }
}