<?php
namespace app\admin\validate;

use think\Validate;

/**
 * 导航管理验证
 */
class MenuValidate extends Validate
{
	protected $rule = [
		'menu'    => 'require|checkMenu:thinkphp',
        'menu2'   => 'require|checkHomeMenu:thinkphp',
    ];

    protected $message  =   [
    	'menu.require'          => 'param_error',
        'menu.checkMenu'        => 'param_error',
        'menu2.checkHomeMenu'   => 'param_error',
    ];

    protected $scene = [
        'save' => ['menu'],
        'save_home' => ['menu2']
    ];

    public function checkMenu($value)
    {
        $navId = [];
        foreach ($value as $k => $v) {
            if(!isset($v['type'])){
                return false;
            }
            // 新增内嵌类型
            if(!in_array($v['type'], ['system', 'plugin', 'custom', 'embedded'])){
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
            if($v['type']=='custom' || $v['type']=='embedded'){
                if(!isset($v['url'])){
                    return false;
                }
                if(!is_string($v['url'])){
                    return false;
                }
                if(strlen($v['url'])>255){
                    return false;
                }
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
                $navId[] = $v['nav_id'];
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
                    if(!in_array($cv['type'], ['system', 'plugin', 'custom', 'embedded'])){
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
                    if($cv['type']=='custom' || $v['type']=='embedded'){
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
                        $navId[] = $cv['nav_id'];
                    }
                }
            }  
        }
        if(count($navId)!=count(array_unique($navId))){
            return 'nav_cannot_repeat_add';
        }
        return true;
    }

    public function checkHomeMenu($value)
    {
        $productId = [];
        $count = 0;
        $navId = [];
        foreach ($value as $k => $v) {
            if(!isset($v['type'])){
                return false;
            }
            if(!in_array($v['type'], ['system', 'plugin', 'custom', 'module', 'res_module','embedded'])){
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
                if(!isset($v['url'])){
                    return false;
                }
                if(!is_string($v['url'])){
                    return false;
                }
                if(strlen($v['url'])>255){
                    return false;
                }
                if(!isset($v['second_reminder'])){
                    return false;
                }
                if(!in_array($v['second_reminder'], ['0', '1'])){
                    return false;
                }
            }else if(in_array($v['type'], ['module', 'res_module'])){
                if(!isset($v['module'])){
                    return false;
                }
                if(!is_string($v['module'])){
                    return false;
                }
                if(!isset($v['product_id'])){
                    return false;
                }
                if(!is_array($v['product_id'])){
                    return false;
                }
                foreach ($v['product_id'] as $vv) {
                    if(!is_integer($vv)){
                        return false;
                    }
                }
                $productId = array_merge($productId, $v['product_id']);
                $count+=count($v['product_id']);
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
                $navId[] = $v['nav_id'];
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
                    if(!in_array($cv['type'], ['system', 'plugin', 'custom', 'module', 'res_module','embedded'])){
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
                    }else if(in_array($cv['type'], ['module', 'res_module'])){
                        if(!isset($cv['module'])){
                            return false;
                        }
                        if(!is_string($cv['module'])){
                            return false;
                        }
                        if(!isset($cv['product_id'])){
                            return false;
                        }
                        if(!is_array($cv['product_id'])){
                            return false;
                        }
                        foreach ($cv['product_id'] as $vv) {
                            if(!is_integer($vv)){
                                return false;
                            }
                        }
                        $productId = array_merge($productId, $cv['product_id']);
                        $count+=count($cv['product_id']);
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
                        $navId[] = $cv['nav_id'];
                    }
                }
            }  
        }
        if($count!=count(array_filter(array_unique($productId)))){
            return 'nav_product_cannot_repeat_add';
        }
        if(count($navId)!=count(array_unique($navId))){
            return 'nav_cannot_repeat_add';
        }
        return true;
    }
}