<?php

namespace app\system\controller;

use hisi\Dir;
use hisi\Database as dbOper;
use think\Db;
use Env;

/**
 * 数据库控制器
 * @package app\system\controller
 */
class Database extends Admin
{
    protected $tabData;    //标签页

    protected $backupPath;    //备份路径

    protected function initialize()
    {
        parent::initialize();

        $this->backupPath = Env::get('root_path').'backup/';

        $this->tabData['menu']=[
                [
                    'title' => '备份数据库',
                    'url'   => 'system/database/index?group=export',
                ],
                [
                    'title' => '恢复数据库',
                    'url'   => 'system/database/index?group=import',
                ],
            ];
    }

    /**
     * 数据库首页
     */
    public function index($group = 'export')
    {
        if($this->request->isAjax()){
            $group = $this->request->param('group');

            if($group == 'export'){
                //列出数据库中的所有表
                $tables=Db::query('show table status');
                //var_dump($tables);
                foreach ($tables as $k=>$v){
                    $tables[$k]['id'] = $v['Name'];
                }

                $data['data'] = $tables;
                $data['code'] = 0;

            }else if($group == 'import'){
                //列出备份文件列表
                if (!is_dir($this->backupPath)) {
                    Dir::create($this->backupPath);
                }

                $flag = \FilesystemIterator::KEY_AS_FILENAME;
                $glob = new \FilesystemIterator($this->backupPath,  $flag);

                $dataList = [];

                foreach ($glob as $name => $file) {

                    if(preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql(?:\.gz)?$/', $name)) {
                        $name = sscanf($name, '%4s%2s%2s-%2s%2s%2s-%d');
                        $date = "{$name[0]}-{$name[1]}-{$name[2]}";
                        $time = "{$name[3]}:{$name[4]}:{$name[5]}";
                        $part = $name[6];

                        if(isset($dataList["{$date} {$time}"])) {

                            $info           = $dataList["{$date} {$time}"];
                            $info['part']   = max($info['part'], $part);
                            $info['size']   = $info['size'] + $file->getSize();

                        } else {

                            $info['part']   = $part;
                            $info['size']   = $file->getSize();

                        }

                        $info['time']       = "{$date} {$time}";
                        $time               = strtotime("{$date} {$time}");
                        $extension          = strtoupper($file->getExtension());
                        $info['compress']   = ($extension === 'SQL') ? '无' : $extension;
                        $info['name']       = date('Ymd-His', $time);
                        $info['id']         = $time;

                        $dataList["{$date} {$time}"] = $info;

                    }

                }

                $data['data'] = $dataList;
                $data['code'] = 0;
            }

            return json($data);
        }

        $tabData=$this->tabData;
        $tabData['current'] = url('?group='.$group);
        $this->assign('tabData', $tabData);
        $this->assign('tabType', 3);
        return $this->fetch($group);
    }

    /**
     * 备份数据库
     */
    public function backup($start = 0)
    {


        if($this->request->isPost()){

            //备份表
            $tables  = $this->request->param('id/a');

            if(count($tables) == 0){
                return $this->error('请选择您要备份的数据表');
            }

            //读取备份配置
            $config = array(
                'path'     => $this->backupPath,
                'part'     => config('databases.part_size'),
                'compress' => config('databases.compress'),
                'level'    => config('databases.compress_level'),
            );

            //检查是否有正在执行的任务
            $backupLock = "{$config['path']}backup.lock";
            if(is_file($backupLock)){
                return $this->error('检测到有一个备份任务正在执行，请等待');
            }else{
                if(!is_dir($config['path'])){
                    Dir::create($config['path']);
                }

                file_put_contents($backupLock, '正在备份中...');
            }

            //生成备份文件信息
            $file = [
                'name' => date('Ymd-His', $this->request->time()),
                'part' => 1,
            ];

            // 创建备份文件
            $database = new dbOper($file, $config);

            if($database->create() !== false) {

                // 备份指定表
                foreach ($tables as $table) {
                    $start = $database->backup($table, $start);
                    while (0 !== $start) {
                        if (false === $start) {
                            return $this->error('备份出错');
                        }
                        $start = $database->backup($table, $start[0]);
                    }
                }

                // 备份完成，删除锁定文件
                unlink($backupLock);


            }
            return $this->success('备份完成');

        }
        return $this->error('备份出错');
    }

    /**
     * 优化数据库
     * @param string $id  数据表名
     */
    public function optimize()
    {
       $tables=$this->request->param('id/a');

       if(empty($tables)){
           return $this->enroll('请选择您要优化的数据表');
       }

        $tables = implode('`,`', $tables);
       $res=Db::query("OPTIMIZE TABLE `{$tables}`");
        if ($res) {
            return $this->success('数据表优化完成');
        }

        return $this->error('数据表优化失败');
    }

    public function repair()
    {
        $tables=$this->request->param('id/a');

        if(empty($tables)){
            return $this->enroll('请选择您要修复的数据表');
        }

        $tables = implode('`,`', $tables);
        $res = Db::query("REPAIR TABLE `{$tables}`");

        if ($res) {
            return $this->success('数据表修复完成');
        }

        return $this->error('数据表修复失败');
    }

    public function backupDel()
    {
        $id=$this->request->param('id/d');

        if (empty($id)) {
            return $this->error('请选择您要删除的备份文件');
        }

        return $this->success('备份文件删除成功');
    }

}