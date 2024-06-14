<?php
namespace app\common\model;

use think\Model;
use think\Db;
use app\common\logic\EmailLogic;
/**
 * @title 邮件模板模型
 * @desc 邮件模板模型
 * @use app\common\model\EmailTemplateModel
 */
class EmailTemplateModel extends Model
{
	protected $name = 'email_template';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'name'       => 'string',
        'subject'       => 'string',
        'message'       => 'string',
        'attachment'    => 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

	/**
     * 时间 2022-05-17
     * @title 邮件模板列表
     * @desc 邮件模板列表
     * @author theworld
     * @version v1
     * @return array list - 邮件模板
     * @return int list[].id - 邮件模板ID
     * @return string list[].name - 名称
     * @return string list[].subject - 标题
     * @return int count - 邮件模板总数
     */
    public function emailTemplateList()
    {

    	$count = $this->field('id')
		    ->count();
    	$emailTemplates = $this->field('id,name,subject')
    		->select()
            ->toArray(); 

    	return ['list' => $emailTemplates, 'count' => $count];
    }

    /**
     * 时间 2022-05-17
     * @title 获取单个邮件模板
     * @desc 获取单个邮件模板
     * @author theworld
     * @version v1
     * @param int id - 邮件模板ID required
     * @return int id - 邮件模板ID
     * @return string name - 名称
     * @return string subject - 标题
     * @return string message - 内容
     */
    public function indexEmailTemplate($id)
    {
        $emailTemplate = $this->field('id,name,subject,message')->find($id);
        if (empty($emailTemplate)){
            return (object)[]; // 转换为对象
        }
        
        return $emailTemplate;
    }

    /**
     * 时间 2022-05-17
     * @title 创建邮件模板
     * @desc 创建邮件模板
     * @author theworld
     * @version v1
     * @param string param.name - 名称 required
     * @param string param.subject - 标题 required
     * @param string param.message - 内容 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function createEmailTemplate($param)
    {
	    $this->startTrans();
		try {
	    	$email_id = $emailTemplate = $this->create([
	    		'name' => $param['name'],
	    		'subject' => $param['subject'],
	    		'message' => $param['message'],
	    		'attachment' => '',
                'create_time' => time()
	    	]);

            # 记录日志
            active_log(lang('admin_create_email_template', ['{admin}'=>request()->admin_name, '{template}'=>'#'.$emailTemplate->id]), 'email_template', $emailTemplate->id);

	        $this->commit();
		} catch (\Exception $e) {
		    // 回滚事务
		    $this->rollback();
		    return ['status' => 400, 'msg' => lang('create_fail')];
		}
    	return ['status' => 200, 'msg' => lang('create_success'), 'id' => $email_id->id];
    }

    /**
     * 时间 2022-05-17
     * @title 修改邮件模板
     * @desc 修改邮件模板
     * @author theworld
     * @version v1
     * @param int id - 邮件模板ID required
     * @param string param.name - 名称 required
     * @param string param.subject - 标题 required
     * @param string param.message - 内容 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function updateEmailTemplate($param)
    {
    	$emailTemplate = $this->find($param['id']);
    	if (empty($emailTemplate)){
            return ['status'=>400, 'msg'=>lang('email_template_is_not_exist')];
        }
    	$this->startTrans();
		try {
            $this->update([
                'name' => $param['name'],
                'subject' => $param['subject'],
                'message' => $param['message'],
				'attachment' => '',
                'update_time' => time()
            ], ['id' => $param['id']]);

            # 记录日志
            active_log(lang('admin_edit_email_template', ['{admin}'=>request()->admin_name, '{template}'=>'#'.$emailTemplate->id]), 'email_template', $emailTemplate->id);

		    $this->commit();
		} catch (\Exception $e) {
		    // 回滚事务
		    $this->rollback();
		    return ['status' => 400, 'msg' => lang('update_fail')];
		}
    	return ['status' => 200, 'msg' => lang('update_success')];
    }

    /**
     * 时间 2022-05-17
     * @title 删除邮件模板
     * @desc 删除邮件模板
     * @author theworld
     * @version v1
     * @param int id - 邮件模板ID required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function deleteEmailTemplate($id)
    {
    	$emailTemplate = $this->find($id);
    	if (empty($emailTemplate)){
            return ['status'=>400, 'msg'=>lang('email_template_is_not_exist')];
        }
    	$this->startTrans();
		try {
            # 记录日志
            active_log(lang('admin_delete_email_template', ['{admin}'=>request()->admin_name, '{template}'=>'#'.$emailTemplate->id]), 'email_template', $emailTemplate->id);
            
			$this->destroy($id);
		    $this->commit();
		} catch (\Exception $e) {
		    // 回滚事务
		    $this->rollback();
		    return ['status' => 400, 'msg' => lang('delete_fail')];
		}
    	return ['status' => 200, 'msg' => lang('delete_success')];
    }
    /**
     * 时间 2022-05-17
     * @title 测试邮件模板
     * @desc 测试邮件模板
     * @author xiong
     * @version v1
     * @param string param.name - 邮件接口标识名称 required
     * @param int param.id - 邮件模板id required
     * @param string param.email - 邮箱 required
     * @return int status - 状态码,200成功,400失败
     * @return string msg - 提示信息
     */
    public function test($param)
    {
		$email_template = $this->field('id,subject,message,attachment')->find($param['id']);
        if (empty($email_template)){
            return ['status'=>400, 'msg'=>lang('email_template_is_not_exist')];
        } 		
		
		$EmailLogic = new EmailLogic();
		
		$code = rand(10000,99999);

		$template_param = [
			'{code}'=>$code,
		];
		$data = [
			'email' => $param['email'],
			'subject' => $email_template['subject'],
			'message' => $email_template['message'],
			'attachments' => $email_template['attachments'],
			'email_name' => $param['name'],
			'template_param' => $template_param,
		];
		
		return $EmailLogic->sendBase($data);
    }
}
