<?php

namespace app\system\model;

use app\system\model\SystemRole as RoleModel;
use think\Db;
use think\Model;
use hisi\Tree;
use Cache;

class SystemMenu extends Model
{




    /**
     * 保存入库
     * @return bool
     */
    public function storage($data = [])
    {
        if (empty($data)) {
            $data = request()->post();
        }

        // system模块 只允许超级管理员在开发模式下修改
        if (isset($data['id']) && !empty($data['id'])) {
            if ($data['module'] == 'system' && (ADMIN_ID != 1 || config('sys.app_debug') == 0)) {
                $this->error = '禁止修改系统模块！';
                return false;
            }
        }

        $data['url'] = trim($data['url'], '/');

        // 扩展参数解析为json
        if ($data['param']) {
            $data['param'] = trim(htmlspecialchars_decode($data['param']), '&');
            parse_str($data['param'], $param);
            ksort($param);
            $data['param'] = http_build_query($param);
        }

        // 验证
        $valid = new \app\system\validate\SystemMenu;
        if($valid->check($data) !== true) {
            $this->error = $valid->getError();
            return false;
        }

        $title = $data['title'];

        if (isset($data['id']) && !empty($data['id'])) {

            if (config('sys.multi_language') == 1) {

                if (Db::name('system_menu_lang')->where(['menu_id' => $data['id'], 'lang' => dblang('admin')])->find()) {
                    Db::name('system_menu_lang')->where(['menu_id' => $data['id'], 'lang' => dblang('admin')])->update(['title' => $title]);
                } else {

                    $map = [];
                    $map['menu_id'] = $data['id'];
                    $map['title'] = $title;
                    $map['lang'] = dblang('admin');
                    Db::name('system_menu_lang')->insert($map);

                }

            }

            $res = $this->update($data);
            Cache::rm('admin_bread_crumbs_'.$data['id']);
        } else {

            $res = $this->create($data);
            if (config('sys.multi_language') == 1) {
                $map = [];
                $map['menu_id'] = $res->id;
                $map['title'] = $title;
                $map['lang'] = dblang('admin');
                Db::name('system_menu_lang')->insert($map);
            }

        }

        if (!$res) {
            $this->error = '保存失败！';
            return false;
        }

        self::getMainMenu(true);
        return $res;
    }

    /**
     * 获取指定节点下的所有子节点(不含快捷收藏的菜单)
     * @param int $pid 父ID
     * @param int $status 状态码 不等于1则调取所有状态
     * @param string $cache_tag 缓存标签名
     * @return array
     */
    public static function getAllChild($pid = 0, $status = 1, $field = 'id,pid,module,title,url,param,target,icon,sort,status', $level = 0, $data = [])
    {
        $cache_tag = md5('_admin_child_menu'.$pid.$field.$status);
        $trees = [];
        if (config('sys.app_debug') == 0 && $level == 0) {
            $trees = cache($cache_tag);
        }

        if (empty($trees)) {
            if (empty($data)) {
                $map = [];
                $map['uid'] = 0;
                if ($status == 1) {
                    $map['status'] = 1;
                }
                $data = self::where($map)->order('sort asc,id asc')->column($field);
                $data = array_values($data);
            }

            foreach ($data as $k => $v) {

                if ($v['pid'] == $pid) {
                    // 过滤没访问权限的节点
                    if (!RoleModel::checkAuth($v['id'])) {
                        unset($data[$k]);
                        continue;
                    }

                    unset($data[$k]);
                    $v['childs'] = self::getAllChild($v['id'], $status, $field, $level+1, $data);
                    $trees[] = $v;
                }

            }

            // 非开发模式，缓存菜单
            if (config('app.app_debug') == 0) {
                cache($cache_tag, $trees);
            }
        }

        return $trees;
    }

    /**
     * 获取后台菜单
     * 后台顶部和左侧使用
     * @param bool $update 是否更新缓存
     * @return array
     */
    public static function getMainMenu($update = false)
    {
        $cacheName = 'admin_main_menu';

        $cacheData = Cache::get($cacheName);

        if (config('app.app_debug') == false && $cacheData && $update == false) {
            return $cacheData;
        }

        $where = [];
        $where[] = ['nav', '=', 1];
        $where[] = ['status', '=', 1];

        if (config('app.app_debug') == false) {
            $where[] = ['debug', '=', 0];
        }

        $menus = self::where($where)->order('sort asc')->column('id,pid,module,title,url,param,target,icon', 'id');


        $auths = Tree::toTree($menus);

        if(config('app.app_debug') == false){
            Cache::set($cacheName, $auths);
        }

        return $auths;
    }

    /**
     * 获取当前节点的面包屑导航
     * @param string $id 节点ID
     * @return array
     */
    public static function getBreadCrumbs($id)
    {

        $menu = Cache::get('admin_bread_crumbs_'.$id);

        if ($menu) {
            return $menu;
        }

        if (!$id) {
            return false;
        }

        $menu = [];
        $row = self::where('id', $id)->field('id,pid,title,url,param')->find();

        if ($row->pid > 0) {


            $menu[] = $row;
            $childs = self::getBreadCrumbs($row->pid);

            if ($childs) {
                $menu = array_merge($childs, $menu);
            }

        }

        Cache::set('admin_bread_crumbs_'.$id, $menu);

        return $menu;
    }

    /**
     * 获取当前访问节点信息，支持扩展参数筛查
     * @param string $id 节点ID
     * @return array
     */
    public static function getInfo($id = 0)
    {
        $map = [];

        if (empty($id)) {

            $module     = request()->module();
            $controller = request()->controller();
            $action     = request()->action();
            $map[] = ['url', '=', $module.'/'.$controller.'/'.$action];
        } else {

            $map[] = ['id', '=', (int)$id];

        }

        $map[] = ['status', '=', 1];

        $rows = self::where($map)->column('id,title,url,param');

        if (!$rows) {
            return false;
        }

        sort($rows);

        $_param = request()->param();

        if (count($rows) > 1) {

            if (!$_param) {

                foreach ($rows as $k => $v) {

                    if ($v['param'] == '') {
                        return $rows[$k];
                    }

                }

            }

            foreach ($rows as $k => $v) {

                if ($v['param']) {

                    parse_str($v['param'], $param);

                    ksort($param);

                    $paramArr = [];

                    foreach ($param as $kk => $vv) {
                        if (isset($_param[$kk])) {
                            $paramArr[$kk] = $_param[$kk];
                        }
                    }

                    $where     = [];
                    $where[]   = ['param', '=', http_build_query($paramArr)];
                    $where[]   =  ['url', '=', $module.'/'.$controller.'/'.$action];

                    $res = self::where($where)->field('id,title,url,param')->find();
                    if ($res) {
                        return $res;
                    }
                }

            }

            $map[] = ['param', '=', ''];

            $res = self::where($map)->field('id,title,url,param')->find();

            if ($res) {
                return $res;
            } else {
                return false;
            }

        }

        // 扩展参数判断
        if ($rows[0]['param']) {
            parse_str($rows[0]['param'], $param);
            ksort($param);
            foreach ($param as $k => $v) {
                if (!isset($_param[$k]) || $_param[$k] != $v) {
                    return false;
                }
            }
        } else {// 排除敏感参数
            $param = ['hisiModel', 'hisiTable', 'hisiValidate', 'hisiScene'];
            foreach ($param as $k => $v) {
                if (isset($_param[$v])) {
                    return false;
                }
            }
        }

        return $rows[0];
    }

    /**
     * 根据指定节点找出顶级节点的ID
     * @param string $id 节点ID
     * @return array
     */
    public static function getParents($id = 0)
    {
        $map = [];

        if (empty($id)) {

            $module     = request()->module();
            $controller = request()->controller();
            $action     = request()->action();
            $map[] = ['url', '=', $module.'/'.$controller.'/'.$action];

        } else {

            $map[] = ['id', '=', (int)$id];

        }

        $res = self::where($map)->find();

        if ($res['pid'] > 0) {
            $id = self::getParents($res['pid']);
        } else {
            $id = $res['id'];
        }

        return $id;
    }

    /**
     * 删除菜单
     * @param string|array $id 节点ID
     * @return bool
     */
    public function del($ids = '') {
        if (is_array($ids)) {

            $error = '';
            foreach ($ids as $k => $v) {

                $map        = [];
                $map['id']  = $v;
                $row = self::where($map)->find();

                if ((ADMIN_ID != 1 && $row['system'] == 1)) {
                    $error .= '['.$row['title'].']禁止删除<br>';
                    continue;
                }

                if (self::where('pid', $row['id'])->find()) {
                    $error .= '['.$row['title'].']请先删除下级菜单<br>';
                    continue;
                }

                self::where($map)->delete();

            }

            if ($error) {
                $this->error = $error;
                return false;
            }

            self::getMainMenu(true);
            return true;
        }
        $this->error = '参数传递错误';
        return false;
    }

}