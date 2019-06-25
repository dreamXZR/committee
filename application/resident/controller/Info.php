<?php

namespace app\resident\controller;

use app\resident\model\Nation;
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

            //默认筛选
            $where[]=['replace_time','neq',0];

            $data['data']=infoModel::where($where)->page($page)->limit($limit)->select();
            $data['count']=infoModel::where($where)->count('*');
            $data['code']=0;
            $data['message']='';

            return json($data);

        }
        return $this->fetch();
    }

    public function add()
    {
        $nation=Nation::all();
        $this->assign('nation',$nation);
        return $this->fetch('info_form');
    }
}