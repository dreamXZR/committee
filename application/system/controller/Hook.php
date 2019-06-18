<?php

namespace app\system\controller;

use app\system\model\SystemHook as HookModel;

/**
 * 钩子
 * @package app\system\controller
 */
class Hook extends Admin
{
    /**
     * 钩子页面首页
     * @return string|\think\response\Json
     */
    public function index()
    {
        if($this->request->isAjax()){
            $map        = $data = [];
            $page       = $this->request->param('page/d', 1);
            $limit      = $this->request->param('limit/d', 15);
            $keyword    = $this->request->param('keyword');

            if ($keyword) {
                $map[] = ['name', 'like', "%{$keyword}%"];
            }

            $data['data']=HookModel::where($map)->page($page)->limit($limit)->select();
            $data['count']=HookModel::where($map)->count('*');
            $data['code']=0;

            return json($data);
        }

        return $this->fetch();
    }

    /**
     * @return mixed|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function status()
    {
        $id         = $this->request->param('id/a');
        $val        = $this->request->param('val/d');
        $map        = [];
        $map['id']  = $id;
        $rows       = HookModel::where($map)->field('id,system')->select();

        foreach ($rows as $v) {

            // 排除系统钩子
            if ($v['system'] == 1) {
                return $this->error('禁止操作系统钩子');
            }

        }

        $res = HookModel::where($map)->setField('status', $val);;
        if ($res === false) {
            return $this->error('操作失败');
        }

        return $this->success('操作成功');
    }
}