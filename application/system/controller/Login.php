<?php

namespace app\system\controller;

use app\common\controller\Common;
use app\system\model\SystemUser;

/**
 * 后台公共控制器
 * @package app\system\controller
 */
class Login extends Common
{
    public function index()
    {
        $model=new SystemUser;
        $loginError = (int)session('admin_login_error');

        if($this->request->isPost()){
            $username   = $this->request->post('username/s');
            $password   = $this->request->post('password/s');
            $data       = [];

            if(!$model->login($username,$password)){
                $loginError = $loginError+1;
                session('admin_login_error', $loginError);
                $data['token'] = $this->request->token();

                return $this->error($model->getError(), url('index'), $data);
            }

            session('admin_login_error', 0);

            return $this->success('登陆成功，页面跳转中...', url('index/index'));
        }

        if($model->isLogin()){
            $this->redirect(url('index/index', '', true, true));
        }

        $this->assign('loginError', $loginError);
        return $this->fetch();

    }

    public function logout()
    {
        model('SystemUser')->logout();
        $this->redirect('login/index');
    }
}