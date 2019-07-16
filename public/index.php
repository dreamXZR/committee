<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;


header('Content-Type:text/html;charset=utf-8');

// 检测PHP环境
if(version_compare(PHP_VERSION,'7.0.0','<'))  die('PHP版本过低，最少需要PHP7.0，请升级PHP版本！');

// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');

// 加载基础文件
require __DIR__ . '/../thinkphp/base.php';

// 支持事先使用静态方法设置Request对象和Config对象

// 检查是否安装
if(!is_file('./../install.lock')) {

    define('INSTALL_ENTRANCE', true);
    Container::get('app')->bind('install')->run()->send();

} else {

    Container::get('app')->run()->send();

}
