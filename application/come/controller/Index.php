<?php

namespace app\come\controller;

use app\system\controller\Admin;
use app\come\model\ComeRegister;
use app\common\controller\SystemUpload;
use app\come\validate\Come;

/**
 * 来访登记控制器
 * @package app\certificate\controller
 */
class Index extends Admin
{
    protected $hisiModel = 'ComeRegister';

    protected $tabData;

    public function initialize()
    {
        parent::initialize();

        $this->tabData['menu']=[
            [
                'title' => '全部',
                'url'   => 'visitor/index/index?is_finish=all',
            ],
            [
                'title' => '未完成',
                'url'   => 'visitor/index/index?is_finish=0',
            ],

        ];
    }

    /**
     * 来访登记首页
     * @return string
     */
    public function index($is_finish='all')
    {
        if($this->request->isAjax()){

            $where= $data = [];
            $page       = $this->request->param('page/d', 1);
            $limit      = $this->request->param('limit/d', 15);
            $where=ComeRegister::whereSql($where,request()->get());
            $data['data']=ComeRegister::where($where)->page($page)->limit($limit)->select();
            $data['count']=ComeRegister::where($where)->count('*');
            $data['code']=0;
            $data['message']='';

            return json($data);
        }

        $tabData=$this->tabData;
        $tabData['current'] = url('?is_finish='.$is_finish);

        $is_finish_count=ComeRegister::where('is_finish',0)->count('*');
        $tabData['menu'][1]['is_finish_count']=$is_finish_count;

        $this->assign('tabData', $tabData);
        $this->assign('tabType', 3);
        return $this->fetch();
    }

    /**
     * 添加登记信息
     * @return mixed|string|void
     */
    public function add()
    {

        if($this->request->isPost()){
            //提交数据
            $data = $this->request->post();

            //验证
            $result = $this->validate($data,'Come');
            if($result !== true){
                return $this->error($result);
            }

            //数据完成
            $data['charge_name']=constant('ADMIN_NICK');  //负责人
            $data['images']=isset($data['image_path']) ? \json_encode($data['image_path']) : '';
            $data['number']=ComeRegister::getNumber();

            if(!ComeRegister::create($data)){
                return $this->error('证明信添加失败');
            }

            return $this->success('证明信添加成功','index');

        }
        $this->assign('upload_url',url('image/upload','folder=visitor'));
        return $this->fetch('come_form');
    }

    /**
     * 登记信息删除
     * @return mixed|void
     */
    public function del()
    {
        $ids = $this->request->param('id/a');
        $certificate=new ComeRegister;
        if($certificate->del($ids)){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }

    /**
     * 登记信息修改
     * @return mixed|string
     */
    public function edit()
    {
        if($this->request->isPost()){
            //提交数据
            $data = $this->request->post();

            if(!$visitor=ComeRegister::find($data['id'])){
                return $this->error('该数据不存在');
            }

            //验证
            $result = $this->validate($data,'Come');
            if($result !== true){
                return $this->error($result);
            }

            //图片数据
            $data['images']=SystemUpload::getUpdateJson($data['image_path'] ?? [],$visitor);

            //信息修改
            if (!ComeRegister::update($data)) {
                return $this->error('修改失败');
            }
            return $this->success('修改成功','index');
        }

        $id = $this->request->param('id/d');

        $formData=ComeRegister::find($id)->toArray();

        $this->assign('formData',$formData);
        $this->assign('upload_url',url('image/upload','folder=visitor'));
        $this->assign('image_url',url('image/index','id='.$formData['id']));
        return $this->fetch('come_form');
    }

    public function show()
    {
        $id=$this->request->param('id/d');

        $come=ComeRegister::findOrEmpty($id);
        $this->assign('come',$come);

        return $this->fetch();
    }

    public function pdf()
    {
        $id=$this->request->param('id/d');

        $come=ComeRegister::findOrEmpty($id);
        $this->assign('come',$come);

        $this->view->engine->layout(false);
        return $this->fetch('come_pdf');
    }
}