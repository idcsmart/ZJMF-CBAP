<?php
namespace server\idcsmart_common\controller\admin;

use app\event\controller\BaseController;
use server\idcsmart_common\model\IdcsmartCommonServerGroupModel;
use server\idcsmart_common\validate\IdcsmartCommonServerGroupValidate;

/**
 * @title 通用商品-子接口
 * @desc 通用商品-子接口
 * @use server\idcsmart_common\controller\admin\IdcsmartCommonServerGroupController
 */
class IdcsmartCommonServerGroupController extends BaseController{

    /**
     * 时间 2023-6-8
     * @title 服务器分组列表
     * @desc 服务器分组列表
     * @url /admin/v1/idcsmart_common/server_group
     * @method  GET
     * @author wyh
     * @version v1
     * @param string modules - 模块，非必传
     * @return array list - 服务器分组列表
     * @return int list[].id - 服务器分组ID
     * @return int list[].name - 服务器分组名称
     * @return int list[].num - 总数量
     * @return int list[].used - 使用
     * @return int list[].mode - 分配方式(1:平均分配;2:满一个算一个(这两个分配方式写死))
     */
    public function serverGroupList(){
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);

        $IdcsmartCommonServerGroupModel = new IdcsmartCommonServerGroupModel();

        $result = $IdcsmartCommonServerGroupModel->serverGroupList($param);

        return json($result);
    }

    /**
     * 时间 2023-6-8
     * @title 添加服务器分组
     * @desc 添加服务器分组
     * @url /admin/v1/idcsmart_common/server_group
     * @method  POST
     * @author wyh
     * @version v1
     * @param string name - 服务器分组名称 required
     * @param string mode - 分配方式(1:平均分配;2:满一个算一个(这两个分配方式写死)) required
     * @param array server_ids - 选择的服务器ID,数组
     */
    public function create(){
        $param = $this->request->param();

        $IdcsmartCommonServerGroupValidate = new IdcsmartCommonServerGroupValidate();
        if (!$IdcsmartCommonServerGroupValidate->scene('create')->check($param)){
            return json(['status'=>400,'msg'=>$IdcsmartCommonServerGroupValidate->getError()]);
        }

        $IdcsmartCommonServerGroupModel = new IdcsmartCommonServerGroupModel();

        $result = $IdcsmartCommonServerGroupModel->createServerGroup($param);

        return json($result);
    }

    /**
     * 时间 2023-6-8
     * @title 服务器分组页面
     * @desc 服务器分组页面
     * @url /admin/v1/idcsmart_common/server_group/:id
     * @method  GET
     * @author wyh
     * @version v1
     * @param int id - 服务器分组ID required
     * @return object server_group - 服务器分组
     * @return int server_group.id - 服务器分组ID
     * @return string server_group.name - 服务器分组名称
     * @return array servers - 服务器
     * @return int servers[].id - 服务器ID
     * @return string servers[].name - 服务器名称
     * @return string servers[].type - 服务器类型
     * @return int servers[].gid - 服务器分组ID
     * @return array select_servers - 当前分组已选择的服务器ID
     */
    public function index(){
        $param = $this->request->param();

        $IdcsmartCommonServerGroupModel = new IdcsmartCommonServerGroupModel();

        $result = $IdcsmartCommonServerGroupModel->indexServerGroup($param);

        return json($result);
    }

    /**
     * 时间 2023-6-8
     * @title 更新服务器分组
     * @desc 更新服务器分组
     * @url /admin/v1/idcsmart_common/server_group/:id
     * @method  put
     * @author wyh
     * @version v1
     * @param string name - 服务器分组名称 required
     * @param string mode - 分配方式(1:平均分配;2:满一个算一个(这两个分配方式写死)) required
     * @param array server_ids - 选择的服务器ID,数组
     */
    public function update(){
        $param = $this->request->param();

        $IdcsmartCommonServerGroupValidate = new IdcsmartCommonServerGroupValidate();
        if (!$IdcsmartCommonServerGroupValidate->scene('update')->check($param)){
            return json(['status'=>400,'msg'=>$IdcsmartCommonServerGroupValidate->getError()]);
        }

        $IdcsmartCommonServerGroupModel = new IdcsmartCommonServerGroupModel();

        $result = $IdcsmartCommonServerGroupModel->updateServerGroup($param);

        return json($result);
    }

    /**
     * 时间 2023-6-8
     * @title 删除服务器分组
     * @desc 删除服务器分组
     * @url /admin/v1/idcsmart_common/server_group/:id
     * @method  delete
     * @author wyh
     * @version v1
     * @param int id - 服务器分组ID required
     */
    public function delete(){
        $param = $this->request->param();

        $IdcsmartCommonServerGroupModel = new IdcsmartCommonServerGroupModel();

        $result = $IdcsmartCommonServerGroupModel->deleteServerGroup($param);

        return json($result);
    }
}