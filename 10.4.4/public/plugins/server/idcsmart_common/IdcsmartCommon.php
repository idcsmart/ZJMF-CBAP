<?php
namespace server\idcsmart_common;

use app\admin\model\PluginModel;
use server\idcsmart_common\logic\IdcsmartCommonLogic;
use server\idcsmart_common\logic\ProvisionLogic;
use server\idcsmart_common\logic\ToolLogic;
use server\idcsmart_common\model\IdcsmartCommonHostConfigoptionModel;
use server\idcsmart_common\model\IdcsmartCommonProductConfigoptionModel;
use server\idcsmart_common\model\IdcsmartCommonProductConfigoptionSubModel;
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
        return ['display_name'=>'通用产品', 'version'=>'2.0.2'];
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
  `cycle_unit` enum('hour','day','month','infinite') NOT NULL DEFAULT 'day' COMMENT '周期单位:hour小时,day天,month月,infinite无限',
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
   `edition` varchar(50) NOT NULL DEFAULT '' COMMENT '版本professional专业版,free免费版',
  `config_option1` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
  `config_option2` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
  `config_option3` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
  `config_option4` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
  `config_option5` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
  `config_option6` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
  `config_option7` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
  `config_option8` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
  `config_option9` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
  `config_option10` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
  `config_option11` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
  `config_option12` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
  `config_option13` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
  `config_option14` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
  `config_option15` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
  `config_option16` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
  `config_option17` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
  `config_option18` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
  `config_option19` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
  `config_option20` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
  `config_option21` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
  `config_option22` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
  `config_option23` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
  `config_option24` varchar(500) NOT NULL DEFAULT '' COMMENT '自定义配置',
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
  `configoption_id` int(11) NOT NULL DEFAULT '0' COMMENT '当前商品其他类型为数量拖动/数量输入的配置项ID',
  `son_product_id` int(11) NOT NULL DEFAULT '0' COMMENT '子商品ID',
  `free` tinyint(1) NOT NULL DEFAULT '0' COMMENT '关联商品首周期是否免费:1是，0否',
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
  `qty_change` int(11) NOT NULL DEFAULT '0' COMMENT '数量变化最小值',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
            "CREATE TABLE `idcsmart_module_idcsmart_common_server_group` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '服务器组ID',
  `name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '组名称',
  `type` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '服务器模块类型',
  `system_type` VARCHAR(255) NOT NULL DEFAULT 'normal' COMMENT '组类型',
  `mode` INT(1) NOT NULL DEFAULT '1' COMMENT '分配方式（1：平均分配  2 满一个算一个）',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;",
            "CREATE TABLE `idcsmart_module_idcsmart_common_server` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '服务器配置ID',
  `gid` INT(11) NOT NULL DEFAULT '0' COMMENT '服务器组ID',
  `name` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '名称',
  `ip_address` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'ip地址',
  `assigned_ips` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '其他IP地址',
  `hostname` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '主机名',
  `monthly_cost` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '每月成本',
  `noc` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '数据中心',
  `status_address` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '服务器状态地址',
  `name_server1` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '主域名服务器',
  `name_server1_ip` VARCHAR(100) NOT NULL DEFAULT '',
  `name_server2` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '次域名服务器',
  `name_server2_ip` VARCHAR(100) NOT NULL DEFAULT '',
  `name_server3` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '第三域名服务器',
  `name_server3_ip` VARCHAR(100) NOT NULL DEFAULT '',
  `name_server4` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '第四域名服务器',
  `name_server4_ip` VARCHAR(100) NOT NULL DEFAULT '',
  `name_server5` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '第五域名服务器',
  `name_server5_ip` VARCHAR(100) NOT NULL DEFAULT '',
  `max_accounts` INT(11) NOT NULL DEFAULT '0' COMMENT '最大账号数量（默认为0）',
  `username` VARCHAR(256) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` VARCHAR(256) NOT NULL DEFAULT '' COMMENT '密码',
  `accesshash` VARCHAR(256) NOT NULL DEFAULT '' COMMENT '访问散列值',
  `secure` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '安全，1:选中复选框使用 SSL 连接模式,0不选(默认)',
  `port` varchar(25) NOT NULL DEFAULT '' COMMENT '访问端口',
  `active` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '1.当前模块类型激活的服务器(或默认服务器),0非默认',
  `disabled` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '1勾选禁用，0使用(默认)(单选框)',
  `server_type` VARCHAR(255) NOT NULL DEFAULT 'normal' COMMENT '服务器类型',
  `link_status` TINYINT(3) NOT NULL DEFAULT '1' COMMENT '服务器连接状态 0失败 1成功',
  `type` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '服务器模块类型',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `gid` (`gid`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;",
            "CREATE TABLE `idcsmart_module_idcsmart_common_server_host_link` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `host_id` int(11) NOT NULL DEFAULT '0',
  `server_id` int(11) NOT NULL DEFAULT '0',
  `dedicatedip` varchar(255) NOT NULL DEFAULT '' COMMENT 'ip',
  `assignedips` varchar(255) NOT NULL DEFAULT '' COMMENT '分配IP',
  `username` varchar(255) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT '密码',
  `bwlimit` int(11) NOT NULL DEFAULT '0' COMMENT '流量限制',
  `os` varchar(255) NOT NULL DEFAULT '' COMMENT '操作系统',
  `bwusage` decimal(10,2) NOT NULL DEFAULT '0.00',
  `vserverid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `host_id` (`host_id`)
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
            "DROP TABLE IF EXISTS `idcsmart_module_idcsmart_common_server_group`;",
            "DROP TABLE IF EXISTS `idcsmart_module_idcsmart_common_server`;",
            "DROP TABLE IF EXISTS `idcsmart_module_idcsmart_common_server_host_link`;",
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
        return ['status'=>200];
        # 配置中关联子模块,调子模块中开通,使用以前的逻辑
//        $ProvisionLogic = new ProvisionLogic();
//        $res = $ProvisionLogic->testLink($params['host']['id'],$params);
//        return $res;
    }

    /**
     * 时间 2022-09-25
     * @title 模块开通
     * @author wyh
     * @version v1
     */
    public function createAccount($params){
        # 配置中关联子模块,调子模块中开通,使用以前的逻辑
        $ProvisionLogic = new ProvisionLogic();
        $res = $ProvisionLogic->createAccount($params['host']['id']);
        return $res;
    }

    /**
     * 时间 2022-09-25
     * @title 模块暂停
     * @author wyh
     * @version v1
     */
    public function suspendAccount($params){
        # 配置中关联子模块,调子模块中开通,使用以前的逻辑
        $ProvisionLogic = new ProvisionLogic();
        $res = $ProvisionLogic->suspendAccount($params['host']['id']);
        return $res;
    }

    /**
     * 时间 2022-09-25
     * @title 模块解除暂停
     * @author wyh
     * @version v1
     */
    public function unsuspendAccount($params){
        # 配置中关联子模块,调子模块中开通,使用以前的逻辑
        $ProvisionLogic = new ProvisionLogic();
        $res = $ProvisionLogic->unsuspendAccount($params['host']['id']);
        return $res;
    }

    /**
     * 时间 2022-09-25
     * @title 模块删除
     * @author wyh
     * @version v1
     */
    public function terminateAccount($params){
        # 配置中关联子模块,调子模块中开通,使用以前的逻辑
        $ProvisionLogic = new ProvisionLogic();
        $res = $ProvisionLogic->terminateAccount($params['host']['id']);
        return $res;
    }

    /**
     * 时间 2022-09-25
     * @title 续费后调用
     * @author wyh
     * @version v1
     */
    public function renew($params){
        # 配置中关联子模块,调子模块中开通,使用以前的逻辑
        $ProvisionLogic = new ProvisionLogic();
        $res = $ProvisionLogic->renew($params['host']['id']);
        return $res;
    }

    /**
     * 时间 2022-09-25
     * @title 升降级后调用
     * @author wyh
     * @version v1
     */
    public function changePackage($params){
        // 升降级成功修改授权数量
        $IdcsmartCommonHostConfigoptionModel = new IdcsmartCommonHostConfigoptionModel();
        $IdcsmartCommonProductConfigoptionModel = new IdcsmartCommonProductConfigoptionModel();
        $IdcsmartCommonProductConfigoptionSubModel = new IdcsmartCommonProductConfigoptionSubModel();
        $IdcsmartCommonLogic = new IdcsmartCommonLogic();
        $data = $params['custom']['configoption']??[];
        try{
            # 获取旧配置
            $packageData = [];
            $oldConfigoptions = $IdcsmartCommonHostConfigoptionModel->where('host_id',$params['host']['id'])->select()->toArray();
            foreach ($oldConfigoptions as $oldConfigoption){
                $configoption = $IdcsmartCommonProductConfigoptionModel->field('option_type,option_name,option_param')->where('id',$oldConfigoption['configoption_id'])->find();
                $sub = $IdcsmartCommonProductConfigoptionSubModel->field('option_name,option_param')->where('id',$oldConfigoption['configoption_sub_id'])->find();
                if (!empty($configoption)){
                    if ($IdcsmartCommonLogic->checkQuantity($configoption['option_type'])){
                        $packageData['configoptions'][$configoption['option_param']] = $oldConfigoption['qty'];
                    }else{
                        $packageData['configoptions'][$configoption['option_param']] = $sub['option_param']??"";
                    }
                }
            }

            foreach ($data as $key=>$value){
                $optionType = $IdcsmartCommonProductConfigoptionModel->where('id',$key)->value('option_type');
                if ($IdcsmartCommonLogic->checkQuantity($optionType)){
                    // 删除旧的
                    $IdcsmartCommonHostConfigoptionModel->where('host_id',$params['host']['id'])
                        ->where('configoption_id',$key)
                        ->delete();
                    // 插入新的数据
                    foreach ($value as $k=>$item){
                        $IdcsmartCommonHostConfigoptionModel->insert([
                            'host_id' => $params['host']['id'],
                            'configoption_id' => $key,
                            'configoption_sub_id' => 0,
                            'qty' => $item,
                            'repeat' => $k
                        ]);
                    }
                }elseif ($IdcsmartCommonLogic->checkMultiSelect($optionType)){
                    // 删除旧的
                    $IdcsmartCommonHostConfigoptionModel->where('host_id',$params['host']['id'])
                        ->where('configoption_id',$key)
                        ->delete();
                    // 插入新的数据
                    foreach ($value as $item){
                        $IdcsmartCommonHostConfigoptionModel->insert([
                            'host_id' => $params['host']['id'],
                            'configoption_id' => $key,
                            'configoption_sub_id' => $item,
                            'qty' => 0,
                            'repeat' => 0
                        ]);
                    }
                }else{
                    $IdcsmartCommonHostConfigoptionModel->where('host_id',$params['host']['id'])
                        ->where('configoption_id',$key)
                        ->update([
                            'configoption_sub_id' => $value
                        ]);
                }
            }
        }catch (\Exception $e){
            file_put_contents(IDCSMART_ROOT.'/upgrade.txt', $e->getMessage());
        }

        # 配置中关联子模块,调子模块中开通,使用以前的逻辑
        $ProvisionLogic = new ProvisionLogic();
        $ProvisionLogic->changePackage($params['host']['id'],$packageData);

        $result['status'] = 200;
        $result['msg'] = '升降级成功';

        return $result;
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
        if (use_mobile()){ // 手机端
            $mobileTheme = configuration('clientarea_theme_mobile');
            $type = 'mobile';
            // 1、配置主题没有走默认的
            if (!file_exists(__DIR__."/template/clientarea/mobile/{$mobileTheme}/product_detail.html")){
                $mobileTheme = "default";
            }
            $res = [
                'template' => "template/clientarea/{$type}/{$mobileTheme}/product_detail.html"
            ];
        }else{ // pc端
            $clientareaTheme = configuration('clientarea_theme');
            if (!file_exists(__DIR__."/template/clientarea/pc/{$clientareaTheme}/product_detail.html")){
                $clientareaTheme = "default";
            }
            $res = [
                'template' => "template/clientarea/pc/{$clientareaTheme}/product_detail.html"
            ];
        }

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
        if (use_mobile()){ // 手机端
            $mobileTheme = configuration('clientarea_theme_mobile');
            $type = 'mobile';
            // 1、配置主题没有走默认的
            if (!file_exists(__DIR__."/template/clientarea/mobile/{$mobileTheme}/product_list.html")){
                $mobileTheme = "default";
            }
            $res = [
                'template' => "template/clientarea/{$type}/{$mobileTheme}/product_list.html"
            ];
        }else{ // pc端
            $clientareaTheme = configuration('clientarea_theme');
            if (!file_exists(__DIR__."/template/clientarea/pc/{$clientareaTheme}/product_list.html")){
                $clientareaTheme = "default";
            }
            $res = [
                'template' => "template/clientarea/pc/{$clientareaTheme}/product_list.html"
            ];
        }

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
        if (use_mobile()){ // 手机端
            $mobileTheme = configuration('cart_theme_mobile');
            // 1、配置主题没有走默认的
            if (!file_exists(__DIR__."/template/cart/mobile/{$mobileTheme}/goods.html")){
                $mobileTheme = "default";
            }
            $res = [
                'vars' => [
                    'template_catalog' => 'clientarea',
                    'themes' => 'mobile/'.configuration('clientarea_theme'),
                    'addons' => $addons['list']
                ],
                'template' => "template/cart/mobile/{$mobileTheme}/goods.html"
            ];
        }else{ // pc端
            $cartTheme = configuration('cart_theme');
            if (!file_exists(__DIR__."/template/cart/pc/{$cartTheme}/goods.html")){
                $cartTheme = "default";
            }
            $res = [
                'vars' => [
                    'template_catalog' => 'clientarea',
                    'themes' => 'pc/'.configuration('clientarea_theme'),
                    'addons' => $addons['list']
                ],
                'template' => "template/cart/pc/{$cartTheme}/goods.html"
            ];
        }

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
        $res = [
            'template'=>'template/admin/host_config.html',
        ];

        return $res;
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
    public function currentConfigOption($params)
    {
        $IdcsmartCommonLogic = new IdcsmartCommonLogic();

        $result = $IdcsmartCommonLogic->currentConfigOption($params['host']['id']);

        return $result;
    }

    public function getPriceCycle($params)
    {
        $IdcsmartCommonProductModel = new \server\idcsmart_common\model\IdcsmartCommonProductModel();
        return $IdcsmartCommonProductModel->productMinPrice($params['product']['id']);
    }

    /**
     * 时间 2023-02-16
     * @title 资源下载
     * @desc 资源下载
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
                'module' => 'idcsmart_common',
                'url' => request()->domain() . '/plugins/server/idcsmart_common/data/abc.zip' , // 下载路径
                'version' => $metaData['version'] ?? '1.0.0',
            ]
        ];
        return $result;
    }

}


