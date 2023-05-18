<?php
namespace addon\idcsmart_cloud\controller\clientarea;

use app\event\controller\PluginBaseController;
use addon\idcsmart_cloud\model\IdcsmartVpcModel;
use addon\idcsmart_cloud\validate\IdcsmartVpcValidate;

/**
 * @title VPC管理
 * @desc VPC管理
 * @use addon\idcsmart_cloud\controller\clientarea\VpcController
 */
class VpcController extends PluginBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new IdcsmartVpcValidate();
    }

    /**
     * 时间 2022-06-10
     * @title VPC列表
     * @desc VPC列表
     * @author theworld
     * @version v1
     * @url /console/v1/vpc
     * @method  GET
     * @return array list - 数据中心列表
     * @return string list[].country - 国家
     * @return string list[].country_code - 国家代码
     * @return string list[].city - 城市
     * @return string list[].area - 区域
     * @return array list[].vpc - VPC
     * @return string list[].vpc[].id - VPCID 
     * @return string list[].vpc[].name - VPC名称 
     * @return string list[].vpc[].ip - IP 
     * @return int list[].vpc[].host_num - 实例数量 
     * @return int list[].vpc[].create_time - 创建时间 
     */
    public function list()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $IdcsmartVpcModel = new IdcsmartVpcModel();

        // 获取VPC列表
        $data = $IdcsmartVpcModel->idcsmartVpcList($param);

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-10
     * @title 添加VPC
     * @desc 添加VPC
     * @author theworld
     * @version v1
     * @url /console/v1/vpc
     * @method  POST
     * @param int data_center_id - 云模块数据中心ID required
     * @param string name - 名称 required
     * @param string ip - IP 不是自动创建IP时需要传,IP地址/掩码,内网IP
     * @param int auto_create_ip - 是否自动创建IP,0:否1:是 required
     */
    public function create()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartVpcModel = new IdcsmartVpcModel();

        // 创建VPC
        $result = $IdcsmartVpcModel->createIdcsmartVpc($param);

        return json($result);
    }

    /**
     * 时间 2022-06-10
     * @title 修改VPC
     * @desc 修改VPC
     * @author theworld
     * @version v1
     * @url /console/v1/vpc/:id
     * @method  PUT
     * @param int id - VPCID required
     * @param string name - 名称 required
     */
    public function update()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($this->validate->getError())]);
        }
        
        // 实例化模型类
        $IdcsmartVpcModel = new IdcsmartVpcModel();

        // 修改VPC
        $result = $IdcsmartVpcModel->updateIdcsmartVpc($param);

        return json($result);
    }

    /**
     * 时间 2022-06-10
     * @title 删除VPC
     * @desc 删除VPC
     * @author theworld
     * @version v1
     * @url /console/v1/vpc/:id
     * @method  DELETE
     * @param int id - VPCID required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartVpcModel = new IdcsmartVpcModel();

        // 删除VPC
        $result = $IdcsmartVpcModel->deleteIdcsmartVpc($param['id']);

        return json($result);
    }
}