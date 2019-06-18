<?php

namespace app\system\controller;

use Env;
use hisi\Cloud as cloudApp;
use app\system\model\SystemModule as ModuleModel;
use app\system\model\SystemPlugins as PluginsModel;

class Cloud extends Admin
{
    protected function initialize()
    {
        parent::initialize();

        $this->rootPath = Env::get('root_path');
        $this->appPath  = Env::get('app_path');
        $this->tempPath = Env::get('runtime_path').'app/';
        $this->cloud    = new cloudApp(config('hs_cloud.identifier'), $this->tempPath);
    }

    /**
     * 云平台列表
     */
    public function index()
    {
        if($this->request->isAjax()){
            $data = $param    = [];
            $param['page']    = $this->request->param('page/d', 1);
            $param['cat_id']  = $this->request->param('cat_id/d', 0);
            $param['type']    = $this->request->param('type/d', 1);
            $param['limit']   = $this->request->param('limit/d', 10);

            $data['code'] = 0;
            $data['data'] = [];

            $cloudData = $this->cloud->data($param)->api('apps');

            if ($cloudData['code'] == 1) {
                switch ($param['type']) {
                    case 1:// 模块
                        $locApp = ModuleModel::where('system', 0)->column('identifier,version');
                        break;
                    case 2:// 插件
                        $locApp = PluginsModel::where('system', 0)->column('identifier,version');
                        break;

                    default:// 主题
                        $locApp = [];
                        break;
                }


                $apps = [];
                foreach ($cloudData['data']['apps'] as $k => $v) {
                    $v['install'] = 0;
                    $v['upgrade'] = 0;
                    // 检查是否已有安装某个分支
                    foreach ($v['branchs'] as $kk => $vv) {
                        if (array_key_exists($kk, $locApp)) {
                            $v['install'] = $kk;
                            if (version_compare($vv['version'], $locApp[$kk], '>')) {
                                $v['upgrade'] = 1;
                            }
                            continue;
                        }
                    }
                    $apps[] = $v;
                }
                $data['data'] = $apps;
                $data['count'] = $cloudData['data']['count'];
            }

            return json($data);
        }
        $data=[];
        $data['cats'] = cache('cloud_app_cats');
        if (!$data['cats']) {
            $cats = $this->cloud->api('cats');
            $data['cats'] = $cats['data'];
            cache('cloud_app_cats', $data['cats']);
        }
        $this->assign('api_url', $this->cloud->apiUrl());
        $this->assign('data', $data);
        return $this->fetch();
    }
}