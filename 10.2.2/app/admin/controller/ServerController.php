<?php
namespace app\admin\controller;

use app\admin\validate\ServerValidate;
use app\common\model\ServerModel;

/**
 * @title 接口管理
 * @desc 接口管理
 * @use app\admin\controller\ServerController
 */
class ServerController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new ServerValidate();
    }

     /**
     * 时间 2022-05-27
     * @title 接口列表
     * @desc 接口列表
     * @url /admin/v1/server
     * @method  GET
     * @author hh
     * @version v1
     * @param string keywords - 关键字,搜索接口ID/接口名称/分组名称
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id,name,server_group_id,status
     * @param string sort - 升/降序 asc,desc
     * @return array list - 接口列表
     * @return int list[].id - 接口ID
     * @return string list[].name - 接口名称
     * @return string list[].module - 模块类型
     * @return string list[].url - 地址
     * @return string list[].username - 用户名
     * @return string list[].password - 密码
     * @return string list[].hash - hash
     * @return int list[].status - 是否启用(0=禁用,1=启用)
     * @return int list[].server_group_id - 接口分组ID
     * @return string list[].server_group_name - 接口分组名称
     * @return int list[].host_num - 已开通数量
     * @return string list[].module_name - 模块名称
     * @return int count - 接口总数
     */
    public function serverList()
    {
        # 合并分页参数
        $param = array_merge($this->request->param(),['page'=>$this->request->page,'limit'=>$this->request->limit,'sort'=>$this->request->sort]);

        $result = [
            'status'=>200,
            'msg'=>lang('success_message'),
            'data' =>(new ServerModel())->serverList($param)
        ];
       return json($result);
    }


    /**
     * 时间 2022-05-27
     * @title 新建接口
     * @desc 新建接口
     * @url /admin/v1/server
     * @method  POST
     * @author hh
     * @version v1
     * @param string name - 接口名称 required
     * @param string module - 模块类型 required
     * @param string url - 地址 required
     * @param string username - 用户名
     * @param string password - 密码
     * @param string hash - hash
     * @param int status 0 是否启用(0=禁用,1=启用)
     */
    public function create()
    {
        $param = $this->request->param();

        //参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new serverModel())->createServer($param);

        return json($result);
    }

    /**
     * 时间 2022-05-27
     * @title 编辑接口
     * @desc 编辑接口
     * @url /admin/v1/server/:id
     * @method  PUT
     * @author hh
     * @version v1
     * @param int id - 接口ID required
     * @param string name - 接口名称 required
     * @param string module - 模块类型 required
     * @param string url - 地址 required
     * @param string username - 用户名
     * @param string password - 密码
     * @param string hash - hash
     * @param int status - 是否启用(0=禁用,1=启用) 
     */
    public function update()
    {
        $param = $this->request->param();
        //参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        $result = (new ServerModel())->updateServer($param);

        return json($result);
    }

    /**
     * 时间 2022-05-27
     * @title 删除接口
     * @desc 删除接口
     * @url /admin/v1/server/:id
     * @method  DELETE
     * @author hh
     * @version v1
     * @param  int id - 接口ID required
     */
    public function delete()
    {
        $param = $this->request->param();

        $result = (new ServerModel())->deleteServer(intval($param['id']));

        return json($result);
    }

    /**
     * 时间 2022-05-27
     * @title 获取接口连接状态
     * @desc 获取接口连接状态
     * @url /admin/v1/server/:id/status
     * @method  GET
     * @author hh
     * @version v1
     * @param   int id - 接口ID required
     */
    public function status()
    {
        $param = $this->request->param();

        $result = (new ServerModel())->status(intval($param['id']));

        return json($result);
    }

}

