<?php

namespace app\resident\model;

use think\Model;

class Resident extends Model
{
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
}