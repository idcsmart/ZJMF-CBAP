<?php
namespace app\common\model;

use think\Model;

/**
 * @title 官网导航模型
 * @desc  官网导航模型
 * @use app\common\model\WebNavModel
 */
class WebNavModel extends Model
{
	protected $name = 'web_nav';

	// 设置字段信息
    protected $schema = [
        'id'                    => 'int',
        'name'                  => 'string',
        'url'                   => 'string',
        'status'                => 'int',
        'web_nav_id'            => 'int',
        'order'                 => 'int',
        'create_time'           => 'int',
        'update_time'           => 'int',
    ];

    /**
     * 时间 2024-03-01
     * @title 官网导航列表
     * @desc  官网导航列表
     * @author hh
     * @version v1
     * @return  int list[].id - 官网导航ID
     * @return  string list[].name - 导航名称
     * @return  int list[].web_nav_id - 所属导航ID
     * @return  string list[].url - 跳转链接
     * @return  int list[].status - 是否展示(0=关闭,1=开启)
     * @return  int list[].child[].id - 二级官网导航ID
     * @return  string list[].child[].name - 导航名称
     * @return  int list[].child[].web_nav_id - 所属导航ID
     * @return  string list[].child[].url - 跳转链接
     * @return  int list[].child[].status - 是否展示(0=关闭,1=开启)
     * @return  int count - 总条数
     */
    public function webNavList()
    {
        $where = [];
        $where[] = ['web_nav_id', '=', 0];

        $whereChild = [];
        $whereChild[] = ['web_nav_id', '>', 0];

        // 不是后台时
        if(app('http')->getName() != 'admin'){
            $where[] = ['status', '=', 0];
            $whereChild[] = ['status', '=', 0];
        }
        // 获取一级导航
        $list = $this
                ->field('id,name,web_nav_id,url,status')
                ->where($where)
                ->order('order,id', 'asc')
                ->select()
                ->toArray();
        // 获取二级导航
        $childWebNavArr = [];
        $childWebNav = $this
                        ->field('id,name,web_nav_id,url,status')
                        ->where($whereChild)
                        ->order('order,id', 'asc')
                        ->select()
                        ->toArray();
        foreach($childWebNav as $v){
            $childWebNavArr[ $v['web_nav_id'] ][] = $v;
        }

        foreach($list as $k=>$v){
            $list[$k]['child'] = $childWebNavArr[ $v['id'] ] ?? [];
        }
        $count = $this->where('web_nav_id', 0)->count();
        return ['list'=>$list, 'count'=>$count];
    }

    /**
     * 时间 2024-03-01
     * @title 新增官网导航
     * @desc  新增官网导航
     * @author hh
     * @version v1
     * @param   string param.name - 导航名称 require
     * @param   int param.web_nav_id - 所属导航ID(0=一级导航) require
     * @param   string param.url - 跳转链接 require
     * @param   int param.status - 是否展示(0=关闭,1=开启) require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 提示信息
     * @return  int data.id - 官网导航ID
     */
    public function webNavCreate($param)
    {
        if(!empty($param['web_nav_id'])){
            $parentWebNav = $this->find($param['web_nav_id']);
            if(empty($parentWebNav)){
                return ['status'=>400, 'msg'=>lang('web_nav_web_nav_id_not_found')];
            }
            // 仅支持二级导航
            if($parentWebNav['web_nav_id'] > 0){
                return ['status'=>400, 'msg'=>lang('web_nav_web_nav_id_error')];
            }
        }else{
            $param['web_nav_id'] = 0;
        }
        $param['create_time'] = time();
        $webNav = $this->create($param, ['name','url','status','web_nav_id','create_time']);

        $description = lang('log_web_nav_create_success', [
            '{name}' => $param['name'],
        ]);
        active_log($description);

        $result = [
            'status' => 200,
            'msg'    => lang('create_success'),
            'data'   => [
                'id' => (int)$webNav->id,
            ],
        ];
        return $result;
    }

    /**
     * 时间 2024-03-01
     * @title 修改官网导航
     * @desc  修改官网导航
     * @author hh
     * @version v1
     * @param   int param.id - 官网导航ID require
     * @param   string param.name - 导航名称 require
     * @param   int param.web_nav_id - 所属导航ID(0=一级导航) require
     * @param   string param.url - 跳转链接 require
     * @param   int param.status - 是否展示(0=关闭,1=开启) require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function webNavUpdate($param)
    {
        $webNav = $this->find($param['id']);
        if(empty($webNav)){
            return ['status'=>400, 'msg'=>lang('web_nav_id_not_found')];
        }
        if(!empty($param['web_nav_id'])){
            if($param['web_nav_id'] == $param['id']){
                return ['status'=>400, 'msg'=>lang('param_error')];
            }
            $parentWebNav = $this->find($param['web_nav_id']);
            if(empty($parentWebNav)){
                return ['status'=>400, 'msg'=>lang('web_nav_web_nav_id_not_found')];
            }
            // 仅支持二级导航
            if($parentWebNav['web_nav_id'] > 0){
                return ['status'=>400, 'msg'=>lang('web_nav_web_nav_id_error')];
            }
            // 一级导航修改为二级导航时
            if($webNav['web_nav_id'] == 0){
                $child = $this->where('web_nav_id', $param['id'])->find();
                if(!empty($child)){
                    return ['status'=>400, 'msg'=>lang('web_nav_cannot_modify_first_to_second_for_have_child')];
                }
            }
        }else{
            $param['web_nav_id'] = 0;
        }
        // 置顶
        if($webNav['web_nav_id'] != $param['web_nav_id']){
            $param['order'] = 0;
        }else{
            $param['order'] = $webNav['order'];
        }
        $param['update_time'] = time();
        $this->update($param, ['id'=>$param['id']], ['name','url','status','web_nav_id','order','update_time']);

        // 日志记录
        $description = [];

        $desc = [
            'name'          => lang('web_nav_name'),
            'url'           => lang('web_nav_url'),
            'status'        => lang('web_nav_status'),
            'web_nav_id'    => lang('web_nav_web_nav_id'),
        ];
        $status = [lang('web_nav_no'),lang('web_nav_yes')];

        foreach($desc as $k=>$v){
            if(isset($param[$k]) && $webNav[$k] != $param[$k]){
                $old = $webNav[$k];
                $new = $param[$k];

                if($k == 'status'){
                    $old = $status[ $old ];
                    $new = $status[ $new ];
                }else if($k == 'web_nav_id'){
                    $old = !empty($old) ? $this->where('id', $old)->value('name') : lang('web_nav_null');
                    $new = !empty($new) ? $this->where('id', $new)->value('name') : lang('web_nav_null');
                }
                $description[] = lang('log_admin_update_description', [
                    '{field}'   => $v,
                    '{old}'     => $old,
                    '{new}'     => $new,
                ]);
            }
        }
        if(!empty($description)){
            $description = lang('log_web_nav_update_success', [
                '{detail}'  => implode(',', $description),
            ]);
            active_log($description);
        }

        $result = [
            'status' => 200,
            'msg'    => lang('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2024-03-01
     * @title 删除官网导航
     * @desc  删除官网导航
     * @author hh
     * @version v1
     * @param   int param.id - 官网导航ID require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 提示信息
     */
    public function webNavDelete($param)
    {
        $webNav = $this->find($param['id']);
        if(empty($webNav)){
            return ['status'=>400, 'msg'=>lang('web_nav_id_not_found')];
        }
        // 是否有二级导航
        $childWebNav = $this->where('web_nav_id', $param['id'])->find();
        if(!empty($childWebNav)){
            return ['status'=>400, 'msg'=>lang('web_nav_delete_fail_for_have_child')];
        }
        $name = $webNav->name;
        $webNav->delete();

        $description = lang('log_web_nav_delete_success', [
            '{name}' => $name,
        ]);
        active_log($description);

        $result = [
            'status' => 200,
            'msg'    => lang('delete_success'),
        ];
        return $result;
    }

    /**
     * 时间 2024-03-01
     * @title 修改官网导航是否展示
     * @desc  修改官网导航是否展示
     * @author hh
     * @version v1
     * @param   int param.id - 官网导航ID require
     * @param   int param.status - 是否展示(0=关闭,1=开启) require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function webNavUpdateStatus($param)
    {
        $webNav = $this->find($param['id']);
        if(empty($webNav)){
            return ['status'=>400, 'msg'=>lang('web_nav_id_not_found')];
        }
        $param['update_time'] = time();
        $this->update($param, ['id'=>$param['id']], ['status','update_time']);

        $status = [lang('web_nav_no'),lang('web_nav_yes')];

        $description = lang('log_web_nav_update_status_success', [
            '{name}'    => $webNav['name'],
            '{status}'  => $status[ $param['status'] ],
        ]);
        active_log($description);
        
        $result = [
            'status' => 200,
            'msg'    => lang('update_success'),
        ];
        return $result;
    }

    /**
     * 时间 2024-03-01
     * @title 拖动排序
     * @desc  拖动排序
     * @author hh
     * @version v1
     * @param   int param.id - 当前导航ID require
     * @param   int param.prev_id - 前一个导航ID(0=表示置顶) require
     * @param   int param.web_nav_id - 所属导航ID(0=一级导航,如果有前一个导航ID,使用前一个导航所属导航ID) require
     * @return  int status - 状态(200=成功,400=失败)
     * @return  string msg - 信息
     */
    public function dragToSort($param)
    {
        $webNav = $this->find($param['id']);
        if(empty($webNav)){
            return ['status'=>400, 'msg'=>lang('web_nav_id_not_found')];
        }
        if($param['prev_id'] == 0){
            if(!empty($param['web_nav_id'])){
                $parentWebNav = $this->find($param['web_nav_id']);
                if(empty($parentWebNav)){
                    return ['status'=>400, 'msg'=>lang('web_nav_web_nav_id_error')];
                }
            }
            $preOrder = -1;
            $order = 0;
        }else{
            $preWebNav = $this->find($param['prev_id']);
            if(empty($preWebNav)){
                return ['status'=>400, 'msg'=>lang('web_nav_id_not_found')];
            }
            $preOrder = $preWebNav['order'];
            $order = $preWebNav['order']+1;
            $param['web_nav_id'] = $preWebNav['web_nav_id'];
        }
        // 顶级导航不能拖到二级导航
        if($webNav['web_nav_id'] == 0 && $param['web_nav_id'] > 0){
            return ['status'=>400, 'msg'=>lang('web_nav_cannot_drag_first_to_second')];
        }
        $this->where('web_nav_id', $param['web_nav_id'])->where('order', '>=', $preOrder)->where('id', '>', $param['prev_id'])->inc('order', 2)->update();
        $this->where('id', $param['id'])->update(['web_nav_id'=>$param['web_nav_id'],'order'=>$order]);

        return ['status'=>200, 'msg'=>lang('success_message')];
    }



}
