<?php

namespace app\system\controller;

use hisi\Cloud;
use Env;

/**
 * 在线升级控制器
 * @package app\system\controller
 */
class Upgrade extends Admin
{
    protected function initialize()
    {
        parent::initialize();

        $this->rootPath = Env::get('root_path');
        $this->updatePath = $this->rootPath.'backup/uppack/';
        $this->cloud = new Cloud(config('hs_cloud.identifier'), $this->updatePath);
    }

    /**
     * 框架升级首页
     * @return string
     */
    public function index()
    {

        $this->assign('api_url', $this->cloud->apiUrl());
        return $this->fetch();
    }
}