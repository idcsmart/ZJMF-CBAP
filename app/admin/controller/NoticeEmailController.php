<?php
namespace app\admin\controller;

use app\common\model\EmailTemplateModel;
use app\admin\validate\NoticeEmailValidate;

/**
 * @title 邮件模板管理
 * @desc 邮件模板管理
 * @use app\admin\controller\NoticeEmailController
 */
class NoticeEmailController extends AdminBaseController
{
	public function initialize()
    {
        parent::initialize();
        $this->validate = new NoticeEmailValidate();
    }

    /**
     * 时间 2022-05-17
     * @title 邮件模板列表
     * @desc 邮件模板列表
     * @author theworld
     * @version v1
     * @url /admin/v1/notice/email/template
     * @method  GET
     * @return array list - 邮件模板
     * @return int list[].id - 邮件模板ID
     * @return string list[].name - 名称
     * @return string list[].subject - 标题
     * @return int count - 邮件模板总数
     */
    public function emailTemplateList()
    {
    	// 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $EmailTemplateModel = new EmailTemplateModel();

        // 获取邮件模板列表
        $data = $EmailTemplateModel->emailTemplateList();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-05-17
     * @title 获取单个邮件模板
     * @desc 获取单个邮件模板
     * @author theworld
     * @version v1
     * @url /admin/v1/notice/email/template/:id
     * @method  GET
     * @param int id - 邮件模板ID required
     * @return object email_template - 邮件模板
     * @return int email_template.id - 邮件模板ID
     * @return string email_template.name - 名称
     * @return string email_template.subject - 标题
     * @return string email_template.message - 内容
     */
	public function index()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $EmailTemplateModel = new EmailTemplateModel();

        // 获取邮件模板
        $email_template = $EmailTemplateModel->indexEmailTemplate($param['id']);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'email_template' => $email_template
            ]
        ];
        return json($result);
	}

	/**
     * 时间 2022-05-17
     * @title 创建邮件模板
     * @desc 创建邮件模板
     * @author theworld
     * @version v1
     * @url /admin/v1/notice/email/template
     * @method  POST
     * @param string name - 名称 required
     * @param string subject - 标题 required
     * @param string message - 内容 required
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
        $EmailTemplateModel = new EmailTemplateModel();
        
        // 新建邮件模板
        $result = $EmailTemplateModel->createEmailTemplate($param);

        return json($result);
	}

	/**
     * 时间 2022-05-17
     * @title 修改邮件模板
     * @desc 修改邮件模板
     * @author theworld
     * @version v1
     * @url /admin/v1/notice/email/template/:id
     * @method  PUT
     * @param int id - 邮件模板ID required
     * @param string name - 名称 required
     * @param string subject - 标题 required
     * @param string message - 内容 required
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
        $EmailTemplateModel = new EmailTemplateModel();
        
        // 修改邮件模板
        $result = $EmailTemplateModel->updateEmailTemplate($param);

        return json($result);
	}

	/**
     * 时间 2022-05-17
     * @title 删除邮件模板
     * @desc 删除邮件模板
     * @author theworld
     * @version v1
     * @url /admin/v1/notice/email/template/:id
     * @method  DELETE
     * @param int id - 邮件模板ID required
     */
	public function delete()
	{
		// 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $EmailTemplateModel = new EmailTemplateModel();
        
        // 删除邮件模板
        $result = $EmailTemplateModel->deleteEmailTemplate($param['id']);

        return json($result);
	}

	/**
     * 时间 2022-05-18
     * @title 测试邮件模板
     * @desc 测试邮件模板
     * @author theworld
     * @version v1
     * @url /admin/v1/notice/email/:name/template/:id/test
     * @method  GET
     * @param string name - 邮件接口标识名称 required
     * @param int id - 邮件模板ID required
     * @param string email - 邮箱 required
     */
	public function test()
	{
		//接收参数
        $param = $this->request->param();
		
		// 参数验证
        if (!$this->validate->scene('test')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }
		
        //实例化模型类
        $EmailTemplateModel = new EmailTemplateModel();
        
        $result = $EmailTemplateModel->test($param);

        return json($result);
	}
}