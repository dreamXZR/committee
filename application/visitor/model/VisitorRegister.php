<?php

namespace app\visitor\model;

use think\Model;
use think\facade\Cache;

class VisitorRegister extends Model
{
    /**
     * 根据id删除信息
     * @param $ids
     * @return bool
     */
    public function del($ids)
    {
        if(is_array($ids)){
            $error = '';
            foreach ($ids as $id){
                if($id<0){
                    $error='参数传递错误';
                    continue;
                }
                self::where(['id'=>$id])->delete();
            }

            if ($error) {
                $this->error = $error;
                return false;
            }
        }else{
            return false;
        }

        return true;
    }

    /**
     * 获取编号
     * @return int|string
     */
    public static function getNumber()
    {
        $today=date('Ymd',time());
        $number=Cache::get('visitor_number');
        if($number){
            if($number[0]!=$today){
                $new_number=date('Ymd',time()).'001';
            }else{
                $new_number=$number[1]+1;
            }
        }else{
            $new_number=date('Ymd',time()).'001';
        }
        Cache::set('visitor_number',[$today,$new_number]);
        return $new_number;
    }
}