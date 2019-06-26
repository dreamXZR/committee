<?php

namespace app\resident\model;

use think\Model;
use think\Db;

class Info extends Model
{
    protected $autoWriteTimestamp = false;

    public static $residence_status_map=[
        1=>'农业',
        2=>'非农业'
    ];
    public static $house_people_map=[
        1=>'业主',
        2=>'租户',
        3=>'空房'
    ];
    public static $house_status_map=[
        1=>'户在',
        2=>'户不在',
        3=>'人在',
        4=>'人不在'
    ];

    public static $people_map=[
        1=>'老人空巢',
        2=>'独居',
        3=>'残疾人',
        4=>'低保户',
        5=>'特困',
        6=>'复退',
        7=>'现役',
        8=>'侨属',

    ];

    public function residents()
    {
        return $this->hasMany('Resident','info_id');
    }

    public function handicappeds()
    {
        return $this->hasMany('Handicapped','info_id');
    }

    //数据库插入读取相关

    public function setResidenceStatusAttr($value)
    {
        return array_search($value, self::$residence_status_map);
    }

    public function getResidenceStatusAttr($value)
    {
        if($value){
            $str=self::$residence_status_map[$value];
            return $str;
        }else{
            return '';
        }
    }

    public function setHousePeopleAttr($value)
    {
        return array_search($value, self::$house_people_map);
    }

    public function getHousePeopleAttr($value)
    {
        if($value){
            $str=self::$house_people_map[$value];
            return $str;
        }else{
            return '';
        }

    }

    public function setHouseStatusAttr($value)
    {

        return str_to_num($value,self::$house_status_map);
    }

    public function getHouseStatusAttr($value)
    {
        if($value){
            $str=num_to_str($value,self::$house_status_map);
            return $str;
        }else{
            return $value;
        }

    }

    public function setPeopleAttr($value)
    {
        return str_to_num($value,self::$people_map);

    }

    public function getPeopleAttr($value)
    {
        if($value){
            $str=num_to_str($value,self::$people_map);
            return $str;
        }else{
            return $value;
        }

    }

    public function getAllPresentAddressAttr($value,$data)
    {
        return $data['housing_estate'].$data['building'].'-'.$data['door'].'-'.$data['no'];
    }


    public static function transactionInsert($info_data,$resident_data,$handicapped_data)
    {
        Db::startTrans();
        try{

            $info=self::create($info_data);

            //居民数据
            if($resident_data){
                $info->residents()->saveAll($resident_data);
            }

            //残疾人士数据
            if($handicapped_data){
                $info->handicappeds()->saveAll($handicapped_data);
            }
            Db::commit();
            return true;
        }catch (\Exception $e) {
            Db::rollback();
            return false;
        }
    }

    public function transactionDelete($id)
    {
        Db::startTrans();
        try{
            $info=self::get($id,['residents','handicappeds']);
            $info->together(['residents','handicappeds'])->delete();
            Db::commit();
            return true;
        }catch (\Exception $e){
            Db::rollback();
            return false;
        }
    }

    public static function transactionUpdate($info_data,$resident_data,$handicapped_data)
    {
        Db::startTrans();
        try{
            $info=self::update($info_data);
            //居民数据
            if($resident_data){
                foreach ($resident_data as $v){
                    if(array_key_exists('id', $v)){
                        Resident::update($v);
                    }else{
                        $info->residents()->save($v);
                    }
                }
            }


            //残疾人士数据
            if($handicapped_data){
                foreach ($handicapped_data as $v){
                    if(array_key_exists('id', $v)){
                        Handicapped::update($v);
                    }else{
                        $info->handicappeds()->save($v);
                    }
                }
            }

            Db::commit();
            return true;
        }catch (\Exception $e) {
            var_dump($e->getFile(),$e->getLine(),$e->getMessage());
            Db::rollback();
            return false;
        }
    }


    public function del($ids)
    {
        if(is_array($ids)){
            $error = '';
            foreach ($ids as $id){
                if($id<0){
                    $error='参数传递错误';
                    continue;
                }
                $this->transactionDelete($id);
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