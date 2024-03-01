<?php 
namespace server\mf_dcim;

use server\mf_dcim\idcsmart_dcim\Dcim;
use server\mf_dcim\model\HostLinkModel;
use server\mf_dcim\model\DurationModel;
use server\mf_dcim\model\ConfigLimitModel;
use server\mf_dcim\validate\CartValidate;
use server\mf_dcim\validate\HostUpdateValidate;
use think\facade\Db;
use server\mf_dcim\logic\ToolLogic;

/**
 * DCIM模块
 */
class MfDcim
{
	/**
	 * 时间 2022-06-28
	 * @title 基础信息
	 * @author hh
	 * @version v1
     * @return  string display_name - 模块名称
     * @return  string version - 版本号
	 */
	public function metaData()
    {
		return ['display_name'=>'DCIM(自定义配置)', 'version'=>'2.0.0'];
	}

	/**
	 * 时间 2022-06-28
	 * @title 添加表
	 * @author hh
	 * @version v1
	 */
	public function afterCreateFirstServer()
    {
		$sql = [
			"CREATE TABLE `idcsmart_module_mf_dcim_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `rand_ssh_port` tinyint(3) NOT NULL DEFAULT '0' COMMENT '随机SSH端口(0=关闭,1=开启)',
  `reinstall_sms_verify` tinyint(3) NOT NULL DEFAULT '0' COMMENT '重装短信验证(0=关闭,1=开启)',
  `reset_password_sms_verify` tinyint(3) NOT NULL DEFAULT '0' COMMENT '重置密码短信验证(0=关闭,1=开启)',
  `manual_resource` tinyint(3) NOT NULL DEFAULT '0' COMMENT '手动资源(0=关闭,1=开启)',
  `level_discount_memory_order` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '内存是否应用等级优惠订购(0=关闭,1=开启)',
  `level_discount_memory_upgrade` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '内存是否应用等级优惠升降级(0=关闭,1=开启)',
  `level_discount_disk_order` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '硬盘是否应用等级优惠订购(0=关闭,1=开启)',
  `level_discount_disk_upgrade` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '硬盘是否应用等级优惠升降级(0=关闭,1=开启)',
  `level_discount_bw_upgrade` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '带宽是否应用等级优惠升降级(0=关闭,1=开启)',
  `level_discount_ip_num_upgrade` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'IP是否应用等级优惠升降级(0=关闭,1=开启)',
  `optional_host_auto_create` tinyint(1) NOT NULL DEFAULT '0' COMMENT '选配机器是否自动开通(0=关闭,1=开启)',
  `level_discount_gpu_order` tinyint(1) NOT NULL DEFAULT '1' COMMENT '显卡是否应用等级优惠订购(0=关闭,1=开启)',
  `level_discount_gpu_upgrade` tinyint(1) NOT NULL DEFAULT '1' COMMENT '显卡是否应用等级优惠升降级(0=关闭,1=开启)',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
			"CREATE TABLE `idcsmart_module_mf_dcim_config_limit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `data_center_id` int(11) NOT NULL DEFAULT '0' COMMENT '数据中心ID',
  `line_id` int(11) NOT NULL DEFAULT '0' COMMENT '线路ID',
  `model_config_id` text NOT NULL COMMENT '配置型号ID',
  `min_bw` varchar(20) NOT NULL DEFAULT '' COMMENT '最小带宽',
  `max_bw` varchar(20) NOT NULL DEFAULT '' COMMENT '最大带宽',
  `min_flow` varchar(20) NOT NULL DEFAULT '' COMMENT '流量',
  `max_flow` varchar(20) NOT NULL DEFAULT '',
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`) USING BTREE,
  KEY `data_center_id` (`data_center_id`),
  KEY `line_id` (`line_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='配置限制表';",
			"CREATE TABLE `idcsmart_module_mf_dcim_data_center` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `country_id` int(11) NOT NULL DEFAULT '0' COMMENT '国家ID',
  `city` varchar(255) NOT NULL DEFAULT '' COMMENT '城市',
  `area` varchar(255) NOT NULL DEFAULT '' COMMENT '区域',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `country_id` (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='数据中心表';",
			"CREATE TABLE `idcsmart_module_mf_dcim_duration` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '周期名称',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '周期时常',
  `unit` varchar(20) NOT NULL DEFAULT '' COMMENT '周期单位(hour=小时,day=天,month=自然月)',
  `price_factor` float(4,2) NOT NULL DEFAULT '1.00' COMMENT '价格系数',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '周期价格',
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='周期表';",
			"CREATE TABLE `idcsmart_module_mf_dcim_host_image_link` (
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `image_id` int(11) NOT NULL DEFAULT '0' COMMENT '镜像ID',
  KEY `host_id` (`host_id`),
  KEY `image_id` (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
			"CREATE TABLE `idcsmart_module_mf_dcim_host_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '魔方云实例ID',
  `data_center_id` int(11) NOT NULL DEFAULT '0' COMMENT '数据中心ID',
  `image_id` int(11) NOT NULL DEFAULT '0' COMMENT '镜像ID',
  `power_status` varchar(30) NOT NULL DEFAULT '' COMMENT '电源状态',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `config_data` text NOT NULL COMMENT '用于缓存购买时的配置价格,用于升降级',
  `password` varchar(255) NOT NULL DEFAULT '',
  `package_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '灵活机型ID',
  `additional_ip` text NOT NULL COMMENT '附加IP',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `host_id` (`host_id`),
  KEY `data_center_id` (`data_center_id`),
  KEY `image_id` (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='产品实例关联表';",
			"CREATE TABLE `idcsmart_module_mf_dcim_image` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `image_group_id` int(11) NOT NULL DEFAULT '0' COMMENT '镜像分组ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `charge` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否收费(0=不收费,1=收费)',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否可用(0=禁用,1=可用)',
  `rel_image_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联DCIM镜像ID',
  PRIMARY KEY (`id`),
  KEY `image_group_id` (`image_group_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='镜像表';",
			"CREATE TABLE `idcsmart_module_mf_dcim_image_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `icon` varchar(50) NOT NULL DEFAULT '' COMMENT '图标',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='镜像分组';",
			"CREATE TABLE `idcsmart_module_mf_dcim_line` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `data_center_id` int(11) NOT NULL DEFAULT '0' COMMENT '数据中心ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '线路名称',
  `bill_type` varchar(20) NOT NULL DEFAULT '' COMMENT 'bw=带宽计费,flow=流量计费',
  `bw_ip_group` varchar(10) NOT NULL DEFAULT '' COMMENT '带宽IP分组',
  `defence_enable` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否启用防护',
  `defence_ip_group` varchar(10) NOT NULL DEFAULT '' COMMENT '防护IP分组',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `data_center_id` (`data_center_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='线路表';",
			"CREATE TABLE `idcsmart_module_mf_dcim_model_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `group_id` int(11) NOT NULL DEFAULT '0' COMMENT 'DCIM分组ID',
  `cpu` varchar(255) NOT NULL DEFAULT '' COMMENT '处理器',
  `cpu_param` varchar(255) NOT NULL DEFAULT '' COMMENT '处理器参数',
  `memory` varchar(255) NOT NULL DEFAULT '' COMMENT '内存',
  `disk` varchar(255) NOT NULL DEFAULT '' COMMENT '硬盘',
  `gpu` varchar(255) NOT NULL DEFAULT '' COMMENT '显卡',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `hidden` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否隐藏(0=隐藏,1=显示)',
  `support_optional` tinyint(1) NOT NULL DEFAULT '0' COMMENT '支持选配(0=不支持,1=支持)',
  `optional_only_for_upgrade` tinyint(1) NOT NULL DEFAULT '0' COMMENT '增值仅用于升降级(0=关闭,1=开启)',
  `leave_memory` int(11) NOT NULL DEFAULT '0' COMMENT '剩余容量',
  `max_memory_num` int(11) NOT NULL DEFAULT '0' COMMENT '可增加内存条数',
  `max_disk_num` int(11) NOT NULL DEFAULT '0' COMMENT '可增加硬盘数量',
  `max_gpu_num` int(11) NOT NULL DEFAULT '0' COMMENT '可增加显卡数量',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='型号配置';",
			"CREATE TABLE `idcsmart_module_mf_dcim_option` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `rel_type` tinyint(7) NOT NULL DEFAULT '0' COMMENT '2=线路带宽计费\r\n3=线路流量计费\r\n4=线路防护配置\r\n5=线路附加IP配置\r\n6=处理器\r\n7=内存\r\n8=硬盘\r\n9=显卡',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联ID(rel_type=2,3,4,5关联线路ID)',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '计费方式(radio=单选，step=阶梯,total=总量)',
  `value` varchar(255) NOT NULL DEFAULT '0' COMMENT '单选值',
  `min_value` int(10) NOT NULL DEFAULT '0' COMMENT '最小值',
  `max_value` int(10) NOT NULL DEFAULT '0' COMMENT '最大值',
  `step` int(10) NOT NULL DEFAULT '1' COMMENT '步长',
  `order` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `other_config` text NOT NULL COMMENT '其他配置,json存储',
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `prr` (`product_id`,`rel_type`,`rel_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='通用配置表';",
			"CREATE TABLE `idcsmart_module_mf_dcim_price` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `rel_type` varchar(30) NOT NULL DEFAULT '' COMMENT '表类型(model_config=型号配置,option=通用配置)',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联ID',
  `duration_id` int(11) NOT NULL DEFAULT '0' COMMENT '周期ID',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `rr` (`rel_type`,`rel_id`),
  KEY `duration_id` (`duration_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='配置价格表';",
			"CREATE TABLE `idcsmart_module_mf_dcim_package` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `group_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'DCIM分组ID',
  `cpu_option_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '处理器关联optionID',
  `cpu_num` int(11) unsigned NOT NULL DEFAULT '1' COMMENT 'CPU数量',
  `mem_option_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '内存关联optionID',
  `mem_num` tinyint(7) unsigned NOT NULL DEFAULT '1' COMMENT '内存数量',
  `disk_option_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '硬盘关联optionID',
  `disk_num` tinyint(7) unsigned NOT NULL DEFAULT '1' COMMENT '硬盘数量',
  `bw` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '带宽',
  `ip_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'IP数量',
  `description` varchar(1000) NOT NULL DEFAULT '' COMMENT '描述',
  `mem_max` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '内存最大容量',
  `mem_max_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '内存最大数量',
  `disk_max_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '硬盘最大数量',
  `order` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `hidden` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏(0=否,1=是)',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`) USING BTREE,
  KEY `idx_cpu_option_id` (`cpu_option_id`) USING BTREE,
  KEY `idx_mem_option_id` (`mem_option_id`) USING BTREE,
  KEY `idx_disk_option_id` (`disk_option_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='套餐表(后续废弃)';",
	"CREATE TABLE `idcsmart_module_mf_dcim_package_option_link` (
  `package_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'dcim套餐id',
  `option_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '可选配optionID',
  `option_rel_type` tinyint(7) unsigned NOT NULL DEFAULT '0' COMMENT 'option表的rel_type',
  KEY `idx_package_id` (`package_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='套餐可选配置表';",
	"CREATE TABLE `idcsmart_module_mf_dcim_host_option_link` (
  `host_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID',
  `option_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '配置ID',
  `num` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '数量',
  KEY `idx_host_id` (`host_id`) USING BTREE,
  KEY `idx_option_id` (`host_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='产品配置关联表';",
	"CREATE TABLE `idcsmart_module_mf_dcim_model_config_option_link` (
  `model_config_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'dcim型号配置id',
  `option_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '可选配optionID',
  `option_rel_type` tinyint(7) unsigned NOT NULL DEFAULT '0' COMMENT 'option表的rel_type',
  KEY `idx_model_config_id` (`model_config_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='型号配置可选配置表';",
		];
		foreach($sql as $v){
			Db::execute($v);
		}
	}

	/**
	 * 时间 2022-06-28
	 * @title 不用之后删除表
	 * @author hh
	 * @version v1
	 */
	public function afterDeleteLastServer()
    {
		$sql = [
			'drop table `idcsmart_module_mf_dcim_config`;',
			'drop table `idcsmart_module_mf_dcim_config_limit`;',
			'drop table `idcsmart_module_mf_dcim_data_center`;',
			'drop table `idcsmart_module_mf_dcim_duration`;',
			'drop table `idcsmart_module_mf_dcim_host_image_link`;',
			'drop table `idcsmart_module_mf_dcim_host_link`;',
			'drop table `idcsmart_module_mf_dcim_image`;',
			'drop table `idcsmart_module_mf_dcim_image_group`;',
			'drop table `idcsmart_module_mf_dcim_line`;',
			'drop table `idcsmart_module_mf_dcim_model_config`;',
			'drop table `idcsmart_module_mf_dcim_option`;',
			'drop table `idcsmart_module_mf_dcim_price`;',
			'drop table `idcsmart_module_mf_dcim_package`;',
			'drop table `idcsmart_module_mf_dcim_package_option_link`;',
            'drop table `idcsmart_module_mf_dcim_host_option_link`;',
			'drop table `idcsmart_module_mf_dcim_model_config_option_link`;',
		];
		foreach($sql as $v){
			Db::execute($v);
		}
	}

	/**
	 * 时间 2022-06-22
	 * @title 测试连接
	 * @author hh
	 * @version v1
	 */
	public function testConnect($param)
    {
		$Dcim = new Dcim($param['server']);
		$res = $Dcim->login();
		if($res['status'] == 200){
			unset($res['data']);
			$res['msg'] = lang_plugins('link_success');
		}
		return $res;
	}

	/**
	 * 时间 2023-02-09
	 * @title 模块开通
	 * @author hh
	 * @version v1
	 */
	public function createAccount($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->createAccount($param);
	}

	/**
	 * 时间 2023-02-09
	 * @title 模块暂停
	 * @author hh
	 * @version v1
	 */
	public function suspendAccount($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->suspendAccount($param);
	}

	/**
	 * 时间 2022-06-22
	 * @title 模块解除暂停
	 * @author hh
	 * @version v1
	 */
	public function unsuspendAccount($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->unsuspendAccount($param);
	}

	/**
	 * 时间 2022-06-22
	 * @title 模块删除
	 * @author hh
	 * @version v1
	 */
	public function terminateAccount($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->terminateAccount($param);
	}

	/**
	 * 时间 2022-06-28
	 * @title 续费后调用
	 * @author hh
	 * @version v1
	 */
	public function renew($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->renew($param);
	}

	/**
	 * 时间 2023-02-13
	 * @title 升降级后调用
	 * @author hh
	 * @version v1
	 */
	public function changePackage($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->changePackage($param);
	}

	/**
	 * 时间 2022-06-28
	 * @title 变更商品后调用
	 * @author hh
	 * @version v1
	 */
	public function changeProduct($param)
    {
		$param['host_id'] = $param['host']['id'];
		$this->afterSettle($param);
	}

	/**
	 * 时间 2022-06-21
	 * @title 价格计算
	 * @author hh
	 * @version v1
     * @param   ProductModel param.product - 商品模型实例 require
     * @param   string param.scene buy 场景(cal_price=价格计算,buy=购买) require
     * @param   int param.custom.duration_id - 周期ID require
     * @param   int param.custom.data_center_id - 数据中心ID require
     * @param   int param.custom.line_id - 线路ID require
     * @param   int param.custom.model_config_id - 型号配置ID require
     * @param   array param.custom.optional_memory - 变更后的内存(["5"=>1],5是ID,1是数量)
     * @param   array param.custom.optional_disk - 变更后的硬盘(["5"=>1],5是ID,1是数量)
     * @param   array param.custom.optional_gpu - 变更后的硬盘(["5"=>1],5是ID,1是数量)
     * @param   int param.custom.image_id - 镜像ID require
     * @param   string param.custom.bw - 带宽
     * @param   string param.custom.flow - 流量
     * @param   string param.custom.ip_num - 公网IP数量
     * @param   int param.custom.peak_defence - 防御峰值(G)
     * @param   string param.custom.notes - 备注
     * @param   int param.custom.auto_renew 0 是否自动续费(0=否,1=是)
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     * @return  string data.price - 价格 
     * @return  string data.renew_price - 续费价格 
     * @return  string data.billing_cycle - 周期 
     * @return  int data.duration - 周期时长
     * @return  string data.description - 订单子项描述
     * @return  string data.base_price - 基础价格
     * @return  string data.preview[].name - 配置项名称
     * @return  string data.preview[].value - 配置项值
     * @return  string data.preview[].price - 配置项价格
     * @return  string data.discount - 用户等级折扣
     * @return  string data.order_item[].type - 订单子项类型(addon_idcsmart_client_level=用户等级)
     * @return  int data.order_item[].rel_id - 关联ID
     * @return  float data.order_item[].amount - 子项金额
     * @return  string data.order_item[].description - 子项描述
	 */
	public function cartCalculatePrice($param)
    {
		$CartValidate = new CartValidate();

		// 仅计算价格验证,不需要验证其他参数
		if($param['scene'] == 'cal_price'){
			if(!$CartValidate->scene('CalPrice')->check($param['custom'])){
	            return ['status'=>400 , 'msg'=>lang_plugins($CartValidate->getError())];
	        }
		}else{
			// 下单的验证
			if(!$CartValidate->scene('cal')->check($param['custom'])){
	            return ['status'=>400 , 'msg'=>lang_plugins($CartValidate->getError())];
	        }
        	$ConfigLimitModel = new ConfigLimitModel();
	        $check = $ConfigLimitModel->checkConfigLimit($param['product']['id'], $param['custom']);
	        if($check['status'] != 200){
	        	return $check;
	        }
		}
        $param['custom']['product_id'] = $param['product']['id'];

		$DurationModel = new DurationModel();

		$res = $DurationModel->cartCalculatePrice($param, $param['scene'] == 'cal_price');
		return $res;
	}

	/**
	 * 时间 2022-06-28
	 * @title 切换商品后的输出
	 * @author hh
	 * @version v1
	 */
	public function serverConfigOption($param)
    {
		$res = [
			'template'=>'template/admin/mf_dcim.html',
		];
		return $res;
	}

	/**
	 * 时间 2022-06-29
	 * @title 前台产品内页输出
	 * @author hh
	 * @version v1
	 */
	public function clientArea()
    {
        $res = [
            'template'=>'template/clientarea/product_detail.html',
        ];
		return $res;
	}

	/**
	 * 时间 2022-10-13
	 * @title 产品列表
	 * @author hh
	 * @version v1
	 */
	public function hostList($param)
    {
        $res = [
            'template'=>'template/clientarea/product_list.html',
        ];
		return $res;
	}

	/**
	 * 时间 2022-10-13
	 * @title 前台购买
	 * @author hh
	 * @version v1
	 */
	public function clientProductConfigOption($param)
    {
        $res = [
            'template'=>'template/clientarea/goods.html',
        ];
		return $res;
	}

	/**
	 * 时间 2022-06-22
	 * @title 结算后调用,保存下单的配置项
	 * @author hh
	 * @version v1
     * @param   int param.host_id - 产品ID require
     * @param   int param.custom.duration_id - 周期ID require
     * @param   int param.custom.data_center_id - 数据中心ID require
     * @param   int param.custom.model_config_id - 型号配置ID require
     * @param   array param.custom.optional_memory - 变更后的内存(["5"=>1],5是ID,1是数量)
     * @param   array param.custom.optional_disk - 变更后的硬盘(["5"=>1],5是ID,1是数量)
     * @param   array param.custom.optional_gpu - 变更后的硬盘(["5"=>1],5是ID,1是数量)
     * @param   int param.custom.image_id - 镜像ID require
     * @param   string param.custom.bw - 带宽
     * @param   string param.custom.flow - 流量
     * @param   string param.custom.ip_num - 公网IP数量
     * @param   int param.custom.peak_defence - 防御峰值(G)
     * @param   int param.custom.auto_renew 0 是否自动续费(0=不,1=是)
     * @param   string param.custom.notes - 用户备注
	 */
	public function afterSettle($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->afterSettle($param);
	}

	/**
	 * 时间 2022-06-29
	 * @title 获取当前配置所有周期价格
	 * @desc 获取当前配置所有周期价格
	 * @author hh
	 * @version v1
	 */
	public function durationPrice($param)
    {
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->durationPrice($param);
	}

    /**
     * 时间 2024-02-18
     * @title 获取商品起售周期价格
     * @desc 获取商品起售周期价格
     * @author hh
     * @version v1
     */
	public function getPriceCycle($param)
	{
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->getPriceCycle($param['product']['id']);
	}

	/**
     * 时间 2024-02-18
     * @title 下载上游资源
     * @desc 下载上游资源
     * @author hh
     * @version v1
     */
	public function downloadResource($param)
    {
		$metaData = $this->metaData();
		
		// 尝试解压到本地目录下
        ToolLogic::unzipToReserver();

		$result = [
			'status' => 200,
			'msg'	 => lang_plugins('success_message'),
			'data'	 => [
				'module' => 'mf_dcim',
				'url' => request()->domain() . '/plugins/server/mf_dcim/data/abc.zip' , // 下载路径
				'version' => $metaData['version'] ?? '1.0.0',
			]
		];
		return $result;
	}

    /**
     * 时间 2023-04-12
     * @title 产品内页模块配置信息输出
     * @desc 产品内页模块配置信息输出
     * @author hh
     * @version v1
     */
    public function adminField($param)
    {
        $HostLinkModel = new HostLinkModel();
        return $HostLinkModel->adminField($param);
    }

    /**
     * 时间 2024-02-18
     * @title 产品保存后
     * @desc 产品保存后
     * @author hh
     * @version v1
     * @param  string param.module_admin_field.model_config_name - 型号配置名称
     * @param  string param.module_admin_field.model_config_cpu - 处理器
     * @param  string param.module_admin_field.model_config_cpu_param - 处理器参数
     * @param  string param.module_admin_field.model_config_memory - 内存
     * @param  string param.module_admin_field.model_config_disk - 硬盘
     * @param  string param.module_admin_field.model_config_gpu - 显卡
     * @param  int param.module_admin_field.image - 镜像ID
     * @param  string param.module_admin_field.bw - 带宽
     * @param  int param.module_admin_field.in_bw - 进带宽
     * @param  string param.module_admin_field.flow - 流量
     * @param  string param.module_admin_field.defence - 防御峰值
     * @param  string param.module_admin_field.ip_num - IP数量
     * @param  string param.module_admin_field.ip - 主IP
     * @param  string param.module_admin_field.additional_ip - 附加IP
     * @param  int param.module_admin_field.zjmf_dcim_id - DCIMID
     */
    public function hostUpdate($param)
    {
        $HostUpdateValidate = new HostUpdateValidate();
        if(!$HostUpdateValidate->scene('update')->check($param['module_admin_field'])){
            return ['status'=>400 , 'msg'=>lang_plugins($HostUpdateValidate->getError())];
        }

        $HostLinkModel = new HostLinkModel();
        return $HostLinkModel->hostUpdate($param);
    }

}


