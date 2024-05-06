<?php
namespace app\common\model;

use think\db\Query;
use think\Model;

/**
 * @title 国家模型
 * @desc 国家模型
 * @use app\common\model\CountryModel
 */
class CountryModel extends Model
{
    protected $name = 'country';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'iso'           => 'string',
        'iso3'          => 'string',
        'name'          => 'string',
        'name_zh'       => 'string',
        'nicename'      => 'string',
        'num_code'      => 'int',
        'phone_code'    => 'int',
        'order'         => 'int',
    ];

    /**
     * 时间 2022-5-10
     * @title 获取国家列表
     * @desc 获取国家列表,包括国家名，中文名，区号
     * @author wyh
     * @version v1
     * @param string param.keywords - 关键字
     * @return array list - 国家列表
     * @return string list[].name - 国家名
     * @return string list[].name_zh - 中文名
     * @return int list[].phone_code - 区号
     * @return string list[].iso - 国家英文缩写
     * @return int count - 国家总数
     */
    public function countryList($param=[])
    {
        $param['keywords'] = $param['keywords'] ?? '';

        $where = function (Query $query) use($param) {
            if(!empty($param['keywords'])){
                $query->where('name|name_zh|phone_code', 'like', "%{$param['keywords']}%");
            }
        };

        $app = app('http')->getName();
        if($app == 'home'){
            $language = get_client_lang();
        }else{
            $language = get_system_lang(true);
        }
        $countryField = ['en-us'=> 'nicename'];
        $countryName = $countryField[ $language ] ?? 'name_zh';

        $countries = $this->field('id,name,'.$countryName.' AS name_zh,phone_code,iso')
            ->where($where)
            ->select()
            ->toArray();

        $count = $this
            ->where($where)
            ->count();

        return ['list'=>$countries, 'count'=>$count];
    }

    /**
     * 时间 2022-5-20
     * @title 验证区号
     * @desc 验证区号
     * @author wyh
     * @version v1
     * @param int phone_code - 区号
     * @return bool
     */
    public function checkPhoneCode($phone_code)
    {
        $country = $this->where('phone_code',$phone_code)->find();
        return $country?true:false;
    }

}
