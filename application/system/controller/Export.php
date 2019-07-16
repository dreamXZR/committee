<?php

namespace app\system\controller;


use app\common\controller\Common;
use think\App;
use hisi\Dir;
use Env;
use hisi\PclZip;

class Export extends Common
{
    public $export_dir;

    public $export_dir_path;

    public $zip_name='';

    public function __construct(App $app = null)
    {
        parent::__construct($app);

        $this->rootPath = Env::get('root_path');

        $this->export_dir=$this->rootPath.'runtime/export/';
    }

    /**
     * 设置相关路径
     */
    public function setPath($array=[])
    {
        if($array['export_dir_name']){
            $this->export_dir_path=$this->export_dir.$array['export_dir_name'].'/';
        }
        return $this;
    }

    public function index_init()
    {
        $export_lock=$this->get_export_lock();
        if(file_exists($export_lock)){
            $status_array=explode('&&',file_get_contents($export_lock));
            if($status_array[0]==='success'){
                @unlink($status_array[1]);
            }elseif ($status_array[0]==='error'){
                Dir::delDir($this->export_dir_path);
            }
            @unlink($export_lock);
        }
    }

    public function create_export_dir()
    {
        $user = session('admin_user');

        //生成下载目录
        if(!is_dir($this->export_dir_path)){
            Dir::create($this->export_dir_path,0777);
        }

        //生成锁文件
        $this->export_lock=$this->export_dir.'/certificate_'.$user['uid'].'.lock';

        if(!file_exists($this->export_lock)){
            file_put_contents($this->export_lock,'error');
        }
    }

    /**
     * 生成zip文件
     * @return bool
     */
    public function create_zip()
    {
        $this->zip_name=time().'.zip';

        $archive = new PclZip($this->zip_name);
        $v_list = $archive->create($this->export_dir_path,PCLZIP_OPT_REMOVE_PATH,$this->export_dir_path);
        if ($v_list == 0) {

            return ['status'=>false,'msg'=>'压缩文件生成失败'];
        }
        $this->clear();

        return ['status'=>true,'msg'=>'压缩文件生成成功','zip_name'=>$this->zip_name];
    }

    public function clear()
    {
        if(is_dir($this->export_dir_path)){
            Dir::delDir($this->export_dir_path);
        }

        $user = session('admin_user');
        $export_lock=$this->export_dir.'/certificate_'.$user['uid'].'.lock';

        if($this->zip_name){

            file_put_contents($export_lock,'success&&'.$this->rootPath.'public/'.$this->zip_name);
        }else{
            file_put_contents($export_lock,'error');
        }

    }

    public function get_export_dir_path()
    {
        return $this->export_dir_path;
    }

    public function get_export_lock()
    {
        $user = session('admin_user');

        return $this->export_dir.'/certificate_'.$user['uid'].'.lock';
    }
}