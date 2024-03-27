<?php
namespace app\admin\controller;

use app\common\model\NoticeSettingModel;
use app\admin\validate\NoticeSettingValidate;

/**
 * @title 通知发送管理
 * @desc 通知发送管理
 * @use app\admin\controller\NoticeSettingController
 */
class NoticeSettingController extends AdminBaseController
{
	public function initialize()
    {
        parent::initialize();
        $this->validate = new NoticeSettingValidate();
    }

    /**
     * 时间 2022-05-18
     * @title 发送管理
     * @desc 发送管理
     * @author xiong
     * @version v1
     * @url /admin/v1/notice/send
     * @method  GET
     * @return array list - 发送管理
     * @return string list[].name - 动作名称 
     * @return int list[].sms_global_name - 短信国际接口名称 
     * @return int list[].sms_global_template - 短信国际接口模板id 
     * @return string list[].sms_name - 短信国内接口名称 
     * @return int list[].sms_template - 短信国内接口模板id 
     * @return int list[].sms_enable - 启用状态，0禁用,1启用 
     * @return string list[].email_name - 邮件接口名称 
     * @return int list[].email_template - 邮件接口模板id 
     * @return int list[].email_enable - 启用状态，0禁用,1启用
     * @return array configuration - 默认接口
     * @return string configuration.send_sms - 默认国内短信接口
     * @return string configuration.send_sms_global - 默认国际短信接口
     * @return string configuration.send_email - 默认邮件接口
     */
	public function settingList(){
        
        //实例化模型类
        $NoticeSettingModel = new NoticeSettingModel();

        //获取产品列表
        $data = $NoticeSettingModel->settingList();

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
	}

	/**
     * 时间 2022-05-18
     * @title 发送设置
     * @desc 发送设置
     * @author xiong
     * @version v1
     * @url /admin/v1/notice/send
     * @method  put
     * @param array $name - 动作名称为键 
     * @param string $name.name - 动作名称 
     * @param int $name.sms_global_name - 短信国际接口名称 
     * @param int $name.sms_global_template - 短信国际接口模板id 
     * @param string $name.sms_name - 短信国内接口名称 
     * @param int $name.sms_template - 短信国内接口模板id 
     * @param int $name.sms_enable - 启用状态，0禁用,1启用 
     * @param string $name.email_name - 邮件接口名称 
     * @param int $name.email_template - 邮件接口模板id 
     * @param int $name.email_enable - 启用状态，0禁用,1启用 
     * @param array configuration - 默认接口
     * @param string configuration.send_sms - 默认国内短信接口
     * @param string configuration.send_sms_global - 默认国际短信接口
     * @param string configuration.send_email - 默认邮件接口
     */
	public function update(){
		//接收参数
        $param = $this->request->param();

        //参数验证
		/* foreach($param as $params){
			if (!$this->validate->scene('update')->check($params)){
				return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
			}
        } */

        //实例化模型类
        $NoticeSettingModel = new NoticeSettingModel();
        
        //修改产品
        $result = $NoticeSettingModel->updateNoticeSetting($param);

        return json($result);
	}

}