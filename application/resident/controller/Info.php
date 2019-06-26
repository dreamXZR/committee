<?php

namespace app\resident\controller;

use app\resident\model\Handicapped;
use app\resident\model\Nation;
use app\resident\model\Resident;
use app\system\controller\Admin;
use app\resident\model\Info as infoModel;
use think\Db;

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

            //默认筛选
            $where[]=['replace_time','=',0];
            $data['data']=infoModel::where($where)->page($page)->limit($limit)
                                    ->field(['id','housing_estate','building','door','no'])
                                    ->append(['all_present_address'])->select();
            $data['count']=infoModel::where($where)->count('*');
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
}