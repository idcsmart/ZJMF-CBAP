<?php
namespace addon\idcsmart_help\controller\clientarea;

use app\event\controller\PluginBaseController;
use addon\idcsmart_help\model\IdcsmartHelpModel;
use addon\idcsmart_help\model\IdcsmartHelpTypeModel;

/**
 * @title 帮助中心
 * @desc 帮助中心
 * @use addon\idcsmart_help\controller\clientarea\IndexController
 */
class IndexController extends PluginBaseController
{
    /**
     * 时间 2022-06-21
     * @title 帮助中心首页
     * @desc 帮助中心首页
     * @author theworld
     * @version v1
     * @url /console/v1/help/index
     * @method  GET
     * @return object index - 帮助中心首页
     * @return int index.1.id - 帮助文档分类ID
     * @return string index.1.name - 帮助文档分类名称
     * @return array index.1.helps - 帮助文档 
     * @return int index.1.helps[].id - 帮助文档ID 
     * @return string index.1.helps[].title - 帮助文档标题
     */
    public function indexIdcsmartHelp()
    {
        // 实例化模型类
        $IdcsmartHelpTypeModel = new IdcsmartHelpTypeModel();

        // 获取帮助中心首页数据
        $data = $IdcsmartHelpTypeModel->indexIdcsmartHelp('home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-20
     * @title 帮助文档列表
     * @desc 帮助文档列表
     * @author theworld
     * @version v1
     * @url /console/v1/help
     * @method  GET
     * @param string keywords - 关键字,搜索范围:标题
     * @return array list - 帮助文档
     * @return int list[].id - 帮助文档分类ID
     * @return string list[].name - 帮助文档分类名称
     * @return array list[].helps - 帮助文档 
     * @return int list[].helps[].id - 帮助文档ID 
     * @return string list[].helps[].title - 帮助文档标题
     * @return boolean list[].helps[].search - 关键字搜索到的文档,为true时代表该文档被匹配到
     */
    public function idcsmartHelp()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartHelpTypeModel = new IdcsmartHelpTypeModel();

        // 获取帮助文档列表
        $data = $IdcsmartHelpTypeModel->idcsmartHelpTypeList($param, 'home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => $data
        ];
        return json($result);
    }

    /**
     * 时间 2022-06-20
     * @title 帮助文档详情
     * @desc 帮助文档详情
     * @author theworld
     * @version v1
     * @url /console/v1/help/:id
     * @method  GET
     * @param int id - 帮助文档ID required
     * @return object help - 帮助文档
     * @return int help.id - 帮助文档ID
     * @return string help.title - 标题 
     * @return string help.content - 内容 
     * @return string help.keywords - 关键字 
     * @return string help.attachment - 附件
     * @return int help.create_time - 创建时间
     * @return object help.prev - 上一篇文档
     * @return string help.prev.id - 文档ID
     * @return string help.prev.title - 标题
     * @return object help.next - 下一篇文档
     * @return string help.next.id - 文档ID
     * @return string help.next.title - 标题
     */
    public function idcsmartHelpDetail()
    {
        // 接收参数
        $param = $this->request->param();
        
        // 实例化模型类
        $IdcsmartHelpModel = new IdcsmartHelpModel();

        // 获取帮助文档
        $help = $IdcsmartHelpModel->idcsmartHelpDetail($param['id'], 'home');

        $result = [
            'status' => 200,
            'msg' => lang_plugins('success_message'),
            'data' => [
                'help' => $help
            ]
        ];
        return json($result);
    }
}