<?php
namespace app\admin\controller;

use app\admin\model\AdminModel;
use app\admin\model\AuthModel;
use app\common\model\ClientModel;
use app\common\logic\IndexLogic;

/**
 * @title 首页管理
 * @desc 首页管理
 * @use app\admin\controller\IndexController
 */
class IndexController extends AdminBaseController
{   
    public function initialize()
    {
        parent::initialize();
        $adminId = get_admin_id();
        $auths = AuthModel::alias('au')
            ->leftjoin('auth_link al', 'al.auth_id=au.id')
            ->leftjoin('admin_role adr', 'adr.id=al.admin_role_id')
            ->leftjoin('admin_role_link adrl', 'adrl.admin_role_id=adr.id')
            ->where('adrl.admin_id', $adminId)
            ->column('au.id');
        if(!in_array(99, $auths)){
            echo json_encode(['status'=>404, 'msg'=>lang('permission_denied', ['{name}'=>lang('auth_index')])]);die;
        }
    }

    /**
     * 时间 2022-09-16
     * @title 首页
     * @desc 首页
     * @author theworld
     * @version v1
     * @url /admin/v1/index
     * @method  GET
     * @return string this_year_amount - 本年销售额
     * @return string this_year_amount_percent - 本年销售额同比增长百分比
     * @return string this_month_amount - 本月销售额
     * @return string this_month_amount_percent - 本月销售额同比增长百分比
     * @return int active_client_count - 活跃用户数量
     * @return string active_client_percent - 活跃用户百分比
     * @return string today_sale_amount - 今日销售额
     */
    public function index()
    {
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new IndexLogic())->index()
        ];
        return json($result);
    }

    /**
     * 时间 2022-09-16
     * @title 本年销售详情
     * @desc 本年销售详情
     * @author theworld
     * @version v1
     * @url /admin/v1/index/this_year_sale
     * @method  GET
     * @return array this_year_month_amount - 本年销售详情
     * @return int this_year_month_amount[].month - 月份
     * @return string this_year_month_amount[].amount - 金额
     */
    public function thisYearSale()
    {
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new IndexLogic())->thisYearSale()
        ];
        return json($result);
    }

    /**
     * 时间 2022-09-16
     * @title 本年大客户
     * @desc 本年大客户
     * @author theworld
     * @version v1
     * @url /admin/v1/index/this_year_client
     * @method  GET
     * @return array clients - 本年大客户
     * @return int clients[].id - 用户ID
     * @return string clients[].username - 姓名
     * @return string clients[].email - 邮箱
     * @return int clients[].phone_code - 国际电话区号
     * @return string clients[].phone - 手机号
     * @return string clients[].company - 公司
     * @return string clients[].amount - 金额
     */
    public function thisYearClient()
    {
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new IndexLogic())->thisYearClient()
        ];
        return json($result);
    }

    /**
     * 时间 2022-09-16
     * @title 在线管理员列表
     * @desc 在线管理员列表
     * @author theworld
     * @version v1
     * @url /admin/v1/index/online_admin
     * @method  GET
     * @param int page - 页数
     * @param int limit - 每页条数
     * @return array list - 管理员列表
     * @return int list[].id - ID
     * @return int list[].nickname - 名称
     * @return int list[].name - 用户名
     * @return int list[].email - 邮箱
     * @return int list[].last_action_time - 上次操作时间
     * @return int count - 管理员总数
     */
    public function onlineAdmin()
    {
        # 合并分页参数
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new AdminModel())->onlineAdminList($param)
        ];
        return json($result);
    }

    /**
     * 时间 2022-09-16
     * @title 最近访问用户列表
     * @desc 最近访问用户列表
     * @author theworld
     * @version v1
     * @url /admin/v1/index/visit_client
     * @method  GET
     * @param int page - 页数
     * @param int limit - 每页条数
     * @return array list - 用户列表
     * @return int list[].id - ID
     * @return int list[].username - 姓名
     * @return int list[].email - 邮箱
     * @return int list[].phone_code - 国际电话区号
     * @return int list[].phone - 手机号
     * @return int list[].company - 公司
     * @return int list[].visit_time - 访问时间
     * @return int count - 用户总数
     */
    public function visitClient()
    {
        # 合并分页参数
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => (new ClientModel())->visitClientList($param)
        ];
        return json($result);
    }
}