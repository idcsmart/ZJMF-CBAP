<?php
namespace app\common\model;

use think\Model;
use think\Db;

/**
 * @title 用户自定义字段模型
 * @desc 用户自定义字段模型
 * @use app\common\model\ClientCustomFieldModel
 */
class ClientCustomFieldModel extends Model
{
	protected $name = 'client_custom_field';

	// 设置字段信息
    protected $schema = [
        'client_id' => 'int',
        'name'      => 'string',
        'value'     => 'string',
    ];

    /**
     * 时间 2022-10-11
     * @title 获取用户自定义字段
     * @desc 获取用户自定义字段
     * @author theworld
     * @version v1
     * @param array param.client_id - 用户ID required
     * @return array list - 用户自定义字段列表
     * @return int list[].client_id - 用户ID
     * @return array list[].custom_field - 自定义字段
     * @return string list[].custom_field[].name - 名称
     * @return string list[].custom_field[].value - 值
     * @return string msg - 提示信息
     */
    public function getClientCustomField($param)
    {
        $customField = $this->whereIn('client_id', $param['client_id'])->select()->toArray();

        $arr = [];
        foreach ($customField as $key => $value) {
            $arr[$value['client_id']][] = ['name' => $value['name'], 'value' => $value['value']];
        }

        $data = [];
        foreach ($param['client_id'] as $key => $value) {
            $data[] = ['client_id' => $value, 'custom_field' => $arr[$value] ?? []];
        }
        return ['list' => $data];
    }

    /**
     * 时间 2022-10-11
     * @title 添加用户自定义字段
     * @desc 添加用户自定义字段
     * @author theworld
     * @version v1
     * @param array param.custom_field - 自定义字段 required
     * @param string param.custom_field[].name - 名称 required
     * @param string param.custom_field[].value - 值 required
     * @param int param.client_id - 用户ID required
     * @return string msg - 提示信息
     */
    public function createClientCustomField($param)
    {
        try {
            $this->whereIn('name', array_column($param['custom_field'], 'name'))->where('client_id', $param['client_id'])->delete();
            $arr = [];
            foreach ($param['custom_field'] as $k => $v) {
                $arr[] = ['client_id' => $param['client_id'], 'name' => $v['name'], 'value' => $v['value']];
            }
            $this->saveAll($arr);
        } catch (\Exception $e) {
            return ['status' => 400, 'msg' => lang('fail_message')];
        }
        return ['status' => 200, 'msg' => lang('success_message')];
    }

    /**
     * 时间 2022-10-11
     * @title 删除用户自定义字段
     * @desc 删除用户自定义字段
     * @author theworld
     * @version v1
     * @param array param.custom_field - 自定义字段名称数组 required
     * @param array param.client_id - 用户ID数组
     * @return string msg - 提示信息
     */
    public function deleteClientCustomField($param)
    {
        try {
            if(isset($param['client_id']) && !empty($param['client_id'])){
                $this->whereIn('name', $param['custom_field'])->whereIn('client_id', $param['client_id'])->delete();
            }else{
                $this->whereIn('name', $param['custom_field'])->delete();
            }
        } catch (\Exception $e) {
            return ['status' => 400, 'msg' => lang('fail_message')];
        }
        return ['status' => 200, 'msg' => lang('success_message')];
    }
}
