<?php
namespace app\common\model;

use think\db\Query;
use think\facade\Cache;
use think\Model;

/**
 * @title SEO模型
 * @desc SEO模型
 * @use app\common\model\SeoModel
 */
class SeoModel extends Model
{
    protected $name = 'seo';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'title'         => 'string',
        'page_address'  => 'string',
        'keywords'      => 'string',
        'description'   => 'string',
        'create_time'   => 'int',
        'update_time'   => 'int',
    ];

    /**
     * 时间 2024-04-08
     * @title 获取SEO
     * @desc 获取SEO
     * @author theworld
     * @version v1
     * @param int param.page - 页数
     * @param int param.limit - 每页条数
     * @param string param.orderby - 排序 id
     * @param string param.sort - 升/降序 asc,desc
     * @return array list - SEO
     * @return int list[].id - SEOID 
     * @return string list[].title - 标题 
     * @return string list[].page_address - 页面地址 
     * @return string list[].keywords - 关键字
     * @return string list[].description - 描述
     * @return int count - SEO数量
     */
    public function seoList($param)
    {
        $param['orderby'] = isset($param['orderby']) && in_array($param['orderby'], ['id']) ? $param['orderby'] : 'id';

        $count = $this->field('id')
            ->count();

        $list = $this->field('id,title,page_address,keywords,description')
            ->limit($param['limit'])
            ->page($param['page'])
            ->order($param['orderby'], $param['sort'])
            ->select()
            ->toArray();

        return ['list' => $list, 'count' => $count];
    }

    /**
     * 时间 2024-04-08
     * @title 添加SEO
     * @desc 添加SEO
     * @author theworld
     * @version v1
     * @param string param.title - 标题 required
     * @param string param.page_address - 页面地址 required
     * @param string param.keywords - 关键字 required
     * @param string param.description - 描述 required
     */
    public function createSeo($param)
    {
        $this->startTrans();
        try{
            $seo = $this->create([
                'title' => $param['title'],
                'page_address' => $param['page_address'],
                'keywords' => $param['keywords'],
                'description' => $param['description'],
                'create_time' => time(),
            ]);

            # 记录日志
            active_log(lang('log_add_seo', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $param['title']]), 'seo', $seo->id);
            
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400, 'msg'=>lang('create_fail')];
        }

        return ['status'=>200, 'msg'=>lang('create_success')];
    }

    /**
     * 时间 2024-04-08
     * @title 编辑SEO
     * @desc 编辑SEO
     * @author theworld
     * @version v1
     * @param int param.id - SEOID required
     * @param string param.title - 标题 required
     * @param string param.page_address - 页面地址 required
     * @param string param.keywords - 关键字 required
     * @param string param.description - 描述 required
     */
    public function updateSeo($param)
    {
        $seo = $this->find($param['id']);
        if(empty($seo)){
            return ['status'=>400, 'msg'=>lang('seo_is_not_exist')];
        }

        $this->startTrans();
        try{
            $this->update([
                'title' => $param['title'],
                'page_address' => $param['page_address'],
                'keywords' => $param['keywords'],
                'description' => $param['description'],
                'update_time' => time(),
            ], ['id' => $param['id']]);

            $description = [];

            $desc = [
                'title'         => lang('seo_title'),
                'page_address'  => lang('seo_page_address'),
                'keywords'      => lang('seo_keywords'),
                'description'   => lang('seo_description'),
            ];

            foreach($desc as $k=>$v){
                if(isset($param[$k]) && $seo[$k] != $param[$k]){
                    $old = $seo[$k];
                    $new = $param[$k];

                    $description[] = lang('log_admin_update_description', [
                        '{field}'   => $v,
                        '{old}'     => $old,
                        '{new}'     => $new,
                    ]);
                }
            }

            if(!empty($description)){
                $description = lang('log_update_seo', [
                    '{admin}' => 'admin#'.get_admin_id().'#'.request()->admin_name.'#',
                    '{name}'   => $seo['title'],
                    '{detail}' => implode(',', $description),
                ]);
                active_log($description, 'seo', $seo->id);
            }

            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('update_fail')];
        }

        return ['status'=>200,'msg'=>lang('update_success')];
    }

    /**
     * 时间 2024-04-08
     * @title 删除SEO
     * @desc 删除SEO
     * @author theworld
     * @version v1
     * @param int id - SEOID required
     */
    public function deleteSeo($id)
    {
        $seo = $this->find($id);
        if(empty($seo)){
            return ['status'=>400, 'msg'=>lang('seo_is_not_exist')];
        }

        $this->startTrans();
        try{
            # 记录日志
            active_log(lang('log_delete_seo', ['{admin}'=> 'admin#'.get_admin_id().'#'.request()->admin_name.'#', '{name}' => $seo['title']]), 'seo', $seo->id);

            $this->destroy($id);
            $this->commit();
        }catch (\Exception $e){
            $this->rollback();
            return ['status'=>400,'msg'=>lang('delete_fail')];
        }

        return ['status'=>200,'msg'=>lang('delete_success')];
    }
}