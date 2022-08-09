<?php 
namespace app\admin\controller;

use app\common\logic\DocLogic;

/**
 * @title 开发文档
 * @desc 开发文档
 * @use app\admin\controller\DocController
 */
class DocController extends AdminBaseController
{
    /**
     * 时间 2022-5-09
     * @title 开发文档
     * @desc 开发文档
     * @author theworld
     * @version v1
     * @url /admin/v1/doc
     * @method  GET
     * @return  array list - 文档列表
     * @return  string list[].section - 版块
     * @return  object list[].doc - 文档
     * @return  string list[].doc.title - 标题
     * @return  string list[].doc.desc - 描述
     * @return  array list[].list - 版块文档列表
     * @return  string list[].list[].class - 分类
     * @return  object list[].list[].doc - 文档
     * @return  string list[].list[].doc.title - 标题
     * @return  string list[].list[].doc.desc - 描述
     * @return  string list[].list[].doc.use - 内部引用
     * @return  array list[].list[].list - 分类文档列表
     * @return  string list[].list[].list[].method - 方法
     * @return  object list[].list[].list[].doc - 文档
     * @return  string list[].list[].list[].doc.title - 标题
     * @return  string list[].list[].list[].doc.desc - 描述
     * @return  string list[].list[].list[].doc.author - 作者
     * @return  string list[].list[].list[].doc.version - 版本
     * @return  string list[].list[].list[].doc.url - 请求地址
     * @return  string list[].list[].list[].doc.method - 请求方式
     * @return  object list[].list[].list[].doc.param - 请求参数
     * @return  string list[].list[].list[].doc.param.type - 类型
     * @return  string list[].list[].list[].doc.param.name - 名称
     * @return  string list[].list[].list[].doc.param.default - 默认值
     * @return  string list[].list[].list[].doc.param.desc - 描述
     * @return  string list[].list[].list[].doc.param.validate - 验证规则
     * @return  object list[].list[].list[].doc.return - 返回参数
     * @return  string list[].list[].list[].doc.return.type - 类型
     * @return  string list[].list[].list[].doc.return.name - 名称
     * @return  string list[].list[].list[].doc.return.default - 默认值
     * @return  string list[].list[].list[].doc.return.desc - 描述
     */
    public function index()
    {
        //实例化逻辑类
        $DocLogic = new DocLogic();
        
        //获取开发文档
        $list = $DocLogic->doc();
        $result = [
            'status' => 200,
            'msg' => lang('success_message'),
            'data' => [
                'list' => $list
            ]
        ];
        return json($result);
    }

    /**
     * 时间 2022-05-09
     * @title 生成开发文档
     * @desc 生成开发文档
     * @author theworld
     * @version v1
     * @url /admin/v1/doc
     * @method  POST
     */
    public function create()
    {
        //实例化逻辑类
        $DocLogic = new DocLogic();

        //生成开发文档
        $DocLogic->createDoc();

        $result = [
            'status' => 200,
            'msg' => lang('success_message')
        ];
        return json($result);
    }
}

