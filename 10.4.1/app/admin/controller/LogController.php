<?php
namespace app\admin\controller;

use app\common\model\SystemLogModel;
use app\admin\model\EmailLogModel;
use app\admin\model\SmsLogModel;

/**
 * @title 系统日志管理
 * @desc 系统日志管理
 * @use app\admin\controller\LogController
 */
class LogController extends AdminBaseController
{   
    /**
     * 时间 2022-05-16
     * @title 系统日志列表
     * @desc 系统日志列表
     * @author theworld
     * @version v1
     * @url /admin/v1/log/system
     * @method  GET
     * @param string keywords - 关键字,搜索范围:描述
     * @param int client_id - 用户ID
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 系统日志
     * @return int list[].id - 系统日志ID 
     * @return string list[].description - 描述 
     * @return int list[].create_time - 时间 
     * @return string list[].ip - IP 
     * @return string list[].user_type - 操作人类型client用户admin管理员system系统cron定时任务
     * @return int list[].user_id - 操作人ID
     * @return string list[].user_name - 操作人名称
     * @return int count - 系统日志总数
     */
	public function systemLogList()
    {
		// 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $SystemLogModel = new SystemLogModel();

        // 获取系统日志列表
        $data = $SystemLogModel->systemLogList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
	}

    /**
     * 时间 2022-05-17
     * @title 邮件日志列表
     * @desc 邮件日志列表
     * @author theworld
     * @version v1
     * @url /admin/v1/log/notice/email
     * @method  GET
     * @param string keywords - 关键字,搜索范围:标题
     * @param int client_id - 用户ID
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 邮件日志
     * @return int list[].id - 邮件日志ID 
     * @return string list[].subject - 标题 
     * @return string list[].message - 内容 
     * @return int list[].create_time - 时间 
     * @return string list[].to - 邮箱 
     * @return string list[].user_type - 接收人类型client用户admin管理员
     * @return int list[].user_id - 接收人ID
     * @return string list[].user_name - 接收人名称
     * @return int list[].status - 状态，1成功，0失败
     * @return string list[].fail_reason - 失败原因
     * @return int count - 邮件日志总数
     */
    public function emailLogList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $EmailLogModel = new EmailLogModel();

        // 获取邮件日志列表
        $data = $EmailLogModel->emailLogList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-05-17
     * @title 短信日志列表
     * @desc 短信日志列表
     * @author theworld
     * @version v1
     * @url /admin/v1/log/notice/sms
     * @method  GET
     * @param string keywords - 关键字,搜索范围:内容
     * @param int client_id - 用户ID
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - 短信日志
     * @return int list[].id - 短信日志ID 
     * @return string list[].content - 内容 
     * @return int list[].create_time - 时间 
     * @return string list[].user_type - 接收人类型client用户admin管理员
     * @return int list[].user_id - 接收人ID
     * @return string list[].user_name - 接收人名称
     * @return int list[].phone_code - 国际电话区号
     * @return string list[].phone - 手机号
     * @return int list[].status - 状态，1成功，0失败
     * @return string list[].fail_reason - 失败原因
     * @return int count - 短信日志总数
     */
    public function smsLogList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $SmsLogModel = new SmsLogModel();

        // 获取短信日志列表
        $data = $SmsLogModel->smsLogList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }
}