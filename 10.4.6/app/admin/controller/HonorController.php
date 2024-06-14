<?php
namespace app\admin\controller;

use app\common\model\HonorModel;
use app\admin\validate\HonorValidate;

/**
 * @title 荣誉资质
 * @desc 荣誉资质
 * @use app\admin\controller\HonorController
 */
class HonorController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new HonorValidate();
    }

    /**
     * 时间 2023-02-28
     * @title 获取荣誉资质
     * @desc 获取荣誉资质
     * @author theworld
     * @version v1
     * @url /admin/v1/honor
     * @method  GET
     * @return array list - 荣誉资质
     * @return int list[].id - 荣誉资质ID 
     * @return string list[].name - 名称 
     * @return string list[].img - 图片地址 
     */
    public function list()
    {
        // 实例化模型类
        $HonorModel = new HonorModel();

        // 获取荣誉资质
        $data = $HonorModel->honorList();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 添加荣誉资质
     * @desc 添加荣誉资质
     * @author theworld
     * @version v1
     * @url /admin/v1/honor
     * @method  POST
     * @param string name - 名称 required
     * @param string img - 图片地址 required
     */
    public function create()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $HonorModel = new HonorModel();
        
        // 新建荣誉资质
        $result = $HonorModel->createHonor($param);

        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 编辑荣誉资质
     * @desc 编辑荣誉资质
     * @author theworld
     * @version v1
     * @url /admin/v1/honor/:id
     * @method  PUT
     * @param int id - 荣誉资质ID required
     * @param string name - 名称 required
     * @param string img - 图片地址 required
     */
    public function update()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $HonorModel = new HonorModel();
        
        // 修改荣誉资质
        $result = $HonorModel->updateHonor($param);

        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 删除荣誉资质
     * @desc 删除荣誉资质
     * @author theworld
     * @version v1
     * @url /admin/v1/honor/:id
     * @method  DELETE
     * @param int id - 荣誉资质ID required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $HonorModel = new HonorModel();
        
        // 删除荣誉资质
        $result = $HonorModel->deleteHonor($param['id']);

        return json($result);

    }

    
}