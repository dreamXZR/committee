<?php

namespace app\resident\controller;

use app\resident\model\Resident;
use app\system\controller\Admin;
use app\resident\model\Nation;
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

            //筛选
            $query=Resident::selectQuery($this->request->get());

            $data['data']=$query->with('info')->page($page)->limit($limit)->select();
            $data['count']=$query->count('*');
            $data['code']=0;
            $data['message']='';

            return json($data);
        }

        $nation=Nation::all();
        $this->assign('nation',$nation);
        return $this->fetch();
    }

    public function excel()
    {
        $excel=new ResidentExcel();
        $excel->export();

    }
}