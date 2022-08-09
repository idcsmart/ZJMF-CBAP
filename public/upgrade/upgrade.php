<?php
require dirname(dirname(__DIR__ )) . '/config.php';
require dirname(dirname(__DIR__ )) .'/vendor/autoload.php';

use think\Cache;

define('IDCSMART_ROOT',dirname(dirname(__DIR__ )). '/'); # 网站根目录
define('WEB_ROOT',dirname(__DIR__ ) . '/'); # 网站入口目录

class UpgradeSystem
{
    public $upload_dir = IDCSMART_ROOT.'public/upgrade/';//客户升级包目录

    public $root_dir = IDCSMART_ROOT; //站点代码的根目录

    public $progress_log = IDCSMART_ROOT . "public/upgrade/progress.log"; //记录进度

    public $file_log = IDCSMART_ROOT . "public/upgrade/file.log"; //记录文件覆盖进度

    public $mysql_log = IDCSMART_ROOT . "public/upgrade/mysql.log"; //记录mysql进度

    public $php_exec_log = IDCSMART_ROOT . "public/upgrade/php_exec.log"; //记录php执行进度

    # 验证是否登录后台
    public function checkLogin()
    {
        $Authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if(empty($Authorization)){
            return ['status'=>400,'msg'=>"未登录后台，请登录后重试"];
        }
        $jwt = count(explode(' ',$Authorization))>1?explode(' ',$Authorization)[1]:'';

        return $this->verifyJwt($jwt);
    }

    protected function verifyJwt($jwt)
    {
        $idcsmart = include IDCSMART_ROOT."/config/idcsmart.php";
        $key = $idcsmart['jwt_key_admin'] . AUTHCODE;

        try{
            $jwtAuth = json_decode(json_encode(\Firebase\JWT\JWT::decode($jwt, new \Firebase\JWT\Key($key,'HS256'))),true);

            if (empty($jwtAuth['info'])){
                return ['status'=>400,'msg'=>'未登录'];
            }

            $info = $jwtAuth['info'];

            $data = [
                'id'                   =>  $info['id'],
                'name'                 =>  $info['name'],
                'remember_password'    =>  isset($info['remember_password'])?$info['remember_password']:0, # 前台不需要就不传此值
                'nbf'                  =>  $jwtAuth['nbf'],
                'ip'                   =>  $jwtAuth['ip'],
                'is_admin'             =>  isset($info['is_admin'])?$info['is_admin']:false, # 是否后台验证
            ];

            return ['status'=>200,'data'=>$data];

        } catch (\Firebase\JWT\SignatureInvalidException $e) { # token无效
            return ['status'=>400,'msg'=>'未登录' . ':' . $e->getMessage()];
        } catch (\Firebase\JWT\ExpiredException $e) { # token过期
            return ['status'=>400,'msg'=>'未登录' . ':' . $e->getMessage()];
        } catch (\Exception $e) {
            return ['status'=>400,'msg'=>'未登录' . ':' . $e->getMessage()];
        }
    }


    public function checkMd5($file)
    {
        if(file_exists($file.'.md5')){
            $md5 = file_get_contents($file.'.md5');
        }else{
            return ['status'=>200];
        }
        if(md5($file)!=$md5){
            return ['status'=>400,'msg'=>"安装包MD5错误"];
        }else{
            return ['status'=>200];
        }
    }

    public function upgradeStart()
    {
        set_time_limit(0);
        $handler = opendir($this->upload_dir);
        while( ($filename = readdir($handler)) !== false ) {
            if ($filename == "." && $filename == "..")continue;
            if (preg_match('/\.zip$/i', $filename))$zips[] = $filename;
        }
        closedir($handler);
        if(empty($zips)){
            return json_encode(['status'=>400,'msg'=>"没有可用的安装包"]);
        }

        $last_version = 0;
        $last_zip = '';
        foreach ($zips as $key => $value) {
            $zip = zip_open($this->upload_dir.$value);
            if($zip){
                while($zip_entry = zip_read($zip)){
                    $filename = zip_entry_name($zip_entry);
                    if (preg_match('/\.version$/i', $filename)){
                        $version = str_replace('.version', '', $filename);
                        if(version_compare($version, $last_version, '>')){
                            $last_version = $version;
                            $last_zip = $value;
                        }
                    }
                    
                }
                zip_close($zip);
            }
        }
        if(empty($last_version)){
            return json_encode(['status'=>400,'msg'=>"没有可升级的安装包"]);
        }
        $this->setSession('upgrade_system_version', $last_version);
        $package_name = glob($this->upload_dir.$last_zip);
        $package = array_pop($package_name);
        $res = $this->checkMd5($package);
        if($res['status']=='400'){
            return json_encode($res);
        }
        $progress_log['progress'] = "0%";
        $progress_log['file_name'] = basename($package);
        $progress_log['package_name'] = $last_version;
        $progress_log['setup'] = "unzip";
        $progress_log['msg'] = '开始解压';
        $progress_log['status'] = 200;
        $this->updateProgress(json_encode($progress_log));

        return $this->upgradeUnzip();

    }


    # 检测系统更新进度
    public function getUpgradeProgress()
    {
        $progress_log = $this->progress_log;
        if(!file_exists($progress_log)){
            return json_encode(['status' => 200 , 'msg' => '']);
        }
        //设置超时时间10s
        $timeout = [
            'http' => [
                'timeout' => 10
            ]
        ];
        $ctx = stream_context_create($timeout);
        //获取升级版本记录信息
        $handle = fopen($progress_log, 'r',false,$ctx);
        if (!$handle){
            return json_encode(['status' => 200 , 'msg' => '']);
        }
        $content = '';
        while(!feof($handle)){
            $content .= fread($handle, 8080);
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
        $last = array_pop($arr);
        $data = json_decode($last,true);
        if(file_exists($this->file_log) && $data['progress']=='40%'){
            $data['file_log'] = array_values(array_filter(explode("\n", file_get_contents($this->file_log))));
        }
        if(file_exists($this->mysql_log) && $data['progress']=='70%'){
            $data['mysql_log'] = array_values(array_filter(explode("\n", file_get_contents($this->mysql_log))));
        }
        if(file_exists($this->php_exec_log) && $data['progress']=='80%'){
            $data['php_exec_log'] = array_values(array_filter(explode("\n", file_get_contents($this->php_exec_log))));
        }
        return json_encode($data);
    }

    # 解压文件
    public function upgradeUnzip()
    {
        $progresslog=file_get_contents($this->progress_log);
        $progresslog=json_decode($progresslog,true);
        if($progresslog['setup']=="unzip"){                 
            $progress_log['progress'] = "10%";
            $progress_log['file_name'] = $progresslog['file_name'];
            $progress_log['package_name'] = $progresslog['package_name'];
            $progress_log['msg'] = '正在解压';
            $progress_log['status'] = 200;
            $this->updateProgress(json_encode($progress_log));
            $res = $this->unzip($this->upload_dir.$progresslog['file_name'],$this->upload_dir);

            if ($res['status'] == 200){
                $progress_log['progress'] = "30%";
                $progress_log['file_name'] = $progresslog['file_name'];
                $progress_log['package_name'] = $progresslog['package_name'];
                $progress_log['setup'] = "copy";
                $progress_log['setup_copy'] = "no";
                $progress_log['msg'] = '解压成功';
                $progress_log['status'] = 200;
                $this->updateProgress(json_encode($progress_log));
                return $this->upgradeFileMove(); 
            }else{
                $progress_log['progress'] = "10%";
                $progress_log['msg'] = '解压失败,失败code:' . $res['msg'] . ";请到网站目录下解压下载的文件或者重新更新系统";
                $progress_log['status'] = 400;
                $this->updateProgress(json_encode($progress_log));
                #清理失败文件
                $this->deleteUpgrdeFile($progresslog['file_name'],$progresslog['package_name']);
                return json_encode(['status'=>400, 'msg'=>$progress_log['msg']]);
            }
        }
        return json_encode(['status'=>400, 'msg'=>'升级步骤错误']);
    }

    # 文件升级替换
    public function upgradeFileMove()
    {
        $progresslog=file_get_contents($this->progress_log);
        $progresslog=json_decode($progresslog,true);
        if($progresslog['setup']=="copy"){
            $package_name = $progresslog['package_name'];
            $file_name = $progresslog['file_name'];
            $progress_log['progress'] = "40%";
            $progress_log['msg'] = '正在复制文件';
            $progress_log['status'] = 200;
            $this->updateProgress(json_encode($progress_log));
            
            
            $php_address = $this->upload_dir . $package_name;
            if (is_dir($php_address)){
                $admin_application = DIR_ADMIN;
                if ($admin_application != 'admin'){
                    rename($php_address . '/public/admin',$php_address . '/public/' .$admin_application);
                }
                $res = $this->recurseCopy($php_address,$this->root_dir);
                if ($res['status'] == 200){
                    //TODO 删除升级包，解压包
                    chmod($this->upload_dir . $package_name,0777);
                    $this->deleteDir($this->upload_dir . $package_name );
                    unlink($this->upload_dir . $file_name );
                    unlink($this->upload_dir . $package_name. '.version' );
                    // 删除自定义前端文件,重命名新的前端admin文件为自定义后台路径
                    /*if ($admin_application != 'admin'){
                        if (is_dir($this->upload_dir . 'public/admin')){ # 且更新的后台文件已经下载下来                                                   
                            //移动后台除了包里面的其它自定义文件
                            $custom_dir=array_diff(scandir($this->upload_dir . 'public/' . $admin_application),scandir($this->upload_dir . 'public/admin'));
                            if(is_array($custom_dir)){
                                foreach($custom_dir as $v){
                                    $admin_path=$this->upload_dir . 'public/admin/'.$v;                 
                                    $original_path=$this->upload_dir . 'public/' . $admin_application."/".$v;
                                    if(is_dir($original_path)){
                                        $this->recurseCopy($original_path,$admin_path);
                                    }else if(is_file($original_path)){
                                        copy($original_path,$admin_path);
                                    }
                                }
                            }
                            $this->deleteDir($this->upload_dir . 'public/' . $admin_application  . "_old"); # 删除旧包  
                            usleep(100000);
                            rename($this->upload_dir . 'public/' . $admin_application,$this->upload_dir . 'public/' .$admin_application . "_old");
                        }
                        usleep(100000);
                        rename($this->upload_dir . 'public/admin',$this->upload_dir . 'public/' .$admin_application);
                    }*/

                    $progress_log['progress'] = "60%";
                    $progress_log['msg'] = '文件覆盖完成';
                    $progress_log['status'] = 200;

                    $this->updateProgress(json_encode($progress_log));
                    return $this->upgradeSql();
                }else{
                    $progress_log['progress'] = "40%";
                    $progress_log['msg'] = '升级失败' . ":文件".$res['data']."复制出错";
                    $progress_log['status'] = 400;
                    $this->updateProgress(json_encode($progress_log));
                    #清理失败文件
                    //$this->deleteUpgrdeFile($file_name,$package_name);
                    return json_encode(['status'=>400, 'msg'=>$progress_log['msg']]);
                }
            }
            return json_encode(['status'=>400, 'msg'=>'升级步骤错误']);

        }
        return json_encode(['status'=>400, 'msg'=>'升级步骤错误']);
    }

    # 更新数据库
    public function upgradeSql()
    {

            $version = $this->configuration('system_version');
            if (empty($version)){
                return json_encode(['status'=>400,'msg'=>"未获取到本系统版本号,请联系系统管理员"]);
            }

            $defaultCharset = 'utf8mb4';
            $charset = DATABASE_CHARSET;
            $charset = $charset?:$defaultCharset;

            $defaultTablePre = 'idcsmart_';
            $prefix = 'idcsmart_';
            $prefix = $prefix?:$defaultTablePre;

            $file_name = IDCSMART_ROOT . "/public/upgrade/upgrade.log";
            $handle = fopen($file_name, 'r');
            if (!$handle){
                return json_encode(['status'=>400,'msg'=>"未找到".$file_name]);
            }
            $content = '';
            while (!feof($handle)) {
                $content .= fread($handle, 8080);
            }
            fclose($handle);
            $arr = explode("\n", $content);
//过滤空值
            $fun = function ($value) {
                if (empty($value)) {
                    return false;
                } else {
                    return true;
                }
            };
            $arr = array_filter($arr, $fun);
            $arr_last_pop = array_pop($arr);
            $arr_last = explode(',', $arr_last_pop);
//获取最新记录
            $arr[] = $arr_last_pop;
            $last_version = $arr_last[1];
            if (version_compare($last_version, $version, '>')) {
                $progress_log['progress'] = "70%";
                $progress_log['msg'] = 'SQL执行开始';
                $progress_log['status'] = 200;
                $this->updateProgress(json_encode($progress_log));
                $db = $this->dbConnect();
                $mysqlLog = '';
                $this->updateProgress($mysqlLog, 'mysql');
                foreach ($arr as $v) {
                    $v = explode(',', $v);
                    $sql_version = $v[1];
                    $sql_file = IDCSMART_ROOT.'public/upgrade/' .$v[1] . '.sql';#sql 文件
                    if (version_compare($sql_version, $version, '>')) {
                        if (file_exists($sql_file)) {
                            //读取SQL文件
                            $sql = file_get_contents($sql_file);
                            $sql = str_replace("\r", "\n", $sql);
                            $sql = str_replace("BEGIN;\n", '', $sql);//兼容 navicat 导出的 insert 语句
                            $sql = str_replace("COMMIT;\n", '', $sql);//兼容 navicat 导出的 insert 语句
                            $sql = str_replace($defaultCharset, $charset, $sql);
                            $sql = trim($sql);
                            //替换表前缀
                            $sql = str_replace(" `{$defaultTablePre}", " `{$prefix}", $sql);
                            $sqls = explode(";\n", $sql);
                            foreach ($sqls as $sql) {
                                try {
                                    $db->exec($sql);
                                    $mysqlLog .= ($sql."\n");
                                    $this->updateProgress($mysqlLog, 'mysql');
                                } catch (\Exception $e) {
                                    $progress_log['progress'] = "70%";
                                    $progress_log['msg'] = '升级失败' . ": SQL执行失败，失败语句: ".$sql.", 错误信息: ".$e->getMessage();
                                    $progress_log['status'] = 400;
                                    $this->updateProgress(json_encode($progress_log));
                                    return json_encode(['status'=>400,'msg'=>"SQL执行失败，失败语句: ".$sql.", 错误信息: ".$e->getMessage()]);
                                }
                            }
                        }
                    }
                }
                $progress_log['progress'] = "80%";
                $progress_log['msg'] = 'SQL执行结束';
                $progress_log['status'] = 200;
                $this->updateProgress(json_encode($progress_log));

                $phpLog = '';
                $this->updateProgress($phpLog, 'php');
                $server_http=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on')?'https://':'http://';
                $httpHost = parse_url($_SERVER['HTTP_HOST']);
                $domain = ($httpHost['host'].($httpHost['port'] ? (':'.$httpHost['port']) : ''))?:$httpHost['path'];

                foreach ($arr as $v) {
                    $v = explode(',', $v);
                    $php_version = $v[1];
                    $php_file = IDCSMART_ROOT.'public/upgrade/' . $v[1] . '.php';#php 文件
                    if (version_compare($php_version, $version, '>')) {
                        if (file_exists($php_file)) {
                            $res = $this->curl($server_http.$domain.'/upgrade/' . $v[1] . '.php');
                            
                            if ($res['http_code']==200) {
                                $phpLog .= ('已执行'.$php_file."\n");
                                $this->updateProgress($phpLog, 'php');
                            }else{
                                $progress_log['progress'] = "90%";
                                $progress_log['msg'] = '升级失败' . ": PHP执行失败，失败PHP: ".$php_file;
                                $progress_log['status'] = 400;
                                $this->updateProgress(json_encode($progress_log));
                                return json_encode(['status'=>400,'msg'=>"PHP执行失败，失败PHP: ".$php_file]);
                            }
                            
                        }
                    }
                }
            }

            $this->updateConfiguration('system_version', $last_version);
            $this->updateConfiguration('executed_update', 1);
            $progress_log['progress'] = "100%";
            $progress_log['msg'] = '升级完成';
            $progress_log['status'] = 200;
            $this->updateProgress(json_encode($progress_log));
// 升级成功,注销登录
            session_start();
            $_SESSION = [];
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600, '/');
            }
            session_destroy();
            @unlink($this->progress_log);
            @unlink($this->file_log);
            @unlink($this->mysql_log);
            @unlink($this->php_exec_log);
            return json_encode(['status'=>200,'msg'=>"恭喜你，升级完成\n系统升级已完成，请删除public/upgrade目录"]);


    }

    /**
     * 返回系统升级的进度
     */
    public function updateProgress($progress, $type = 'progress')
    {
        if($type=='file'){
            file_put_contents($this->file_log,$progress."\n");
        }else if($type=='mysql'){
            file_put_contents($this->mysql_log,$progress."\n");
        }else if($type=='php'){
            file_put_contents($this->php_exec_log,$progress."\n");
        }else{
            file_put_contents($this->progress_log,$progress."\n");
        }
        
    }



    #节点错误时候执行,清理异常文件
    private function deleteUpgrdeFile($file_name,$package_name)
    {
        $check_version = $this->getSession('upgrade_system_version');
        #无验证数据不执行，防止服务器上面数据被错误删除
        if (empty($check_version))return false;
        #删除压缩包
        if (!empty($file_name) && strpos($file_name,$check_version) !== false) {
            @unlink($this->upload_dir . $file_name);
        }
        #删除解压目录
        if (!empty($package_name) && strpos($package_name,$check_version) !== false) {
            chmod($this->upload_dir . $package_name, 0777);
            $this->deleteDir($this->upload_dir . $package_name);
        }
    }

    /*
     * 递归复制文件
     * @src 原目录
     * @dst 复制到的目录
     * @out 排除
     */
    private function recurseCopy($src,$dst,$out=[])
    {
        $dir = opendir($src);
        @mkdir($dst,0777,true);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    if (empty($out) || !in_array($file,$out)){
                        $this->recurseCopy($src . '/' . $file,$dst . '/' . $file);
                    }

                } else {
                    if(!file_exists($this->file_log)){
                        $this->updateProgress('', 'file');
                    }
                    $fileLog = file_get_contents($this->file_log);
                    $fileLog .= ('正在移动文件'.$src . '/' . $file."到".$dst."\n");
                    $this->updateProgress($fileLog, 'file');
                    $res = rename($src . '/' . $file,$dst . '/' . $file);
                    if (!$res){ // TODO 只要复制失败,中断复制,中文文件！
                        return ['status' => 400,'data'=>$file];
                    }
                }
            }
        }
        closedir($dir);
        return ['status' => 200];
    }

    /*
     * 递归删除目录
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


    /*
     * 解压文件
     */
    private function unzip($filepath,$path)
    {
        $zip = new ZipArchive();

        $res = $zip->open($filepath);
        if ( $res === true) {
            //解压文件到获得的路径a文件夹下
            if (!file_exists($path)){
                mkdir($path,0777,true);
            }
            $zip->extractTo($path);
            //关闭
            $zip->close();
            return ['status' => 200 , 'msg' => '成功'];
        } else {
            return ['status' => 400 , 'msg' => $res];
        }
    }

    public function configuration($name)
    {
        $database = include IDCSMART_ROOT."/config/database.php";
        $database = $database['connections']['mysql'];
        $db = new PDO("mysql:host={$database['hostname']};port={$database['hostport']};dbname={$database['database']}",$database['username'],$database['password']);
        $configuration = $db->query("SELECT * FROM {$database['prefix']}configuration WHERE setting='{$name}'")->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($configuration)){
            $configuration = $configuration[0];
        }
        return $configuration['value']??'';
    }

    public function updateConfiguration($name, $value)
    {
        $database = include IDCSMART_ROOT."/config/database.php";
        $database = $database['connections']['mysql'];
        $db = new PDO("mysql:host={$database['hostname']};port={$database['hostport']};dbname={$database['database']}",$database['username'],$database['password']);
        $configuration = $db->query("SELECT * FROM {$database['prefix']}configuration WHERE setting='{$name}'")->fetchAll(PDO::FETCH_ASSOC);
        $time = time();
        if(!empty($configuration)){
            $db->exec("UPDATE {$database['prefix']}configuration SET value='{$value}',update_time={$time} WHERE setting='{$name}'");
        }else{
            $db->exec("INSERT INTO {$database['prefix']}configuration (`setting`,`value`,`create_time`) VALUES('{$name}','{$value}',{$time})");
        }
        return true;
    }

    public function dbConnect()
    {
        $database = include IDCSMART_ROOT."/config/database.php";
        $database = $database['connections']['mysql'];
        $db = new PDO("mysql:host={$database['hostname']};port={$database['hostport']};dbname={$database['database']}",$database['username'],$database['password']);
        return $db;
    }

    /**
     * Session-session_start()
     * @return string   
     */
    public function startSession() {
        @session_start();
    }

    /**
     * Session-设置session值
     * @param  string $key    key值，可以为单个key值，也可以为数组
     * @param  string $value  value值
     * @return string   
     */
    public function setSession($key='', $value='') {
        if (!session_id()) $this->startSession();
        if (!is_array($key)) {
            $_SESSION[$key] = $value;
        } else {
            foreach ($key as $k => $v) $_SESSION[$k] = $v;
        }
        return true;
    }

    /**
     * Session-获取session值
     * @param  string $key    key值
     * @return string   
     */
    public function getSession($key='') {
        if (!session_id()) $this->startSession();
        $res=(isset($_SESSION[$key])) ? $_SESSION[$key] : NULL;
        return $res;
    }

    /**
     * Session-删除session值
     * @param  string $key    key值
     * @return string   
     */
    public function delSession($key='') {
        if (!session_id()) $this->startSession();
        if (is_array($key)) {
            foreach ($key as $k){
                if (isset($_SESSION[$k])) unset($_SESSION[$k]);
            }
        } else {
            if (isset($_SESSION[$key])) unset($_SESSION[$key]);
        }
        return true;
    }
    /**
     * Session-清空session
     * @return   
     */
    public function clearSession() {
        if (!session_id()) $this->startSession();
        session_destroy();
        $_SESSION = array();
    }

    # 公共curl
    public function curl($url, $data = [], $timeout = 30, $request = 'POST', $header = [])
    {
        $curl = curl_init();
        $request = strtoupper($request);

        if($request == 'GET'){
            $s = '';
            if(!empty($data)){
                foreach($data as $k=>$v){
                    if(empty($v)){
                        $data[$k] = '';
                    }
                }
                $s = http_build_query($data);
            }
            if($s){
                $s = '?'.$s;
            }
            curl_setopt($curl, CURLOPT_URL, $url.$s);
        }else{
            curl_setopt($curl, CURLOPT_URL, $url);
        }
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //curl_setopt($curl, CURLOPT_REFERER, request() ->host());
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

    private function getClientIp(){
        if(getenv('HTTP_CLIENT_IP')) { 
            $onlineip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR')) { 
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR')) { 
            $onlineip = getenv('REMOTE_ADDR');
        } else { 
            $onlineip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
        }
        if($onlineip=='::1'){
            return '127.0.0.1';
        }else if($onlineip==''){
            return '127.0.0.1';
        }else if (!preg_match('/^(?:(?:2[0-4][0-9]\.)|(?:25[0-5]\.)|(?:1[0-9][0-9]\.)|(?:[1-9][0-9]\.)|(?:[0-9]\.)){3}(?:(?:2[0-4][0-9])|(?:25[0-5])|(?:1[0-9][0-9])|(?:[1-9][0-9])|(?:[0-9]))$/', $onlineip)) {
            return '0.0.0.0';           
        }else{
            return $onlineip;
        }
    }
}

$UpgradeSystem = new UpgradeSystem();

$res = $UpgradeSystem->checkLogin(); 
if($res['status']==400){
    echo json_encode($res);die;
}

if($param['action']=='progress'){
    $res = $UpgradeSystem->getUpgradeProgress();
}else if($param['action']=='upgrade'){
    $res = $UpgradeSystem->upgradeStart();
}else{
    $res = json_encode(['status'=>400, 'msg'=>'请求错误']);
}
echo $res;