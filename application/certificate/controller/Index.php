<?php

namespace app\certificate\controller;

use app\system\controller\Admin;
use app\certificate\model\VisitorRegister;

/**
 * 证明信控制器
 * @package app\certificate\controller
 */
class Index extends Admin
{
    /**
     * 证明信首页
     * @return string
     */
    public function index()
    {
        if($this->request->isAjax()){

            $where= $data = [];
            $page       = $this->request->param('page/d', 1);
            $limit      = $this->request->param('limit/d', 15);

            $data['data']=VisitorRegister::where($where)->page($page)->limit($limit)->select();
            $data['count']=VisitorRegister::where($where)->count('*');
            $data['code']=0;
            $data['message']='';

            return json($data);
        }
        return $this->fetch();
    }

    /**
     * 添加信息卡
     * @return mixed|string|void
     */
    public function add()
    {

        if($this->request->isPost()){
            //提交数据
            $data = $this->request->post();

            //验证
            $result = $this->validate($data,'Certificate');
            if($result !== true){
                return $this->error($result);
            }

            //数据完成
            $data['charge_name']=constant('ADMIN_NICK');  //负责人
            $data['images']='';
            $data['number']=VisitorRegister::getNumber();

            if(!VisitorRegister::create($data)){
                return $this->error('证明信添加失败');
            }

            return $this->success('证明信添加成功','index/index');

        }
        return $this->fetch('certificate_form');
    }

    /**
     * 信息卡删除
     * @return mixed|void
     */
    public function del()
    {
        $ids = $this->request->param('id/a');
        $certificate=new VisitorRegister;
        if($certificate->del($ids)){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }

    /**
     * 信息卡修改
     * @return mixed|string
     */
    public function edit()
    {
        if($this->request->isPost()){
            //提交数据
            $data = $this->request->post();

            //验证
            $result = $this->validate($data,'Certificate');
            if($result !== true){
                return $this->error($result);
            }

            //信息修改
            if (!VisitorRegister::update($data)) {
                return $this->error('修改失败');
            }
            return $this->success('修改成功');
        }

        $id = $this->request->param('id/d');

        $formData=VisitorRegister::find($id)->toArray();
        $this->assign('formData',$formData);

        return $this->fetch('certificate_form');
    }
}