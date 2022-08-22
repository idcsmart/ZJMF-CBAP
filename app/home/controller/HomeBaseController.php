<?php
namespace app\home\controller;

/**
 * idcsmart控制器基础类
 */
class HomeBaseController extends BaseController
{
    public function initialize()
    {
        //维护模式
        if (configuration('maintenance_mode')==1){
            echo json_encode(['status'=>503, 'msg'=>configuration('maintenance_mode_message')??'维护中……']);die;
        }
    }
}

