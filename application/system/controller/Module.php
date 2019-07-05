<?php

namespace app\system\controller;

use app\system\model\SystemModule as ModuleModel;
use app\system\model\SystemMenu as MenuModel;
use app\system\model\SystemHook as HookModel;
use hisi\Dir;
use hisi\PclZip;
use think\Db;
use think\Xml;
use Env;

/**
 * 模块管理控制器
 * @package app\system\controller
 */
class Module extends Admin
{
    protected $tabData=[];

    protected $hisiModel = 'SystemModule';

    protected function initialize()
    {
        parent::initialize();

        $this->tabData['menu']=[
            [
                'title' => '已启用',
                'url' => 'system/module/index?status=2',
            ],
            [
                'title' => '已停用',
                'url' => 'system/module/index?status=0',
            ],


        ];

        $this->appPath = Env::get('app_path');
    }

    /**
     * 本地模块列表
     */
    public function index()
    {
        $status=$this->request->param('status/d',2);
        $tabData= $this->tabData;
        $tabData['current'] = url('?status='.$status);

        $map=[
            'status'=>$status,
            'system'=>0
        ];
        $modules=ModuleModel::where($map)->order('sort,id')
                                         ->column('id,title,intro,icon,system,config,name,version,status');


        $this->assign('emptyTips','<tr><td colspan="5" align="center" height="100">未发现相关模块</td></tr>');
        $this->assign('module_list',array_values($modules));
        $this->assign('tabData', $tabData);
        $this->assign('tabType', 3);
        return $this->fetch();
    }



    /**
     * 执行安装模块
     * @param $id
     * @param string $clear
     * @return bool
     */
    public function execInstall($id,$clear='')
    {
        $mod = ModuleModel::where('id', $id)->find();
        if (!$mod) {
            return '模块不存在';
        }

        if ($mod['status'] > 0) {
            return '请勿重复安装此模块';
        }

        //获取模块中的info.php文件
        $modPath = $this->appPath.$mod['name'].'/';
        if (!file_exists($modPath.'info.php')) {
            return '模块配置文件不存在[info.php]';
        }
        $info = include_once $modPath.'info.php';

        // 过滤系统表
        foreach ($info['tables'] as $t) {
            if (in_array($t, config('system.tables'))) {
                return '模块数据表与系统表重复['.$t.']';
            }
        }

        // 导入安装SQL
        $sqlFile = realpath($modPath.'sql/install.sql');
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            $sqlList = parse_sql($sql, 0, [$info['db_prefix'] => config('database.prefix')]);
            if ($sqlList) {
                if ($clear == 1) {// 清空所有数据
                    foreach ($info['tables'] as $table) {
                        if (Db::query("SHOW TABLES LIKE '".config('database.prefix').$table."'")) {
                            Db::execute('DROP TABLE IF EXISTS `'.config('database.prefix').$table.'`;');
                        }
                    }
                }
                $sqlList = array_filter($sqlList);
                foreach ($sqlList as $v) {
                    // 过滤sql里面的系统表
                    foreach (config('system.tables') as $t) {
                        if (stripos($v, '`'.config('database.prefix').$t.'`') !== false) {
                            return 'install.sql文件含有系统表['.$t.']';
                        }
                    }
                    if (stripos($v, 'DROP TABLE') === false) {
                        try {
                            Db::execute($v);
                        } catch(\Exception $e) {
                            return $e->getMessage();
                        }
                    }
                }
            }
        }

        // 导入演示SQL
        $sqlFile = realpath($modPath.'sql/demo.sql');
        if (file_exists($sqlFile) && $this->request->param('demo_data/d', 0) === 1) {
            $sql = file_get_contents($sqlFile);
            $sqlList = parse_sql($sql, 0, [$info['db_prefix'] => config('database.prefix')]);
            if ($sqlList) {
                $sqlList = array_filter($sqlList);
                foreach ($sqlList as $v) {
                    // 过滤sql里面的系统表
                    foreach (config('hs_system.tables') as $t) {
                        if (stripos($v, '`'.config('database.prefix').$t.'`') !== false) {
                            return 'demo.sql文件含有系统表['.$t.']';
                        }
                    }

                    if (stripos($v, 'DROP TABLE') === false) {
                        try {
                            Db::execute($v);
                        } catch(\Exception $e) {
                            return $e->getMessage();
                        }
                    }
                }
            }
        }

        // 导入路由
        if ( file_exists($modPath.'route.php') ) {
            copy($modPath.'route.php', Env::get('route_path').$mod['name'].'.php');
        }

        // 导入菜单
        if ( file_exists($modPath.'menu.php') ) {
            $menus = include_once $modPath.'menu.php';
            // 如果不是数组且不为空就当JSON数据转换
            if (!is_array($menus) && !empty($menus)) {
                $menus = json_decode($menus, true);
            }
            if (MenuModel::importMenu($menus, $mod['name']) == false) {
                // 执行回滚
                MenuModel::where('module', $mod['name'])->delete();
                return '添加菜单失败，请重新安装';
            }
        }

        // 导入模块钩子
        if (!empty($info['hooks'])) {
            $hookModel = new HookModel;
            foreach ($info['hooks'] as $k => $v) {
                $map            = [];
                $map['name']    = $k;
                $map['intro']   = $v;
                $map['source']  = 'module.'.$mod['name'];
                $hookModel->storage($map);
            }
        }

        cache('hook_plugins', null);

        // 导入模块配置
        if (isset($info['config']) && !empty($info['config'])) {
            $menu           = [];
            $menu['pid']    = 10;
            $menu['module'] = $mod['name'];
            $menu['title']  = $mod['title'].'配置';
            $menu['url']    = 'system/system/index';
            $menu['param']  = 'group='.$mod['name'];
            $menu['system'] = 0;
            $menu['debug']  = 0;
            $menu['sort']   = 100;
            $menu['status'] = 1;
            $menu_mod = new MenuModel;
            $menu_mod->storage($menu);
            ModuleModel::where('id', $id)->setField('config', json_encode($info['config'], 1));
        }

        // 更新模块基础信息
        $sqlmap                 = [];
        $sqlmap['title']        = $info['title'];
        $sqlmap['identifier']   = $info['identifier'];
        $sqlmap['intro']        = $info['intro'];
        $sqlmap['version']      = $info['version'];
        $sqlmap['status']       = 2;

        ModuleModel::where('id', $id)->update($sqlmap);
        ModuleModel::getConfig('', true);
        return true;

    }


    /**
     * 模块卸载
     */
    public function unload()
    {
        $id = get_num();
        $module = ModuleModel::where('id', $id)->find();
        if (!$module) {
            return $this->error('模块不存在');
        }
        if ($module['status'] == 0) {
            return $this->error('模块未安装');
        }

        if($this->request->isPost()){
            $modPath =$this->appPath.$module['name'].'/';

            //检查并载入配置文件
            if (!file_exists($modPath.'info.php')) {
                return $this->error('模块配置文件不存在[info.php]');
            }
            $info = include_once $modPath.'info.php';

            // 过滤系统表
            foreach ($info['tables'] as $t) {
                if (in_array($t, config('system.tables'))) {
                    return $this->error('模块数据表与系统表重复['.$t.']');
                }
            }

            $post = $this->request->post();
            // 导入SQL
            $sqlFile = realpath($modPath.'sql/uninstall.sql');
            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                $sqlList = parse_sql($sql, 0, [$info['db_prefix'] => config('database.prefix')]);
                if ($sqlList) {
                    $sqlList = array_filter($sqlList);
                    foreach ($sqlList as $v) {
                        // 防止删除整个数据库
                        if (stripos(strtoupper($v), 'DROP DATABASE') !== false) {
                            return $this->error('uninstall.sql文件疑似含有删除数据库的SQL');
                        }
                        // 过滤sql里面的系统表
                        foreach (config('system.tables') as $t) {
                            if (stripos($v, '`'.config('database.prefix').$t.'`') !== false) {
                                return $this->error('uninstall.sql文件含有系统表['.$t.']');
                            }
                        }
                        try {
                            Db::execute($v);
                        } catch(\Exception $e) {
                            return $e->getMessage();
                        }
                    }
                }
            }
            // 删除路由
            if ( file_exists(Env::get('route_path').$module['name'].'.php') ) {
                unlink(Env::get('route_path').$module['name'].'.php');
            }
            // 删除当前模块菜单
            MenuModel::where('module', $module['name'])->delete();
            // 删除模块钩子
            model('SystemHook')->where('source', 'module.'.$module['name'])->delete();
            cache('hook_plugins', null);
            // 更新模块状态为未安装
            ModuleModel::where('id', $id)->update(['status' => 0, 'default' => 0, 'config' => '']);
            ModuleModel::getConfig('', true);
            $this->success('模块已卸载成功', url('index?status=0'));

        }

        $this->assign('module', $module);
        return $this->fetch();
    }
}