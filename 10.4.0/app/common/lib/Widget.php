<?php
namespace app\common\lib;

/**
 * @title 挂件抽象类,挂件需要继承该类
 * @use   app\common\lib\Widget
 */
abstract class Widget
{
    // 名称
    protected $title = NULL;

    // 占用列数(1-4)
    protected $columns = 1;

    // 排序权重
    protected $weight = 100;

    // 语言数组,示例
    // [
    //    'zh-cn' => [
    //         'person' => '人',
    //     ],
    //    'en-us' => [
    //         'person' => 'person',
    //     ],
    // ]
    protected $language = [];

    /**
     * 时间 2023-05-04
     * @title 获取挂件标识
     * @desc 获取挂件标识
     * @author hh
     * @version v1
     * @return  string
     */
    public function getId()
    {
        $id = explode('\\', get_class($this));
        return end($id);
    }

    /**
     * 时间 2023-05-04
     * @title 获取标题
     * @desc  获取标题
     * @author hh
     * @version v1
     * @return  string
     */
    public function getTitle()
    {
        return (string)$this->title;
    }

    /**
     * 时间 2023-05-04
     * @title 获取列数
     * @desc  获取列数
     * @author hh
     * @version v1
     * @return  int
     */
    public function getColumns(): int
    {
        return (int) $this->columns;
    }

    /**
     * 时间 2023-05-05
     * @title 获取权重
     * @desc  获取权重
     * @author hh
     * @version v1
     * @return  int
     */
    public function getWeight(): int
    {
        return (int) $this->weight;
    }

    /**
     * 时间 2023-08-29
     * @title 多语言替换
     * @desc  多语言替换
     * @author hh
     * @version v1
     * @param   string $key  - 语言标识 require
     * @param   array  $vars - 替换数据
     * @return  string
     */
    public function lang(string $key, array $vars = []): string
    {
        $lang = get_system_lang();
        $res = $key;
        if(isset($this->language[$lang][$key])){
            $res = (string)$this->language[$lang][$key];

            if(!empty($vars)){
                $replace = array_keys($vars);
                foreach ($replace as &$v) {
                    $v = '{'.$v.'}';
                }
                $res = str_replace($replace, $vars, $res);
            }
        }
        return $res;
    }

    /**
     * 时间 2023-05-04
     * @title 获取数据
     * @desc  获取数据
     * @author hh
     * @version v1
     * @return  array
     */
    public abstract function getData();

    /**
     * 时间 2023-05-04
     * @title 输出
     * @desc  输出
     * @author hh
     * @version v1
     * @return  string
     */
    public abstract function output();

}
