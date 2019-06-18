<?php

namespace app\visitor\controller;

use app\system\controller\Admin;
use app\visitor\model\VisitorRegister;

/**
 * 来访登记控制器
 * @package app\certificate\controller
 */
class Index extends Admin
{
    protected $hisiModel = 'VisitorRegister';

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
            [
                'title' => '已完成',
                'url'   => 'visitor/index/index?is_finish=1',
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

            $data['data']=VisitorRegister::where($where)->page($page)->limit($limit)->select();
            $data['count']=VisitorRegister::where($where)->count('*');
            $data['code']=0;
            $data['message']='';

            return json($data);
        }

        $tabData=$this->tabData;
        $tabData['current'] = url('?is_finish='.$is_finish);
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
            $result = $this->validate($data,'Visitor');
            if($result !== true){
                return $this->error($result);
            }

            //数据完成
            $data['charge_name']=constant('ADMIN_NICK');  //负责人
            $data['number']=VisitorRegister::getNumber();

            if(!VisitorRegister::create($data)){
                return $this->error('证明信添加失败');
            }

            return $this->success('证明信添加成功','index/index');

        }
        return $this->fetch('visitor_form');
    }

    /**
     * 登记信息删除
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
     * 登记信息修改
     * @return mixed|string
     */
    public function edit()
    {
        if($this->request->isPost()){
            //提交数据
            $data = $this->request->post();

            //验证
            $result = $this->validate($data,'Visitor');
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

        return $this->fetch('visitor_form');
    }
}