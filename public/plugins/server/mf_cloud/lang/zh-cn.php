<?php 

return [
	'change' => '变更',
	'null' => '空',
	'param_error'=>'参数错误',
	'success_message'=>'请求成功',
	'create_success' => '修改成功',
	'create_failed' => '修改失败',
	'update_success' => '修改成功',
	'update_failed' => '修改失败',
	'delete_success'=>'删除成功',
	'delete_failed'=>'删除失败',

	'host_is_not_exist'=>'产品不存在',
	'can_not_do_this'=>'不能执行该操作',
	'host_not_link_server'=>'产品未关联接口',
	'on'=>'开机',
	'off'=>'关机',
	'suspend'=>'暂停',
	'operating'=>'操作中',
	'fault'=>'故障',
	'start_boot_success'=>'开机发起成功',
	'start_boot_failed'=>'开机发起失败',
	'start_off_success'=>'关机发起成功',
	'start_off_failed'=>'关机发起失败',
	'start_hard_off_success'=>'强制关机发起成功',
	'start_hard_off_failed'=>'强制关机发起失败',
	'start_reboot_success'=>'重启发起成功',
	'start_reboot_failed'=>'重启发起失败',
	'start_hard_reboot_success'=>'重启强制发起成功',
	'start_hard_reboot_failed'=>'重启强制发起失败',
	'vnc_start_failed'=>'控制台启动失败',
	'start_reset_password_success'=>'重置密码发起成功',
	'start_reset_password_failed'=>'重置密码发起失败',
	'start_rescue_success'=>'救援系统发起成功',
	'start_rescue_failed'=>'救援系统发起失败',
	'start_exit_rescue_success'=>'退出救援模式发起成功',
	'start_exit_rescue_failed'=>'退出救援模式发起失败',
	'image_not_found'=>'镜像不存在',
	'image_not_enable'=>'镜像未启用',
	'image_not_in_zjmf_cloud'=>'找不到魔方云里对应镜像',
	'start_reinstall_success'=>'重装发起成功',
	'start_reinstall_failed'=>'重装发起失败',
	'chart_type_error'=>'图表类型错误',
	'vpc_network_not_change'=>'VPC网络没有变更',
	'vpc_network_change_success'=>'切换网络成功',
	'vpc_network_change_failed'=>'切换网络失败',
	'not_limited'=>'不限',
	'flow_info_get_failed'=>'流量信息获取失败',
	'disk_not_found'=>'磁盘不存在',
	'start_create_snapshot_success'=>'快照创建发起成功',
	'start_create_snapshot_failed'=>'快照创建发起失败',
	'snapshot_not_found'=>'快照不存在',
	'start_snapshot_restore_success'=>'快照还原发起成功',
	'start_snapshot_restore_failed'=>'快照还原发起失败',
	'delete_snapshot_success'=>'快照删除成功',
	'delete_snapshot_failed'=>'快照删除失败',
	'start_create_backup_success'=>'备份创建发起成功',
	'start_create_backup_failed'=>'备份创建发起失败',
	'backup_not_found'=>'备份不存在',
	'start_backup_restore_success'=>'备份还原发起成功',
	'start_backup_restore_failed'=>'备份还原发起失败',
	'delete_backup_success'=>'备份删除成功',
	'delete_backup_failed'=>'备份删除失败',
	'image_is_charge_please_buy'=>'镜像为付费镜像,请先购买',
	'template_not_found'=>'模板不存在',
	'create_template_success'=>'模板创建成功',
	'create_template_failed'=>'模板创建失败',
	'delete_template_success'=>'模板删除成功',
	'delete_template_failed'=>'模板删除失败',
	'mf_cloud_unmount_disk_success'=>'卸载磁盘成功',
	'mf_cloud_unmount_disk_fail'=>'卸载磁盘失败',
	'mf_cloud_mount_disk_success'=>'挂载磁盘成功',
	'mf_cloud_mount_disk_fail'=>'挂载磁盘失败',
	'mf_cloud_snap_creating_wait_to_retry'=>'快照正在创建中,请稍后重试',
	'mf_cloud_backup_creating_wait_to_retry'=>'备份正在创建中,请稍后重试',
	'mf_cloud_over_max_disk_num'=>'最多只能添加{num}个数据盘',
	'mf_cloud_not_support_this_duration_to_upgrade'=>'暂不支持该周期,无法升级',
	'mf_cloud_not_support_this_data_disk'=>'不支持数据盘{data_disk}G',
	'upgrade_buy_and_cancel_data_disk'=>'取消订购磁盘(GB):{del},新增磁盘(GB):{add}',
	'upgrade_buy_data_disk'=>'新增磁盘(GB):{add}',
	'upgrade_cancel_data_disk'=>'取消订购磁盘(GB):{del}',
	'upgrade_data_disk_size'=>'磁盘{name}大小(GB):{old} => {new}<br/>',
	'mf_cloud_data_disk_cannot_down_size'=>'数据盘不能降级',
	'mf_cloud_line_not_found_to_upgrade_ip_num'=>'暂不支持该实例IP升级',
	'mf_cloud_ip_num_not_change'=>'IP数量未变更',
	'mf_cloud_ip_num_error'=>'IP数量错误',
	'mf_cloud_upgrade_ip_num'=>'IP数量: {old}个=>{new}个',
	'mf_cloud_normal_network_cannot_change_to_vpc'=>'经典网络不能切换到VPC网络',
	'mf_cloud_change_vpc_network_success'=>'变更网络成功',
	'mf_cloud_change_vpc_network_fail'=>'变更网络失败,请联系管理员处理',
	'mf_cloud_start_change_vpc_network_success'=>'变更网络发起成功',
	'mf_cloud_not_support_bw_upgrade'=>'暂不支持该实例带宽升级',
	'mf_cloud_not_support_flow_upgrade'=>'暂不支持该实例流量升级',
	'mf_cloud_not_support_defence_upgrade'=>'暂不支持该实例防御升级',
	'mf_cloud_not_change_config'=>'未变更配置',
	'mf_cloud_vpc_network_not_change'=>'实例已处于该VPC网络,无需变更',

	// model
	'product_not_found'=>'商品不存在',
	'product_not_link_idcsmart_cloud_module'=>'商品未关联魔方云(自定义配置)模块接口',
	'already_add_the_same_number'=>'已添加相同的允许数量',
	'over_max_allow_num'=>'最多只能添加5条允许的数量',
	'modify_backup_price'=>',{type}数量{num}价格由{old_price}修改为{new_price}',
	'add_backup_num'=>',添加{type}数量{num}',
	'del_backup_num'=>',删除{type}数量:{num}',
	'please_add_cpu_config_first'=>'请先添加CPU配置',
	'please_add_memory_config_first'=>'请先添加内存配置',
	'cpu_config_not_found'=>'CPU配置不存在',
	'memory_config_not_found'=>'内存配置不存在',
	'line_not_found'=>'线路不存在',
	'recommend_config_exist_this_config_cannot_add'=>'推荐配置中存在该配置,不能添加该配置限制',
	'already_add_the_same_config_limit'=>'已添加相同配置限制',
	'config_limit_not_found'=>'配置限制不存在',
	'cannot_select_this_config'=>'不能选择该配置',
	'config_conflict_please_edit_recommend_config'=>'无法满足推荐配置中已配置项目，请先编辑推荐配置',
	'country_id_error'=>'国家ID错误',
	'the_same_data_center_already_add'=>'已添加相同数据中心',
	'cannot_delete_data_center_for_line_exist'=>'数据中心下有线路存在,不能删除',
	'capacity_range_intersect'=>'添加容量范围存在交集',
	'disk_limit_not_found'=>'性能限制不存在',
	'duration_not_found'=>'周期不存在',
	'system_disk_config_not_found'=>'系统盘配置不存在',
	'data_disk_config_not_found'=>'数据盘配置不存在',
	'line_bw_not_found'=>'带宽配置不存在',
	'line_flow_not_found'=>'流量配置不存在',
	'line_defence_not_found'=>'防御配置不存在',
	'line_add_ip_not_found'=>'附加IP配置不存在',
	'mf_cloud_os_not_found'=>'操作系统不存在',
	'mf_cloud_os'=>'操作系统',
	'mf_cloud_image_group_name_already_add'=>'分类名称已填加',
	'mf_cloud_image_group_not_found'=>'操作系统分类不存在',
	'mf_cloud_image_group_cannot_delete'=>'操作系统分类下还有操作系统,不能删除',
	'line_name_exist'=>'线路名称已存在',
	'this_config_in_recommend_config_cannot_add'=>'当前推荐配置属于配置限制,不能添加',
	'recommend_config_not_found'=>'推荐配置不存在',
	'mf_cloud_please_check_cpu'=>'请检查计算配置-CPU配置是否支持{cpu}核',
	'mf_cloud_please_check_memory'=>'请检查计算配置-内存配置是否支持{memory}G',
	'mf_cloud_please_check_system_disk'=>'请检查存储配置-系统盘配置是否支持{system_disk}G',
	'mf_cloud_please_check_data_disk'=>'请检查存储配置-数据盘配置是否支持{data_disk}G',
	'mf_cloud_please_check_network_type'=>'请检查其他设置是否支持{network_type}',
	'mf_cloud_please_check_line_bw'=>'请检查数据中心配置-线路{line}是否支持{bw}Mbps',
	'mf_cloud_please_check_line_flow'=>'请检查数据中心配置-线路{line}是否支持{flow}G',
	'mf_cloud_please_check_line_peak_defence'=>'请检查数据中心配置,线路防御峰值配置是否支持{peak_defence}G',
	'vpc_network_not_found'=>'VPC网络不存在',
	'vpc_network_used_cannot_delete'=>'当前VPC网络正在使用,不能删除',
	'mf_cloud_already_add_the_same_option'=>'已添加相同配置',
	'mf_cloud_option_intersect'=>'添加配置范围存在交集',
	'mf_cloud_option_not_found'=>'配置不存在',
	'mf_cloud_cannot_delete_this_config_for_config_limit_exist'=>'无法删除当前配置,请删除配置限制后重试',
	'mf_cloud_disable_ssh_key_addon'=>'未启用SSH密钥插件',

	// validate
	'product_id_error'=>'商品ID错误',
	'please_input_backup_config_num'=>'请输入数量',
	'num_must_between_1_999'=>'数量只能是1-999的整数',
	'please_input_price'=>'请输入价格',
	'price_must_be_number'=>'价格只能是数字',
	'price_cannot_lt_zero'=>'请输入正确的价格',
	'backup_config_type_error'=>'类型参数错误',
	'data_center_id_error'=>'数据中心ID错误',
	'please_select_cpu_config'=>'请选择CPU配置',
	'please_select_memory_config'=>'请选择内存配置',
	'please_select_os'=>'请选择操作系统',
	'please_select_system_disk_config'=>'请选择系统盘配置',
	'please_select_data_disk_config'=>'请选择数据盘配置',
	'please_select_disk_size'=>'请选择磁盘大小',
	'backup_num_error'=>'备份数量错误',
	'snap_num_error'=>'快照数量错误',
	'please_select_pay_duration'=>'请选择付款周期',
	'please_set_login_password'=>'请设置登录密码',
	'mf_cloud_password_format_error'=>'密码必须在6位以上，不能以“/”开头，只能输入大写字母、小写字母、数字、~!@#$&*()_-+=|{}[];:<>?,./中的特殊符号，且必须包含小写字母，大写字母，数字',
	'password_and_ssh_key_must_have_one'=>'密码/SSH密钥至少需要一种',
	'ssh_key_format_error'=>'SSH密钥格式错误',
	'instance_name_length_error'=>'实例名称不能超过1000个字',
	'please_select_network_type'=>'请选择网络类型',
	'bw_error'=>'带宽错误',
	'data_center_not_found'=>'数据中心不存在',
	'security_group_rule_error'=>'安全组规则错误',
	'please_input_password'=>'请输入密码',
	'please_select_rescue_type'=>'请选择救援系统类型',
	'please_input_port'=>'请输入端口',
	'port_format_error'=>'端口只能是1-65535间的整数',
	'ssh_key_error'=>'SSH密钥错误',
	'cancel_disk_and_add_disk_must_have_one'=>'取消的磁盘和购买的磁盘必须有其中一种',
	'cancel_disk_param_error'=>'取消磁盘参数格式错误',
	'add_disk_param_error'=>'购买磁盘参数格式错误',
	'resize_disk_param_error'=>'扩容磁盘参数错误',
	'please_select_append_ip_num'=>'请选择附加IP数量',
	'append_ip_num_format_error'=>'附加IP数量只能是1-99999的整数',
	'disk_error'=>'磁盘错误',
	'data_disk_size_error'=>'数据盘大小错误',
	'data_disk_type_error'=>'数据盘类型错误',
	'config_limit_type_param_error'=>'限制配置类型参数错误',
	'please_select_data_center'=>'请选择数据中心',
	'please_select_bw_line'=>'请选择带宽线路',
	'please_input_bw_min_value'=>'请输入带宽最小值',
	'bw_min_value_format_error'=>'带宽最小值只能是0-99999999的整数',
	'please_input_bw_max_value'=>'请输入带宽最大值',
	'bw_max_value_format_error'=>'带宽最大值只能是0-99999999的整数',
	'bw_max_value_must_gt_bw_min_value'=>'带宽最大值必须大于带宽最小值',
	'please_input_memory_min_value'=>'请输入内存最小值',
	'memory_min_value_format_error'=>'内存最小值只能是1-512的整数',
	'please_input_memory_max_value'=>'请输入内存最大值',
	'memory_max_value_format_error'=>'内存最大值只能是1-512的整数',
	'memory_max_value_must_gt_memory_min_value'=>'内存最大值必须大于内存最小值',
	'please_select_node_priority'=>'请选择开通平衡规则',
	'ip_mac_bind_param_error'=>'嵌套虚拟化参数错误',
	'support_ssh_key_param_error'=>'是否允许使用SSH密钥参数错误',
	'rand_ssh_port_param_error'=>'随机SSH端口参数错误',
	'support_normal_network_param_error'=>'经典网络参数错误',
	'support_vpc_network_param_error'=>'VPC网络参数错误',
	'support_public_ip_param_error'=>'是否允许公网IP参数错误',
	'backup_enable_param_error'=>'是否启用备份参数错误',
	'snap_enable_param_error'=>'是否启用备份参数错误',
	'disk_limit_enable_param_error'=>'是否启用性能限制参数错误',
	'please_put_status_param'=>'请传入状态参数',
	'at_least_enable_one_network'=>'至少需要启用一种网络',
	'please_input_cpu_core'=>'请输入核心数',
	'cpu_core_format_error'=>'核心数只能是1-240的整数',
	'price_must_between_0_999999'=>'价格只能是0-999999的数字',
	'other_config_param_error'=>'其他配置参数错误',
	'advanced_cpu_rule_error'=>'智能CPU配置规则错误',
	'cpu_limit_format_error'=>'CPU限制只能是0-100的整数',
	'ipv6_num_format_error'=>'IPv6数量只能是0-1000的整数',
	'country_select_error'=>'国家选择错误',
	'please_input_city'=>'请输入城市',
	'city_format_error'=>'城市不能超过255个字',
	'please_input_area'=>'请输入区域名称',
	'mf_cloud_area_format_error'=>'区域名称长度不能超过255个字',
	'cloud_config_id_cannot_be_empty'=>'魔方云配置ID不能为空',
	'please_input_disk_min_value'=>'请输入最小值',
	'disk_min_value_format_error'=>'最小值只能是0-1048576的整数',
	'please_input_disk_max_value'=>'请输入最大值',
	'disk_max_value_format_error'=>'最大值只能是1-1048576的整数',
	'disk_max_value_must_gt_disk_min_value'=>'最大值必须大于最小值',
	'please_input_read_bytes'=>'请输入随机读',
	'read_bytes_format_error'=>'随机读只能是0-99999999的整数',
	'please_input_write_bytes'=>'请输入随机写',
	'write_bytes_format_error'=>'随机写只能是0-99999999的整数',
	'please_input_read_iops'=>'请输入IOPS读',
	'read_iops_format_error'=>'IOPS读只能是0-99999999的整数',
	'please_input_write_iops'=>'请输入IOPS写',
	'write_iops_format_error'=>'IOPS写只能是0-99999999的整数',
	'please_select_config_type'=>'请选择配置方式',
	'config_type_error'=>'配置方式错误',
	'please_input_disk_size'=>'请输入容量',
	'disk_size_format_error'=>'容量只能是1-1048576的整数',
	'please_input_disk_step'=>'请输入最小变化值',
	'disk_step_format_error'=>'最小变化值只能是1-1048576的整数',
	'disk_type_format_error'=>'磁盘类型不能超过50个字',
	'step_must_gt_diff_of_max_and_min'=>'最小变化值不能超过最大值和最小值的差值',
	'please_input_duration_name'=>'请输入周期名称',
	'duration_name_format_error'=>'周期名称不能超过10个字',
	'please_input_duration_num'=>'请输入周期时长',
	'duration_num_format_error'=>'周期时长只能是1-999的整数',
	'duration_unit_param_error'=>'周期时长单位参数错误',
	'mf_cloud_please_input_image_group_name'=>'请输入分类名称',
	'mf_cloud_image_group_name_format_error'=>'分类名称不能超过50个字',
	'mf_cloud_please_select_image_group_icon'=>'请选择系统图标',
	'mf_cloud_please_select_image_group'=>'请选择系统分类',
	'mf_cloud_please_imput_image_name'=>'请输入系统名称',
	'mf_cloud_image_name_format_error'=>'系统名称长度不能超过255个字',
	'charge_param_error'=>'是否付费参数格式错误',
	'price_format_error'=>'价格格式错误',
	'enable_param_require'=>'是否启用参数必须',
	'enable_param_error'=>'是否启用参数错误',
	'mf_cloud_please_input_rel_image_id'=>'请输入操作系统ID',
	'mf_cloud_rel_image_id_format_error'=>'操作系统ID只能是整数',
	'please_select_line_bw_type'=>'请选择计费方式',
	'line_bw_type_error'=>'计费方式错误',
	'please_input_bw'=>'请输入带宽',
	'line_bw_format_error'=>'带宽只能是1-30000的整数',
	'please_input_line_bw_min_value'=>'请输入最小值',
	'line_bw_min_value_format_error'=>'最小值只能是1-30000的整数',
	'please_input_line_bw_max_value'=>'请输入最大值',
	'line_bw_max_value_format_error'=>'最大值只能是1-30000的整数',
	'line_bw_max_value_must_gt_min_value'=>'最大值必须大于最小值',
	'please_input_line_bw_step'=>'请输入最小变化值',
	'line_bw_step_format_error'=>'最小变化值只能是1-30000的整数',
	'mf_cloud_in_bw_format_error'=>'流入带宽只能是1-30000的整数',
	'advanced_bw_format_error'=>'智能带宽配置规则只能是整数',
	'please_input_peak_defence'=>'请输入防御峰值',
	'peak_defence_format_error'=>'防御峰值只能是1-999999的整数',
	'please_input_line_flow'=>'请输入流量',
	'line_flow_format_error'=>'流量只能是0-999999的整数',
	'option_other_config_param_error'=>'配置参数错误',
	'please_input_flow_in_bw'=>'请输入进站带宽',
	'flow_in_bw_format_error'=>'进站带宽只能是0-30000的整数',
	'please_input_flow_out_bw'=>'请输入出站带宽',
	'flow_out_bw_format_error'=>'出站带宽只能是0-30000的整数',
	'please_select_flow_traffic_type'=>'请选择计费方向',
	'please_select_flow_bill_cycle'=>'请选择计费周期',
	'please_input_line_ip_num'=>'请输入IP数量',
	'line_ip_num_format_error'=>'IP数量只能是1-10000的整数',
	'please_input_line_name'=>'请输入线路名称',
	'line_name_length_error'=>'线路名称不能超过50个字',
	'please_select_line_bill_type'=>'请选择计费类型',
	'line_bw_ip_group_must_int'=>'带宽IP分组只能是整数',
	'line_defence_enable_param_error'=>'防护价格配置参数错误',
	'line_defence_ip_group_must_int'=>'防护IP分组只能是整数',
	'line_ip_enable_param_error'=>'附加IP价格配置参数错误',
	'please_add_at_lease_one_bw_data'=>'请添加至少一条带宽规则',
	'please_add_at_lease_one_flow_data'=>'请添加至少一条流量计费',
	'please_add_at_lease_one_defence_data'=>'请添加至少一条防护价格配置',
	'please_add_at_lease_one_ip_data'=>'请添加至少一条附加IP价格配置',
	'option_type_must_only_one_type'=>'计费方式只能选择一种',
	'line_bw_range_intersect'=>'带宽范围重复',
	'line_bw_already_exist'=>'带宽不能重复',
	'line_flow_already_exist'=>'流量不能重复',
	'line_defence_already_exist'=>'防御峰值不能重复',
	'line_ip_already_exist'=>'IP数量不能重复',
	'please_input_memory_value'=>'请输入内存容量',
	'memory_value_format_error'=>'内存容量只能是1-512的整数',
	'please_input_memory_step'=>'请输入最小变化值',
	'memory_step_format_error'=>'最小变化值只能是1-512的整数',
	'please_input_recommend_config_name'=>'请输入推荐配置名称',
	'recommend_config_name_length_error'=>'推荐配置名称不能超过50个字',
	'recommend_config_description_length_error'=>'推荐描述不能超过65535个字',
	'order_id_format_error'=>'排序ID只能是0-999的整数',
	'please_select_line'=>'请选择线路',
	'please_input_recommend_config_cpu'=>'请输入CPU',
	'recommend_config_cpu_foramt_error'=>'CPU只能是大于0的整数',
	'please_input_recommend_config_memory'=>'请输入内存',
	'recommend_config_memory_format_error'=>'内存只能是大于0的整数',
	'please_input_recommend_config_system_disk_size'=>'请输入系统盘大小',
	'recommend_config_system_disk_size_format_error'=>'系统盘容量只能是大于0的整数',
	'system_disk_type_not_found'=>'系统盘类型不存在',
	'recommend_config_data_disk_size_format_error'=>'数据盘容量只能是整数',
	'data_disk_type_not_found'=>'数据盘类型不存在',
	'recommend_config_peak_defence_format_error'=>'防御峰值只能是0-999999的整数',
	'please_input_vpc_network_name'=>'请输入网络名称',
	'vpc_network_name_format_error'=>'网络名称不能超过255个字',
	'vpc_network_ips_format_error'=>'IP网段格式错误',

	// main
	'link_success'=>'连接成功',
	'host_already_created'=>'产品已开通',
	'package_cal_not_found'=>'套餐中的计算型号不存在',
	'package_bw_not_found'=>'套餐中的带宽不存在',
	'ssh_key_not_found'=>'SSH密钥不存在',
	'ssh_key_create_failed'=>'SSH密钥创建失败',
	'host_create_success'=>'开通成功',
	'host_create_failed'=>'开通失败',
	'not_input_idcsmart_cloud_id'=>'未填写魔方云ID',
	'suspend_success'=>'暂停成功',
	'suspend_failed'=>'暂停失败',
	'unsuspend_success'=>'解除暂停成功',
	'unsuspend_failed'=>'解除暂停失败',
	'delete_failed'=>'删除失败',
	'package'=>'套餐',
	'host_not_found'=>'实例不存在',

	'bw_type'=>'带宽类型',
	'data_center'=>'数据中心',
	'bw'=>'带宽',
	'flow'=>'流量',
	'price'=>'价格',
	'description'=>'描述',
	'flow_type'=>'流量统计方向',
	'in_bw_enable'=>'是否启用独立进带宽',
	'in_bw'=>'进带宽',
	'in'=>'进',
	'out'=>'出',
	'in_plus_out'=>'进+出',
	'name'=>'名称',
	'order'=>'排序',
	'cal_group'=>'计算型号分组',
	'memory'=>'内存',
	'disk'=>'硬盘',
	'other_param'=>'其他参数',
	'country'=>'国家',
	'country_code'=>'国家代码',
	'city'=>'城市',
	'area'=>'区域',
	'server'=>'接口',
	'cal_ratio'=>'计算型号比例',
	'bw_ratio'=>'带宽比例',
	'is_enable'=>'是否启用',
	'is_charge'=>'是否付费',
	'cal'=>'计算型号',
	'snap'=>'快照',
	'backup'=>'备份',
	'num'=>'数量',
	'node_id'=>'节点ID',
	'area_id'=>'区域ID',
	'node_group_id'=>'节点分组ID',
	'cloud_config'=>'魔方云配置',
	'support_ssh_key'=>'是否支持SSH密钥',
	'buy_data_disk'=>'是否支持独立订购',
	'per_10_price'=>'每10G价格',
	'disk_min_size'=>'最低容量',
	'disk_max_size'=>'最高容量',
	'max_add_disk_num'=>'最大附加数量',
	'store_id'=>'存储ID',
	'backup_enable'=>'是否启用备份',
	'snap_enable'=>'是否启用快照',
	'switch_on'=>'开启',
	'switch_off'=>'关闭',
	'system_disk_size'=>'系统盘大小(GB)',
	'system_disk_store'=>'系统盘储存ID',
	'free_disk'=>'赠送数据盘',
	'free_disk_store'=>'赠送数据盘存储ID',
	'system'=>'系统',
	'backup_function'=>'备份功能',
	'snap_function'=>'快照功能',
	'data_disk'=>'数据盘',
	'number'=>'个',
	'node_priority_1'=>'数量平均',
	'node_priority_2'=>'负载最低',
	'node_priority_3'=>'内存最低',
	'mf_cloud_config_node_priority'=>'开通平衡规则',
	'mf_cloud_config_ip_mac_bind'=>'嵌套虚拟化',
	'mf_cloud_config_support_ssh_key'=>'是否允许使用SSH密钥',
	'mf_cloud_config_rand_ssh_port'=>'随机SSH端口',
	'mf_cloud_config_support_normal_network'=>'是否允许经典网络',
	'mf_cloud_config_support_vpc_network'=>'是否允许VPC网络',
	'system_disk'=>'系统盘',
	'data_disk'=>'数据盘',
	'disk_limit_range'=>'限制范围',
	'disk_limit_read_bytes'=>'随机读(MB/s)',
	'disk_limit_write_bytes'=>'随机写(MB/s)',
	'disk_limit_read_iops'=>'随机读(IOPS/s)',
	'disk_limit_write_iops'=>'随机写(IOPS/s)',
	'mf_cloud_image_group'=>'操作系统分类',
	'mf_cloud_image_name'=>'名称',
	'mf_cloud_image_charge'=>'是否收费',
	'mf_cloud_image_enable'=>'是否可用',
	'mf_cloud_image_rel_image_id'=>'关联操作系统ID',
	'mf_cloud_enable'=>'启用',
	'mf_cloud_disable'=>'禁用',
	'mf_cloud_line_name'=>'线路名称',
	'mf_cloud_line_bw_ip_group'=>'带宽计费IP分组',
	'mf_cloud_line_defence_enable'=>'启用防护价格配置',
	'mf_cloud_line_defence_ip_group'=>'防护IP分组',
	'mf_cloud_line_ip_enable'=>'启用附加IP',
	'mf_cloud_recommend_config_name'=>'配置名称',
	'mf_cloud_recommend_config_description'=>'描述',
	'mf_cloud_recommend_config_order'=>'排序ID',
	'mf_cloud_recommend_config_network_type'=>'网络类型',
	'mf_cloud_recommend_config_peak_defence'=>'防御峰值',
	'mf_cloud_recommend_config_normal_network'=>'经典网络',
	'mf_cloud_recommend_config_vpc_network'=>'VPC网络',
	'mf_cloud_option_0'=>'CPU配置',
	'mf_cloud_option_1'=>'内存配置',
	'mf_cloud_option_2'=>'线路带宽配置',
	'mf_cloud_option_3'=>'线路流量配置',
	'mf_cloud_option_4'=>'线路防护配置',
	'mf_cloud_option_5'=>'线路附加IP配置',
	'mf_cloud_option_6'=>'系统盘配置',
	'mf_cloud_option_7'=>'数据盘配置',
	'mf_cloud_option_value_0'=>'核心数',
	'mf_cloud_option_value_1'=>'内存',
	'mf_cloud_option_value_2'=>'带宽',
	'mf_cloud_option_value_3'=>'流量',
	'mf_cloud_option_value_4'=>'防御峰值',
	'mf_cloud_option_value_5'=>'IP数量',
	'mf_cloud_option_value_6'=>'系统盘容量',
	'mf_cloud_option_value_7'=>'数据盘容量',
	'mf_cloud_option_traffic_type_in'=>'进',
	'mf_cloud_option_traffic_type_out'=>'出',
	'mf_cloud_option_traffic_type_all'=>'进+出',
	'mf_cloud_option_bill_cycle_month'=>'自然月',
	'mf_cloud_option_bill_cycle_last_30days'=>'购买日循环',
	'mf_cloud_advanced_cpu'=>'智能CPU配置规则',
	'mf_cloud_cpu_limit'=>'CPU限制',
	'mf_cloud_ipv6_num'=>'IPv6数量',
	'mf_cloud_line_bw_in_bw'=>'流入带宽',
	'mf_cloud_advanced_bw'=>'智能带宽配置规则',
	'mf_cloud_line_flow_in_bw'=>'进带宽限制',
	'mf_cloud_line_flow_out_bw'=>'出带宽限制',
	'mf_cloud_line_flow_traffic_type'=>'计费方向',
	'mf_cloud_line_flow_bill_cycle'=>'计费周期',
	'mf_cloud_disk_type'=>'硬盘类型',

	// log
	'log_modify' => ',{name}修改为{value}',
	'log_common_modify' => ',{name}由{old}修改为{new}',
	'log_add_backup_config_success'=>'添加允许{type}数量成功,数量:{num},价格:{price}',
	'log_modify_backup_config_success'=>'修改允许{type}数量成功{detail}',
	'log_delete_backup_config_success'=>'删除允许{type}数量成功,数量:{num},价格:{price}',
	'log_add_config_limit_success'=>'添加配置限制成功,数据中心:{data_center},cpu:{cpu},内存:{memory}',
	'log_modify_config_limit_success'=>'修改配置限制成功,ID:{id}{detail}',
	'log_delete_config_limit_success'=>'删除配置限制成功,数据中心:{data_center},cpu:{cpu},内存:{memory}',
	'log_modify_config_success'=>'修改商品其他配置成功{detail}',
	'log_create_data_center_success'=>'创建数据中心成功, 名称: {name}',
	'log_modify_data_center_success'=>'修改数据中心成功, 名称: {name}{detail}',
	'log_delete_data_center_success'=>'删除数据中心成功, 名称: {name}',
	'log_add_disk_limit_success'=>'添加{disk_type}性能限制成功,容量范围:{range}',
	'log_update_disk_limit_success'=>'修改{disk_type}性能限制成功{detail}',
	'log_delete_disk_limit_success'=>'删除{disk_type}性能限制成功,容量范围:{range}',
	'log_add_duration_success'=>'添加周期成功, 名称: {name}',
	'log_modify_duration_success'=>'修改周期成功, 原名称:{name},新名称:{new_name}',
	'log_delete_duration_success'=>'删除周期成功, 名称:{name}',
	'log_mf_cloud_add_image_group_success'=>'添加操作系统分类成功, 名称:{name}',
	'log_mf_cloud_modify_image_group_success'=>'修改操作系统分类成功, 原名称:{name},新名称:{new_name}',
	'log_mf_cloud_delete_image_group_success'=>'删除操作系统分类成功, 名称:{name}',
	'log_mf_cloud_add_image_success'=>'添加操作系统成功, 名称:{name}',
	'log_mf_cloud_modify_image_success'=>'修改操作系统成功{detail}',
	'log_mf_cloud_delete_image_success'=>'删除操作系统成功, 名称:{name}',
	'log_mf_cloud_toggle_image_enable_success'=>'{act}操作系统成功, 名称:{name}',
	'log_mf_cloud_add_line_success'=>'添加线路成功, 名称:{name}',
	'log_mf_cloud_modify_line_success'=>'修改线路成功{detail}',
	'log_mf_cloud_delete_line_success'=>'删除线路成功, 名称:{name}',
	'log_mf_cloud_add_recommend_config_success'=>'添加推荐配置成功,名称:{name},CPU:{cpu}核,内存:{memory}G',
	'log_mf_cloud_modify_recommend_config_success'=>'修改推荐配置成功{detail}',
	'log_mf_cloud_delete_recommend_config_success'=>'删除推荐配置成功,名称:{name},CPU:{cpu}核,内存:{memory}G',
	'log_mf_cloud_add_vpc_network_success'=>'添加VPC网络成功,名称:{name},网段:{ips}',
	'log_mf_cloud_modify_vpc_network_success'=>'修改VPC网络成功,原名称:{name},新名称:{new_name}',
	'log_mf_cloud_delete_vpc_network_success'=>'删除VPC网络成功,名称:{name},网段:{ips}',
	'log_host_start_boot_success'=>'实例{hostname}开机发起成功',
	'log_host_start_boot_failed'=>'实例{hostname}开机发起失败',
	'log_admin_host_start_boot_failed'=>'实例{hostname}开机发起失败,原因:{reason}',
	'log_host_start_off_success'=>'实例{hostname}关机发起成功',
	'log_host_start_off_failed'=>'实例{hostname}关机发起失败',
	'log_admin_host_start_off_failed'=>'实例{hostname}关机发起失败,原因:{reason}',
	'log_host_start_hard_off_success'=>'实例{hostname}强制关机发起成功',
	'log_host_start_hard_off_failed'=>'实例{hostname}强制关机发起失败',
	'log_admin_host_start_hard_off_failed'=>'实例{hostname}强制关机发起失败,原因:{reason}',
	'log_host_start_reboot_success'=>'实例{hostname}重启发起成功',
	'log_host_start_reboot_failed'=>'实例{hostname}重启发起失败',
	'log_admin_host_start_reboot_failed'=>'实例{hostname}重启发起失败,原因:{reason}',
	'log_host_start_hard_reboot_success'=>'实例{hostname}强制重启发起成功',
	'log_host_start_hard_reboot_failed'=>'实例{hostname}强制重启发起失败',
	'log_admin_host_start_hard_reboot_failed'=>'实例{hostname}强制重启发起失败,原因:{reason}',
	'log_host_start_reset_password_success'=>'实例{hostname}重置密码发起成功',
	'log_host_start_reset_password_failed'=>'实例{hostname}重置密码发起失败',
	'log_admin_host_start_reset_password_failed'=>'实例{hostname}重置密码发起失败,原因:{reason}',
	'log_host_start_rescue_success'=>'实例{hostname}救援模式发起成功',
	'log_host_start_rescue_failed'=>'实例{hostname}救援模式发起失败',
	'log_admin_host_start_rescue_failed'=>'实例{hostname}救援系统发起失败,原因:{reason}',
	'log_host_start_exit_rescue_success'=>'实例{hostname}退出救援模式发起成功',
	'log_host_start_exit_rescue_failed'=>'实例{hostname}退出救援模式发起失败',
	'log_admin_host_start_exit_rescue_failed'=>'实例{hostname}退出救援系统发起失败,原因:{reason}',
	'log_host_start_reinstall_success'=>'实例{hostname}重装系统发起成功',
	'log_host_start_reinstall_failed'=>'实例{hostname}重装系统发起失败',
	'log_admin_host_start_reinstall_failed'=>'实例{hostname}重装系统发起失败,原因:{reason}',
	'log_host_start_create_snap_success'=>'实例{hostname}发起创建快照成功, 快照名称:{name}',
	'log_host_start_create_snap_failed'=>'实例{hostname}发起创建快照失败, 快照名称:{name}',
	'log_host_start_snap_restore_success'=>'实例{hostname}发起快照还原成功, 快照名称: {name}',
	'log_host_start_snap_restore_failed'=>'实例{hostname}发起快照还原失败, 快照名称: {name}',
	'log_host_delete_snap_success'=>'实例{hostname}删除快照成功, 快照名称:{name}',
	'log_host_delete_snap_failed'=>'实例{hostname}删除快照失败, 快照名称:{name}',
	'log_host_start_create_backup_success'=>'实例{hostname}发起创建备份成功, 备份名称:{name}',
	'log_host_start_create_backup_failed'=>'实例{hostname}发起创建备份失败, 备份名称:{name}',
	'log_host_start_backup_restore_success'=>'实例{hostname}发起备份还原成功, 备份名称:{name}',
	'log_host_start_backup_restore_failed'=>'实例{hostname}发起备份还原失败, 备份名称:{name}',
	'log_host_delete_backup_success'=>'实例{hostname}删除备份成功, 备份名称:{name}',
	'log_host_delete_backup_failed'=>'实例{hostname}删除备份失败, 备份名称:{name}',
	'log_mf_cloud_host_unmount_disk_success'=>'实例{hostname}卸载磁盘成功, 磁盘名称:{name}',
	'log_mf_cloud_host_unmount_disk_fail'=>'实例{hostname}卸载磁盘失败, 磁盘名称:{name}',
	'log_mf_cloud_host_mount_disk_success'=>'实例{hostname}挂载磁盘成功, 磁盘名称:{name}',
	'log_mf_cloud_host_mount_disk_fail'=>'实例{hostname}挂载磁盘失败, 磁盘名称:{name}',
	'log_mf_cloud_change_vpc_network_success'=>'实例{hostname}变更网络成功,新网络:{name}',
	'log_mf_cloud_change_vpc_network_fail'=>'实例{hostname}变更网络失败,目标网络:{name},原因:变更网络超时',
	'log_mf_cloud_start_change_vpc_network_success'=>'实例{hostname}变更网络发起成功,新网络:{name}',
	'log_mf_cloud_start_change_vpc_network_fail'=>'实例{hostname}变更网络发起失败,目标网络:{name}',
	'log_mf_cloud_add_option_success'=>'添加{option}成功,{name}{detail}',
	'log_mf_cloud_delete_option_success'=>'删除{option}成功,{name}{detail}',
	'log_mf_cloud_modify_option_success'=>'修改{option}成功{detail}',
];


