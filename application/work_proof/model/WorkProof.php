<?php

namespace app\work_proof\model;

use think\Model;
use think\facade\Cache;

class WorkProof extends Model
{
    /**
     * 根据id删除信息卡
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


}