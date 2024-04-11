<?php
namespace app\admin\controller;

use app\common\model\WebNavModel;
use app\admin\validate\WebNavValidate;

/**
 * @title 官网导航管理
 * @desc 官网导航管理
 * @use app\admin\controller\WebNavController
 */
class WebNavController extends AdminBaseController
{
	public function initialize()
    {
        parent::initialize();
    }

    /**
     * 时间 2024-03-01
     * @title 官网导航列表
     * @desc  官网导航列表
     * @url /admin/v1/web_nav
     * @method  GET
     * @author hh
     * @version v1
     * @return  int list[].id - 官网导航ID
     * @return  string list[].name - 导航名称
     * @return  int list[].web_nav_id - 所属导航ID
     * @return  string list[].url - 跳转链接
     * @return  int list[].status - 是否展示(0=关闭,1=开启)
     * @return  int list[].child[].id - 二级官网导航ID
     * @return  string list[].child[].name - 导航名称
     * @return  int list[].child[].web_nav_id - 所属导航ID
     * @return  string list[].child[].url - 跳转链接
     * @return  int list[].child[].status - 是否展示(0=关闭,1=开启)
     * @return  int count - 总条数
     */
	public function list()
    {
        $WebNavModel = new WebNavModel();

        $data = $WebNavModel->webNavList();

        $result = [
            'status' => 200,
            'msg'    => lang('success_message'),
            'data'   => $data
        ];
        return json($result);
	}

    /**
     * 时间 2024-03-01
     * @title 新增官网导航
     * @desc 新增官网导航
     * @url /admin/v1/web_nav
     * @method  POST
     * @author hh
     * @version v1
     * @param   string name - 导航名称 require
     * @param   int web_nav_id - 所属导航ID(0=一级导航) require
     * @param   string url - 跳转链接 require
     * @param   int status - 是否展示(0=关闭,1=开启) require
     * @return  int id - 官网导航ID
     */
	public function create()
    {
		$param = $this->request->param();

        $WebNavValidate = new WebNavValidate();
        if (!$WebNavValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($WebNavValidate->getError())]);
        }

        $WebNavModel = new WebNavModel();
        
        $result = $WebNavModel->webNavCreate($param);
        return json($result);
	}

    /**
     * 时间 2024-03-01
     * @title 修改官网导航
     * @desc  修改官网导航
     * @url /admin/v1/web_nav/:id
     * @method  PUT
     * @author hh
     * @version v1
     * @param   int id - 官网导航ID require
     * @param   string name - 导航名称 require
     * @param   int web_nav_id - 所属导航ID(0=一级导航) require
     * @param   string url - 跳转链接 require
     * @param   int status - 是否展示(0=关闭,1=开启) require
     */
    public function update()
    {
        $param = $this->request->param();

        $WebNavValidate = new WebNavValidate();
        if (!$WebNavValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($WebNavValidate->getError())]);
        }

        $WebNavModel = new WebNavModel();
        
        $result = $WebNavModel->webNavUpdate($param);
        return json($result);
    }

    /**
    * 时间 2024-03-01
    * @title 删除官网导航
    * @desc  删除官网导航
    * @url /admin/v1/web_nav/:id
    * @method  DELETE
    * @author hh
    * @version v1
    * @param   int id - 官网导航ID require
    */
	public function delete()
    {
        $param = $this->request->param();

        $WebNavModel = new WebNavModel();
        
        $result = $WebNavModel->webNavDelete($param);
        return json($result);
	}

    /**
     * 时间 2024-03-01
     * @title 修改官网导航是否展示
     * @desc  修改官网导航是否展示
     * @url /admin/v1/web_nav/:id/status
     * @method  PUT
     * @author hh
     * @version v1
     * @param   int id - 官网导航ID require
     * @param   int status - 是否展示(0=关闭,1=开启) require
     */
    public function updateStatus()
    {
        $param = $this->request->param();

        $WebNavValidate = new WebNavValidate();
        if (!$WebNavValidate->scene('update_status')->check($param)){
            return json(['status' => 400 , 'msg' => lang($WebNavValidate->getError())]);
        }

        $WebNavModel = new WebNavModel();
        
        $result = $WebNavModel->webNavUpdateStatus($param);
        return json($result);
    }

    /**
     * 时间 2024-03-01
     * @title 拖动排序
     * @desc 拖动排序
     * @url /admin/v1/web_nav/:id/drag
     * @method  PUT
     * @author hh
     * @version v1
     * @param   int id - 当前导航ID require
     * @param   int prev_id - 前一个导航ID(0=表示置顶) require
     * @param   int web_nav_id - 所属导航ID(0=一级导航,如果有前一个导航ID,使用前一个导航所属导航ID) require
     */
    public function dragToSort()
    {
        $param = request()->param();

        $WebNavValidate = new WebNavValidate();
        if (!$WebNavValidate->scene('drag_to_sort')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($WebNavValidate->getError())]);
        }        
        $WebNavModel = new WebNavModel();

        $result = $WebNavModel->dragToSort($param);
        return json($result);
    }

}