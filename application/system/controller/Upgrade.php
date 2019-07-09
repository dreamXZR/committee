<?php

namespace app\system\controller;

use hisi\Cloud;
use Env;
use GuzzleHttp\Client;

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
        $this->appPath          = Env::get('app_path');
        $this->updatePath       = $this->rootPath.'backup/uppack/';
        $this->updateBackPath   = $this->rootPath.'backup/upback/';
        $this->updatePath = $this->rootPath.'backup/uppack/';
        $this->cloud = new Cloud(config('cloud.identifier'), $this->updatePath);
    }

    /**
     * 框架升级首页
     * @return string
     */
    public function index()
    {
        $client=new Client();

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
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function lists()
    {
        if ($this->request->isPost()) {
            if (!config('hs_cloud.identifier')) {
                return $this->error('请绑定云平台');
            }

            $result = $this->getVersion();
            return json($result);
        }

        //$this->assign('identifier', $this->identifier);
        //$this->assign('app_type', $this->appType);
        //$this->assign('app_version', $this->appVersion);
        return $this->fetch();
    }


}