<?php

namespace app\common\behavior;

use app\system\model\SystemConfig as ConfigModel;
use app\system\model\SystemModule as ModuleModel;
use Env;
use Request;
use View;
/**
 * 初始化基础配置行为
 * 将扩展的全局配置本地化
 */
class Base
{
    public function run()
    {
        // 获取当前模块名称
        $module = strtolower(Request::module());

        // 安装操作直接return
        if (defined('INSTALL_ENTRANCE')) return;


        // 设置系统配置
        config(ConfigModel::getConfig());
        // 设置模块配置
        config(ModuleModel::getConfig());


        // 判断模块是否存在且已安装
        if ($module != 'index' && !defined('ENTRANCE')) {

            if (empty($module)) {
                $module = config('default_module');
            }

            $modInfo = ModuleModel::where(['name' => $module, 'status' => 1])->find();
            if (!$modInfo) {
                exit($module.' 模块可能未启用或者未安装！');
            }


        }

        // 获取站点根目录
        $entry  = request()->baseFile();
        $rootDir= preg_replace(['/index.php$/', '/plugins.php$/', '/'.config('sys.admin_path').'$/'], ['', '', ''], $entry);
        define('ROOT_DIR', $rootDir);
        
        //静态目录扩展配置
        $viewReplaceStr = [
            // 站点根目录
            '__ROOT_DIR__'      => $rootDir,
            // 静态资源根目录
            '__STATIC__'        => $rootDir.'static',
            // 文件上传目录
            '__UPLOAD__'        => $rootDir.'upload',

            // 后台公共静态目录
            '__ADMIN_CSS__'     => $rootDir.'static/system/css',
            '__ADMIN_JS__'      => $rootDir.'static/system/js',
            '__ADMIN_IMG__'     => $rootDir.'static/system/image',
            // 后台模块静态目录
            '__ADMIN_MOD_CSS__' => $rootDir.'static/'.$module.'/css',
            '__ADMIN_MOD_JS__'  => $rootDir.'static/'.$module.'/js',
            '__ADMIN_MOD_IMG__' => $rootDir.'static/'.$module.'/image',
            // 前台公共静态目录
            '__PUBLIC_CSS__'    => $rootDir.'static/css',
            '__PUBLIC_JS__'     => $rootDir.'static/js',
            '__PUBLIC_IMG__'    => $rootDir.'static/image',


        ];

        if (isset($_GET['_p'])) {
            $viewReplaceStr = array_merge($viewReplaceStr, [
                '__PLUGINS_CSS__'   => $rootDir.'static/plugins/'.$_GET['_p'].'/static/css',
                '__PLUGINS_JS__'    => $rootDir.'static/plugins/'.$_GET['_p'].'/static/js',
                '__PLUGINS_IMG__'   => $rootDir.'static/plugins/'.$_GET['_p'].'/static/image',
            ]);
        }

        View::config(['tpl_replace_string' => $viewReplaceStr]);


    }
}
