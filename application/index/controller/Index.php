<?php
namespace app\index\controller;


use think\Controller;

class Index extends Controller
{
    public function index()
    {
        //var_dump(config());
        $this->redirect('system/login/index');
    }


}
