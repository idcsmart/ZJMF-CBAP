<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 导航管理验证
 */
class MenuValidate extends Validate
{
	protected $rule = [
		'menu' 						=> 'require|checkMenu:thinkphp',
    ];

    protected $message  =   [
    	'menu.require'     => 'param_error',
        'menu.checkMenu'   => 'param_error',
    ];

    protected $scene = [
        'save' => ['menu']
    ];

    public function checkMenu($value)
    {
        foreach ($value as $k => $v) {
            if(!isset($v['type'])){
                return false;
            }
            if(!in_array($v['type'], ['system', 'plugin', 'custom'])){
                return false;
            }
            if(!isset($v['name'])){
                return false;
            }
            if(!is_string($v['name'])){
                return false;
            }
            if(mb_strlen($v['name'])>20){
                return false;
            }
            if(!is_array($v['language'])){
                return false;
            }
            if($v['type']=='custom'){
                /*if(!isset($v['url'])){
                    return false;
                }
                if(!is_string($v['url'])){
                    return false;
                }
                if(strlen($v['url'])>255){
                    return false;
                }*/
            }else{
                if(!isset($v['nav_id'])){
                    return false;
                }
                if(!is_integer($v['nav_id'])){
                    return false;
                }
                if($v['nav_id']<=0){
                    return false;
                }
            }
            if(!isset($v['child'])){
                return false;
            }
            if(!is_array($v['child'])){
                return false;
            }
            if(!empty($v['child'])){
                foreach ($v['child'] as $ck => $cv) {
                    if(!isset($cv['type'])){
                        return false;
                    }
                    if(!in_array($cv['type'], ['system', 'plugin', 'custom'])){
                        return false;
                    }
                    if(!isset($cv['name'])){
                        return false;
                    }
                    if(!is_string($cv['name'])){
                        return false;
                    }
                    if(mb_strlen($cv['name'])>20){
                        return false;
                    }
                    if(!is_array($cv['language'])){
                        return false;
                    }
                    if($cv['type']=='custom'){
                        if(!isset($cv['url'])){
                            return false;
                        }
                        if(!is_string($cv['url'])){
                            return false;
                        }
                        if(strlen($cv['url'])>255){
                            return false;
                        }
                    }else{
                        if(!isset($cv['nav_id'])){
                            return false;
                        }
                        if(!is_integer($cv['nav_id'])){
                            return false;
                        }
                        if($cv['nav_id']<=0){
                            return false;
                        }
                    }
                }
            }  
        }
        return true;
    }
}