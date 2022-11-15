<?php 
namespace server\idcsmart_common;

use app\admin\model\PluginModel;
use server\idcsmart_common\logic\IdcsmartCommonLogic;
use server\idcsmart_common\validate\IdcsmartCommonProductValidate;
use think\facade\Db;

/**
 * 通用产品模块系统方法
 */
class IdcsmartCommon
{

	/**
	 * 时间 2022-09-25
	 * @title 基础信息
	 * @author wyh
	 * @version v1
	 */
	public function metaData()
    {
		return ['display_name'=>'通用产品', 'version'=>'1.0'];
	}

	/**
	 * 时间 2022-09-25
	 * @title 添加表TODO 
	 * @author wyh
	 * @version v1
	 */
	public function afterCreateFirstServer()
    {
	    $sql = [
	        "CREATE TABLE `idcsmart_module_idcsmart_common_custom_cycle` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品自定义周期',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联商品ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '周期名称',
  `cycle_time` int(11) NOT NULL DEFAULT '0' COMMENT '周期时长',
  `cycle_unit` enum('hour','day','month') NOT NULL DEFAULT 'day' COMMENT '周期单位:hour小时,day天,month月',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;",
            "CREATE TABLE `idcsmart_module_idcsmart_common_custom_cycle_pricing` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自定义周期价格设置',
  `custom_cycle_id` int(11) NOT NULL DEFAULT '0' COMMENT '自定义周期ID',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联ID:如商品ID,配置项ID',
  `type` enum('product','configoption') NOT NULL DEFAULT 'product' COMMENT '价格类型:product商品,configoption配置项',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '自定义周期金额:-1表示前台不显示此周期,>=0时表示此周期金额,默认为-1',
  PRIMARY KEY (`id`),
  KEY `type_rel_id` (`rel_id`,`type`),
  KEY `custom_cycle_id` (`custom_cycle_id`),
  KEY `ctr` (`custom_cycle_id`,`rel_id`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;",
            "CREATE TABLE `idcsmart_module_idcsmart_common_host_configoption` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '产品关联配置表',
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `configoption_id` int(11) NOT NULL DEFAULT '0' COMMENT '配置项ID',
  `configoption_sub_id` int(11) NOT NULL DEFAULT '0' COMMENT '子项ID',
  `qty` int(11) NOT NULL DEFAULT '0' COMMENT '配置项为数量类型时的数量',
  `repeat` int(11) NOT NULL DEFAULT '0' COMMENT '重复',
  PRIMARY KEY (`id`),
  KEY `host_id` (`host_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;",
            "CREATE TABLE `idcsmart_module_idcsmart_common_pricing` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('configoption','product') NOT NULL DEFAULT 'product' COMMENT '对应类型:product商品(默认),configoption配置项',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联ID:如商品ID',
  `onetime` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '一次性金额',
  PRIMARY KEY (`id`),
  KEY `type_rel_id` (`type`,`rel_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;",
            "CREATE TABLE `idcsmart_module_idcsmart_common_product` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `order_page_description` text COMMENT '订购页面描述,支持html',
  `allow_qty` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否允许选择数量:1是，0否默认',
  `auto_support` tinyint(1) NOT NULL DEFAULT '0' COMMENT '自动化支持:开启后所有配置选项都可输入参数',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `type` varchar(25) NOT NULL DEFAULT 'server' COMMENT '接口类型：server服务器，server_group服务器组(插件下的服务器和服务器组以及相应的模块)',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联ID:服务器ID,服务器组ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;",
            "CREATE TABLE `idcsmart_module_idcsmart_common_product_configoption` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '配置项表',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `option_name` varchar(255) NOT NULL DEFAULT '' COMMENT '配置项名称',
  `option_type` varchar(255) NOT NULL DEFAULT 'select' COMMENT '配置项类型：select下拉单选，multi_select下拉多选，radio点击单选，quantity数量输入，quantity_range数量拖动，yes_no是否，area区域',
  `option_param` varchar(255) NOT NULL DEFAULT '' COMMENT '参数:请求接口',
  `qty_min` int(11) NOT NULL DEFAULT '0' COMMENT '最小值',
  `qty_max` int(11) NOT NULL DEFAULT '0' COMMENT '最大值',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `hidden` int(11) NOT NULL DEFAULT '0' COMMENT '是否隐藏:1是，0否',
  `unit` varchar(255) NOT NULL DEFAULT '' COMMENT '单位',
  `allow_repeat` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否允许重复:开启后,前台购买时，可通过点击添加按钮，自动创建一个新的配置项，取名如bw1',
  `max_repeat` int(11) NOT NULL DEFAULT '5' COMMENT '最大允许重复数量',
  `fee_type` varchar(25) NOT NULL DEFAULT 'stage' COMMENT '数量的类型的计费方式：stage阶梯计费，qty数量计费(当前区间价格*数量)',
  `description` text COMMENT '说明',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;",
            "CREATE TABLE `idcsmart_module_idcsmart_common_product_configoption_sub` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '配置子项表',
  `product_configoption_id` int(11) NOT NULL DEFAULT '0' COMMENT '配置项ID',
  `option_name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `option_param` varchar(255) NOT NULL DEFAULT '' COMMENT '参数',
  `qty_min` int(11) NOT NULL DEFAULT '0' COMMENT '最小值',
  `qty_max` int(11) NOT NULL DEFAULT '0' COMMENT '最大值',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `hidden` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否隐藏:1是，0否默认',
  `country` varchar(255) NOT NULL DEFAULT '' COMMENT '国家:类型为区域时选择',
  PRIMARY KEY (`id`),
  KEY `product_configoption_id` (`product_configoption_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;",
            "CREATE TABLE `idcsmart_module_idcsmart_common_product_custom_field` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自定义字段表',
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '字段名称',
  `type` varchar(25) NOT NULL DEFAULT 'text' COMMENT '字段类型:text单行文本，areatext多行文本，select下拉单选，multi_select下拉多选，check_box勾选框',
  `description` varchar(2000) NOT NULL DEFAULT '' COMMENT '描述',
  `options` text COMMENT '选项:多选，下拉的',
  `regexpr` text COMMENT '正则匹配，验证规则',
  `require` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否必须:1是，0否默认',
  `admin_only` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否仅管理员可见:1是，0否默认',
  `param` text COMMENT '参数，json格式',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
            "CREATE TABLE `idcsmart_module_idcsmart_common_product_custom_field_value` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `field_id` int(11) NOT NULL DEFAULT '0' COMMENT '自定义字段ID',
  `value` text COMMENT '值',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
        ];
		foreach($sql as $v){
			Db::execute($v);
		}
	}

	/**
	 * 时间 2022-09-25
	 * @title 不用之后删除表TODO
	 * @author wyh
	 * @version v1
	 */
	public function afterDeleteLastServer()
    {
		$sql = [
            "DROP TABLE IF EXISTS `idcsmart_module_idcsmart_common_custom_cycle`;",
            "DROP TABLE IF EXISTS `idcsmart_module_idcsmart_common_custom_cycle_pricing`;",
            "DROP TABLE IF EXISTS `idcsmart_module_idcsmart_common_host_configoption`;",
            "DROP TABLE IF EXISTS `idcsmart_module_idcsmart_common_pricing`;",
            "DROP TABLE IF EXISTS `idcsmart_module_idcsmart_common_product`;",
            "DROP TABLE IF EXISTS `idcsmart_module_idcsmart_common_product_configoption`;",
            "DROP TABLE IF EXISTS `idcsmart_module_idcsmart_common_product_configoption_sub`;",
            "DROP TABLE IF EXISTS `idcsmart_module_idcsmart_common_product_custom_field`;",
            "DROP TABLE IF EXISTS `idcsmart_module_idcsmart_common_product_custom_field_value`;",
        ];

		foreach($sql as $v){
			Db::execute($v);
		}
	}

	/**
	 * 时间 2022-09-25
	 * @title 测试连接
	 * @author wyh
	 * @version v1
	 */
	public function testConnect($params){
		return ['status'=>200,'msg'=>lang_plugins('link_success')];
	}

	/**
	 * 时间 2022-09-25
	 * @title 模块开通
	 * @author wyh
	 * @version v1
	 */
	public function createAccount($params){
        # 配置中关联子模块,调子模块中开通,使用以前的逻辑
    }

	/**
	 * 时间 2022-09-25
	 * @title 模块暂停
	 * @author wyh
	 * @version v1
	 */
	public function suspendAccount($params){

    }

	/**
	 * 时间 2022-09-25
	 * @title 模块解除暂停
	 * @author wyh
	 * @version v1
	 */
	public function unsuspendAccount($params){

	}

	/**
	 * 时间 2022-09-25
	 * @title 模块删除
	 * @author wyh
	 * @version v1
	 */
	public function terminateAccount($params){

    }

	/**
	 * 时间 2022-09-25
	 * @title 续费后调用
	 * @author wyh
	 * @version v1
	 */
	public function renew($params){

	}

	/**
	 * 时间 2022-09-25
	 * @title 升降级后调用
	 * @author wyh
	 * @version v1
	 */
	public function changePackage($params){

    }

	/**
	 * 时间 2022-09-25
	 * @title 变更商品后调用
	 * @author wyh
	 * @version v1
	 */
	public function changeProduct($params)
    {
		$params['host_id'] = $params['host']['id'];

		$this->afterSettle($params);
	}

	/**
	 * 时间 2022-09-27
	 * @title 价格计算
	 * @author wyh
	 * @version v1
	 * @param   ProductModel params.product - 商品模型
	 * @param   array params.custom - 自定义参数
	 * @param   int params.custom.data_center_id - 数据中心ID require
	 * @param   int params.custom.package_id - 套餐ID require
	 * @param   int params.custom.image_id - 镜像ID
	 * @param   string params.custom.hostname - 主机名
	 * @param   string params.custom.password - 密码
	 * @param   int params.custom.backup_enable 0 是否启用备份(0=不启用,1=启用)
	 * @param   int params.custom.panel_enable 0 是否启用面板密码(0=不启用,1=启用)
	 * @param   int params.custom.duration_price_id - 周期价格ID require
	 * @return  [type]         [description]
	 */
	public function cartCalculatePrice($params)
    {
		$IdcsmartCommonProductValidate = new IdcsmartCommonProductValidate();

		$params['custom']['product_id'] = $params['product']['id'];

		$params['custom']['qty'] = $params['qty'];

		if(!$IdcsmartCommonProductValidate->scene('cart_calculate')->check($params['custom'])){
            return ['status'=>400 , 'msg'=>lang_plugins($IdcsmartCommonProductValidate->getError())];
        }

		$IdcsmartCommonLogic = new IdcsmartCommonLogic();

		$res = $IdcsmartCommonLogic->cartCalculatePrice($params['custom']);

		return $res;
	}

	/**
	 * 时间 2022-09-25
	 * @title 切换商品后的输出
	 * @author wyh
	 * @version v1
	 */
	public function serverConfigOption()
    {
		$res = [
			'template'=>'template/admin/common_config.html',
		];

		return $res;
	}

	/**
	 * 时间 2022-09-27
	 * @title 前台产品内页输出,TODO
	 * @author wyh
	 * @version v1
	 */
	public function clientArea($params)
    {
    	$res = [
			'template'=>'template/clientarea/product_detail.php',
		];

		return $res;
	}

	/**
	 * 时间 2022-10-13
	 * @title 产品列表
	 * @author hh
	 * @version v1
	 * @param   [type] $params [description]
	 * @return  [type]         [description]
	 */
	public function hostList($params){
		$res = [
			'template'=>'template/clientarea/product_list.php',
		];

		return $res;
	}

	/**
	 * 时间 2022-10-13
	 * @title 前台购买
	 * @author hh
	 * @version v1
	 * @param   string x       -             x
	 * @param   [type] $params [description]
	 * @return  [type]         [description]
	 */
	public function clientProductConfigOption($params){
        $PluginModel = new PluginModel();
        $addons = $PluginModel->plugins('addon');
		$res = [
		    'vars' => [
		        'template_catalog' => 'clientarea',
                'themes' => configuration('clientarea_theme'),
                'addons' => $addons['list']
            ],
			'template'=>'template/clientarea/goods.php',
		];

		return $res;
	}


	/**
	 * 时间 2022-09-27
	 * @title 后台产品内页输出,TODO
	 * @author wyh
	 * @version v1
	 */
	public function adminArea()
    {
		return '';
	}

	/**
	 * 时间 2022-09-27
	 * @title 结算后调用,保存下单的配置项
	 * @author wyh
	 * @version v1
	 */
	public function afterSettle($params)
    {
        $IdcsmartCommonLogic = new IdcsmartCommonLogic();

        $res = $IdcsmartCommonLogic->afterSettle($params);

        return $res;
    }

	/**
	 * 时间 2022-09-27
	 * @title 获取产品所有周期价格
	 * @desc 获取产品所有周期价格
	 * @author wyh
	 * @version v1
     * @param int host_id - 产品ID
     * @return array [
     * [
    'duration' => 134,
    'price' => 1,
    'billing_cycle' => 2
    ],
     * ]
	 */
	public function durationPrice($params)
    {
        $IdcsmartCommonLogic = new IdcsmartCommonLogic();

		$result = $IdcsmartCommonLogic->currentDurationPrice($params['host']['id']);

		return $result;
	}

    /**
     * 时间 2022-09-27
     * @title 获取商品所有配置
     * @desc 获取商品所有配置
     * @author wyh
     * @version v1
     * @param int product_id - 商品ID
     * @return  array [
    'name' => $configoption['option_name'],
    'field' => "configoption[{$configoption['id']}]",
    'type' => 'dropdown',
    'option' => $subArr
    ]
     */
	public function allConfigOption($params)
    {

        $IdcsmartCommonLogic = new IdcsmartCommonLogic();

        $result = $IdcsmartCommonLogic->allConfigOption($params['product']['id']);

        return $result;
	}

    /**
     * 时间 2022-09-27
     * @title 获取产品当前配置
     * @desc 获取产品当前配置
     * @author wyh
     * @version v1
     * @param int host_id - 产品ID
     * @return  array ["配置项ID":"子项ID"]
     */
	public function currentConfigOptioin($params)
    {
        $IdcsmartCommonLogic = new IdcsmartCommonLogic();

        $result = $IdcsmartCommonLogic->currentConfigOptioin($params['host']['id']);

        return $result;
	}

}


