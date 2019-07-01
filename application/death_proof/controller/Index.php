<?php

namespace app\death_proof\controller;

use app\system\controller\Admin;
use app\death_proof\model\DeathProof;
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

            $where=DeathProof::whereSql($where,request()->get());

            $data['data']=DeathProof::where($where)->page($page)->limit($limit)->select();
            $data['count']=DeathProof::where($where)->count('*');
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
            if($data['agent']){
                $result=$this->validate($data,'DeathProof.hasAgent');
            }else{
                $result = $this->validate($data,'DeathProof');
            }

            if($result !== true){
                return $this->error($result);
            }

            //数据完成
            $data['images']=isset($data['image_path']) ? \json_encode($data['image_path']) : '';
            $data['number']=DeathProof::getNumber();


            if(!DeathProof::create($data)){
                return $this->error('证明信添加失败');
            }

            return $this->success('证明信添加成功','index');

        }
        $this->assign('upload_url',url('image/upload','folder=deathProof'));
        return $this->fetch('death_proof_form');
    }

    /**
     * 信息卡删除
     * @return mixed|void
     */
    public function del()
    {
        $ids = $this->request->param('id/a');
        $certificate=new DeathProof;
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

            if(!$death_proof=DeathProof::find($data['id'])){
                return $this->error('该数据不存在');
            }

            //验证
            if($data['agent']){
                $result=$this->validate($data,'DeathProof.hasAgent');
            }else{
                $result = $this->validate($data,'DeathProof');
            }

            $data['images']=SystemUpload::getUpdateJson($data['image_path'] ?? [],$death_proof);

            //信息修改
            if (!DeathProof::update($data)) {
                return $this->error('修改失败');
            }
            return $this->success('修改成功','index');
        }

        $id = $this->request->param('id/d');

        $formData=DeathProof::find($id)->toArray();

        $this->assign('formData',$formData);
        $this->assign('upload_url',url('image/upload','folder=deathProof'));
        $this->assign('image_url',url('image/index','id='.$formData['id']));
        return $this->fetch('death_proof_form');
    }

    public function show()
    {
        $id=$this->request->param('id/d');

        $death_proof=DeathProof::findOrEmpty($id);
        $this->assign('death_proof',$death_proof);

        return $this->fetch();
    }

    public function pdf()
    {
        $id=$this->request->param('id/d');

        $death_proof=DeathProof::findOrEmpty($id);
        $this->assign('death_proof',$death_proof);

        $this->view->engine->layout(false);
        return $this->fetch('death_proof_pdf');
    }
}