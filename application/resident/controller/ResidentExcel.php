<?php

namespace app\resident\controller;


use app\resident\model\Resident;
use app\system\controller\Excel;

class ResidentExcel extends Excel
{
    public $fileName='居民信息.xlsx';

    protected $select;

    public function withSelect($select)
    {
        $this->select=$select;
        return $this;
    }

    public function query()
    {
        return Resident::where('is_replace',0)->select();
    }

    public function map($resident)
    {

        return [
            $resident->name,
            ' '.$resident->id_number,
            $resident->info->housing_estate.$resident->info->building.' - '.$resident->info->door.' - '.$resident->info->no,
            $resident->residence_address,
            $resident->info->residence_status,
            $resident->sex,
            $resident->nation,
            $resident->birthday,
            $resident->relationship,
            $resident->culture,
            $resident->face,
            $resident->marriage,
            $resident->identity,
            $resident->unit,
            ' '.$resident->phone,
            $resident->other
        ];
    }

    public function headings()
    {
        return [
            '姓名',
            '身份证号',
            '现居住地址',
            '户籍所在地',
            '户籍性质',
            '性别',
            '民族',
            '生日',
            '与户主关系',
            '文化程度',
            '政治面貌',
            '婚姻状况',
            '身份类别',
            '工作单位',
            '联系电话',
            '备注'

        ];
    }

    public function title()
    {
        return '居民信息';
    }
}