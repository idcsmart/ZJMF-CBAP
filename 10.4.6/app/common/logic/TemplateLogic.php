<?php 
namespace app\common\logic;

use app\admin\model\PluginModel;

/**
 * @title 模板控制器逻辑
 * @desc 模板控制器逻辑
 * @use app\common\logic\TemplateLogic
 */
class TemplateLogic
{   
    /**
     * 时间 2024-05-21
     * @title 模板控制器Tab
     * @desc 模板控制器Tab
     * @author theworld
     * @version v1
     * @param string param.theme - 主题标识,不传递时默认为当前系统设置的主题
     * @return array list - 模板控制器Tab列表
     * @return string list[].name - 标识
     * @return string list[].title - 标题
     * @return string list[].url - 地址
     */
    public function templateTabList($param)
    {   
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'list' => []
            ]
        ];

        $theme = $param['theme'] ?? configuration('web_theme');

        $lang = lang();

        $list = [
            ['name' => 'tab_template_controller_nav', 'title' => $lang['tab_template_controller_nav'], 'url' => 'template_nav.htm'],
            ['name' => 'tab_template_controller_default_product', 'title' => $lang['tab_template_controller_default_product'], 'url' => 'template_host_config.htm'],
            ['name' => 'tab_template_controller_bottom_bar', 'title' => $lang['tab_template_controller_bottom_bar'], 'url' => 'template_bottom_nav.htm'],
            ['name' => 'tab_template_controller_web', 'title' => $lang['tab_template_controller_web'], 'url' => 'template_web_config.htm'],
            ['name' => 'tab_template_controller_seo', 'title' => $lang['tab_template_controller_seo'], 'url' => 'template_seo_manage.htm'],
            ['name' => 'tab_template_controller_index_banner', 'title' => $lang['tab_template_controller_index_banner'], 'url' => 'template_index_banner.htm'],
            ['name' => 'tab_template_controller_side_floating_window', 'title' => $lang['tab_template_controller_side_floating_window'], 'url' => 'template_side_manage.htm'],
        ];

        $name = parse_name($theme, 1);
        if (class_exists("template\\{$theme}\\controller\\{$name}")){
            $PluginModel = new PluginModel();
            $res = $PluginModel->install(['module' => 'template', 'name' => $name]);
        }

        // 通过hook修改模板控制器Tab
        $result_hook = hook('template_tab_list', ['theme' => $theme, 'list' => $list]);
        $result_hook = array_values(array_filter($result_hook ?? []));
        foreach ($result_hook as $key => $value) {
            if(isset($value['list'])){
                $list = $value['list'];
                break;
            }
        }

        $result['data']['list'] = $list;
        return $result;
    }
}