<?php

namespace app\system\model;

use think\Model;
use app\system\model\SystemMenu as MenuModel;
use app\system\model\SystemRole as RoleModel;
use app\system\model\SystemLog as LogModel;

class SystemUser extends Model
{

    /**
     * 用户登录
     * @param string $username
     * @param string $password
     * @param bool $remember
     * @return bool|mixed
     *
     */
    public function login($username = '', $password = '', $remember = false)
    {
        $username = trim($username);
        $password = trim($password);
        $map['status'] = 1;
        $map['username'] = $username;

        //验证
        $validate = new \app\system\validate\SystemUser;
        file_put_contents('log.log',input('post.'));
        if ($validate->scene('login')->check(input('post.')) !== true) {
            $this->error = $validate->getError();
            return false;
        }

        $user = self::where($map)->find();
        if (!$user) {
            $this->error = '用户不存在或被禁用！';
            return false;
        }

        // 密码校验
        if (!password_verify($password, $user->password)) {
            $this->error = '登陆密码错误！';
            return false;
        }

        // 检查是否分配角色
//        if ($user->role_id == 0) {
//            $this->error = '禁止访问(原因：未分配角色)！';
//            return false;
//        }

        // 角色信息
//        $role = RoleModel::where('id', $user->role_id)->find()->toArray();
//        if (!$role || $role['status'] == 0) {
//            $this->error = '禁止访问(原因：角色分组可能被禁用)！';
//            return false;
//        }

        // 自动清除过期的系统日志
        LogModel::where('create_time', '<', strtotime('-'.(int)config('sys.system_log_retention').' days'))->delete();

        // 更新登录信息
        $user->last_login_time = time();
        $user->last_login_ip   = get_client_ip();
        if ($user->save()) {
            // 执行登陆
            $login = [];
            $login['uid'] = $user->id;
            $login['role_id'] = $user->role_id;
//            $login['role_name'] = $role['name'];
            $login['nick'] = $user->nick;
//            // 缓存角色权限
//            session('role_auth_'.$user->role_id, $user->auth ? $user->auth : $role['auth']);
//            // 缓存登录信息
            session('admin_user', $login);
            session('admin_user_sign', $this->dataSign($login));
            return $user->id;
        }
        return false;
    }



    /**
     * 判断是否登录
     * @return bool|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function isLogin()
    {
        $user = session('admin_user');
        if(isset($user['uid'])){
            if (!self::where('id', $user['uid'])->find()) {
                return false;
            }

            return session('admin_user_sign') == $this->dataSign($user) ? $user : false;
        }

        return false;
    }

    /**
     * 删除用户
     * @param string $id 用户ID
     * @return bool
     */
    public function del($id = 0)
    {
        $menu_model = new MenuModel();
        if (is_array($id)) {
            $error = '';
            foreach ($id as $k => $v) {
                if ($v == ADMIN_ID) {
                    $error .= '不能删除当前登陆的用户['.$v.']！<br>';
                    continue;
                }

                if ($v == 1) {
                    $error .= '不能删除超级管理员['.$v.']！<br>';
                    continue;
                }

                if ($v <= 0) {
                    $error .= '参数传递错误['.$v.']！<br>';
                    continue;
                }

                $map = [];
                $map['id'] = $v;
                // 删除用户
                self::where($map)->delete();

            }

            if ($error) {
                $this->error = $error;
                return false;
            }
        } else {
            $id = (int)$id;
            if ($id <= 0) {
                $this->error = '参数传递错误！';
                return false;
            }

            if ($id == ADMIN_ID) {
                $this->error = '不能删除当前登陆的用户！';
                return false;
            }

            if ($id == 1) {
                $this->error = '不能删除超级管理员！';
                return false;
            }

            $map = [];
            $map['id'] = $id;
            // 删除用户
            self::where($map)->delete();
            // 删除关联表
            $menu_model->delUser($id);
        }

        return true;
    }

    /**
     * 数据签名认证
     * @param array $data 被认证的数据
     * @return string 签名
     */
    public function dataSign($data = [])
    {
        if (!is_array($data)) {
            $data = (array) $data;
        }
        ksort($data);
        $code = http_build_query($data);
        $sign = sha1($code);
        return $sign;
    }

    public function logout()
    {
        session('admin_user', null);
        session('admin_user_sign', null);
    }

    // 权限
    public function role()
    {
        return $this->hasOne('SystemRole', 'id', 'role_id');
    }

}