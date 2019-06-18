<?php

namespace app\system\model;

use think\Model;

class SystemLog extends Model
{

    public function user()
    {
        return $this->hasOne('SystemUser', 'id', 'uid');
    }
}