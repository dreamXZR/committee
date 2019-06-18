<?php

namespace app\system\controller;

use app\system\model\SystemConfig as ConfigModel;
use app\system\model\SystemModule as ModuleModel;
use app\system\model\SystemPlugins as PluginsModel;
use think\Validate;
use Env;

/**
 * 系统基本设置
 * @package app\system\controller
 */
class System extends Admin
{
    public function index($group = 'base')
    {
        $tabData = [];
        foreach (config('system.config_group') as $key => $value) {
            $arr = [];
            $arr['title'] = $value;
            $arr['url'] = '?group='.$key;
            $tabData['menu'][] = $arr;
        }

        $map = [];
        $map['group'] = $group;
        $map['status'] = 1;

        $dataList = ConfigModel::where($map)->order('sort,id')->column('id,name,title,group,url,value,type,options,tips');
        foreach ($dataList as $k => &$v) {
            $v['id'] = $v['name'];
            if (!empty($v['options'])) {
                $v['options'] = parse_attr($v['options']);
            }
        }

        $tabData['current'] = url('?group='.$group);
        $this->assign('data_list', $dataList);
        $this->assign('tabData', $tabData);
        $this->assign('tabType', 3);
        return $this->fetch();
    }
}