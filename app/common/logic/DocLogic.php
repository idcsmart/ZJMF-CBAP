<?php 
namespace app\common\logic;

/**
 * @title 开发文档逻辑
 * @desc 开发文档逻辑
 * @use app\common\logic\DocLogic
 */
class DocLogic
{   
    /**
     * 时间 2022-05-09
     * @title 获取开发文档
     * @desc 获取开发文档
     * @author theworld
     * @version v1
     * @return  array
     * @return  string [].section - 版块
     * @return  object [].doc - 文档
     * @return  string [].doc.title - 标题
     * @return  string [].doc.desc - 描述
     * @return  array [].list - 版块文档列表
     * @return  string [].list[].class - 分类
     * @return  object [].list[].doc - 文档
     * @return  string [].list[].doc.title - 标题
     * @return  string [].list[].doc.desc - 描述
     * @return  string [].list[].doc.use - 内部引用
     * @return  array [].list[].list - 分类文档列表
     * @return  string [].list[].list[].method - 方法
     * @return  object [].list[].list[].doc - 文档
     * @return  string [].list[].list[].doc.title - 标题
     * @return  string [].list[].list[].doc.desc - 描述
     * @return  string [].list[].list[].doc.author - 作者
     * @return  string [].list[].list[].doc.version - 版本
     * @return  string [].list[].list[].doc.url - 请求地址
     * @return  string [].list[].list[].doc.method - 请求方式
     * @return  object [].list[].list[].doc.param - 请求参数
     * @return  string [].list[].list[].doc.param.type - 类型
     * @return  string [].list[].list[].doc.param.name - 名称
     * @return  string [].list[].list[].doc.param.default - 默认值
     * @return  string [].list[].list[].doc.param.desc - 描述
     * @return  string [].list[].list[].doc.param.validate - 验证规则
     * @return  object [].list[].list[].doc.return - 返回参数
     * @return  string [].list[].list[].doc.return.type - 类型
     * @return  string [].list[].list[].doc.return.name - 名称
     * @return  string [].list[].list[].doc.return.default - 默认值
     * @return  string [].list[].list[].doc.return.desc - 描述
     */
    public function doc()
    {
        $doc_data = base_path().'/../doc_data';
        if(is_file($doc_data)){
            $list = json_decode(file_get_contents($doc_data), true);
        }else{
            $list = [];
        }
        return $list;
    }

	/**
     * 时间 2022-05-09
     * @title 生成开发文档
     * @desc 生成开发文档
     * @author theworld
     * @version v1
     * @return  boolean
     */
    public function createDoc()
    {
        $adminControllerDir = base_path().'/admin/controller';
        $homeControllerDir = base_path().'/home/controller';
        $commonModelDir = base_path().'/common/model';
        $adminModelDir = base_path().'/admin/model';
        $homeModelDir = base_path().'/home/model';
        $commonLogicDir = base_path().'/common/logic';
        $doc_data = base_path().'/../doc_data';
        $pluginDir = IDCSMART_ROOT.'public/plugins';
        $classDoc = [
            'title'  => '未定义标题',
            'desc'    => '未定义描述',
            'use'    => '',
        ];
        $key = 0;
        
        /*$handle = opendir($dir);$readDir=array();
        while(($file = readdir($handle)) !== false){
            if($file != "." && $file != ".."){
                if(!is_dir("$dir/$file") && $type!="fileDir"){
                    if($type=="fileDisk"){
                        $readDir[]="$dir/$file";
                    }else if($type=="fileName"){
                        $readDir[]="$file";
                    }                   
                }else{
                    if($type=="fileDir"){
                        $readDir[]="$dir/$file";
                    }
                }                   
            }
        }*/
        $arr = [];
        // 获取插件下controller
        $files = read_dir($pluginDir);
        foreach ($files as $key => $value) {
            if(strpos($value, 'Controller')!==false && (strpos($value, '/addon/')!==false || strpos($value, '/server/')!==false)){
                $value = str_replace('.php', '', str_replace($pluginDir.'/', '', $value));
                $arr[] = implode("\\", explode('/', $value));
            }
        }
        //admin下controller
        $handle = opendir($adminControllerDir);
        if ($handle) {//目录打开正常
            while (($file = readdir($handle)) !== false) {
                if  ($file != '.' && $file != '..') {
                    $class = str_replace('.php', '', $file);
                    $class = "app\\admin\\controller\\" . $class;
                    $arr[] = $class;
                }
            }
            closedir($handle);//关闭句柄
        }
        //home下controller
        $handle = opendir($homeControllerDir);
        if ($handle) {//目录打开正常
            while (($file = readdir($handle)) !== false) {
                if  ($file != '.' && $file != '..') {
                    $class = str_replace('.php', '', $file);
                    $class = "app\\home\\controller\\" . $class;
                    $arr[] = $class;
                }
            }
            closedir($handle);//关闭句柄
        }
        //common下model
        $handle = opendir($commonModelDir);
        if ($handle) {//目录打开正常
            while (($file = readdir($handle)) !== false) {
                if  ($file != '.' && $file != '..') {
                    $class = str_replace('.php', '', $file);
                    $class = "app\\common\\model\\" . $class;
                    $arr[] = $class;
                }
            }
            closedir($handle);//关闭句柄
        }
        //admin下model
        $handle = opendir($adminModelDir);
        if ($handle) {//目录打开正常
            while (($file = readdir($handle)) !== false) {
                if  ($file != '.' && $file != '..') {
                    $class = str_replace('.php', '', $file);
                    $class = "app\\admin\\model\\" . $class;
                    $arr[] = $class;
                }
            }
            closedir($handle);//关闭句柄
        }
        //home下model
        $handle = opendir($homeModelDir);
        if ($handle) {//目录打开正常
            while (($file = readdir($handle)) !== false) {
                if  ($file != '.' && $file != '..') {
                    $class = str_replace('.php', '', $file);
                    $class = "app\\home\\model\\" . $class;
                    $arr[] = $class;
                }
            }
            closedir($handle);//关闭句柄
        }
        //common下logic
        $handle = opendir($commonLogicDir);
        if ($handle) {//目录打开正常
            while (($file = readdir($handle)) !== false) {
                if  ($file != '.' && $file != '..') {
                    $class = str_replace('.php', '', $file);
                    $class = "app\\common\\logic\\" . $class;
                    $arr[] = $class;
                }
            }
            closedir($handle);//关闭句柄
        }

        //循环生成注释文档数据
        foreach ($arr as $k => $class) {
            if (class_exists($class)) {
                $reflection = new \ReflectionClass($class);
                $doc_str = $reflection->getDocComment();
                if($doc_str === false){
                    continue;
                }
				$cmd="";
				if(strpos($class,"app\\admin\\controller\\")!==false && strpos($class,"app\\admin\\controller\\View")===false){
					$cmd="admin_".str_replace("Controller","",str_replace("app\\admin\\controller\\","",$class));
				}else if(strpos($class,"app\\home\\controller\\")!==false && strpos($class,"app\\home\\controller\\View")===false){
					$cmd="home_".str_replace("Controller","",str_replace("app\\home\\controller\\","",$class));
				}
                $doc = $this->parse($doc_str);
                if(isset($doc['title'])){
                    //$method = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
                    $method = $reflection->getMethods();

                    $list[$k]['class'] = $class;
                    $list[$k]['doc'] = array_merge($classDoc, $doc);
                    $list[$k]['list'] = [];

                    foreach($method as $kk=>$vv){
                        if(preg_match('/^__\S*$/',$vv->name)!==0){
                            continue;
                        }
                        $doc_str = $vv->getDocComment();
                        if($doc_str === false){
                            continue;
                        }
                        $doc = $this->parse($doc_str);
                        if(isset($doc['title'])){
							$doc_method=[
                                'method'=>$vv->name,
                                'doc'=>$this->parse($doc_str)
                            ];
							if($cmd){
								$doc_method['cmd']=$cmd . '_' . $vv->name;
							}
							$list[$k]['list'][] = $doc_method;
                            /* $list[$k]['list'][] = [
                                'method'=>$vv->name,
                                'doc'=>$this->parse($doc_str)
                            ]; */
                        }
                    }
                }
                
                $key = $k+1;
            }
        }

        //获取所有自定义方法
        $functions = get_defined_functions();
        $arr = $functions['user'];
        $list[$key]['class'] = 'common';
        $list[$key]['doc'] = [
                'title'  => '公共函数',
                'desc'    => '公共函数',
        ];
        $list[$key]['list'] = [];

        //循环生成注释文档数据
        foreach ($arr as $k => $v) {
            $reflection = new \ReflectionFunction($v);
            $doc_str = $reflection->getDocComment();
            if($doc_str === false){
                continue;
            }
            $doc = $this->parse($doc_str);
            if(isset($doc['title'])){
                $list[$key]['list'][] = [
                    'method'=>$v,
                    'doc'=>$this->parse($doc_str)
                ];
            }
            
        }

        //将文档分为三个版块
        $doc_list = [
            [
                'section' =>'home',
                'doc' => [
                    'title' => '前台接口',
                    'desc' => '前台接口',
                ],
                'list' => []
            ],
            [
                'section' => 'admin',
                'doc'=> [
                    'title' => '后台接口',
                    'desc' => '后台接口',
                ],
                'list' => []
            ],
            [
                'section' => 'function',
                'doc' => [
                    'title' => '函数',
                    'desc' => '函数',
                ],
                'list' => []
            ],

        ];
        foreach ($list as $k => $v) {
            if(preg_match("/^app\\\\home\\\\controller\\\\\S*$/",$v['class'])!==0 || preg_match("/^addon\\\\\S*\\\\controller\\\\clientarea\\\\\S*$/",$v['class'])!==0 || preg_match("/^server\\\\\S*\\\\controller\\\\home\\\\\S*$/",$v['class'])!==0){
                $doc_list[0]['list'][] = $v;
            }else if(preg_match("/^app\\\\admin\\\\controller\\\\\S*$/",$v['class'])!==0 || preg_match("/^addon\\\\\S*\\\\controller\\\\\S*$/",$v['class'])!==0 || preg_match("/^server\\\\\S*\\\\controller\\\\admin\\\\\S*$/",$v['class'])!==0){
                $doc_list[1]['list'][] = $v;
            }else{
                $doc_list[2]['list'][] = $v;
            }
        }
        //写入数据
        file_put_contents($doc_data, json_encode($doc_list));

        return true;
    }

    protected function parse($comment)
    {
        if(empty($comment)){
            return [];
        }
        $comment = preg_replace('/[ ]+/', ' ', $comment);
        preg_match_all('/\*[\s+]?@(.*?)[\n|\r]/is', $comment, $matches);
        $arr = [];
        foreach ($matches[1] as $key => $match) {
            $arr[$key] = explode(' ', $match);
        }
        $newArr = [];
        foreach ($arr as $item) {
            switch (strtolower($item[0])) {
                case 'title':
                case 'desc':
                case 'version':
                case 'author':
                case 'url':
                case 'method':
                case 'use':
                default:
                    $newArr[$item[0]] = isset($item[1]) ? $item[1] : '-';
                    break;
                case 'param':
                    $key = array_shift($item);
                    $type = array_shift($item);
                    $name = array_shift($item) ?? '-';
                    $name = str_replace('$', '', $name);
                    $default = array_shift($item) ?? '-';
                    $desc = array_shift($item) ?? '-';
                    $validate = implode('', $item) ?? '-';
                    $newArr[$key][] = [
                        'type' => $type,
                        'name' => $name,
                        'default' => $default,
                        'desc' => $desc,
                        'validate' => $validate
                    ];
                    break;
                case 'return':
                    $key = array_shift($item);
                    $type = array_shift($item);
                    $name = array_shift($item) ?? '-';
                    $name = str_replace('$', '', $name);
                    $default = array_shift($item) ?? '-';
                    $desc = array_shift($item) ?? '-';
                    $newArr[$key][] = [
                        'type' => $type,
                        'name' => $name,
                        'default' => $default,
                        'desc' => $desc
                    ];
                    break;
            }
        }
        return $newArr;
    }
}