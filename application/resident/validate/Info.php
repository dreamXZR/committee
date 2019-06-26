<?php

namespace app\resident\validate;

use think\Validate;

class Info extends Validate
{
    //定义验证规则
    protected $rule=[
        'housing_estate|小区名称'=>'require|max:20',
        'building|楼信息'=>'require|integer',
        'door|门信息'=>'require|integer',
        'no|号信息'=>'require|integer',
        'residents'=>'require',
        '__token__'      => 'require|token',
    ];

    //定义验证提示
    protected $message = [
        'residents.require'=>'最少填写一位居民信息',
    ];

}