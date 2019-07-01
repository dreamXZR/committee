<?php

namespace app\certificate\controller;

use app\system\controller\Admin;
use app\certificate\model\Certificate;
use app\common\controller\SystemUpload;

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

            $where=Certificate::whereSql($where,request()->get());

            $data['data']=Certificate::where($where)->page($page)->limit($limit)->select();
            $data['count']=Certificate::where($where)->count('*');
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
            $data['images']=isset($data['image_path']) ? \json_encode($data['image_path']) : '';
            $data['number']=Certificate::getNumber();

            if(!Certificate::create($data)){
                return $this->error('证明信添加失败');
            }

            return $this->success('证明信添加成功','index');

        }
        $this->assign('upload_url',url('image/upload','folder=certificate'));
        return $this->fetch('certificate_form');
    }

    /**
     * 信息卡删除
     * @return mixed|void
     */
    public function del()
    {
        $ids = $this->request->param('id/a');
        $certificate=new Certificate;
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

            if(!$certificate=Certificate::find($data['id'])){
                return $this->error('该数据不存在');
            }

            //验证
            $result = $this->validate($data,'Certificate');
            if($result !== true){
                return $this->error($result);
            }

            //图片数据
            $data['images']=SystemUpload::getUpdateJson($data['image_path'] ?? [],$certificate);

            //信息修改
            if (!Certificate::update($data)) {
                return $this->error('修改失败');
            }
            return $this->success('修改成功','index');
        }

        $id = $this->request->param('id/d');

        $formData=Certificate::find($id)->toArray();

        $this->assign('formData',$formData);
        $this->assign('upload_url',url('image/upload','folder=certificate'));
        $this->assign('image_url',url('image/index','id='.$formData['id']));
        return $this->fetch('certificate_form');
    }



    public function show()
    {
        $id=$this->request->param('id/d');

        $certificate=Certificate::findOrEmpty($id);
        $this->assign('certificate',$certificate);

        return $this->fetch();
    }

    public function pdf()
    {
        $id=$this->request->param('id/d');

        $certificate=Certificate::findOrEmpty($id);
        $this->assign('certificate',$certificate);

        $this->view->engine->layout(false);
        return $this->fetch('certificate_pdf');
    }
}