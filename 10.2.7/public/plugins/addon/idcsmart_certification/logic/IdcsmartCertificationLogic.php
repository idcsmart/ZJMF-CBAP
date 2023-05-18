<?php
namespace addon\idcsmart_certification\logic;

use addon\idcsmart_certification\IdcsmartCertification;
use app\admin\model\PluginModel;

class IdcsmartCertificationLogic
{
    # 默认配置
    public static function getDefaultConfig($name = '')
    {
        $fileConfig = include dirname(__DIR__) . '/config/config.php';

        $dbConfig = (new IdcsmartCertification())->getConfig();

        $config = array_merge($fileConfig?:[],$dbConfig?:[]);

        return isset($config[$name])?$config[$name]:$config;
    }

    public function getConfig()
    {
        $PluginModel = new PluginModel();
        $plugin = $PluginModel->where('name','IdcsmartCertification')
            ->where('module','addon')
            ->find();
        return json_decode($plugin['config']??(object)[],true);
    }

    public function setConfig($param)
    {
        $old = $this->getConfig();
        $new = $param;
        $description = [];
        foreach ($old as $key=>$value){
            if (isset($new[$key]) && ($value != $new[$key])){
                $value = !empty($value) ? lang_plugins('addon_idcsmart_certification_open') : lang_plugins('addon_idcsmart_certification_close');
                $new[$key] = !empty($new[$key]) ? lang_plugins('addon_idcsmart_certification_open') : lang_plugins('addon_idcsmart_certification_close');
                $description[] = lang('log_admin_update_description',['{field}'=>lang_plugins('field_idcsmart_certification_'.$key),'{old}'=>$value,'{new}'=>$new[$key]]);
            }
        }

        $description = implode(',', $description);

        $PluginModel = new PluginModel();
        $PluginModel->where('name','IdcsmartCertification')
            ->where('module','addon')
            ->update([
                'config' => json_encode($param),
                'update_time' => time()
            ]);

        if(!empty($description)){
            # 记录日志
            active_log(lang_plugins('idcsmart_certification_update_config', ['{admin}'=>request()->admin_name, '{description}'=>$description]), 'admin', request()->admin_id);
        }
        return true;
    }
}