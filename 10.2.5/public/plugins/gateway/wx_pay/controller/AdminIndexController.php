<?php

namespace gateway\wx_pay\controller;

use app\admin\model\PluginModel;
use cmf\controller\PluginAdminBaseController;
use think\Db;
use think\Validate;

class AdminIndexController extends PluginAdminBaseController
{

    function _initialize()
    {
        $adminId = cmf_get_current_admin_id();//获取后台管理员id，可判断是否登录
        if (!empty($adminId)) {
            $this->assign("admin_id", $adminId);
        }
    }

    /**
     * 配置支付信息
     * @adminMenu(
     *     'name'   => '微信配置',
     *     'parent' => 'admin/Plugin/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '微信配置',
     *     'param'  => ''
     * )
     */
//修改上述parent的路劲放置你想要的导航主目录或者子目录下
    public function setting()
    {
        $id = $this->getId();

        $pluginModel = new PluginModel();
        $plugin = $pluginModel->find($id);


        $plugin = $plugin->toArray();

        $pluginClass = cmf_get_plugin_class('WxPay');


        $pluginObj = new $pluginClass;
        //$plugin['plugin_path']   = $pluginObj->plugin_path;
        //$plugin['custom_config'] = $pluginObj->custom_config;
        $pluginConfigInDb = $plugin['config'];
        $plugin['config'] = include $pluginObj->getConfigFilePath();

        if ($pluginConfigInDb) {
            $pluginConfigInDb = json_decode($pluginConfigInDb, true);
            foreach ($plugin['config'] as $key => $value) {
                if ($value['type'] != 'group') {
                    if (isset($pluginConfigInDb[$key])) {
                        $plugin['config'][$key]['value'] = $pluginConfigInDb[$key];
                    }
                } else {
                    foreach ($value['options'] as $group => $options) {
                        foreach ($options['options'] as $gkey => $value) {
                            if (isset($pluginConfigInDb[$gkey])) {
                                $plugin['config'][$key]['options'][$group]['options'][$gkey]['value'] = $pluginConfigInDb[$gkey];
                            }
                        }
                    }
                }
            }
        }

        $this->assign('data', $plugin);
//        if ($plugin['custom_config']) {
//            $this->assign('custom_config', $this->fetch($plugin['plugin_path'] . $plugin['custom_config']));
//        }

        $this->assign('id', $id);
        return $this->fetch();
    }

    public function settingPost()
    {
//        var_dump($this->request->param());die();
        if ($this->request->isPost()) {
            $id = $this->getId();

            $pluginModel = new PluginModel();
            $plugin = $pluginModel->find($id)->toArray();

            if (!$plugin) {
                $this->error('插件未安装!');
            }

            $pluginClass = cmf_get_plugin_class($plugin['name']);
            if (!class_exists($pluginClass)) {
                $this->error('插件不存在!');
            }

            $pluginObj = new $pluginClass;
            //$plugin['plugin_path']   = $pluginObj->plugin_path;
            //$plugin['custom_config'] = $pluginObj->custom_config;
            $pluginConfigInDb = $plugin['config'];
            $plugin['config'] = include $pluginObj->getConfigFilePath();

            $rules = [];
            $messages = [];

            foreach ($plugin['config'] as $key => $value) {
                if ($value['type'] != 'group') {
                    if (isset($value['rule'])) {
                        $rules[$key] = $this->_parseRules($value['rule']);
                    }

                    if (isset($value['message'])) {
                        foreach ($value['message'] as $rule => $msg) {
                            $messages[$key . '.' . $rule] = $msg;
                        }
                    }

                } else {
                    foreach ($value['options'] as $group => $options) {
                        foreach ($options['options'] as $gkey => $value) {
                            if (isset($value['rule'])) {
                                $rules[$gkey] = $this->_parseRules($value['rule']);
                            }

                            if (isset($value['message'])) {
                                foreach ($value['message'] as $rule => $msg) {
                                    $messages[$gkey . '.' . $rule] = $msg;
                                }
                            }
                        }
                    }
                }
            }

            $config = $this->request->param('config/a');

            $validate = new Validate($rules, $messages);
            $result = $validate->check($config);
            if ($result !== true) {
                $this->error($validate->getError());
            }

            $pluginModel = new PluginModel();
            $pluginModel->save(['config' => json_encode($config)], ['id' => $id]);
            $this->success('保存成功', cmf_plugin_url('WxPay://AdminIndex/setting'));
        }
    }

    public function getId()
    {
        $id = Db::name('plugin')->where('name', '=', 'WxPay')->value('id');
        return $id;
    }
}