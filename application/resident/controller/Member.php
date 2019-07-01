<?php

namespace app\resident\controller;

use app\resident\model\Resident;
use app\system\controller\Admin;
use app\system\controller\Excel;
use app\resident\controller\ResidentExcel;

class Member extends Admin
{
    public function index()
    {
        if($this->request->isAjax()){
            $where= $data = [];
            $page       = $this->request->param('page/d', 1);
            $limit      = $this->request->param('limit/d', 15);

            //默认筛选
            $where[]=['is_replace','=',0];

            $data['data']=Resident::where($where)->with('info')->page($page)->limit($limit)->select();
            $data['count']=Resident::where($where)->count('*');
            $data['code']=0;
            $data['message']='';

            return json($data);
        }
        return $this->fetch();
    }

    public function excel()
    {
        $excel=new ResidentExcel();
        $excel->export();

    }
}