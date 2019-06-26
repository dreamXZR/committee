<?php

namespace app\resident\model;

use think\Model;

class Resident extends Model
{
    protected $autoWriteTimestamp = false;

    public static $culture_map=[
        1=>'小学以下',
        2=>'小学',
        3=>'初中',
        4=>'高中',
        5=>'大专',
        6=>'大学',
        7=>'大学以上'
    ];

    public static $face_map=[
        1=>'中共党员',
        2=>'群众',
        3=>'共青团',
        4=>'农工党',
        5=>'其他'
    ];

    public static $marriage_map=[
        1=>'已婚',
        2=>'未婚',
        3=>'离异',
        4=>'丧偶'
    ];

    public static $identity_map=[
        1=>'在职',
        2=>'退休',
        3=>'学生',
        4=>'学龄前',
        5=>'无业',
        6=>'失业'
    ];

    public static $relationship_map=[
        1=>'租户',
        2=>'本人',
        50=>'其它'
    ];

    public function info()
    {
        return $this->belongsTo('Info','info_id');
    }

    public function setCultureAttr($value)
    {
        return array_search($value,self::$culture_map);
    }

    public function getCultureAttr($value)
    {
        $str=self::$culture_map[$value];
        return $str;
    }

    public function setFaceAttr($value)
    {
        return array_search($value,self::$face_map);
    }

    public function getFaceAttr($value)
    {
        $str=self::$face_map[$value];
        return $str;
    }

    public function setMarriageAttr($value)
    {
        return array_search($value,self::$marriage_map);
    }

    public function getMarriageAttr($value)
    {
        $str=self::$marriage_map[$value];
        return $str;
    }

    public function setIdentityAttr($value)
    {
        return array_search($value,self::$identity_map);
    }

    public function getIdentityAttr($value)
    {
        $str=self::$identity_map[$value];
        return $str;
    }



    public function getSexAttr($value)
    {
        if($value==0)
        {
            return '女';
        }else{
            return '男';
        }
    }

    public function setSexAttr($value)
    {
        if($value=='男'){

            return 1;
        }else if($value=='女'){
            return 0;
        }else{
            return $value;
        }
    }

    public static function del($id)
    {
        self::where(['id'=>$id])->delete();
    }


}