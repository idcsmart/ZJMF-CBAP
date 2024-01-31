<?php 
namespace server\mf_dcim\model;

use think\Model;

/**
 * @title 产品配置关联模型
 * @use server\mf_dcim\model\HostOptionLinkModel
 */
class HostOptionLinkModel extends Model{

	protected $name = 'module_mf_dcim_host_option_link';

    // 设置字段信息
    protected $schema = [
        'host_id'   => 'int',
        'option_id' => 'int',
        'num'       => 'int',
    ];

    public function getHostOptional($hostId = 0){
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

    public function hostHaveOptional($hostId){
        $data = $this
                ->alias('hol')
                ->join('module_mf_dcim_option o', 'hol.option_id=o.id')
                ->where('hol.host_id', $hostId)
                ->value('hol.host_id');
        return !empty($data);
    }


}