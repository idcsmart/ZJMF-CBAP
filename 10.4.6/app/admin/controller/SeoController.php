<?php
namespace app\admin\controller;

use app\common\model\SeoModel;
use app\admin\validate\SeoValidate;

/**
 * @title 模板控制器-SEO
 * @desc 模板控制器-SEO
 * @use app\admin\controller\SeoController
 */
class SeoController extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
        $this->validate = new SeoValidate();
    }

    /**
     * 时间 2024-04-08
     * @title 获取SEO
     * @desc 获取SEO
     * @author theworld
     * @version v1
     * @url /admin/v1/seo
     * @method  GET
     * @param int page - 页数
     * @param int limit - 每页条数
     * @param string orderby - 排序 id
     * @param string sort - 升/降序 asc,desc
     * @return array list - SEO
     * @return int list[].id - SEOID 
     * @return string list[].title - 标题 
     * @return string list[].page_address - 页面地址 
     * @return string list[].keywords - 关键字
     * @return string list[].description - 描述
     * @return int count - SEO数量
     */
    public function list()
    {
        // 合并分页参数
        $param = array_merge($this->request->param(), ['page' => $this->request->page, 'limit' => $this->request->limit, 'sort' => $this->request->sort]);
        
        // 实例化模型类
        $SeoModel = new SeoModel();

        // 获取SEO
        $data = $SeoModel->seoList($param);

        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2024-04-08
     * @title 添加SEO
     * @desc 添加SEO
     * @author theworld
     * @version v1
     * @url /admin/v1/seo
     * @method  POST
     * @param string title - 标题 required
     * @param string page_address - 页面地址 required
     * @param string keywords - 关键字 required
     * @param string description - 描述 required
     */
    public function create()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('create')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $SeoModel = new SeoModel();
        
        // 添加SEO
        $result = $SeoModel->createSeo($param);

        return json($result);
    }

    /**
     * 时间 2024-04-08
     * @title 编辑SEO
     * @desc 编辑SEO
     * @author theworld
     * @version v1
     * @url /admin/v1/seo/:id
     * @method  PUT
     * @param int id - SEOID required
     * @param string title - 标题 required
     * @param string page_address - 页面地址 required
     * @param string keywords - 关键字 required
     * @param string description - 描述 required
     */
    public function update()
    {
        // 接收参数
        $param = $this->request->param();

        // 参数验证
        if (!$this->validate->scene('update')->check($param)){
            return json(['status' => 400 , 'msg' => lang($this->validate->getError())]);
        }

        // 实例化模型类
        $SeoModel = new SeoModel();
        
        // 编辑SEO
        $result = $SeoModel->updateSeo($param);

        return json($result);
    }

    /**
     * 时间 2024-04-08
     * @title 删除SEO
     * @desc 删除SEO
     * @author theworld
     * @version v1
     * @url /admin/v1/seo/:id
     * @method  DELETE
     * @param int id - SEOID required
     */
    public function delete()
    {
        // 接收参数
        $param = $this->request->param();

        // 实例化模型类
        $SeoModel = new SeoModel();
        
        // 删除SEO
        $result = $SeoModel->deleteSeo($param['id']);

        return json($result);

    }

    
}