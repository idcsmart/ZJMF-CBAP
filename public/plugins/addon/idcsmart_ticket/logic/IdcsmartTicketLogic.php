<?php
namespace addon\idcsmart_ticket\logic;

use addon\idcsmart_ticket\model\IdcsmartTicketInternalModel;
use addon\idcsmart_ticket\model\IdcsmartTicketModel;
use addon\idcsmart_ticket\IdcsmartTicket;

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
        if ($prefix == 'YHGD'){
            $IdcsmartTicketModel = new IdcsmartTicketModel();

            $todayMaxNum = $IdcsmartTicketModel->where('create_time','>=',strtotime(date('Y-m-d')))
                ->where('create_time','<=',strtotime(date('Y-m-d'))+24*3600-1)
                ->max('num');
        }else{
            $IdcsmartTicketInternalModel = new IdcsmartTicketInternalModel();

            $todayMaxNum = $IdcsmartTicketInternalModel->where('create_time','>=',strtotime(date('Y-m-d')))
                ->where('create_time','<=',strtotime(date('Y-m-d'))+24*3600-1)
                ->max('num');
        }


        $num = intval($todayMaxNum) + 1;

        $str = str_pad($num,4,'0',STR_PAD_LEFT);

        $ticketNum = $prefix . date('Ymd') . $str;

        return [$ticketNum,$num];
    }

}