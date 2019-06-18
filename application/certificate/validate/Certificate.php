<?php

namespace app\certificate\validate;


use think\Validate;

class Certificate extends Validate
{
    //定义验证规则
    protected $rule=[
        'name|证明人'=>'require|max:50',
        'community_name|社区名称'=>'require|max:50',
        'present_address|居住地址'=>'require|max:100',
        'residence_address|户籍地址'=>'require|max:100',
        'use|用处'=>'require|max:100',
        'basis|依据'=>'require|max:100',
        'title|抬头'=>'require|max:100',
        '__token__'      => 'require|token',
    ];

    //定义验证提示
    protected $message = [
        'name.max'=>'证明人不能超过50个字符'
    ];
}