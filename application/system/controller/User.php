<?php

namespace app\system\controller;

use app\system\model\SystemUser as UserModel;
use app\system\model\SystemRole as RoleModel;
use app\system\model\SystemMenu as MenuModel;

/**
 * 后台用户控制器
 * @package app\system\controller
 */
class User extends Admin
{

    protected $hisiModel = 'SystemUser';

    protected $tabData;

    protected function initialize()
    {
        parent::initialize();

        $this->tabData['menu']=[

            [
                'title' => '系统管理员',
                'url' => 'system/user/index',
            ],
            [
                'title' => '管理员角色',
                'url' => 'system/user/role',
            ],
        ];
    }

    /**
     * 用户列表
     * @return string
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            $where      = $data = [];
            $page       = $this->request->param('page/d', 1);
            $limit      = $this->request->param('limit/d', 15);
            $keyword    = $this->request->param('keyword/s');
            $where[]    = ['id', 'neq', 1];
            if ($keyword) {
                $where[] = ['username', 'like', "%{$keyword}%"];
            }

            $data['data'] = UserModel::with('role')->where($where)->page($page)->limit($limit)->select();
            $data['count'] = UserModel::where($where)->count('id');
            $data['code'] = 0;
            $data['msg'] = '';
            return json($data);
        }

        $this->assign('tabData', $this->tabData);
        $this->assign('tabType', 3);
        return $this->fetch();
    }

    /**
     * 添加用户
     * @return string|void
     */
    public function addUser()
    {
        if($this->request->isPost()){

            $data = $this->request->post();
            $data['password'] = md5($data['password']);
            $data['password_confirm'] = md5($data['password_confirm']);


            // 验证
            $result = $this->validate($data, 'SystemUser');
            if($result !== true) {
                return $this->error($result);
            }
            unset($data['id'], $data['password_confirm']);

            $data['last_login_ip'] = '';
            $data['auth'] = '';
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

            if (!UserModel::create($data)) {
                return $this->error('添加失败');
            }

            return $this->success('添加成功','index');
        }
        $this->assign('roleOptions', RoleModel::getOption());
        $this->assign('menu_list', '');
        return $this->fetch();
    }

    /**
     * 删除用户
     */
    public function delUser()
    {
        $ids   = $this->request->param('id/a');
        $model = new UserModel();
        if($model->del($ids)){
            return $this->success('删除成功');
        }

        return $this->error($model->getError());
    }

    /**
     * 修改用户信息
     * @param int $id
     * @return string|void
     */
    public function editUser($id = 0)
    {
        if ($id == 1 && ADMIN_ID != $id) {
            return $this->error('禁止修改超级管理员');
        }

        if($this->request->isPost()){
            $data = $this->request->post();
            if (!isset($data['auth'])) {
                $data['auth'] = '';
            }

            $row = UserModel::where('id', $id)->field('role_id,auth')->find();
            if ($data['id'] == 1 || ADMIN_ID == $id) {// 禁止更改超管角色，当前登陆用户不可更改自己的角色和自定义权限
                unset($data['role_id'], $data['auth']);
                if (!$row['auth']) {
                    $data['auth'] = '';
                }
            } else if ($row['role_id'] != $data['role_id']) {// 如果分组不同，自定义权限无效
                $data['auth'] = '';
            }

            if (isset($data['role_id']) && RoleModel::where('id', $data['role_id'])->value('auth') == json_encode($data['auth'])) {// 如果自定义权限与角色权限一致，则设置自定义权限为空
                $data['auth'] = '';
            }

            if ($data['password']) {
                $data['password'] = md5($data['password']);
                $data['password_confirm'] = md5($data['password_confirm']);
            }

            // 验证
            $result = $this->validate($data, 'SystemUser.update');
            if($result !== true) {
                return $this->error($result);
            }

            if ($data['password']) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            } else {
                unset($data['password']);
            }

            if (!UserModel::update($data)) {
                return $this->error('修改失败');
            }
            return $this->success('修改成功');
        }


        $user=UserModel::where('id',$id)->field('id,username,role_id,nick,email,mobile,auth,status')->find()->toArray();
        $this->assign('formData', $user);
        $this->assign('roleOptions', RoleModel::getOption($user['role_id']));

        return $this->fetch('add_user');

    }

    /**
     * 用户个人信息及修改
     * @return string
     */
    public function info()
    {
        if($this->request->isPost()){
            //提交数据
            $data = $this->request->post();
            $data['id'] = ADMIN_ID;

            // 防止伪造篡改
            unset($data['role_id'], $data['status']);

            // 验证
            $result = $this->validate($data, 'SystemUser.info');
            if($result !== true) {
                return $this->error($result);
            }

            if ($data['password']) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            } else {
                unset($data['password']);
            }

            if (!UserModel::update($data)) {
                return $this->error('修改失败');
            }
            return $this->success('修改成功');

        }
        $user=UserModel::where('id',ADMIN_ID)->field('username,nick,email,mobile')->find()->toArray();
        $this->assign('formData',$user);
        return $this->fetch();
    }

    /**
     * 角色列表
     * @return string|\think\response\Json
     */
    public function role()
    {
        if($this->request->isAjax()){
            $page = $this->request->param('page/d', 1);
            $limit = $this->request->param('limit/d', 15);

            $data['data']=RoleModel::where('id','<>',1)->page($page)->limit($limit)->select();
            $data['count'] = RoleModel::where('id', '<>', 1)->count('*');
            $data['code'] = 0;
            $data['msg'] = '';
            return json($data);
        }

        $this->assign('tabData', $this->tabData);
        $this->assign('tabType', 3);
        return $this->fetch();
    }

    public function addRole()
    {
        if($this->request->isPost()){
            $data=$this->request->post();

            // 验证
            $result = $this->validate($data, 'SystemRole');
            if($result !== true) {
                return $this->error($result);
            }
            unset($data['id']);
            if (!RoleModel::create($data)) {
                return $this->error('添加失败');
            }
            return $this->success('添加成功');

        }
        $tabData['menu'] = [
            ['title' => '角色添加'],
            ['title' => '权限设置'],
        ];
        $this->assign('menu_list', MenuModel::getAllChild());
        $this->assign('tabData', $tabData);
        $this->assign('tabType', 2);
        return $this->fetch();
    }

    /**
     * 删除角色
     * @param int $id
     * @return mixed
     */
    public function delRole()
    {
        $ids   = $this->request->param('id/a');
        $model = new RoleModel();
        if ($model->del($ids)) {
            return $this->success('删除成功');
        }
        return $this->error($model->getError());
    }
}