<?php
/*
 *  定义权限
 */
return [
    [
        'title' => 'auth_site_management_file_download',
        'url' => '',
        'description' => '文件下载', # 权限描述
        'parent' => 'auth_site_management', # 父权限 
        'child' => [
            [
                'title' => 'auth_site_management_file_download_view',
                'url' => 'index',
                'auth_rule' => [
                    'addon\idcsmart_file_download\controller\AdminIndexController::idcsmartFileList',
                    'addon\idcsmart_file_download\controller\AdminIndexController::idcsmartFileFolderList',
                    'app\admin\controller\ProductController::productList',
                ],
                'description' => '查看页面',
            ],
            [
                'title' => 'auth_site_management_file_download_upload_file',
                'url' => '',
                'auth_rule' => [
                    'addon\idcsmart_file_download\controller\AdminIndexController::createIdcsmartFile',
                ],
                'description' => '上传文件',
            ],
            [
                'title' => 'auth_site_management_file_download_update_file',
                'url' => '',
                'auth_rule' => [
                    'addon\idcsmart_file_download\controller\AdminIndexController::idcsmartFileDetail',
                    'addon\idcsmart_file_download\controller\AdminIndexController::updateIdcsmartFile',
                ],
                'description' => '编辑文件',
            ],
            [
                'title' => 'auth_site_management_file_download_move_file',
                'url' => '',
                'auth_rule' => [
                    'addon\idcsmart_file_download\controller\AdminIndexController::moveIdcsmartFile',
                ],
                'description' => '移动文件',
            ],
            [
                'title' => 'auth_site_management_file_download_delete_file',
                'url' => '',
                'auth_rule' => [
                    'addon\idcsmart_file_download\controller\AdminIndexController::deleteIdcsmartFile',
                ],
                'description' => '删除文件',
            ],
            [
                'title' => 'auth_site_management_file_download_file_order',
                'url' => '',
                'auth_rule' => [
                    'addon\idcsmart_file_download\controller\AdminIndexController::idcsmartFileOrder',
                ],
                'description' => '拖动文件排序',
            ],
            [
                'title' => 'auth_site_management_file_download_file_show_hide',
                'url' => '',
                'auth_rule' => [
                    'addon\idcsmart_file_download\controller\AdminIndexController::hiddenIdcsmartFile',
                ],
                'description' => '文件显隐开关',
            ],
            [
                'title' => 'auth_site_management_file_download_file_folder',
                'url' => '',
                'auth_rule' => [
                    'addon\idcsmart_file_download\controller\AdminIndexController::createIdcsmartFileFolder',
                    'addon\idcsmart_file_download\controller\AdminIndexController::updateIdcsmartFileFolder',
                    'addon\idcsmart_file_download\controller\AdminIndexController::deleteIdcsmartFileFolder',
                    'addon\idcsmart_file_download\controller\AdminIndexController::setDefaultFileFolder',
                ],
                'description' => '文件夹管理',
            ],
        ]
    ],
];