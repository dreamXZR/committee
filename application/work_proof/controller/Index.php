<?php

namespace app\work_proof\controller;

use app\system\controller\Admin;
use app\work_proof\model\WorkProof;

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

            $where=WorkProof::whereSql($where,request()->get());

            $data['data']=WorkProof::where($where)->page($page)->limit($limit)->select();
            $data['count']=WorkProof::where($where)->count('*');
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
            $result = $this->validate($data,'WorkProof');
            if($result !== true){
                return $this->error($result);
            }

            //数据完成
            $data['images']=$data['image_path'] ?? [];


            if(!WorkProof::create($data)){
                return $this->error('证明信添加失败');
            }

            return $this->success('证明信添加成功','index/index');

        }
        $this->assign('upload_url',url('image/upload','folder=workProof'));
        return $this->fetch('work_proof_form');
    }

    /**
     * 信息卡删除
     * @return mixed|void
     */
    public function del()
    {
        $ids = $this->request->param('id/a');
        $certificate=new WorkProof;
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
            $result = $this->validate($data,'WorkProof');
            if($result !== true){
                return $this->error($result);
            }

            $data['images']=$data['image_path'] ?? [];

            //信息修改
            if (!WorkProof::update($data)) {
                return $this->error('修改失败');
            }
            return $this->success('修改成功');
        }

        $id = $this->request->param('id/d');

        $formData=WorkProof::find($id)->toArray();

        $this->assign('formData',$formData);
        $this->assign('upload_url',url('image/upload','folder=workProof'));
        $this->assign('image_url',url('image/index','id='.$formData['id']));
        return $this->fetch('work_proof_form');
    }
}