<?php

namespace app\resident\controller;

use app\resident\model\Handicapped;
use app\resident\model\Nation;
use app\resident\model\Resident;
use app\system\controller\Admin;
use app\resident\model\Info as infoModel;

class Info extends Admin
{
    /**
     * 居民信息卡首页
     * @return string|\think\response\Json
     */
    public function index()
    {
        if($this->request->isAjax()){
            $where= $data = [];
            $page       = $this->request->param('page/d', 1);
            $limit      = $this->request->param('limit/d', 15);


            //筛选
            $query=infoModel::selectQuery($this->request->get());

            $data['data']=$query->order('housing_estate,building,door,no')->page($page)->limit($limit)
                                    ->field(['hisi_info.id','housing_estate','building','door','no'])
                                    ->append(['all_present_address'])->select();

            $data['count']=$query->count('*');
            $data['code']=0;
            $data['message']='';

            return json($data);

        }
        return $this->fetch();
    }

    /**
     * 信息卡提交
     * @return mixed|string|void
     */
    public function add()
    {
        if($this->request->isPost()){
            //提交数据
            $data = $this->request->post();

            //验证
            $result = $this->validate($data,'Info');
            if($result !== true){
                return $this->error($result);
            }

            //提交数据
            $insert=infoModel::transactionInsert(
                $this->request->except(['handicappeds','residents']),
                $this->request->residents,
                $this->request->handicappeds
            );
            if($insert){
                return $this->success('数据提交成功','info/index');
            }else{
                return $this->error('数据提交失败');
            }
        }
        $nation=Nation::all();
        $this->assign('nation',$nation);
        return $this->fetch('info_form');
    }

    /**
     * 信息卡删除
     * @return mixed|void
     */
    public function del()
    {
        $ids = $this->request->param('id/a');
        $info=new infoModel();

        if($info->del($ids)){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }

    /**
     * 修改数据
     * @return mixed|string|void
     */
    public function edit()
    {
        if($this->request->isPost()){
            //提交数据
            $data = $this->request->post();

            //验证
            $result = $this->validate($data,'Info');
            if($result !== true){
                return $this->error($result);
            }

            //更新数据
            $insert=infoModel::transactionUpdate(
                $this->request->except(['handicappeds','residents']),
                $this->request->residents,
                $this->request->handicappeds
            );
            if($insert){
                return $this->success('数据更新成功','info/index');
            }else{
                return $this->error('数据更新失败');
            }
        }
        $info_id = $this->request->param('id/d');
        $nation=Nation::all();
        $this->assign('nation',$nation);
        $this->assign('info_id',$info_id);
        return $this->fetch('info_edit_form');
    }

    public function getInformation()
    {
        $info_id = $this->request->param('info_id/d');
        $info=infoModel::find($info_id);
        $residents=$info->residents;
        $handicappeds=$info->handicappeds;

        return json([
            'information'=>$info,
            'residents'=>$residents,
            'handicappeds'=>$handicappeds
        ]);
    }

    /**
     * 删除信息卡内的单条居民信息
     */
    public function delSingleInfo()
    {
        $type = $this->request->param('type');
        $id   = $this->request->param('id');

        if($type=='resident'){
            Resident::del($id);
        }else if($type=='handicapped'){

            Handicapped::del($id);
        }

    }

    public function show()
    {
        $id=$this->request->param('id/d');

        $info=infoModel::with(['residents','handicappeds'])->findOrEmpty($id);
        $handicappeds=$info->handicappeds;
        $residents=$info->residents;

        $tabData['menu']=[
            [
                'title' => '居民信息',
                'url'   => url('show',['id'=>$info->id]),
            ],
            [
                'title' => '历史记录',
                'url'   => url('history',['id'=>$info->id]),
            ],
        ];
        $tabData['current'] = url('show',['id'=>$info->id]);

        $this->assign('tabData', $tabData);
        $this->assign('tabType', 3);
        $this->assign('info',$info);
        $this->assign('residents',$residents);
        $this->assign('handicappeds',$handicappeds);

        return $this->fetch();
    }

    public function history()
    {
        $id=$this->request->param('id/d');
        $info=infoModel::with(['residents','handicappeds'])->findOrEmpty($id);

        $tabData['menu']=[
            [
                'title' => '居民信息',
                'url'   => url('show',['id'=>$info->id]),
            ],
            [
                'title' => '历史记录',
                'url'   => url('history',['id'=>$info->id]),
            ],
        ];
        $tabData['current'] = url('history',['id'=>$info->id]);

        $this->assign('tabData', $tabData);
        $this->assign('tabType', 3);
        return $this->fetch();
    }

    public function pdf()
    {
        $id=$this->request->param('id/d');

        $info=infoModel::with(['residents','handicappeds'])->findOrEmpty($id);
        $handicappeds=$info->handicappeds;
        $residents=$info->residents;
        $fill=[
            10-count($residents),
            4-count($handicappeds)
        ];

        $this->assign('info',$info);
        $this->assign('residents',$residents);
        $this->assign('handicappeds',$handicappeds);
        $this->assign('fill',$fill);

        $this->view->engine->layout(false);
        return $this->fetch('info_pdf');
    }
}