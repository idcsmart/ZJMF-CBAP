<?php

namespace app\common\lib;

use app\admin\model\PluginModel;
use app\exception\TemplateNotFoundException;
use think\View;

/**
 * @desc 插件抽象类,实现插件需要继承
 * @author wyh
 * @time 2022-05-26
 * @use app\common\lib\Plugin
 */
abstract class Plugin
{
    /**
     * 视图实例对象
     * @var view
     * @access protected
     */
    private $view = null;

    // 插件视图目录
    private $template = 'template';

    // 插件视图后缀
    public $suffix = '.php';

    public static $vendorLoaded = [];
    public $info = [];
    private $pluginPath = '';
    private $name = '';
    private $configFilePath = '';
    private $themeRoot = "";

    /**
     * Plugin constructor.
     */
    public function __construct()
    {
        $this->name = $this->getName();

        $nameCStyle = parse_name($this->name);

        $module = explode('\\',get_class($this))[0]?:'addon';

        $module = $module . '/';

        $this->pluginPath     = WEB_ROOT . 'plugins/' . $module . $nameCStyle . '/';

        $this->configFilePath = $this->pluginPath . 'config.php';

        if (empty(self::$vendorLoaded[$this->name])) {
            $pluginVendorAutoLoadFile = $this->pluginPath . 'vendor/autoload.php';
            if (file_exists($pluginVendorAutoLoadFile)) {
                require_once $pluginVendorAutoLoadFile;
            }

            self::$vendorLoaded[$this->name] = true;
        }

        $config = $this->getConfig();

        $theme = isset($config['theme']) ? $config['theme'] : '';

        $themeDir = empty($theme) ? "" : '/' . $theme;

        $themePath = $this->template . $themeDir;

        $this->themeRoot = $this->pluginPath . $themePath . '/';

        $this->view = new View(app('app'));

    }

    /**
     * 加载模板输出
     * @access protected
     * @param string $template 模板文件名
     * @return string
     * @throws \Exception
     */
    final protected function fetch($template)
    {
        if (!is_file($template)) {
            $template     = $this->themeRoot . $template . $this->suffix;#  . $engineConfig['view_suffix'];
        }

        // 模板不存在 抛出异常
        if (!is_file($template)) {
            throw new TemplateNotFoundException('template not exists:' . $template, $template);
        }

        return $this->view->fetch($template);
    }

    /**
     * 渲染内容输出
     * @access protected
     * @param string $content 模板内容
     * @return mixed
     */
    final protected function display($content = '')
    {
        return $this->view->display($content);
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param mixed $name  要显示的模板变量
     * @param mixed $value 变量的值
     * @return void
     */
    final protected function assign($name, $value = '')
    {
        $this->view->assign($name, $value);
    }

    /**
     * 获取插件名
     * @return string
     */
    final public function getName()
    {
        if (empty($this->name)) {
            $class = get_class($this);

            $this->name = substr($class, strrpos($class, '\\') + 1);
        }

        return $this->name;

    }

    /**
     * 检查插件信息完整性
     * @return bool
     */
    final public function checkInfo()
    {
        $infoCheckKeys = ['name', 'title', 'description', 'author', 'version'];
        foreach ($infoCheckKeys as $value) {
            if (!array_key_exists($value, $this->info))
                return false;
        }
        return true;
    }

    /**
     * 获取插件根目录绝对路径
     * @return string
     */
    final public function getPluginPath()
    {

        return $this->pluginPath;
    }

    /**
     * 获取插件配置文件绝对路径
     * @return string
     */
    final public function getConfigFilePath()
    {
        return $this->configFilePath;
    }

    /**
     *
     * @return string
     */
    final public function getThemeRoot()
    {
        return $this->themeRoot;
    }

    /**
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * 获取插件的配置数组
     * @return array
     */
    final public function getConfig()
    {
        $name = $this->getName();

        if (PHP_SAPI != 'cli') {
            static $_config = [];
            if (isset($_config[$name])) {
                return $_config[$name];
            }
        }

        $PluginModel = new PluginModel();
        $config = $PluginModel->where('name', $name)->value('config');
        if (!empty($config) && $config != "null") {
            $config = json_decode($config, true);
        } else {
            $config = $this->getDefaultConfig();

        }

        $_config[$name] = $config;
        return $config;
    }

    /**
     * 获取插件的配置数组
     * @return array
     */
    final public function getDefaultConfig()
    {
        $config = [];
        if (file_exists($this->configFilePath)) {
            $tempArr = include $this->configFilePath;
            if (!empty($tempArr) && is_array($tempArr)) {
                foreach ($tempArr as $key => $value) {
                    if ($value['type'] == 'group') {
                        foreach ($value['options'] as $gkey => $gvalue) {
                            foreach ($gvalue['options'] as $ikey => $ivalue) {
                                $config[$ikey] = $ivalue['value'];
                            }
                        }
                    } else {
                        $config[$key] = $tempArr[$key]['value'];
                    }
                }
            }
        }

        return $config;
    }

    //必须实现安装
    abstract public function install();

    //必须卸载插件方法
    abstract public function uninstall();
}
