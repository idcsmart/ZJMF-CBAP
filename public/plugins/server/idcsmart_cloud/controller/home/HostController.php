<?php
namespace server\idcsmart_cloud\controller\home;

use server\idcsmart_cloud\model\HostLinkModel;

/**
 * @title 产品管理
 * @desc 产品管理
 * @use server\idcsmart_cloud\controller\home\HostController
 */
class HostController{

	/**
	* 时间 2022-06-24
	* @title 产品列表
	* @desc 产品列表
	* @url /console/v1/idcsmart_cloud
	* @method  GET
	* @author hh
	* @version v1
     * @param   int page 1 页数
     * @param   int limit - 每页条数
     * @param   string orderby - 排序(id,due_time,status)
     * @param   string sort - 升/降序
     * @param   string keywords - 关键字搜索,搜索套餐名称/主机名/IP
     * @param   int data_center_id - 数据中心搜索
     * @return  array data.list - 列表数据
     * @return  int data.list[].id - 列表数据
     * @return  string data.list[].name - 产品标识
     * @return  string data.list[].status - 产品状态(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除,Failed=开通失败)
     * @return  int data.list[].due_time - 到期时间
     * @return  string data.list[].country - 国家
     * @return  string data.list[].country_code - 国家代码
     * @return  string data.list[].city - 城市
     * @return  string data.list[].area - 区域
     * @return  string data.list[].package_name - 套餐名称
     * @return  string data.list[].ip - IP
     * @return  string data.list[].image_name - 镜像名称
     * @return  string data.list[].icon - 镜像图标
     * @return  string data.list[].power_status - 电源状态(on=开机,off=关机,operating=操作中,fault=故障)
	 */
	public function list(){
		$param = request()->param();

		$HostLinkModel = new HostLinkModel();
		$result = $HostLinkModel->idcsmartCloudList($param);

		return json($result);
	}

    /**
     * 时间 2022-06-24
     * @title 获取所有产品
     * @desc 获取所有产品
     * @url /console/v1/idcsmart_cloud/all
     * @method  GET
     * @author hh
     * @version v1
     * @return  array data.list - 列表数据
     * @return  int data.list[].id - 列表数据
     * @return  string data.list[].name - 产品标识
      */
     public function getAll(){

          $HostLinkModel = new HostLinkModel();
          $result = $HostLinkModel->getAllIdcsmartCloud();

          return json($result);
     }

}

