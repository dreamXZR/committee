<?php

namespace app\system\controller;

use hisi\Cloud;
use Env;
use app\system\model\SystemModule as ModuleModel;
use hisi\Dir;
use hisi\PclZip;
use think\Db;

/**
 * 在线升级控制器
 * @package app\system\controller
 */
class Upgrade extends Admin
{
    public $appType = 'system';

    public $identifier = 0;

    public $appVersion = '';

    protected function initialize()
    {
        parent::initialize();

        $this->rootPath = Env::get('root_path');
        $this->appPath          = Env::get('app_path');
        $this->updatePath       = $this->rootPath.'backup/uppack/';
        $this->updateBackPath   = $this->rootPath.'backup/upback/';
        $this->appType          = $this->request->param('app_type/s', 'system');
        $this->identifier       = $this->request->param('identifier/s', 'system');
        $this->cloud = new Cloud(config('cloud.identifier'), $this->updatePath);
        $this->cacheUpgradeList = 'upgrade_version_list'.$this->identifier;

        $map = [];
        $map[] = ['identifier', '=', $this->identifier];
        $map[] = ['status', '<>', 1];

        switch ($this->appType){
            case 'module':
                $module = ModuleModel::where($map)->find();
                $this->appVersion   = $module->version;
                $this->module_name  = $module->name;
                break;
            case 'system':
                $this->appVersion = config('committee.version');
                break;
        }

        if (!$this->appVersion) {
            return $this->error('未安装模块禁止更新');
        }
    }

    /**
     * 框架升级首页
     * @return string
     */
    public function index()
    {
        if($this->request->isPost()){
            $data=[
                'email'    => $this->request->post('account/s'),
                'password' => $this->request->post('password/s')
            ];

            $response=$this->cloud->data($data)->type('POST')->api('bind');
            $res_data=\json_decode($response->getBody()->getContents(),true);

            if(isset($res_data['code']) && $res_data['code'] == 1){
                $file = $this->rootPath.'config/cloud.php';
                $str = "<?php\n// 请妥善保管此文件，谨防泄漏\nreturn ['identifier' => '".$res_data['data']."'];\n";

                if (file_exists($file)) {
                    unlink($file);
                }

                file_put_contents($file, $str);

                if (!file_exists($file)) {
                    return $this->error('config/cloud.php写入失败');
                }

                return $this->success('恭喜您，已成功绑定云平台账号');
            }else{
                return $this->error($res_data['msg']);
            }


        }

        $this->assign('api_url', $this->cloud->apiUrl());
        return $this->fetch();
    }

    /**
     * 升级文件列表
     * @return mixed
     */
    public function lists()
    {
        if ($this->request->isPost()) {
            if (!config('cloud.identifier')) {
                return $this->error('请绑定云平台');
            }

            $result = $this->getVersion();
            return json($result);
        }

        $this->assign('identifier', $this->identifier);
        $this->assign('app_type', $this->appType);
        $this->assign('app_version', $this->appVersion);
        return $this->fetch();
    }

    /**
     * 获取升级版本
     */
    public function getVersion()
    {
        $cache = cache($this->cacheUpgradeList);

        if(isset($cache['data']) && !empty($cache['data'])){
            return $cache;
        }

        $response=$this->cloud->data([
            'version' => $this->appVersion,
            'app_identifier' => $this->identifier,
            'app_type' => $this->appType
        ])->type('GET')->api('getVersions');

        $cloudData=\json_decode($response->getBody()->getContents(),true);
        return $cloudData;
    }

    public function download($version = '')
    {
        if (!$this->request->isPost()) {
            return $this->error('参数传递错误');
        }

        if (empty($version)) {
            return $this->error('参数传递错误');
        }

        if (!is_dir($this->updatePath)) {
            Dir::create($this->updatePath, 0755);
        }

        $versions = $this->getVersion();

        //检查是否符合升级条件
        if($this->downloadCheck($versions['data'][$version]) == false){
            return $this->error($this->error);
        }

        $file = '';

        foreach ($versions['data'] as $k=>$v){
            if(version_compare($k,$version,'>=')){
                if (version_compare($k, $version, '=')) {

                    //下载升级包
                    $file = $this->cloud->data([
                        'file_path' => $v['file_path'],
                    ])->down();

                }
                break;
            }else{
                //前置版本下载
                $file = $this->cloud->data([
                    'file_path' => $v['file_path'],
                ])->down();

                if ($file === false) {
                    $this->clearCache($file);
                    return $this->error('前置版本 '.$k.' 升级失败');

                } else {
                    if (self::_install($file, $k, $this->appType,false) === false) {
                        $this->clearCache($file);
                        return $this->error($this->error);
                    }
                }
            }
        }

        if ($file === false || empty($file)) {
            $this->clearCache($file);
            return $this->error('获取升级包失败');
        }

        return $this->success(basename($file).'&&'.$this->identifier);
    }

    /**
     * 检查是否符合升级要求+ 加锁
     * @param $check条件
     */
    public function downloadCheck($check)
    {
        $current_frame_version=config('committee.version');

        if(version_compare($current_frame_version,$check['frame_version'],'<')){
            $this->error='框架版本必须大于等于'.$check['frame_version'];
            return false;
        }

        //放置锁文件防止重复操作
        $lock = $this->updatePath.$this->identifier.'upgrade.lock';
        if (!is_file($lock)) {
            file_put_contents($lock, time());
        } else {
            $this->error='升级任务执行中，请手动删除此文件后重试！<br>文件地址：/uppack/'.$this->identifier.'upgrade.lock';
            return false;
        }
        return true;

    }

    /**
     * 安装下载的包
     * @return mixed
     */
    public function install($file = '', $version = '')
    {
        if (!$this->request->isPost()) {
            return $this->error('参数传递错误');
        }
        $file = $this->updatePath.$file;
        if (!file_exists($file)) {
            $this->clearCache($file);
            return $this->error($version.' 升级包异常，请重新升级');
        }

        if (self::_install($file, $version, $this->appType) === false) {
            $this->clearCache($file);
            return $this->error($this->error);
        }

        $moduleInfo = include $this->appPath.$this->module_name.'/info.php';

        ModuleModel::where('identifier', $this->identifier)->setField([
            'version'=>$version,
            'identifier'=>$moduleInfo['identifier']
        ]);

        return $this->success('升级包安装成功');
    }

    /**
     * 执行安装
     * @return bool
     */
    private function _install($file = '', $version = '', $app_type = 'system',$dir_uppack_stauts=true)
    {
        if (empty($file) || empty($version)) {
            $this->error = '参数传递错误';
            return false;
        }
        switch ($app_type) {
            case 'module':// 模块升级安装
                return self::_moduleInstall($file, $version,$dir_uppack_stauts);
                break;

            case 'system':// 系统升级安装
                return self::_systemInstall($file, $version);
                break;
        }
        clearstatcache();
    }

    /**
     * 系统升级
     * @return bool
     */
    private function _systemInstall($file, $version)
    {
        $_version = cache($this->cacheUpgradeList);
        $_version = $_version['data'];

        if (!is_dir($this->updateBackPath)) {
            Dir::create($this->updateBackPath);
        }
        $decomPath = $this->updatePath.basename($file,".zip");
        if (!is_dir($decomPath)) {
            Dir::create($decomPath, 0777);
        }
        // 解压升级包
        $archive = new PclZip();
        die;
        $archive->PclZip($file);
        if(!$archive->extract(PCLZIP_OPT_PATH, $decomPath, PCLZIP_OPT_REPLACE_NEWER)) {
            $this->error = '升级失败，请开启[/backup/uppack]文件夹权限';
            return false;
        }
        // 备份需要升级的旧版本
        $upInfo = include_once $decomPath.'/upgrade.php';
        $backPath = $this->updateBackPath.config('hisiphp.version').'/';
        if (!is_dir($backPath)) {
            Dir::create($backPath, 0777);
        }
        $layout = '';
        array_push($upInfo['update'], '/version.php');
        //备份旧文件
        foreach ($upInfo['update'] as $k => $v) {
            $v = trim($v, '/');
            $_dir = $backPath.dirname($v).'/';
            if (!is_dir($_dir)) {
                Dir::create($_dir, 0777);
            }
            if (basename($v) == 'layout.html') {
                $layout = $this->appPath.'system/view/layout.html';
            }
            if (is_file($this->rootPath.$v)) {
                @copy($this->rootPath.$v, $_dir.basename($v));
            }
        }

        // 根据升级补丁删除文件
        if (isset($upInfo['delete'])) {
            foreach ($upInfo['delete'] as $k => $v) {
                $v = trim($v, '/');
                if (is_file($this->rootPath.$v)) {
                    @unlink($this->rootPath.$v);
                }
            }
        }

        // 更新升级文件
        Dir::copyDir($decomPath.'/upload', $this->rootPath);

        // 同步更新扩展模块下的layout.html TODO
        // 导入SQL
        $sqlFile = realpath($decomPath.'/database.sql');
        if (is_file($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            $sqlList = parse_sql($sql, 0, ['hisiphp_' => config('database.prefix')]);
            if ($sqlList) {
                $sqlList = array_filter($sqlList);
                foreach ($sqlList as $v) {
                    try {
                        Db::execute($v);
                    } catch(\Exception $e) {
                        $this->error = 'SQL更新失败';
                        return false;
                    }
                }
            }
        }
        $this->clearCache('', $version);
        return true;
    }

    /**
     * 模块升级
     * @return bool
     */
    private function _moduleInstall($file, $version,$dir_uppack_stauts)
    {
        $module     = ModuleModel::where('identifier', $this->identifier)->find();
        $backPath   = $this->updateBackPath.'module/'.$module->name.'/'.$module->version.'/';

        if (!is_dir($backPath)) {
            Dir::create($backPath);
        }

        $decomPath = $this->updatePath.basename($file,".zip");
        if (!is_dir($decomPath)) {
            Dir::create($decomPath, 0777);
        }

        // 解压升级包
        $archive = new PclZip();
        $archive->PclZip($file);
        if(!$archive->extract(PCLZIP_OPT_PATH, $decomPath, PCLZIP_OPT_REPLACE_NEWER)) {
            $this->error = '升级失败，请开启[/backup/uppack]文件夹权限';
            return false;
        }

        // 获取本次升级信息
        if (!is_file($decomPath.'/upgrade.php')) {
            $this->error = '升级失败，升级包文件不完整';
            return false;
        }

        $upInfo = include_once $decomPath.'/upgrade.php';

        //备份需要升级的旧版本
        if (isset($upInfo['update'])) {
            foreach ($upInfo['update'] as $k => $v) {
                $v  = trim($v, '/');
                $dir = $backPath.dirname($v).'/';
                if (!is_dir($dir)) {
                    Dir::create($dir, 0777);
                }
                if (is_file($this->rootPath.$v)) {
                    @copy($this->rootPath.$v, $dir.basename($v));
                }
            }
        }
        // 根据升级补丁删除文件
        if (isset($upInfo['delete'])) {
            foreach ($upInfo['delete'] as $k => $v) {
                $v = trim($v, '/');
                // 锁定删除文件范围
                if ( ( substr($v, 0, strlen('application/'.$module->name)) == 'application/'.$module->name ||
                        substr($v, 0, strlen('public/static/'.$module->name)) == 'public/static/'.$module->name ) && strpos($v, '..') === false) {
                    if (is_file($this->rootPath.$v)) {
                        @unlink($this->rootPath.$v);
                    }
                }
            }
        }

        //根据升级文件清单升级
        foreach ($upInfo['update'] as $k => $v) {
            $v = trim($v, '/');
            $dir = $this->rootPath.dirname($v).'/';
            if (!is_dir($dir)) {
                Dir::create($dir, 0777);
            }

            if (is_file($decomPath.'/upload/'.$v)) {
                @copy($decomPath.'/upload/'.$v, $dir.basename($v));
            }
        }

        // 读取模块info
        if (!is_file($this->appPath.$module->name.'/info.php')) {
            $this->error = $module->name.'模块配置文件[info.php]丢失';
            return false;
        }

        $moduleInfo = include_once $this->appPath.$module->name.'/info.php';
        if (!isset($moduleInfo['db_prefix']) || empty($moduleInfo['db_prefix'])) {
            $moduleInfo['db_prefix'] = 'db_';
        }

        // 导入SQL
        $sqlFile = realpath($decomPath.'/database.sql');
        if (is_file($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            $sqlList = parse_sql($sql, 0, [$moduleInfo['db_prefix'] => config('database.prefix')]);
            if ($sqlList) {
                $sqlList = array_filter($sqlList);
                foreach ($sqlList as $v) {
                    try {
                        Db::execute($v);
                    } catch(\Exception $e) {
                        $this->error = 'SQL更新失败';
                        return false;
                    }
                }
            }
        }

        // 更新模块版本信息
        ModuleModel::where('identifier', $this->identifier)->setField('version', $version);

        $this->clearCache('', $version,$dir_uppack_stauts);
        //$this->identifier=$moduleInfo['identifier'];
        return true;
    }

    /**
     * 清理升级包、升级锁、升级版本列表、升级解压文件
     * @param string $file 升级包文件路径
     * @param string $version 当前升级版本号
     */
    private function clearCache($file = '', $version = '',$dir_uppack_status=true)
    {
        if (is_file($this->updatePath.$this->identifier.'upgrade.lock')) {
            unlink($this->updatePath.$this->identifier.'upgrade.lock');
        }

        if (is_file($file)) {
            unlink($file);
        }

        // 在升级缓存列表里面清除已升级的版本信息
        if ($version) {
            $versionCache = cache($this->cacheUpgradeList);
            unset($versionCache['data'][$version]);
            cache($this->cacheUpgradeList, $versionCache, 3600);
        }

        // 删除升级解压文件
        if ($dir_uppack_status == true && is_dir($this->updatePath)) {
            Dir::delDir($this->updatePath);
        }

        // 删除系统缓存
        Dir::delDir(Env::get('runtime_path').'cache');
        Dir::delDir(Env::get('runtime_path').'temp');
    }


}