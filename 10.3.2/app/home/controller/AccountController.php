<?php
namespace app\home\controller;

use app\common\model\ClientModel;
use app\common\model\ClientCreditModel;
use app\home\validate\AccountValidate;

/**
 * @title 账户管理
 * @desc 账户管理
 * @use app\home\controller\AccountController
 */
class AccountController extends HomeBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new AccountValidate();
    }

    /**
     * 时间 2022-05-19
     * @title 账户详情
     * @desc 账户详情
     * @author theworld
     * @version v1
     * @url /console/v1/account
     * @method  GET
     * @return object account - 账户
     * @return string account.username - 姓名 
     * @return string account.email - 邮箱 
     * @return int account.phone_code - 国际电话区号 
     * @return string account.phone - 手机号 
     * @return string account.company - 公司 
     * @return string account.country - 国家 
     * @return string account.address - 地址 
     * @return string account.language - 语言 
     * @return string account.notes - 备注 
     * @return string account.credit - 余额 
     * @return object account.customfiled - 自定义字段 
     */
    public function index()
    {
        // 接收参数
        $param = $this->request->param();
        $id = get_client_id(false); // 获取用户ID
        
        // 实例化模型类
        $ClientModel = new ClientModel();

        // 获取用户
        $account = $ClientModel->indexClient($id);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'account' => $account
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-05-19
     * @title 账户编辑
     * @desc 账户编辑
     * @author theworld
     * @version v1
     * @url /console/v1/account
     * @method  PUT
     * @param string username - 姓名
     * @param string company - 公司
     * @param string country - 国家
     * @param string address - 地址
     * @param string language - 语言
     * @param string notes - 备注
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
        $ClientModel = new ClientModel();
        
        // 修改用户
        $result = $ClientModel->updateClient($param);

        return json($result);
    }

    /**
     * 时间 2022-05-19
     * @title 验证原手机
     * @desc 验证原手机
     * @author theworld
     * @version v1
     * @url /console/v1/account/phone/old
     * @method  PUT
     * @param string code - 验证码 required
     */
    public function verifyOldPhone()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('verify_old_phone')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ClientModel = new ClientModel();
        
        // 验证原手机
        $result = $ClientModel->verifyOldPhone($param);

        return json($result);
    }

    /**
     * 时间 2022-05-19
     * @title 修改手机
     * @desc 修改手机,如果已经绑定了手机需要验证原手机
     * @author theworld
     * @version v1
     * @url /console/v1/account/phone
     * @method  PUT
     * @param int phone_code - 国际电话区号 required
     * @param string phone - 手机号 required
     * @param string code - 验证码 required
     */
    public function updatePhone()
    {
        // 接收参数
        $param = $this->request->param();
        $param['id'] = get_client_id(false); // 获取用户ID用于验证

        // 参数验证
        if (!$this->validate->scene('update_phone')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ClientModel = new ClientModel();
        
        // 修改手机
        $result = $ClientModel->updateClientPhone($param);

        return json($result);
    }

    /**
     * 时间 2022-05-19
     * @title 验证原邮箱
     * @desc 验证原邮箱
     * @author theworld
     * @version v1
     * @url /console/v1/account/email/old
     * @method  PUT
     * @param string code - 验证码 required
     */
    public function verifyOldEmail()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('verify_old_email')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ClientModel = new ClientModel();
        
        // 验证原邮箱
        $result = $ClientModel->verifyOldEmail($param);

        return json($result);
    }

    /**
     * 时间 2022-05-19
     * @title 修改邮箱
     * @desc 修改邮箱,如果已经绑定了邮箱需要验证原邮箱
     * @author theworld
     * @version v1
     * @url /console/v1/account/email
     * @method  PUT
     * @param string email - 邮箱 required
     * @param string code - 验证码 required
     */
    public function updateEmail()
    {
        // 接收参数
        $param = $this->request->param();
        $param['id'] = get_client_id(false); // 获取用户ID用于验证

        // 参数验证
        if (!$this->validate->scene('update_email')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ClientModel = new ClientModel();
        
        // 修改邮箱
        $result = $ClientModel->updateClientEmail($param);

        return json($result);
    }

    /**
     * 时间 2022-05-19
     * @title 修改密码
     * @desc 修改密码
     * @author theworld
     * @version v1
     * @url /console/v1/account/password
     * @method  PUT
     * @param string old_password - 旧密码 required
     * @param string new_password - 新密码 required
     * @param string repassword - 确认密码 required
     */
    public function updatePassword()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update_password')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ClientModel = new ClientModel();
        
        // 修改用户密码
        $result = $ClientModel->updateClientPassword($param);

        return json($result);
    }

    /**
     * 时间 2022-05-23
     * @title 验证码修改密码
     * @desc 验证码修改密码
     * @author wyh
     * @version v1
     * @url /console/v1/account/password/code
     * @method  PUT
     * @param string type phone 验证类型:phone手机,email邮箱 required
     * @param string code 1234 验证码 required
     * @param string password 123456 密码 required
     * @param string re_password 1 重复密码 required
     */
    public function codeUpdatePassword()
    {
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('code_update_password')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $ClientModel = new ClientModel();

        // 修改用户密码
        $result = $ClientModel->codeUpdatePassword($param);

        return json($result);
    }

    /**
     * 时间 2022-5-23
     * @title 注销
     * @desc 注销
     * @url /console/v1/logout
     * @method  post
     * @author wyh
     * @version v1
     */
    public function logout()
    {
        // 接收参数
        $param = $this->request->param();

        $result = (new ClientModel())->logout($param);

        return json($result);
    }

    /**
     * 时间 2022-07-19
     * @title 余额变更记录列表
     * @desc 余额变更记录列表
     * @author theworld
     * @version v1
     * @url /console/v1/credit
     * @method  GET
     * @param int start_time - 开始时间，时间戳(s)
     * @param int end_time - 结束时间，时间戳(s)
     * @param string type - 类型:人工Artificial,充值Recharge,应用至订单Applied,超付Overpayment,少付Underpayment,退款Refund 
     * @param string keywords - 关键字:记录ID,备注
     * @param string type - 类型:人工Artificial,充值Recharge,应用至订单Applied,退款Refund,提现Withdraw
     * @param int order_id - 订单ID
     * @param int page - 页数
     * @param int limit - 每页条数
     * @return array list - 记录
     * @return int list[].id - 记录ID
     * @return string list[].type - 类型:人工Artificial,充值Recharge,应用至订单Applied,退款Refund,提现Withdraw
     * @return string list[].amount - 金额 
     * @return string list[].notes - 备注 
     * @return int list[].create_time - 变更时间 
     * @return string list[].admin_name - 管理员名称 
     * @return int count - 记录总数
     */
    public function creditList()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        // 实例化模型类
        $ClientCreditModel = new ClientCreditModel();

        // 获取记录
        $data = $ClientCreditModel->clientCreditList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }
}