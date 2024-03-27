<?php
namespace server\mf_cloud\controller\admin;

use server\mf_cloud\model\DurationModel;
use server\mf_cloud\validate\DurationValidate;
use server\mf_cloud\model\DurationRatioModel;
use app\common\validate\ProductDurationRatioValidate;

/**
 * @title 魔方云(自定义配置)-周期
 * @desc 魔方云(自定义配置)-周期
 * @use server\mf_cloud\controller\admin\DurationController
 */
class DurationController
{
	/**
	 * 时间 2023-01-31
	 * @title 添加周期
	 * @desc 添加周期
	 * @url /admin/v1/mf_cloud/duration
	 * @method  POST
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @param   string name - 周期名称 require
     * @param   int num - 周期时长 require
     * @param   string unit - 单位(hour=小时,day=天,month=月) require
     * @param   float price_factor 1 价格系数
     * @param   float price 0 周期价格
     * @return  int id - 添加成功的周期ID
	 */
	public function create()
	{
		$param = request()->param();

		$DurationValidate = new DurationValidate();
		if (!$DurationValidate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($DurationValidate->getError())]);
        }
		$DurationModel = new DurationModel();

		$result = $DurationModel->durationCreate($param);
		return json($result);
	}

	/**
	 * 时间 2023-01-31
	 * @title 周期列表
	 * @desc 周期列表
	 * @url /admin/v1/mf_cloud/duration
	 * @method  GET
	 * @author hh
	 * @version v1
     * @param   int page - 页数
     * @param   int limit - 每页条数
     * @param   string orderby - 排序字段(id,num)
     * @param   string sort - 升降序(asc,desc)
     * @param   int product_id - 商品ID
     * @return  int list[].id - 周期ID
     * @return  string list[].name - 周期名称
     * @return  int list[].num - 周期时长
     * @return  string list[].unit - 单位(hour=小时,day=天,month=月)
     * @return  float list[].price_factor - 价格系数
     * @return  string list[].price - 周期价格
     * @return  string list[].ratio - 周期比例
     * @return  int count - 总条数
	 */
	public function list()
	{
		$param = request()->param();

		$DurationModel = new DurationModel();

		$result = $DurationModel->durationList($param);
		return json($result);
	}

	/**
	 * 时间 2023-01-31
	 * @title 修改周期
	 * @desc 修改周期
	 * @url /admin/v1/mf_cloud/duration/:id
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int id - 周期ID require
     * @param   string name - 周期名称 require
     * @param   int num - 周期时长 require
     * @param   string unit - 单位(hour=小时,day=天,month=月) require
     * @param   float price_factor - 价格系数
     * @param   float price - 周期价格
	 */
	public function update()
	{
		$param = request()->param();

		$DurationValidate = new DurationValidate();
		if (!$DurationValidate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang_plugins($DurationValidate->getError())]);
        }        
		$DurationModel = new DurationModel();

		$result = $DurationModel->durationUpdate($param);
		return json($result);
	}

	/**
	 * 时间 2023-01-31
	 * @title 删除周期
	 * @desc 删除周期
	 * @url /admin/v1/mf_cloud/duration/:id
	 * @method  DELETE
	 * @author hh
	 * @version v1
	 * @param   int id - 周期ID require
	 */
	public function delete()
	{
		$param = request()->param();

		$DurationModel = new DurationModel();

		$result = $DurationModel->durationDelete($param);
		return json($result);
	}

	/**
	 * 时间 2023-10-20
	 * @title 获取周期比例
	 * @desc 获取周期比例
	 * @url /admin/v1/mf_cloud/duration_ratio
	 * @method  GET
	 * @author hh
	 * @version v1
	 * @param   int product_id - 商品ID require
	 * @return  int list[].id - 周期ID
	 * @return  string list[].name - 周期名称
	 * @return  int list[].num - 周期时长
	 * @return  float list[].price_factor - 价格系数
	 * @return  string list[].unit - 单位(hour=小时,day=天,month=月)
	 * @return  string list[].ratio - 比例
	 */
	public function indexDurationRatio()
	{
		$param = request()->param();

		$DurationRatioModel = new DurationRatioModel();

		$data = $DurationRatioModel->indexRatio($param['product_id'] ?? 0);

		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => [
				'list' => $data,
			],
		];
		return json($result);
	}

	/**
	 * 时间 2023-10-20
	 * @title 保存周期比例
	 * @desc 保存周期比例
	 * @url /admin/v1/mf_cloud/duration_ratio
	 * @method  PUT
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @param   object ratio - 比例(如{"2":"1.5"},键是周期ID,值是比例) require
	 */
	public function saveDurationRatio()
	{
		$param = request()->param();

		$ProductDurationRatioValidate = new ProductDurationRatioValidate();
		if (!$ProductDurationRatioValidate->scene('save')->check($param)){
            return json(['status' => 400 , 'msg' => lang($ProductDurationRatioValidate->getError())]);
        }        
		$DurationRatioModel = new DurationRatioModel();

		$result = $DurationRatioModel->saveRatio($param);
		return json($result);
	}

	/**
	 * 时间 2023-10-20
	 * @title 周期比例填充
	 * @desc 周期比例填充
	 * @url /admin/v1/mf_cloud/duration_ratio/fill
	 * @method  POST
	 * @author hh
	 * @version v1
     * @param   int product_id - 商品ID require
     * @param   object price - 价格(如{"2":"1.5"},键是周期ID,值是价格) require
     * @return  object list - 周期价格(如{"2":"1.5"},键是周期ID,值是价格)
	 */
	public function fillDurationRatio()
	{
		$param = request()->param();

		$ProductDurationRatioValidate = new ProductDurationRatioValidate();
		if (!$ProductDurationRatioValidate->scene('fill')->check($param)){
            return json(['status' => 400 , 'msg' => lang($ProductDurationRatioValidate->getError())]);
        }        
		$DurationRatioModel = new DurationRatioModel();

		$result = $DurationRatioModel->autoFill($param);
		return json($result);
	}



}