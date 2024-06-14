<?php
namespace app\common\logic;

use app\common\lib\Widget;

/**
 * @title 挂件逻辑类
 * @use   app\common\logic\WidgetLogic
 */
class WidgetLogic
{
    // 所有挂件类
    protected $widgets = NULL;

    // 追加挂架hook名称
    protected $hookName = 'admin_widget';

    // 挂件路径
    protected $path = WEB_ROOT . 'plugins/widget/';

    /**
     * 时间 2023-05-04
     * @title 加载所有挂件
     * @desc  加载所有挂件
     * @author hh
     * @version v1
     * @return  array
     */
    public function loadWidget(): array
    {
        $this->widgets = [];
        if(is_dir($this->path)){
            if($handle = opendir($this->path)){
                while(($file = readdir($handle)) !== false){
                    if($file != '.' && $file != '..' && is_file($this->path . $file) && preg_match('/^[A-Za-z0-9_]{0,99}\.php$/', $file)){
                        $class = "\\widget\\" . str_replace('.php', '', $file);
                        try {
                            if (class_exists($class)) {
                                $widget = new $class();

                                if($widget instanceof Widget){
                                    $this->widgets[] = $widget;
                                }
                            }
                        } catch (\Exception $e) {
                            
                        }
                    }
                }
                closedir($handle);
            }
        }
        // 加载hook里面的挂件
        $this->loadHookWidget();
        usort($this->widgets, function ($a, $b) {
            return $b->getWeight() <= $a->getWeight() ? 1 : -1;
        });
        return $this->widgets;
    }

    /**
     * 时间 2023-05-05
     * @title 加载hook挂件
     * @desc  加载hook挂件
     * @author hh
     * @version v1
     * @return  bool
     */
    protected function loadHookWidget(): bool
    {
        $hookRes = hook($this->hookName);
        if (count($hookRes) == 0) {
            return false;
        }
        foreach ($hookRes as $widget) {
            // 多个挂件
            if(is_array($widget)){
                foreach($widget as $v){
                    if($v instanceof Widget){
                        $this->widgets[] = $v;
                    }
                }
            }else if($widget instanceof Widget){
                $this->widgets[] = $widget;
            }
        }
        return true;
    }

    /**
     * 时间 2023-05-05
     * @title 加载所有挂件
     * @desc  加载所有挂件,同loadWidget
     * @author hh
     * @version v1
     * @return  array
     */
    public function getAllWidgets(): array
    {
        if (is_null($this->widgets)) {
            $this->loadWidget();
        }
        return $this->widgets;
    }

    /**
     * 时间 2023-05-05
     * @title 获取所有挂件标识
     * @desc  获取所有挂件标识
     * @author hh
     * @version v1
     * @return  array
     */
    public function getAllWidgetId(): array
    {
        $widgets = $this->getAllWidgets();

        $data = [];
        foreach($widgets as $widget){
            $data[] = $widget->getId();
        }
        return $data;
    }

    /**
     * 时间 2023-05-05
     * @title 获取挂件output
     * @desc  获取挂件output
     * @author hh
     * @version v1
     * @param   string widgetId - 挂件标识 require
     * @return  string content - 挂件内容
     */
    public function output($widgetId)
    {
        $output = '';    
        $widget = $this->findWidget($widgetId);
        if(!is_null($widget)){
            $output = $widget->output();
        }
        return ['content'=>$output];
    }

    /**
     * 时间 2023-05-05
     * @title 获取挂件数据
     * @desc  获取挂件数据
     * @author hh
     * @version v1
     * @param   string $widgetId - 挂件标识
     * @return  array
     */
    public function getData($widgetId)
    {
        $data = (object)[];
        $widget = $this->findWidget($widgetId);
        if(!is_null($widget)){
            $data = $widget->getData();
        }
        return $data;
    }

    /**
     * 时间 2023-05-05
     * @title 查找挂件
     * @desc  查找挂件
     * @author hh
     * @version v1
     * @param   string $widgetId - 挂件标识 require
     * @return  Widget|NULL
     */
    public function findWidget($widgetId)
    {
        $widgets = $this->getAllWidgets();

        foreach($widgets as $widget){
            if($widget->getId() == $widgetId){
                return $widget;
            }
        }
        return NULL;
    }


}

