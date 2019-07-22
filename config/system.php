<?php
/**
 * 系统扩展配置，非TP框架配置
 */
return [
    // +----------------------------------------------------------------------
    // | 系统相关设置
    // +----------------------------------------------------------------------
    // 系统数据表
    'tables'            => [

        'system_config',
        'system_log',
        'system_menu',
        'system_module',
        'system_role',
        'system_user',
    ],
    // 系统设置分组
    'config_group'      => [
        'base'      => '基础',
        'sys'       => '系统',
        'upload'    => '上传',
        'databases'  => '数据库',
    ],
    // 系统标准模块
    'modules' => ['system', 'common', 'index', 'install'],
    // 系统标准配置文件
    'config' => ['app', 'cache', 'cookie', 'database', 'log', 'queue', 'session', 'template', 'trace', 'hs_auth', 'hs_cloud', 'hs_system', 'hisiphp'],
];