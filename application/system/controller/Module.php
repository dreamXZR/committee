<?php

namespace app\system\controller;

use app\system\model\SystemModule as ModuleModel;
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

            [
                'title' => '导入模块',
                'url' => 'system/module/import',
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
                                         ->column('id,title,author,intro,icon,default,system,app_keys,identifier,config,name,version,status');


        $this->assign('emptyTips','<tr><td colspan="5" align="center" height="100">未发现相关模块</td></tr>');
        $this->assign('module_list',array_values($modules));
        $this->assign('tabData', $tabData);
        $this->assign('tabType', 3);
        return $this->fetch();
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
            $module_path=$this->appPath.$module['name'].'/';

            //检查并载入配置文件
            if (!file_exists($module_path.'info.php')) {
                return $this->error('模块配置文件不存在[info.php]');
            }
            $info = include_once $module_path.'info.php';
        }

        $this->assign('module', $module);
        return $this->fetch();
    }
}