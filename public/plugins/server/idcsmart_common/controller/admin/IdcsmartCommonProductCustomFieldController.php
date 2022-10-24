<?php
namespace server\idcsmart_common\controller\admin;

use app\event\controller\BaseController;
use server\idcsmart_common\logic\IdcsmartCommonLogic;
use server\idcsmart_common\model\IdcsmartCommonProductCustomFieldModel;
use server\idcsmart_common\validate\IdcsmartCommonProductCustomFieldValidate;

/**
 * @title 商品自定义字段管理
 * @desc 商品自定义字段管理
 * @use server\idcsmart_common\controller\admin\IdcsmartCommonProductCustomFieldController
 */
class IdcsmartCommonProductCustomFieldController extends BaseController
{
    public $validate;
    # 初始验证
    public function initialize()
    {
        parent::initialize();

        $this->validate = new IdcsmartCommonProductCustomFieldValidate();

        $param = $this->request->param();

        $IdcsmartCommonLogic = new IdcsmartCommonLogic();

        $IdcsmartCommonLogic->validate($param);
    }

    public function listCustomField()
    {
        $param = $this->request->param();

        $IdcsmartCommonProductCustomFieldModel = new IdcsmartCommonProductCustomFieldModel();

        $result = $IdcsmartCommonProductCustomFieldModel->listCustomField($param);

        return json($result);
    }

    public function index()
    {
        $param = $this->request->param();

        $IdcsmartCommonProductCustomFieldModel = new IdcsmartCommonProductCustomFieldModel();

        $result = $IdcsmartCommonProductCustomFieldModel->indexCustomField($param);

        return json($result);
    }

    public function create()
    {
        $param = $this->request->param();

        $IdcsmartCommonProductCustomFieldModel = new IdcsmartCommonProductCustomFieldModel();

        $result = $IdcsmartCommonProductCustomFieldModel->createCustomField($param);

        return json($result);
    }

    public function update()
    {
        $param = $this->request->param();

        $IdcsmartCommonProductCustomFieldModel = new IdcsmartCommonProductCustomFieldModel();

        $result = $IdcsmartCommonProductCustomFieldModel->updateCustomField($param);

        return json($result);
    }

    public function delete()
    {
        $param = $this->request->param();

        $IdcsmartCommonProductCustomFieldModel = new IdcsmartCommonProductCustomFieldModel();

        $result = $IdcsmartCommonProductCustomFieldModel->deleteCustomField($param);

        return json($result);
    }

    public function status()
    {
        $param = $this->request->param();

        $IdcsmartCommonProductCustomFieldModel = new IdcsmartCommonProductCustomFieldModel();

        $result = $IdcsmartCommonProductCustomFieldModel->statusCustomField($param);

        return json($result);
    }
    
}


