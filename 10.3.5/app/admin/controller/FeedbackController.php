<?php
namespace app\admin\controller;

use app\common\model\FeedbackModel;
use app\common\model\FeedbackTypeModel;
use app\admin\validate\FeedbackTypeValidate;

/**
 * @title 意见反馈
 * @desc 意见反馈
 * @use app\admin\controller\FeedbackController
 */
class FeedbackController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new FeedbackTypeValidate();
    }

    /**
     * 时间 2023-02-28
     * @title 意见反馈列表
     * @desc 意见反馈列表
     * @author theworld
     * @version v1
     * @url /admin/v1/feedback
     * @method  GET
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 意见反馈
     * @return int list[].id - 意见反馈ID 
     * @return string list[].title - 标题 
     * @return string list[].type - 类型 
     * @return string list[].description - 描述
     * @return int list[].client_id - 用户ID 
     * @return string list[].username - 用户名
     * @return string list[].contact - 联系方式
     * @return array list[].attachment - 附件
     * @return int list[].create_time - 反馈时间
     * @return int count - 意见反馈总数
     */
	public function feedbackList()
    {
		// 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $FeedbackModel = new FeedbackModel();

        // 获取意见反馈列表
        $data = $FeedbackModel->feedbackList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
	}

    /**
     * 时间 2023-02-28
     * @title 获取意见反馈类型
     * @desc 获取意见反馈类型
     * @author theworld
     * @version v1
     * @url /admin/v1/feedback/type
     * @method  GET
     * @return array list - 意见反馈类型
     * @return int list[].id - 意见反馈类型ID 
     * @return string list[].name - 名称 
     * @return string list[].description - 描述
     */
    public function feedbackTypeList()
    {  
        // 实例化模型类
        $FeedbackTypeModel = new FeedbackTypeModel();

        // 获取意见反馈类型
        $data = $FeedbackTypeModel->feedbackTypeList();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 添加意见反馈类型
     * @desc 添加意见反馈类型
     * @author theworld
     * @version v1
     * @url /admin/v1/feedback/type
     * @method  POST
     * @param string name - 名称 required
     * @param string description - 描述 required
     */
    public function createFeedbackType()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $FeedbackTypeModel = new FeedbackTypeModel();
        
        // 新建意见反馈类型
        $result = $FeedbackTypeModel->createFeedbackType($param);

        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 编辑意见反馈类型
     * @desc 编辑意见反馈类型
     * @author theworld
     * @version v1
     * @url /admin/v1/feedback/type/:id
     * @method  PUT
     * @param int id - 意见反馈类型ID required
     * @param string name - 名称 required
     * @param string description - 描述 required
     */
    public function updateFeedbackType()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
        
        // 实例化模型类
        $FeedbackTypeModel = new FeedbackTypeModel();
        
        // 修改意见反馈类型
        $result = $FeedbackTypeModel->updateFeedbackType($param);

        return json($result);
    }

    /**
     * 时间 2023-02-28
     * @title 删除意见反馈类型
     * @desc 删除意见反馈类型
     * @author theworld
     * @version v1
     * @url /admin/v1/feedback/type/:id
     * @method  DELETE
     * @param int id - 意见反馈类型ID required
     */
    public function deleteFeedbackType()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $FeedbackTypeModel = new FeedbackTypeModel();
        
        // 删除意见反馈类型
        $result = $FeedbackTypeModel->deleteFeedbackType($param['id']);

        return json($result);

    }

    
}