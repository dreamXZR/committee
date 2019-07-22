<?php

namespace app\system\controller;

use Env;
use hisi\Cloud as cloudApp;
use app\system\model\SystemModule as ModuleModel;
use hisi\Dir;
use hisi\PclZip;

class Cloud extends Admin
{
    protected function initialize()
    {
        parent::initialize();

        $this->rootPath = Env::get('root_path');
        $this->appPath  = Env::get('app_path');
        $this->tempPath = Env::get('runtime_path').'app/';
        $this->cloud    = new cloudApp(config('cloud.identifier'), $this->tempPath);
    }

    /**
     * 云平台列表
     */
    public function index()
    {
        if($this->request->isAjax()){
            $data = $param    = [];
            $param['page']    = $this->request->param('page/d', 1);
            //$param['type']    = $this->request->param('type/d', 1);
            $param['limit']   = $this->request->param('limit/d', 10);

            $data['code'] = 0;
            $data['data'] = [];

            $cloudData = $this->cloud->data($param)->type('GET')->api('modules');

            if ($cloudData['data']) {

                $locApp = ModuleModel::where('system', 0)->column('name');


                $apps = [];
                foreach ($cloudData['data'] as $k => $v) {
                    $v['install'] = 0;
                    // 检查是否已有安装某个分支
                    if(in_array($v['alias'],$locApp)){
                        $v['install'] = 1;
                    }
                    $apps[] = $v;
                }

                $data['data'] = $apps;
                $data['count'] = $cloudData['meta']['pagination']['total'];
            }

            return json($data);
        }


        $this->assign('api_url', $this->cloud->apiUrl());
        return $this->fetch();
    }

    /**
     *安装应用模块
     */
    public function install()
    {
        $module_name    = $this->request->get('module_name');
        $file_path    = $this->request->get('file_path');
        $data               = [];
        $data['file_path']   = $file_path;

        // 下载应用安装包
        $file = $this->cloud->data($data)->down('downModuleInstall');


        if (!file_exists($file)) {
            return $this->error('安装文件获取失败，请稍后在试');
        }

        // 解压包路径
        $unzipPath = $this->tempPath.basename($file,".zip");
        if (!is_dir($unzipPath)) {
            Dir::create($unzipPath, 0777);
        }

        // 解压安装包
        $archive = new PclZip('');
        $archive->PclZip($file);
        if(!$archive->extract(PCLZIP_OPT_PATH, $unzipPath, PCLZIP_OPT_REPLACE_NEWER)) {
            return $this->error('安装失败（安装包可能已损坏）');
        }

        //模块安装
        $res=self::_moduleInstall($module_name, $file, $unzipPath);

        if ($res === true) {
            return $this->success('安装成功');
        }

        return $this->error($res);
    }

    public function _moduleInstall($module_name, $file, $unzipPath)
    {

        // 防止重复安装
        if (ModuleModel::where('name', $module_name)->find()) {
            Dir::delDir($unzipPath);
            @unlink($file);
            return true;
        }

        // 应用目录
        $appPath = $unzipPath.'/upload/application/'.$module_name.'/';

        // 获取模块信息
        $info = include_once $appPath.'info.php';

        // 复制app目录
        if (!is_dir($unzipPath.'/upload/application')) {
            return '安装失败（安装包可能已损坏）';
        }

        if (!is_dir($this->rootPath.'application/'.$module_name)) {
            Dir::copyDir($unzipPath.'/upload/application', $this->appPath);
        } else {
            return '已存在同名模块';
        }

        // 复制static目录
        if (is_dir($unzipPath.'/upload/public/static')) {
            Dir::copyDir($unzipPath.'/upload/public/static', $this->rootPath.'public/static');
        }



        // 删除临时目录和安装包
        Dir::delDir($unzipPath);
        @unlink($file);
        clearstatcache();

        // 注册模块
        $sqlmap                = [];
        $sqlmap['name']        = $module_name;
        $sqlmap['identifier']  = $info['identifier'];
        $sqlmap['title']       = $info['title'];
        $sqlmap['intro']       = $info['intro'];
        $sqlmap['icon']        = '/static/'.$module_name.'/'.$module_name.'.png';
        $sqlmap['version']     = $info['version'];
        $sqlmap['config']      = '';

        $result = ModuleModel::create($sqlmap);
        if (!$result) {
            return '异常错误，请<a href="'.url('module/index/status/0').'">点此进入模块管理</a>页面手动安装！';
        }

        $result = action('system/module/execInstall', ['id' => $result->id, 'clear' => 1]);
        if ($result !== true) {
            return $result.'<br><br>请<a href="'.url('module/index?status=0').'" class="layui-btn layui-btn-xs layui-btn-normal">点此进入模块管理</a>页面手动安装！';
        }

        return true;
    }
}