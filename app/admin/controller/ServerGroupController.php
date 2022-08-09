<?php
namespace app\admin\controller;

use app\admin\validate\ServerGroupValidate;
use app\common\model\ServerGroupModel;

/**
 * @title 接口分组管理
 * @desc 接口分组管理
 * @use app\admin\controller\ServerGroupController
 */
class ServerGroupController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new ServerGroupValidate();
    }

     /**
     * 时间 2022-05-27
     * @title 接口分组列表
     * @desc 接口分组列表
     * @url /admin/v1/server/group
     * @method  GET
     * @author hh
     * @version v1
     * @param string keywords - 关键字
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id,name
     * @param string sort - 升/降序 asc,desc
     * @return array list - 接口分组列表
     * @return int list[].id - 接口分组ID
     * @return string list[].name - 分组名称
     * @return int list[].create_time - 创建时间
     * @return array list[].server - 接口列表
     * @return int list[].server[].id - 接口ID
     * @return string list[].server[].name - 接口名称
     * @return int count - 接口分组总数
     */
    public function serverGroupList()
    {
        # 合并分页参数
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new ServerGroupModel())->serverGroupList($param)
        ];
       return json($result);
    }


    /**
     * 时间 2022-05-27
     * @title 新建接口分组
     * @desc 新建接口分组
     * @url /admin/v1/server/group
     * @method  POST
     * @author hh
     * @version v1
     * @param string name - 分组名称 required
     * @param array server_id - 接口ID required
     * @return int id - 接口分组ID
     */
    public function create()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ServerGroupModel())->createServerGroup($param);

        return json($result);
    }

    /**
     * 时间 2022-05-27
     * @title 修改接口分组
     * @desc 修改接口分组
     * @url /admin/v1/server/group/:id
     * @method  PUT
     * @author hh
     * @version v1
     * @param string name - 分组名称 required
     * @param array server_id - 接口ID required
     */
    public function update()
    {
        $param = $this->request->param();
        //参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ServerGroupModel())->updateServerGroup($param);

        return json($result);
    }

    /**
     * 时间 2022-05-27
     * @title 删除接口分组
     * @desc 删除接口分组
     * @url /admin/v1/server/group/:id
     * @method  DELETE
     * @author hh
     * @version v1
     * @param   int id - 接口分组ID
     */
    public function delete()
    {
        $param = $this->request->param();

        $result = (new ServerGroupModel())->deleteServerGroup(intval($param['id']));

        return json($result);
    }
}

