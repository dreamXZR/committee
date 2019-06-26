<?php

namespace app\resident\model;


use think\Model;

class Handicapped extends Model
{
    protected $autoWriteTimestamp = false;

    public static function del($id)
    {
        self::where(['id'=>$id])->delete();
    }
}