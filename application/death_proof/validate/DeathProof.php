<?php

namespace app\death_proof\validate;


use think\Validate;

class DeathProof extends Validate
{
    //定义验证规则
    protected $rule=[
        'name|人员姓名'=>'require|max:20',
        'id_number|身份证号'=>['/(^\d(15)$)|((^\d{18}$))|(^\d{17}(\d|X|x)$)/', 'require'],
        'residence_address|户籍地址'=>'require|max:50',
        'present_address|现居住地址'=>'require|max:50',
        'death_date|死亡时间'=>'require|date',
        'death_address|死亡地点'=>'require|max:50',
        'applicant|申请人'=>'require|max:20',
        'applicant_id_number|申请人身份证'=>['/(^\d(15)$)|((^\d{18}$))|(^\d{17}(\d|X|x)$)/', 'require'],
        'applicant_death_relation|与死者关系'=>'require|integer',
        '__token__'      => 'require|token',
    ];

    //定义验证提示
    protected $message = [
        'id_number'=>'身份证号格式有误',
        'applicant_id_number'=>'申请人身份证号格式有误',
        'agent_id_number'=>'委托人身份证号格式有误',

    ];

    //存在委托人的场景
    public function sceneHasAgent()
    {
        return $this->append('agent|委托人','require|max:20')
                    ->append('agent_id_number|委托人身份证',['/(^\d(15)$)|((^\d{18}$))|(^\d{17}(\d|X|x)$)/', 'require'])
                    ->append('agent_applicant_relation|委托人与申请人关系','require|max:20');
    }
}