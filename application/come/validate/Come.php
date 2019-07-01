<?php

namespace app\come\validate;


use think\Validate;

class Come extends Validate
{
    //定义验证规则
    protected $rule=[
        'name|证明人'=>'require|max:50',
        'type|居民类型'=>'integer',
        'phone|联系电话'=>['require','/^1(3|4|5|6|7|8|9)\d{9}$/'],
        'address|居住地址'=>'require|max:50',
        'call_time|来电时间'=>'require|date',
        'call_content|来电内容'=>'require|max:255',
        '__token__'      => 'require|token',
    ];

    //定义验证提示
    protected $message = [
        'phone'=>'请填写正确的联系电话'
    ];
}