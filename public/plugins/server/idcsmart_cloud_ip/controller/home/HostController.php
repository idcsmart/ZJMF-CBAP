<?php
namespace server\idcsmart_cloud_ip\controller\home;

use server\idcsmart_cloud_ip\model\HostLinkModel;

/**
 * @title 魔方云IP产品管理
 * @desc 魔方云IP产品管理
 * @use server\idcsmart_cloud_ip\controller\home\HostController
 */
class HostController{

	/**
	 * 时间 2022-06-28
	 * @title 产品列表
	 * @desc 产品列表
	 * @url /console/v1/idcsmart_cloud_ip
	 * @method  GET
	 * @author thewold
	 * @version v1
     * @param int page 1 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序(id,status)
     * @param string sort - 升/降序
     * @param string keywords - 关键字搜索
     * @param int data_center_id - 数据中心搜索
     * @return array list - 列表数据
     * @return int list[].id - 产品ID
     * @return string list[].name - 产品标识
     * @return string list[].status - 产品状态(Unpaid=未付款,Pending=开通中,Active=已开通,Suspended=已暂停,Deleted=已删除,Failed=开通失败)
     * @return int list[].ip - IP
     * @return int list[].bw_size - 带宽大小
     * @return string list[].host_id - 实例ID
     * @return string list[].cloud_name - 实例
     * @return string list[].first_payment_amount - 付款金额
     * @return string list[].billing_cycle_name - 周期
     * @return int count - 总数
	 */
	public function list()
	{
		$param = request()->param();

		$HostLinkModel = new HostLinkModel();

		$data = $HostLinkModel->idcsmartCloudIpList($param);

		$result = [
            'status' => 200,
            'msg'    => lang_plugins('success_message'),
            'data'   => $data
        ];
		return json($result);
	}

	/**
     * 时间 2022-06-28
     * @title 挂载IP
     * @desc 挂载IP
     * @url /console/v1/idcsmart_cloud_ip/:id/mount
     * @method  PUT
     * @author thewold
     * @version v1
     * @param int id - IP ID required
     * @param int host_id - 实例ID required
     */
	public function mount()
	{
		$param = request()->param();

		$HostLinkModel = new HostLinkModel();

		$result = $HostLinkModel->mountIdcsmartCloudIp($param);

		return json($result);
	}

	/**
     * 时间 2022-06-28
     * @title 卸载IP
     * @desc 卸载IP
     * @url /console/v1/idcsmart_cloud_ip/:id/umount
     * @method  PUT
     * @author thewold
     * @version v1
     * @param int id - IP ID required
     */
	public function umount()
	{
		$param = request()->param();

		$HostLinkModel = new HostLinkModel();

		$result = $HostLinkModel->umountIdcsmartCloudIp($param);

		return json($result);
	}

}

