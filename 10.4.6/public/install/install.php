<?php
define('IDCSMART_ROOT',dirname(dirname(__DIR__ )). '/'); # 网站根目录


/**
 * @title 系统安装
 * Class Install
 */
class InstallSystem
{
    public function checkIsInstalled()
    {
        if(file_exists(IDCSMART_ROOT.'/config.php')){
            require IDCSMART_ROOT . '/config.php';
            $host = defined('DATABASE_HOSTNAME') ? DATABASE_HOSTNAME : '';
            $port = defined('DATABASE_PORT') ? DATABASE_PORT : '';
            $dbname = defined('DATABASE_NAME') ? DATABASE_NAME : '';
            $user = defined('DATABASE_USERNAME') ? DATABASE_USERNAME : '';
            $pass = defined('DATABASE_PASSWORD') ? DATABASE_PASSWORD : '';
            $charset = defined('DATABASE_CHARSET') ? DATABASE_CHARSET : '';
            try{
                $opts_values = array(PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES '.$charset);
                $dbObject = new PDO("mysql:host={$host};port={$port};dbname={$dbname}",$user,$pass,$opts_values);
                $res = $dbObject->query("SHOW TABLES")->fetchAll(PDO::FETCH_ASSOC);
                $tables = [];
                foreach ($res as $k => $v) {
                    $tables[] = $v['Tables_in_'.DATABASE_NAME];
                }
                if(in_array('idcsmart_configuration', $tables)){
                    $res = $dbObject->query("SELECT * FROM idcsmart_admin")->fetchAll(PDO::FETCH_ASSOC);
                    if(!empty($res)){
                        return json_encode(['status' => 400, 'msg' => '系统已安装']);
                    }
                }
                return json_encode(['status' => 200]);
            }catch (PDOException $e){
                //return json_encode(['status' => 400, 'msg' => $e->getMessage()]);
                return json_encode(['status' => 200]);
            }
        }else{
            return json_encode(['status' => 200]);
        }
    }

    # 环境检测
    public function envMonitor()
    {
        /*$status = $this->getSession('session_is_open');
        if (!$status) {
            return json_encode(['status' => 400, 'msg' => '请开启浏览器Cookie']);
        }*/
        $error = 0;
        $envs = [];
        #监测-PHP版本
        if (!version_compare(phpversion(), '7.2.5', '>=') || !version_compare(phpversion(), '7.4.0', '<')) {
            $error++;
            $env['status'] = 0;
        }else{
            $env['status'] = 1;
        }
        $env['name'] = 'PHP版本';
        $env['suggest'] = '>=7.2.5, <7.4.0';
        $env['current'] = phpversion();
        $env['worst'] = '7.2.5';
        $envs[] = $env;

        $modules = [];
        /*#监测-opcache
        if (extension_loaded('Zend OPcache')) {
            $error++;
            $module['status'] = 0;
            $module['current'] = '已开启';
        }else{
            $module['status'] = 1;
            $module['current'] = '未开启';
        }
        $module['name'] = 'opcache';
        $module['suggest'] = '未开启';
        $module['worst'] = '未开启';
        $modules[] = $module;*/

        #监测-session
        if (!function_exists('session_start')) {
            $error++;
            $module['status'] = 0;
            $module['current'] = '不支持';
        }else{
            $module['status'] = 1;
            $module['current'] = '支持';
        }
        $module['name'] = 'session';
        $module['suggest'] = '开启';
        $module['worst'] = '开启';
        $modules[] = $module;

        #监测-PDO
        if (!class_exists('pdo')) {
            $error++;
            $module['status'] = 0;
            $module['current'] = '未开启';
        }else{
            $module['status'] = 1;
            $module['current'] = '开启';
        }
        $module['name'] = 'PDO';
        $module['suggest'] = '开启';
        $module['worst'] = '开启';
        $modules[] = $module;

        #监测-PDO_MySQL
        if (!extension_loaded('pdo_mysql')) {
            $error++;
            $module['status'] = 0;
            $module['current'] = '未开启';
        }else{
            $module['status'] = 1;
            $module['current'] = '开启';
        }
        $module['name'] = 'PDO_MySQL';
        $module['suggest'] = '开启';
        $module['worst'] = '开启';
        $modules[] = $module;

        #监测-CURL
        if (!extension_loaded('curl')) {
            $error++;
            $module['status'] = 0;
            $module['current'] = '未开启';
        }else{
            $module['status'] = 1;
            $module['current'] = '开启';
        }
        $module['name'] = 'CURL';
        $module['suggest'] = '开启';
        $module['worst'] = '开启';
        $modules[] = $module;

        /*#监测-伪静态
        if (!extension_loaded('curl')) {
            $error++;
            $module['status'] = 0;
            $module['current'] = 'CURL未开启,无法检测';
        }else{
            $server_http=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on')?'https://':'http://';
            $arr = parse_url($_SERVER['HTTP_HOST']);
            $domain = ($arr['host'].($arr['port'] ? (':'.$arr['port']) : ''))?:$arr['path'];

            $res = $this->curl($server_http.$domain.'/console/v1/login', [], 2);
            
            if ($res['http_code']==404) {
                $error++;
                $module['status'] = 0;
                $module['current'] = '未开启';
            }else{
                $module['status'] = 1;
                $module['current'] = '开启';
            } 
        }
        
        $module['name'] = '伪静态';
        $module['suggest'] = '开启';
        $module['worst'] = '开启';
        $module['doc'] = 'Apache伪静态代码
        <IfModule mod_rewrite.c>

  RewriteEngine On

  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^(.*)$ index.php?s=$1 [QSA,PT,L]

  RewriteCond %{HTTP:Authorization} .
  RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

</IfModule>
Nginx伪静态代码
location / {
    if (!-e $request_filename){
        rewrite  ^(.*)$  /index.php?s=$1  last;   break;
    }
}';
        $modules[] = $module;*/

        #监测-GD
        if (!extension_loaded('gd')) {
            $error++;
            $module['status'] = 0;
            $module['current'] = '未开启';
        }else{
            $module['status'] = 1;
            $module['current'] = '开启';
        }
        $module['name'] = 'GD';
        $module['suggest'] = '开启';
        $module['worst'] = '开启';
        $modules[] = $module;

        #监测-GD拓展
        if (!function_exists('imagettftext')) {
            $module['current'] .= '未开启';
            $module['status'] = 0;
            $error++;
        }else{
            $module['current'] = '开启';
            $module['status'] = 1;
        }
        $module['name'] = 'FreeType Support';
        $module['suggest'] = '开启';
        $module['worst'] = '开启';
        $modules[] = $module;

        #监测-MBstring
        if (!extension_loaded('mbstring')) {
            $module['current'] = '未开启';
            $module['status'] = 0;
            $error++;
        }else{
            $module['status'] = 1;
            $module['current'] = '开启';
        }
        $module['name'] = 'MBstring';
        $module['suggest'] = '开启';
        $module['worst'] = '开启';
        $modules[] = $module;

        #监测-fileinfo
        if (!extension_loaded('fileinfo')) {
            $error++;
            $module['current'] = '未开启';
            $module['status'] = 0;
        }else{
            $module['current'] = '开启';
            $module['status'] = 1;
        }
        $module['name'] = 'fileinfo';
        $module['suggest'] = '开启';
        $module['worst'] = '开启';
        $modules[] = $module;

        #监测-ionCube（升级需要此模块须强制）
        /*if (!extension_loaded('ionCube Loader')) {
            $error++;
            $module['current'] = '未开启';
            $module['status'] = 0;
        }else{
            $module['current'] = '开启';
            $module['status'] = 1;
        }
        $module['name'] = 'ionCube';
        $module['suggest'] = '开启';
        $module['worst'] = '开启';
        $modules[] = $module;*/


        #监测-附件上传
        if (!ini_get('file_uploads')) {
            $error++;
            $module['current'] = '禁止上传';
            $module['status'] = 0;
        }else{
            $size = ini_get('upload_max_filesize');
            $module['current'] = $size;
            $module['status'] = 1;
        }
        $module['name'] = '附件上传';
        $module['suggest'] = '>50M';
        $module['worst'] = '不限制';
        $modules[] = $module;

        #监测-ssl
        if (!extension_loaded('openssl')) {
            $error++;
            $module['current'] = '未开启';
            $module['status'] = 0;
        }else{
            $module['current'] = '开启';
            $module['status'] = 1;
        }
        $module['name'] = 'openssl';
        $module['suggest'] = '开启';
        $module['worst'] = '开启';
        $modules[] = $module;

        #监测-bcmath
        if (!extension_loaded('bcmath')) {
            $error++;
            $module['current'] = '未开启';
            $module['status'] = 0;
        }else{
            $module['current'] = '开启';
            $module['status'] = 1;
        }
        $module['name'] = 'bcmath';
        $module['suggest'] = '开启';
        $module['worst'] = '开启';
        $modules[] = $module;

        #监测-zip
        if (!extension_loaded('zip')) {
            $error++;
            $module['current'] = '未开启';
            $module['status'] = 0;
        }else{
            $module['current'] = '开启';
            $module['status'] = 1;
        }
        $module['name'] = 'zip';
        $module['suggest'] = '开启';
        $module['worst'] = '开启';
        $modules[] = $module;

        #监测-ionCube
        if (!extension_loaded('ionCube Loader')) {
            $module['current'] = '未开启';
            $module['status'] = 0;
            $error++;
        }else{
            $module['current'] = '开启';
            $module['status'] = 1;
        }
        $module['name'] = 'ionCube';
        $module['suggest'] = '开启';
        $module['worst'] = '开启';
        $modules[] = $module;

        

        #检测-文件
        $folders = [
            realpath('../plugins') . DIRECTORY_SEPARATOR,
            realpath('../upload') . DIRECTORY_SEPARATOR,
            realpath('../../'),
        ];
        $newFolders = [];
        foreach ($folders as $k => $dir) {
            $testDir = $dir;
            if (strpos($dir,'.') === false){
                $this->spDirCreate($testDir);
            }
            if (!$this->newIsWriteable($testDir)) {
                $newFolders[$k]['name'] = $dir;
                $newFolders[$k]['write']= 0;
                $newFolders[$k]['read']= '';
                $error++;
            }else{
                $newFolders[$k]['name'] = $dir;
                $newFolders[$k]['write']= 1;
                $newFolders[$k]['read']= ''; 
            }
            if (!is_readable($testDir)) {
                $newFolders[$k]['name'] = $dir;
                $newFolders[$k]['read']= 0;
                $newFolders[$k]['write']= $newFolders[$k]['write'] ?: '';
                $error++;
            }else{
                $newFolders[$k]['name'] = $dir;
                $newFolders[$k]['read']= 1;
                $newFolders[$k]['write']= $newFolders[$k]['write'] ?: '';
            }
        }

        $this->setSession('install_error',$error);
        $data['envs'] = $envs;
        $data['modules'] = $modules;
        $data['folders'] = $newFolders;
        $data['error'] = $error;
        return json_encode(['status' => 200, 'data' => $data]);
    }

    # 数据库检测
    public function dbMonitor($param)
    {
        $error = $this->getSession('install_error');
        if ($error) {
            return json_encode(['status' => 400, 'msg' => '为保证软件正常使用,请修复检测未通过项！']);
        }
        
        $config['hostname'] = $param['hostname'];
        $config['username'] = $param['username'];
        $config['password'] = $param['password'];
        $config['hostport'] = $param['hostport'];
        $config['type'] = "mysql";
        $dbname = $param['dbname'];

        if (empty($config['hostname'])) return json_encode(['status' => 400, 'msg' => '数据库地址不可以为空！']);
        if (empty($dbname)) return json_encode(['status' => 400, 'msg' => '数据库名不可以为空！']);
        if (empty($config['username'])) return json_encode(['status' => 400, 'msg' => '用户名不可以为空！']);
        if (empty($config['password'])) return json_encode(['status' => 400, 'msg' => '密码不可以为空！']);
        if (empty($config['hostport'])) return json_encode(['status' => 400, 'msg' => '端口不可以为空！']);

        #数据库连接
        try {
            $opts_values = array(PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES utf8mb4');
            $dbObject = new PDO("mysql:host={$config['hostname']};port={$config['hostport']}",$config['username'],$config['password'],$opts_values);
            $version = $dbObject->query("SELECT version();")->fetchAll(PDO::FETCH_ASSOC);
            $version = $version[0]['version()'];

            $engines = $dbObject->query("SHOW ENGINES;")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($engines as $engine) {
                if ($engine['Engine'] == 'InnoDB' && $engine['Support'] != 'NO') {
                    $supportInnoDb = true;
                    break;
                }
            }
            ##库状态
            $databases = $dbObject->query("SHOW DATABASES")->fetchAll(PDO::FETCH_ASSOC);
            foreach($databases as $v){
                if ($v['Database'] === $dbname) {
                    $supportDdname = true;
                    break;
                }
            }
            if (!isset($supportDdname)){
                $dbObject->query("CREATE DATABASE `{$dbname}`");
            }
        } catch (PDOException $e) {
            return json_encode(['status' => 400, 'msg' => '数据库链接失败'.$e->getMessage()]);
        }

        if(version_compare($version, '5.6','>=') && version_compare($version, '5.8','<')){
            if ($supportInnoDb === true){
                $this->setSession('install_db_data',$config);
                $this->setSession('install_db_name',$dbname);
                $this->setSession('install_error',0);
                return json_encode(['status' => 200, 'data' => ['version' => $version]]);
            }else {
                return json_encode(['status' => 400, 'msg' => '数据库账号密码验证通过，但不支持InnoDb!']);
            }
        }else{
            return json_encode(['status' => 400, 'msg' => '数据库版本仅支持5.6,5.7']);
        }
    }


    # 网站配置
    public function envSystem($param)
    {
        $config = $this->getSession('install_db_data');

        #数据库配置
        $config['charset'] = 'utf8mb4';

        #网站配置
        $site_name = $param['sitename'];
        $arr = parse_url($_SERVER['HTTP_HOST']);
        $server_http=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on')?'https://':'http://';
        $domain = ($arr['host'].($arr['port'] ? (':'.$arr['port']) : ''))?:$arr['path'];
        $domain = $server_http.$domain;

        $admin_application = strtolower($this->randStr(8, 'CHAR'));

        if (empty($site_name)) return json_encode(['status' => 400, 'msg' => '系统名称不能为空！']);

        #管理员配置
        $user_login = $param['username'];
        $user_pass  = $param['password'];
        $user_email = $param['email'];
        $license = $param['license'];
        if(!empty($user_email)){
            $chars = "/^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/";
            if (strpos($user_email, '@') !== false && strpos($user_email, '.') !== false){
                if (!preg_match($chars, $user_email)){
                    return json_encode(['status' => 400, 'msg' => '邮箱格式错误！']);  
                }
            }else{
                return json_encode(['status' => 400, 'msg' => '邮箱格式错误！']);  
            }      
        } 
        
        if (empty($user_login)) return json_encode(['status' => 400, 'msg' => '管理员帐号不可以为空！']);
        if (empty($user_pass)) return json_encode(['status' => 400, 'msg' => '密码不可以为空！']);
        if (strlen($user_pass) < 6) return json_encode(['status' => 400, 'msg' => '密码长度最少6位！']);
        if (strlen($user_pass) > 32) return json_encode(['status' => 400, 'msg' => '密码长度最多32位！']);
        if(!empty($license)){
            $res = $this->get_idcsamrt_auth($license);
            if(!$res){
                return json_encode(['status' => 400, 'msg' => '授权码错误！']);
            }
        }

        #连接数据库
        $dbObject = new PDO("mysql:host={$config['hostname']};port={$config['hostport']}",$config['username'],$config['password']);
        $db_name = $this->getSession('install_db_name');
        $sql    = "CREATE DATABASE IF NOT EXISTS `{$db_name}` DEFAULT CHARACTER SET " . $config['charset'];
        if ($dbObject->exec($sql) === false) return json_encode(['status' => 400, 'msg' =>$dbObject->getError()]);

        #数据整合
        $config['database'] = $db_name;
        $config['admin_application'] = $admin_application;
        $config['prefix'] = 'idcsmart_';//数据表前缀

        $this->setSession('install_license', $license);
        $this->setSession('install_db_config', $config);

        $dir = realpath(IDCSMART_ROOT . '/public/install/idcsmart.sql');
        $sql  = $this->splitSql($dir, $config['prefix'], $config['charset']);

        $this->setSession('install_sql', $sql);
        $this->setSession('install_error', 0);
        $this->setSession('install_site_info', [
            'title' => $site_name,
            'domain' => $domain,
            'admin_application' => $admin_application,
        ]);

        $this->setSession('install_admin_info', [
            'name' => $user_login,
            'password'  => $user_pass,
            'email' => $user_email
        ]);
        $sql_num = ceil(count($sql)/100);
        return json_encode(['status' => 200, 'data' => ['sql_num'=>$sql_num]]);
    }

    # 写入config文件
    public function setDbConfig()
    {
        $config = $this->getSession('install_db_config');

        $config['authcode'] = $this->randStr(18);

        $this->setSession('install_authcode',$config['authcode']);

        $result = $this->spCreateDbConfig($config);

        if ($result) {
            return json_encode(['status' => 200, 'msg' => '配置文件写入成功！']);
        } else {
            return json_encode(['status' => 400, 'msg' => '配置文件写入失败！']);
        }
    }

    # 数据库安装
    public function install()
    {
        $config = $this->getSession('install_db_config');
        $sql = $this->getSession('install_sql');
        if (empty($config) || empty($sql)) {
            return json_encode(['status' => 400, 'msg' => '非法安装！']);
        }

        $sql_index = 0;

        $db = new PDO("mysql:host={$config['hostname']};port={$config['hostport']};dbname={$config['database']}",$config['username'],$config['password']);

        $i = count($sql);
        for ($x=0; $x<=$i; $x++) {
            $index = $sql_index*$i+$x;

            if ($index >= count($sql)) {

                $install_error = $this->getSession('install_error');
                return json_encode(['status' => 200, 'msg' => '安装完成！','data'=>['done' => 1, 'error' => $install_error]]);
            }

            $sql_to_exec = str_replace('idcsmart_',$config['prefix'],$sql[$index]) . ';';

            $result = $this->spExecuteSql($db, $sql_to_exec);

            if (!empty($result['error'])) {
                $install_error = $this->getSession('install_error');
                $install_error = empty($install_error) ? 0 : $install_error;

                $this->setSession('install_error', $install_error + 1);
                return json_encode(['status' => 400, 'msg' => $result['message'],'data'=>['sql'=> $sql_to_exec, 'exception' => $result['exception']]]);
            }
        }
        $index = $sql_index+1;

        return json_encode(['status' => 200, 'msg' => '[sql'.$index.']执行成功']);
    }

    # 写入数据
    public function setSite()
    {
        $config = $this->getSession('install_db_config');
        $authcode = $this->getSession('install_authcode');

        if (empty($config)) {
            return json_encode(['status' => 400, 'msg' => '非法安装！']);
        }

        $siteInfo               = $this->getSession('install_site_info');
        $admin                  = $this->getSession('install_admin_info');
        $license                = $this->getSession('install_license');
        $admin['id']            = 1;
        $admin['password']     = $this->idcsmart_password($admin['password'],$authcode);
        $admin['create_time']   = time();
        $admin['status']   = 1;
        $admin['nickname'] = $admin['name'];
        $db = new PDO("mysql:host={$config['hostname']};port={$config['hostport']};dbname={$config['database']}",$config['username'],$config['password']);
        try {
            $db->beginTransaction();
            //$auth = $db->query("SELECT `id` FROM `{$config['prefix']}auth`")->fetchAll(PDO::FETCH_ASSOC);
            //$auth = array_column($auth,'id');
            //$db->exec("DELETE FROM `{$config['prefix']}admin_role` WHERE `id`=1");
            $time = time();
            /*$db->exec("INSERT INTO `{$config['prefix']}admin_role` (`id`,`status`,`name`,`description`,`create_time`,`update_time`) VALUES (1,1,'超级管理员','拥有网站最高管理员权限',{$time},{$time})");
            $roleId = $db->lastInsertId();*/

            $db->exec("UPDATE `{$config['prefix']}admin` SET `nickname`='{$admin['nickname']}',`name`='{$admin['name']}',`password`='{$admin['password']}',`email`='{$admin['email']}',`status`={$admin['status']},`create_time`={$time},`update_time`={$time} WHERE `id`=1");
            /*$db->exec("DELETE FROM `{$config['prefix']}admin` WHERE `id`=1");
            $db->exec("INSERT INTO `{$config['prefix']}admin` (`id`,`nickname`,`name`,`password`,`email`,`status`,`create_time`,`update_time`) VALUES ({$admin['id']},'{$admin['nickname']}','{$admin['name']}','{$admin['password']}','{$admin['email']}',{$admin['status']},{$time},{$time})");*/
            //$userId = $db->lastInsertId();

            //$db->exec("INSERT INTO `{$config['prefix']}admin` (`id`,`nickname`,`name`,`password`,`email`,`status`,`create_time`,`update_time`) VALUES ({$admin['id']},'{$admin['nickname']}','{$admin['name']}','{$admin['password']}','{$admin['email']}',{$admin['status']},{$time},{$time})");

            /*$db->exec("INSERT INTO `{$config['prefix']}admin_role_link` (`admin_role_id`,`admin_id`) VALUES ({$roleId},{$userId})");

            foreach ($auth as $key => $value) {
                $db->exec("INSERT INTO `{$config['prefix']}auth_link` (`auth_id`,`admin_role_id`) VALUES ({$value},{$roleId})");
            }*/
            
            # 旧后台应用目录,实现重复安装,多次安装
            if(file_exists(IDCSMART_ROOT.'/config.php')){
                require IDCSMART_ROOT . '/config.php';
                $old_admin_application = defined('DIR_ADMIN') ? DIR_ADMIN : 'admin';
            }else{
                $old_admin_application = 'admin';
            }
            
            $db->exec("DELETE FROM `{$config['prefix']}configuration` WHERE `setting`='system_license'");
            $db->exec("INSERT INTO `{$config['prefix']}configuration` (`setting`,`value`,`create_time`,`update_time`,`description`) VALUES ('system_license','{$license}',{$time},{$time},'授权码')");
            $db->exec("DELETE FROM `{$config['prefix']}configuration` WHERE `setting`='website_url'");
            $db->exec("INSERT INTO `{$config['prefix']}configuration` (`setting`,`value`,`create_time`,`update_time`,`description`) VALUES ('website_url','{$siteInfo['domain']}',{$time},{$time},'网站域名地址')");
            $db->exec("DELETE FROM `{$config['prefix']}configuration` WHERE `setting`='website_name'");
            $db->exec("INSERT INTO `{$config['prefix']}configuration` (`setting`,`value`,`create_time`,`update_time`,`description`) VALUES ('website_name','{$siteInfo['title']}',{$time},{$time},'网站名称')");
            $db->exec("DELETE FROM `{$config['prefix']}configuration` WHERE `setting`='terms_service_url'");
            $db->exec("INSERT INTO `{$config['prefix']}configuration` (`setting`,`value`,`create_time`,`update_time`,`description`) VALUES ('terms_service_url','{$siteInfo['domain']}/agreement.htm?id=2',{$time},{$time},'服务条款地址')");
            $db->exec("DELETE FROM `{$config['prefix']}configuration` WHERE `setting`='terms_privacy_url'");
            $db->exec("INSERT INTO `{$config['prefix']}configuration` (`setting`,`value`,`create_time`,`update_time`,`description`) VALUES ('terms_privacy_url','{$siteInfo['domain']}/agreement.htm?id=1',{$time},{$time},'隐私条款地址')");

            $db->commit();
        } catch (PDOException $e) {
            $db->rollBack();
            return json_encode(['status' => 400, 'msg' => '网站创建失败！'. $e->getMessage()]);
        }

        // 重命名后台前端文件
        $admin_application = $config['admin_application']??'admin';
        if ($admin_application != 'admin'){
            if(file_exists(IDCSMART_ROOT . 'public/' . $old_admin_application)){
                rename(IDCSMART_ROOT . 'public/' . $old_admin_application,IDCSMART_ROOT . 'public/' . $admin_application);
            }else if(file_exists(IDCSMART_ROOT . 'public/admin')){
                rename(IDCSMART_ROOT . 'public/admin',IDCSMART_ROOT . 'public/' . $admin_application);
            }
        }
        rename(IDCSMART_ROOT . 'config.simple.php',IDCSMART_ROOT . 'config.php');
        $this->deleteDir(IDCSMART_ROOT.'public//upgrade', ['api','css','img','js','lang','utils','update.html','upgrade.log','upgrade.php']);
        $this->setSession('install_step', 4);
        return json_encode(['status' => 200, 'msg' => '网站创建完成！']);
    }

    # 完成步骤检测
    public function stepLast()
    {
        if ($this->getSession("install_step") == 4) {
            $server_http=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on')?'https://':'http://';
            $arr = parse_url($_SERVER['HTTP_HOST']);
            $domain = ($arr['host'].($arr['port'] ? (':'.$arr['port']) : ''))?:$arr['path'];
            $data['admin_url'] = $server_http.$domain.'/'.$this->getSession('install_db_config')['admin_application'];
            $data['admin_name'] = $this->getSession('install_admin_info')['name'];
            $data['admin_pass'] = $this->getSession('install_admin_info')['password'];
            $this->deleteDir(IDCSMART_ROOT.'public/install');
            return json_encode(['status' => 200, 'msg' => '安装完成！','data'=>$data]);
        } else {
            return json_encode(['status' => 200, 'msg' => '非法安装！']);
        }
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

    public function randStr($len=8,$format='ALL'){
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
            $password = $this->randStr($len,$format);
        }

        return $password;
    }

    public function spDirCreate($path, $mode = 0777)
    {
        if (is_dir($path))
            return true;
        $ftp_enable = 0;
        $path       = $this->spDirPath($path);
        $temp       = explode('/', $path);
        $cur_dir    = '';
        $max        = count($temp) - 1;
        for ($i = 0; $i < $max; $i++) {
            $cur_dir .= $temp[$i] . '/';
            if (@is_dir($cur_dir))
                continue;
            @mkdir($cur_dir, 0777, true);
            @chmod($cur_dir, 0777);
        }
        return is_dir($path);
    }

    public function spDirPath($path)
    {
        $path = str_replace('\\', '/', $path);
        if (substr($path, -1) != '/')
            $path = $path . '/';
        return $path;
    }

    public function newIsWriteable($file) {
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

    public function spExecuteSql($db, $sql)
    {
        $sql = trim($sql);
        preg_match('/CREATE TABLE .+ `([^ ]*)`/', $sql, $matches);
        if ($matches) {
            $table_name = $matches[1];
            $msg        = "创建数据表{$table_name}";
            try {
                $db->exec($sql);
                return [
                    'error'   => 0,
                    'message' => $msg . ' 成功！'
                ];
            } catch (PDOException $e) {
                return [
                    'error'     => 1,
                    'message'   => $msg . ' 失败！',
                    'exception' => $e->getTraceAsString()
                ];
            }

        } else {
            try {
                $db->exec($sql);
                return [
                    'error'   => 0,
                    'message' => 'SQL执行成功!'
                ];
            } catch (PDOException $e) {
                return [
                    'error'     => 1,
                    'message'   => 'SQL执行失败！',
                    'exception' => $e->getTraceAsString()
                ];
            }
        }
    }
    public function spCreateDbConfig($config)
    {
        if (is_array($config)) {
            //读取配置内容
            $conf = file_get_contents( IDCSMART_ROOT . '/public/install/config.php');

            //替换配置项
            foreach ($config as $key => $value) {
                $conf = str_replace("#{$key}#", $value, $conf);
            }

            try {
                file_put_contents(IDCSMART_ROOT . '/config.simple.php', $conf);
            } catch (\Exception $e) {

                return false;

            }

            return true;

        }
    }

    public function splitSql($file, $tablePre, $charset = 'utf8mb4', $defaultTablePre = 'idcsmart_', $defaultCharset = 'utf8mb4')
    {
        if (file_exists($file)) {
            //读取SQL文件
            $sql = file_get_contents($file);
            $sql = str_replace("\r", "\n", $sql);
            $sql = str_replace("BEGIN;\n", '', $sql);//兼容 navicat 导出的 insert 语句
            $sql = str_replace("COMMIT;\n", '', $sql);//兼容 navicat 导出的 insert 语句
            $sql = str_replace($defaultCharset, $charset, $sql);
            $sql = trim($sql);
            //替换表前缀
            $sql  = str_replace(" `{$defaultTablePre}", " `{$tablePre}", $sql);
            $sqls = explode(";\n", $sql);
            return $sqls;
        }

        return [];
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
    public function idcsmart_password($pw, $authCode = '')
    {
        if (is_null($pw)){
            return '';
        }

        if (empty($authCode)) {
            $authCode = AUTHCODE;
        }

        $result = "###" . md5(md5($authCode . $pw));
        return $result;
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

    private function get_idcsamrt_auth($license = '')
    {
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
        
        $version = '10.0.0';//系统当前版本
        $data = [
            'ip' => $ip,
            'domain' => $domain,
            'type' => $type,
            'license' => $license,
            'install_version' => $version,
            'request_time' => time(),
        ];
        
        $url = "https://license.soft13.idcsmart.com/app/api/auth_rc";
        $res = $this->curl($url,$data,20,'POST');
        if($res['http_code'] == 200){
            $result = json_decode($res['content'], true);
        }else{
            return false;
        }
        if(isset($result['status']) && $result['status']==200){
            return true;
        }else{
            return false;
        }
    }

}
$InstallSystem = new InstallSystem();
$param = $_REQUEST;

if($param['action']!='step_7'){
    $res = $InstallSystem->checkIsInstalled();
    $result = json_decode($res, true);
    if(isset($result['status']) && $result['status']==400){
        echo $res;die;
    }
}

if($param['action']=='step_1'){
    $res = $InstallSystem->envMonitor();
}else if($param['action']=='step_2'){
    $res = $InstallSystem->dbMonitor($param);
}else if($param['action']=='step_3'){
    $res = $InstallSystem->envSystem($param);
}else if($param['action']=='step_4'){
    $res = $InstallSystem->setDbConfig();
}else if($param['action']=='step_5'){
    $res = $InstallSystem->install();
}else if($param['action']=='step_6'){
    $res = $InstallSystem->setSite();
}else if($param['action']=='step_7'){
    $res = $InstallSystem->steplast();
}
echo $res;