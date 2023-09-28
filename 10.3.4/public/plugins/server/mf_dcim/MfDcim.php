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
class MfDcim{

	/**
	 * 时间 2022-06-28
	 * @title 基础信息
	 * @author hh
	 * @version v1
	 */
	public function metaData(){
		return ['display_name'=>'DCIM(自定义配置)', 'version'=>'1.0.2'];
	}

	/**
	 * 时间 2022-06-28
	 * @title 添加表
	 * @author hh
	 * @version v1
	 */
	public function afterCreateFirstServer(){
		$sql = [
			"CREATE TABLE `idcsmart_module_mf_dcim_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `rand_ssh_port` tinyint(3) NOT NULL DEFAULT '0' COMMENT '随机SSH端口(0=关闭,1=开启)',
  `reinstall_sms_verify` tinyint(3) NOT NULL DEFAULT '0' COMMENT '重装短信验证(0=关闭,1=开启)',
  `reset_password_sms_verify` tinyint(3) NOT NULL DEFAULT '0' COMMENT '重置密码短信验证(0=关闭,1=开启)',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='配置限制表';",
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='数据中心表';",
			"CREATE TABLE `idcsmart_module_mf_dcim_duration` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '周期名称',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '周期时常',
  `unit` varchar(20) NOT NULL DEFAULT '' COMMENT '周期单位(hour=小时,day=天,month=自然月)',
  `price_factor` float(4,2) NOT NULL DEFAULT '1.00' COMMENT '价格系数',
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='周期表';",
			"CREATE TABLE `idcsmart_module_mf_dcim_host_image_link` (
  `host_id` int(11) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `image_id` int(11) NOT NULL DEFAULT '0' COMMENT '镜像ID',
  KEY `host_id` (`host_id`),
  KEY `image_id` (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
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
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `host_id` (`host_id`),
  KEY `data_center_id` (`data_center_id`),
  KEY `image_id` (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='产品实例关联表';",
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='镜像表';",
			"CREATE TABLE `idcsmart_module_mf_dcim_image_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `icon` varchar(50) NOT NULL DEFAULT '' COMMENT '图标',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='镜像分组';",
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='线路表';",
			"CREATE TABLE `idcsmart_module_mf_dcim_model_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `group_id` int(11) NOT NULL DEFAULT '0' COMMENT 'DCIM分组ID',
  `cpu` varchar(255) NOT NULL DEFAULT '' COMMENT '处理器',
  `cpu_param` varchar(255) NOT NULL DEFAULT '' COMMENT '处理器参数',
  `memory` varchar(255) NOT NULL DEFAULT '' COMMENT '内存',
  `disk` varchar(255) NOT NULL DEFAULT '' COMMENT '硬盘',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='型号配置';",
			"CREATE TABLE `idcsmart_module_mf_dcim_option` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `rel_type` tinyint(7) NOT NULL DEFAULT '0' COMMENT '2=线路带宽计费\r\n3=线路流量计费\r\n4=线路防护配置\r\n5=线路附加IP配置',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联ID',
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '计费方式(radio=单选，step=阶梯,total=总量)',
  `value` varchar(10) NOT NULL DEFAULT '0' COMMENT '单选值',
  `min_value` int(10) NOT NULL DEFAULT '0' COMMENT '最小值',
  `max_value` int(10) NOT NULL DEFAULT '0' COMMENT '最大值',
  `step` int(10) NOT NULL DEFAULT '1' COMMENT '步长',
  `other_config` text NOT NULL COMMENT '其他配置,json存储',
  `create_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `prr` (`product_id`,`rel_type`,`rel_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='通用配置表';",
			"CREATE TABLE `idcsmart_module_mf_dcim_price` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL COMMENT '商品ID',
  `rel_type` varchar(30) NOT NULL DEFAULT '' COMMENT '表类型(model_config=型号配置,option=通用配置)',
  `rel_id` int(11) NOT NULL DEFAULT '0' COMMENT '关联ID',
  `duration_id` int(11) NOT NULL DEFAULT '0' COMMENT '周期ID',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `rr` (`rel_type`,`rel_id`),
  KEY `duration_id` (`duration_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='配置价格表';",
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
	public function afterDeleteLastServer(){
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
	public function testConnect($params){
		$Dcim = new Dcim($params['server']);
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
	public function createAccount($params){
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->createAccount($params);
	}

	/**
	 * 时间 2023-02-09
	 * @title 模块暂停
	 * @author hh
	 * @version v1
	 */
	public function suspendAccount($params){
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->suspendAccount($params);
	}

	/**
	 * 时间 2022-06-22
	 * @title 模块解除暂停
	 * @author hh
	 * @version v1
	 */
	public function unsuspendAccount($params){
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->unsuspendAccount($params);
	}

	/**
	 * 时间 2022-06-22
	 * @title 模块删除
	 * @author hh
	 * @version v1
	 */
	public function terminateAccount($params){
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->terminateAccount($params);
	}

	/**
	 * 时间 2022-06-28
	 * @title 续费后调用
	 * @author hh
	 * @version v1
	 */
	public function renew($params){
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->renew($params);
	}

	/**
	 * 时间 2023-02-13
	 * @title 升降级后调用
	 * @author hh
	 * @version v1
	 */
	public function changePackage($params){
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->changePackage($params);
	}

	/**
	 * 时间 2022-06-28
	 * @title 变更商品后调用
	 * @author hh
	 * @version v1
	 */
	public function changeProduct($params){
		$params['host_id'] = $params['host']['id'];
		$this->afterSettle($params);
	}

	/**
	 * 时间 2022-06-21
	 * @title 价格计算
	 * @author hh
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
	public function cartCalculatePrice($params){
		$CartValidate = new CartValidate();

		// 仅计算价格验证,不需要验证其他参数
		if($params['scene'] == 'cal_price'){
			if(!$CartValidate->scene('CalPrice')->check($params['custom'])){
	            return ['status'=>400 , 'msg'=>lang_plugins($CartValidate->getError())];
	        }
		}else{
			// 下单的验证
			if(!$CartValidate->scene('cal')->check($params['custom'])){
	            return ['status'=>400 , 'msg'=>lang_plugins($CartValidate->getError())];
	        }
	        $ConfigLimitModel = new ConfigLimitModel();
	        $check = $ConfigLimitModel->checkConfigLimit($params['product']['id'], $params['custom']);
	        if($check['status'] != 200){
	        	return $check;
	        }
		}
        $params['custom']['product_id'] = $params['product']['id'];

		$DurationModel = new DurationModel();

		$res = $DurationModel->cartCalculatePrice($params, $params['scene'] == 'cal_price');
		return $res;
	}

	/**
	 * 时间 2022-06-28
	 * @title 切换商品后的输出
	 * @author hh
	 * @version v1
	 */
	public function serverConfigOption($params){
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
	public function clientArea(){
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
	 * @param   [type] $params [description]
	 * @return  [type]         [description]
	 */
	public function hostList($params){
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
	 * @param   string x       -             x
	 * @param   [type] $params [description]
	 * @return  [type]         [description]
	 */
	public function clientProductConfigOption($params){
		$res = [
			'template'=>'template/clientarea/goods.html',
		];

		return $res;
	}

	/**
	 * 时间 2022-06-29
	 * @title 后台产品内页输出,TODO
	 * @author hh
	 * @version v1
	 */
	public function adminArea(){
		return '';
	}

	// 后台商品配置项输出,好像不需要
	// public function adminProductConfigOption(){}

	/**
	 * 时间 2022-06-22
	 * @title 结算后调用,保存下单的配置项
	 * @author hh
	 * @version v1
     * @param   int param.custom.data_center_id - 数据中心ID require
     * @param   int param.custom.package_id - 套餐ID require
     * @param   int param.custom.image_id - 镜像ID require
     * @param   string param.custom.password - 密码 和SSHKEYID一起2个之中必须传一个
     * @param   string param.custom.ssh_key_id - SSHKEYID 和密码一起2个之中必须传一个
	 */
	public function afterSettle($params){
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->afterSettle($params);
	}

	/**
	 * 时间 2022-06-29
	 * @title 获取当前配置所有周期价格
	 * @desc 获取当前配置所有周期价格
	 * @author hh
	 * @version v1
	 */
	public function durationPrice($params){
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->durationPrice($params);
	}

	public function getPriceCycle($params)
	{
		$HostLinkModel = new HostLinkModel();
		return $HostLinkModel->getPriceCycle($params['product']['id']);
	}

	/**
	 * 时间 2023-02-16
	 * @title 资源下载
	 * @desc 资源下载
	 * @author hh
	 * @version v1
	 * @param   string x      -             x
	 * @param   [type] $param [description]
	 * @return  [type]        [description]
	 */
	public function downloadResource($param){
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
     * @title 要输出的值
     * @desc 要输出的值
     * @author hh
     * @version v1
     */
    public function adminField($param){
        $HostLinkModel = new HostLinkModel();
        return $HostLinkModel->adminField($param);
    }

    public function hostUpdate($param){
        $HostUpdateValidate = new HostUpdateValidate();
        if(!$HostUpdateValidate->scene('update')->check($param['module_admin_field'])){
            return ['status'=>400 , 'msg'=>lang_plugins($HostUpdateValidate->getError())];
        }

        $HostLinkModel = new HostLinkModel();
        return $HostLinkModel->hostUpdate($param);
    }


}


