<?php
namespace app\event\controller;

use think\exception\ValidateException;
use think\facade\Config;
use think\paginator\driver\Bootstrap;
use app\exception\TemplateNotFoundException;
use app\home\model\ClientareaAuthRuleModel;
use think\facade\Cache;

class PluginBaseController extends BaseController
{
    private $plugin;
    # 模块
    public $module='addon';

    /**
     * 初始化
     * @access public
     */
    protected function initialize()
    {
        parent::initialize();

        $result = $this->getPlugin();
        if (!$result){
            abort(404, lang('missing_route_paramters',['{param}'=>':_plugin']));
        }

        if(!$this->checkClientareaAccess()){
            $param = $this->request->param();
            $module     = 'addon';
            $plugin     = $param['_plugin']??'';
            $controller = (isset($param['_controller']) && !empty($param['_controller']))?ucfirst(parse_name($param['_controller'],1)):'';
            $action     = (isset($param['_action']) && !empty($param['_action']))?lcfirst(parse_name($param['_action'],1)):'';

            $rule = $module.'\\'.$plugin .'\\controller\\'. $controller .'Controller::'. $action;

            // 查找权限,未找到设置了则放行
            $ClientareaAuthRuleModel = new ClientareaAuthRuleModel();
            $name = $ClientareaAuthRuleModel->getAuthName($rule);
            if(!empty($name)){
                echo json_encode(['status'=>404, 'msg'=>lang('permission_denied')]);die;
            }

        }

        $this->view = $this->plugin->getView();
    }

    private function checkClientareaAccess()
    {
        $clientId = get_client_id(false);
        if(empty($clientId)){
            return true;
        }

        $param = $this->request->param();
        $module     = 'addon';
        $plugin     = $param['_plugin']??'';
        $controller = (isset($param['_controller']) && !empty($param['_controller']))?ucfirst(parse_name($param['_controller'],1)):'';
        $action     = (isset($param['_action']) && !empty($param['_action']))?lcfirst(parse_name($param['_action'],1)):'';

        $rule = $module.'\\'.$plugin .'\\controller\\clientarea\\'. $controller .'Controller::'. $action;

        // 先获取缓存的权限
        if(Cache::has('home_auth_rule_'.$clientId)){
            $auth = json_decode(Cache::get('home_auth_rule_'.$clientId), true);
            if(!in_array($rule, $auth)){
                return false;
            }else{
                return true;
            }
        }

        $result = hook('home_check_access', ['rule' => $rule, 'client_id' => $clientId]);
        $result = array_values(array_filter($result ?? []));
        foreach ($result as $key => $value) {
            if(isset($value['status'])){
                if($value['status']==200){
                    return true;
                }else{
                    return false;
                }
            }
        }
        return true;
    }


    public function getPlugin()
    {
        if (is_null($this->plugin)) {
            $pluginName   = $this->request->param('_plugin');
            if (empty($pluginName)){
                return null;
            }
            $pluginName   = parse_name($pluginName, 1);
            $class        = get_plugin_class($pluginName,$this->module);
            $this->plugin = new $class;
        }

        return $this->plugin;

    }

    /**
     * 加载模板输出(支持:/index/index,index/index,index,空,:index,/index)
     * @access protected
     * @param string $template 模板文件名
     * @param array  $vars     模板输出变量
     * @param array  $replace  模板替换
     * @param array  $config   模板参数
     * @return mixed|string
     * @throws \Exception
     */
    protected function fetch($template = '', $vars = [], $replace = [], $config = [])
    {
        $template = $this->parseTemplate($template);

        // 模板不存在 抛出异常
        if (!is_file($template)) {
            throw new TemplateNotFoundException('template not exists:' . $template, $template);
        }

        return $this->view->fetch($template, $vars, $replace, $config);
    }


    /**
     * 自动定位模板文件
     * @access private
     * @param string $template 模板文件规则
     * @return string
     */
    private function parseTemplate($template)
    {
        // 分析模板文件规则
        $viewEngineConfig = Config::get('idcsmart.template');

        $path = $this->plugin->getThemeRoot();

        $depr = $viewEngineConfig['view_depr'];

        $data       = $this->request->param();
        if (!isset($data['_controller'])){
            abort(404, lang('missing_route_paramters',['{param}'=>':_controller']));
        }
        if (!isset($data['_action'])){
            abort(404, lang('missing_route_paramters',['{param}'=>':_action']));
        }
        $controller = $data['_controller'];
        $action     = $data['_action'];

        if (0 !== strpos($template, '/')) {
            $template   = str_replace(['/', ':'], $depr, $template);
            $controller = parse_name($controller);
            if ($controller) {
                if ('' == $template) {
                    // 如果模板文件名为空 按照默认规则定位
                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . $action;
                } elseif (false === strpos($template, $depr)) {
                    $template = str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . $template;
                }
            }
        } else {
            $template = str_replace(['/', ':'], $depr, substr($template, 1));
        }

        return $path . ltrim($template, '/') . '.' . ltrim($viewEngineConfig['view_suffix'], '.');
    }

    /**
     * 渲染内容输出
     * @access protected
     * @param string $content 模板内容
     * @param array  $vars    模板输出变量
     * @param array  $replace 替换内容
     * @param array  $config  模板参数
     * @return mixed
     */
    protected function display($content = '', $vars = [], $replace = [], $config = [])
    {
        return $this->view->display($content, $vars, $replace, $config);
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param mixed $name  要显示的模板变量
     * @param mixed $value 变量的值
     * @return void
     */
    protected function assign($name, $value = '')
    {
        $this->view->assign($name, $value);
    }

    /**
     * 设置验证失败后是否抛出异常
     * @access protected
     * @param  bool $fail 是否抛出异常
     * @return $this
     */
    protected function validateFailException($fail = true)
    {
        $this->failException = $fail;
        return $this;
    }

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @param  mixed        $callback 回调方法（闭包）
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate($data, $validate, $message = [], $batch = false, $callback = null)
    {
        if (is_array($validate)) {
            $v = $this->app->validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                list($validate, $scene) = explode('.', $validate);
            }
            $v = $this->app->validate('\\plugins\\' . parse_name($this->plugin->getName()) . '\\validate\\' . $validate . 'Validate');
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        if (is_array($message)) {
            $v->message($message);
        }

        if ($callback && is_callable($callback)) {
            call_user_func_array($callback, [$v, &$data]);
        }

        if (!$v->check($data)) {
            if ($this->failException) {
                throw new ValidateException($v->getError());
            }
            return $v->getError();
        }

        return true;
    }

    /*
     * 分页
     * @param showdata array 分页数据
     * @param listRow number 每页条数
     * @param curpage number 当前页数
     * @param total number 总页数
     * @param isHome bool 是否前台
     */
    protected function ajaxPages($showdata = [], $listRow = 10, $curpage = 1, $total = 0,$isHome=false)
    {
        # 构造地址
        if ($isHome){
            $url = '/' . request()->action();
        }else{
            $url='/'. DIR_ADMIN ."/addon/".request()->action();
        }
        $p = Bootstrap::make($showdata, $listRow, $curpage, $total, false, [
            'var_page' => 'page',
            'path'     => $url,//这里根据需要修改url
            'fragment' => '',
            'query' => $_GET,
        ]);
        $pages = $p->render();
        $default_pages = '<li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
	<li class="page-item active"><a class="page-link" href="#">1</a></li>
	<li class="page-item disabled"><a class="page-link" href="#">&raquo;</a></li>';
        $pages = !empty($pages) ? $pages : $default_pages;
        return $pages;
    }
}