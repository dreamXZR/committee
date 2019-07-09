<?php

namespace app\common\behavior;

use Env;
use Request;
use Route;

/**
 * 应用初始化行为
 */
class Init
{
    public function run()
    {

        define('ROOT_PATH', Env::get('root_path'));
        define('IN_SYSTEM', true);

        if (defined('INSTALL_ENTRANCE')) return;


        // 系统版本
        $version = include_once(Env::get('root_path').'version.php');
        config($version);
    }
}
