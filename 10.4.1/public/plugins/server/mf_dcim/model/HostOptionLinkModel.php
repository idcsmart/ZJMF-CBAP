<?php 
namespace server\mf_dcim\model;

use think\Model;

/**
 * @title 产品配置关联模型
 * @use server\mf_dcim\model\HostOptionLinkModel
 */
class HostOptionLinkModel extends Model
{
	protected $name = 'module_mf_dcim_host_option_link';

    // 设置字段信息
    protected $schema = [
        'host_id'   => 'int',
        'option_id' => 'int',
        'num'       => 'int',
    ];

    /**
     * 时间 2024-02-18
     * @title 获取产品可选配置
     * @desc  获取产品可选配置
     * @author hh
     * @version v1
     * @param   int hostId - 产品ID require
     * @return  int optional_memory[].option_id - 可选内存配置ID
     * @return  int optional_memory[].num - 数量
     * @return  int optional_memory[].rel_type - 配置类型(7=内存)
     * @return  string optional_memory[].value - 内存配置名称
     * @return  int optional_disk[].option_id - 可选硬盘配置ID
     * @return  int optional_disk[].num - 数量
     * @return  int optional_disk[].rel_type - 配置类型(8=硬盘)
     * @return  string optional_disk[].value - 硬盘配置名称
     * @return  int optional_gpu[].option_id - 可选显卡配置ID
     * @return  int optional_gpu[].num - 数量
     * @return  int optional_gpu[].rel_type - 配置类型(9=显卡)
     * @return  string optional_gpu[].value - 显卡配置名称
     */
    public function getHostOptional($hostId = 0)
    {
    	$data = $this
    			->alias('hol')
    			->field('hol.option_id,hol.num,o.rel_type,o.value')
    			->join('module_mf_dcim_option o', 'hol.option_id=o.id')
    			->where('hol.host_id', $hostId)
    			->select();
    	$res = [
    		'optional_memory' => [],
            'optional_disk'   => [],
    		'optional_gpu'	  => [],
    	];
    	foreach($data as $v){
    		if(OptionModel::MEMORY == $v['rel_type']){
    			$res['optional_memory'][] = $v;
    		}else if(OptionModel::DISK == $v['rel_type']){
    			$res['optional_disk'][] = $v;
    		}else if(OptionModel::GPU == $v['rel_type']){
                $res['optional_gpu'][] = $v;
            }
    	}
    	return $res;
    }

    /**
     * 时间 2024-02-18
     * @title 产品是否有可选配置
     * @desc  产品是否有可选配置
     * @author hh
     * @version v1
     * @param   int hostId - 产品ID require
     * @return  bool
     */
    public function hostHaveOptional($hostId)
    {
        $data = $this
                ->alias('hol')
                ->join('module_mf_dcim_option o', 'hol.option_id=o.id')
                ->where('hol.host_id', $hostId)
                ->value('hol.host_id');
        return !empty($data);
    }


}