<?php
/*
 *  定义权限
 */
return [
    [
        'title' => 'auth_site_management_announcement',
        'url' => '',
        'description' => '公告中心', # 权限描述
        'parent' => 'auth_site_management', # 父权限 
        'child' => [
            [
                'title' => 'auth_site_management_announcement_view',
                'url' => 'index',
                'auth_rule' => [
                    'addon\idcsmart_announcement\controller\AdminIndexController::idcsmartAnnouncementList',
                ],
                'description' => '查看页面',
            ],
            [
                'title' => 'auth_site_management_announcement_create_announcement',
                'url' => 'announcement_create',
                'auth_rule' => [
                    'addon\idcsmart_announcement\controller\AdminIndexController::idcsmartAnnouncementTypeList',
                    'addon\idcsmart_announcement\controller\AdminIndexController::createIdcsmartAnnouncement',
                ],
                'description' => '新增公告',
            ],
            [
                'title' => 'auth_site_management_announcement_type',
                'url' => '',
                'auth_rule' => [
                    'addon\idcsmart_announcement\controller\AdminIndexController::idcsmartAnnouncementTypeList',
                    'addon\idcsmart_announcement\controller\AdminIndexController::createIdcsmartAnnouncementType',
                    'addon\idcsmart_announcement\controller\AdminIndexController::updateIdcsmartAnnouncementType',
                    'addon\idcsmart_announcement\controller\AdminIndexController::deleteIdcsmartAnnouncementType',
                ],
                'description' => '公告分类管理',
            ],
            [
                'title' => 'auth_site_management_announcement_show_hide',
                'url' => '',
                'auth_rule' => [
                    'addon\idcsmart_announcement\controller\AdminIndexController::hiddenIdcsmartAnnouncement',
                ],
                'description' => '显隐开关',
            ],
            [
                'title' => 'auth_site_management_announcement_update_announcement',
                'url' => 'announcement_create',
                'auth_rule' => [
                    'addon\idcsmart_announcement\controller\AdminIndexController::idcsmartAnnouncementDetail',
                    'addon\idcsmart_announcement\controller\AdminIndexController::idcsmartAnnouncementTypeList',
                    'addon\idcsmart_announcement\controller\AdminIndexController::updateIdcsmartAnnouncement',
                ],
                'description' => '编辑公告',
            ],
            [
                'title' => 'auth_site_management_announcement_delete_announcement',
                'url' => '',
                'auth_rule' => [
                    'addon\idcsmart_announcement\controller\AdminIndexController::deleteIdcsmartAnnouncement',
                ],
                'description' => '删除公告',
            ],
        ]
    ],
];