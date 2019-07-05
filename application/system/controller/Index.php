<?php

namespace app\system\controller;

use Env;
use hisi\Dir;

/**
 * 后台首页控制器
 * @package app\system\controller
 */
class Index extends Admin
{
    /**
     * 首页
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 清理缓存
     */
    public function clear()
    {
        $path   = Env::get('runtime_path');
        $cache  = $this->request->param('cache/d', 0);
        $log    = $this->request->param('log/d', 0);
        $temp   = $this->request->param('temp/d', 0);

        if ($cache == 1) {
            Dir::delDir($path.'cache');
        }

        if ($temp == 1) {
            Dir::delDir($path.'temp');
        }

        if ($log == 1) {
            Dir::delDir($path.'log');
        }

        return $this->success('任务执行成功');
    }


}