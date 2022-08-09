<?php

/*
 * 模块注册钩子,匿名函数方式放置钩子
 * @author wyh
 * @time 2022-06-07
 * @description 放置钩子:hook('idcsmart_hook_test_one',['param1'=>1,'param2'=>2]);
 * */
add_hook('idcsmart_hook_test_one',function ($param){
    var_dump($param);die;
});

/*
 * 模块注册钩子,类方法放置钩子
 * @author wyh
 * @time 2022-06-07
 * @description 放置钩子:hook('idcsmart_hook_test_two',['param1'=>1,'param2'=>2]);
 * */
add_hook('idcsmart_hook_test_two',[new \app\common\model\CountryModel(),'countryList']);

