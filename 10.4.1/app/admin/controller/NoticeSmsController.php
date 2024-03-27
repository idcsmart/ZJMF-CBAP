<?php
namespace app\admin\controller;

use app\common\model\SmsTemplateModel;
use app\admin\validate\NoticeSmsValidate;

/**
 * @title 短信模板管理
 * @desc 短信模板管理
 * @use app\admin\controller\NoticeSmsController
 */
class NoticeSmsController extends AdminBaseController
{
	public function initialize()
    {
        parent::initialize();
        $this->validate = new NoticeSmsValidate();
    }

    /**
     * 时间 2022-05-17
     * @title 获取短信模板
     * @desc 获取短信模板
     * @author xiong
     * @version v1
     * @url /admin/v1/notice/sms/:name/template
     * @method  GET
     * @param string name - 短信接口标识名称 required
     * @return array list - 短信模板
     * @return string list[].id - 短信模板ID 
     * @return string list[].template_id - 短信接口模板ID 
     * @return string list[].type - 模板类型（0大陆，1国际 ）
     * @return string list[].sms_name - 接口标识名称
     * @return string list[].title - 模板标题 
     * @return string list[].content - 模版内容 
     * @return string list[].notes - 备注 
     * @return string list[].status - 状态（0未提交审核，1审核中，2通过审核，3未通过审核）
     */
	public function templateList(){
		# 合并分页参数
        $param = $this->request->param();
        
        //实例化模型类
        $SmsTemplateModel = new SmsTemplateModel();

        //获取短信模板列表
        $data = $SmsTemplateModel->smsTemplateList($param['name']);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
	}

	/**
     * 时间 2022-05-17
     * @title 获取单个短信模板
     * @desc 获取单个短信模板
     * @author xiong
     * @version v1
     * @url /admin/v1/notice/sms/:name/template/:id
     * @method  GET
     * @param string name - 短信接口标识名称 required
     * @param int id - 短信模板id required
     * @return string template_id - 模板ID 
     * @return string type - 模板类型（0大陆，1国际）
     * @return string title - 模板标题 
     * @return string content - 模版内容 
     * @return string notes - 备注 
     * @return string status - 状态（0未提交审核，1审核中，2通过审核，3未通过审核）
     * @return string product_url - 应用场景 
     * @return string remark - 场景说明 
     */
	public function index(){
		//接收参数
        $param = $this->request->param();
        
        //实例化模型类
        $SmsTemplateModel = new SmsTemplateModel();

        //获取短信模板
        $sms = $SmsTemplateModel->indexSmsTemplate($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $sms,
        ];
        return json($result);
	}
	/**
     * 时间 2022-05-17
     * @title 更新模板审核状态
     * @desc 更新模板审核状态
     * @author xiong
     * @version v1
     * @url /admin/v1/notice/sms/:name/template/status
     * @method  GET
     * @param string name - 短信接口标识名称 required
     */
	public function status(){
		//接收参数
        $param = $this->request->param();
        
        //实例化模型类
        $SmsTemplateModel = new SmsTemplateModel();

        //获取短信模板
        $result = $SmsTemplateModel->statusSmsTemplate($param);
        return json($result);
	}	
	/**
     * 时间 2022-05-17
     * @title 提交审核短信模板
     * @desc 提交审核短信模板
     * @author xiong
     * @version v1
     * @url /admin/v1/notice/sms/:name/template/audit
     * @method  POST
     * @param string name - 短信接口标识名称 required
     * @param array ids[] - 模板ID required
     */
	public function audit(){
		//接收参数
        $param = $this->request->param();
        
        //实例化模型类
        $SmsTemplateModel = new SmsTemplateModel();

        //获取短信模板
        $result = $SmsTemplateModel->auditSmsTemplate($param);

        return json($result);
	}	
	/**
     * 时间 2022-05-17
     * @title 创建短信模板
     * @desc 创建短信模板
     * @author xiong
     * @version v1
     * @url /admin/v1/notice/sms/:name/template
     * @method  POST
     * @param string name - 短信接口标识名称 required
     * @param string template_id - 模板ID 
     * @param string type - 模板类型（0大陆，1国际） required
     * @param string title - 模板标题 
     * @param string content - 模版内容 
     * @param string notes - 备注 
     * @param string status - 状态（0未提交审核，2通过审核，3未通过审核）
     * @param string product_url - 应用场景 阿里云短信必填
     * @param string remark - 场景说明 阿里云短信必填
     */
	public function create(){
		//接收参数
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        //实例化模型类
        $SmsTemplateModel = new SmsTemplateModel();
        
        //修改产品
        $result = $SmsTemplateModel->createSmsTemplate($param);

        return json($result);
	}
	/**
     * 时间 2022-05-17
     * @title 修改短信模板
     * @desc 修改短信模板
     * @author xiong
     * @version v1
     * @url /admin/v1/notice/sms/:name/template/:id
     * @method  PUT
     * @param string name - 短信接口标识名称 required
     * @param int id - 短信模板id required
     * @param string template_id - 模板ID 
     * @param string type - 模板类型（0大陆，1国际）
     * @param string title - 模板标题 
     * @param string content - 模版内容 
     * @param string notes - 备注 
     * @param string status - 状态（0未提交审核，2通过审核，3未通过审核）
     * @param string product_url - 应用场景 阿里云短信必填
     * @param string remark - 场景说明 阿里云短信必填
     */
	public function update(){
		//接收参数
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        //实例化模型类
        $SmsTemplateModel = new SmsTemplateModel();
        
        //修改产品
        $result = $SmsTemplateModel->updateSmsTemplate($param);

        return json($result);
	}

	/**
     * 时间 2022-05-17
     * @title 删除短信模板
     * @desc 删除短信模板
     * @author xiong
     * @version v1
     * @url /admin/v1/notice/sms/:name/template/:id
     * @method  DELETE
     * @param string name - 短信接口标识名称 required
     * @param int id - 短信模板id required
     */
	public function delete(){
		//接收参数
        $param = $this->request->param();

        //实例化模型类
        $SmsTemplateModel = new SmsTemplateModel();
        
        //删除产品
        $result = $SmsTemplateModel->deleteSmsTemplate($param['id']);

        return json($result);
	}
	/**
     * 时间 2022-05-17
     * @title 测试短信模板
     * @desc 测试短信模板
     * @author xiong
     * @version v1
     * @url /admin/v1/notice/sms/:name/template/:id/test
     * @method  GET
     * @param string name - 短信接口标识名称 required
     * @param int id - 短信模板id required
     * @param string phone_code - 手机区号 
     * @param string phone - 手机号 required
     */
	public function test(){
		//接收参数
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('test')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        //实例化模型类
        $SmsTemplateModel = new SmsTemplateModel();
        
        $result = $SmsTemplateModel->test($param);

        return json($result);
	}
}