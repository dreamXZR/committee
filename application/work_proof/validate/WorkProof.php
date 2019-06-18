<?php

namespace app\work_proof\validate;


use think\Validate;

class WorkProof extends Validate
{
    //定义验证规则
    protected $rule=[
        'name|人员姓名'=>'require|max:20',
        'id_number|身份证号'=>['/(^\d(15)$)|((^\d{18}$))|(^\d{17}(\d|X|x)$)/', 'require'],
        'present_address|现居住地址'=>'require|max:100',
        'phone|联系电话'=>'require|max:100',
        'work_content|工作内容'=>'require|max:100',
        'work_place|工作地点'=>'require|max:100',
        'child_name|子女姓名'=>'require|max:100',
        'child_sex|子女性别'=>['/(^\d(15)$)|((^\d{18}$))|(^\d{17}(\d|X|x)$)/', 'require'],
        'child_id_number|子女身份证号'=>'require|max:100',
        '__token__'      => 'require|token',
    ];

    //定义验证提示
    protected $message = [
        'name.max'=>'证明人不能超过50个字符',
        'id_number'=>'身份证号格式有误',
        'child_id_number'=>'子女身份证号格式有误',
    ];
}