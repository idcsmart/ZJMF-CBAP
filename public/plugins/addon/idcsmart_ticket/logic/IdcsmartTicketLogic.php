<?php
namespace addon\idcsmart_ticket\logic;

use addon\idcsmart_ticket\model\IdcsmartTicketInternalModel;
use addon\idcsmart_ticket\model\IdcsmartTicketModel;
use addon\idcsmart_ticket\IdcsmartTicket;
use app\admin\model\PluginModel;

class IdcsmartTicketLogic
{
    # 默认配置
    public static function getDefaultConfig($name = '')
    {
        $fileConfig = include dirname(__DIR__) . '/config/config.php';

        $dbConfig = (new IdcsmartTicket())->getConfig();
        
        $config = array_merge($fileConfig?:[],$dbConfig?:[]);

        return isset($config[$name])?$config[$name]:$config;
    }

    # 工单号生成
    public function ticketNum($prefix='YHGD')
    {
        $IdcsmartTicketModel = new IdcsmartTicketModel();

        $ticketNum = rand_str(7,'NUMBER');

        for ($i=0;$i<10;$i++){ # 至多10次比较
            $exist = $IdcsmartTicketModel->where('ticket_num',$ticketNum)->find();
            if (empty($exist)){
                break;
            }
            $ticketNum = rand_str(7,'NUMBER');
        }

        return [$ticketNum,$ticketNum];

        /*$todayMaxNum = $IdcsmartTicketModel->where('create_time','>=',strtotime(date('Y-m-d')))
            ->where('create_time','<=',strtotime(date('Y-m-d'))+24*3600-1)
            ->max('num');


        $num = intval($todayMaxNum) + 1;

        $str = str_pad($num,4,'0',STR_PAD_LEFT);

        $ticketNum = $prefix . date('Ymd') . $str;*/

        return [$ticketNum,$num];
    }

    public function setConfig($param)
    {
        $PluginModel = new PluginModel();

        $plugin = $PluginModel->where('name','IdcsmartTicket')->find();

        $config =  json_decode($plugin['config'],true);

        $config['refresh_time'] = $param['refresh_time'];

        $plugin->save([
            'config' => json_encode($config)
        ]);

        return [
            'status' => 200,
            'msg' => lang_plugins('success_message')
        ];
    }

}