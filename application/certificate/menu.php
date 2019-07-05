<?php
// +----------------------------------------------------------------------
// | HisiPHP框架[基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 http://www.hisiphp.com
// +----------------------------------------------------------------------
// | HisiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
// +----------------------------------------------------------------------
// | Author: 橘子俊 <364666827@qq.com>，开发者QQ群：50304283
// +----------------------------------------------------------------------
/**
 * 模块菜单
 * 字段说明
 * url 【链接地址】格式：example/控制器/方法，可填写完整外链[必须以http开头]
 * param 【扩展参数】格式：a=123&b=234555
 */
return [
  [
    'pid'=>3,
    'title' => '证明信模块',
    'icon' => 'aicon ai-caidan',
    'module' => 'certificate',
    'url' => 'example',
    'param' => '',
    'target' => '_self',
    'sort' => 100,
    'childs' => [
      [
        'title' => '证明信管理',
        'icon' => 'typcn typcn-clipboard',
        'module' => 'certificate',
        'url' => 'certificate/index/index',
        'param' => '',
        'target' => '_self',
        'sort' => 0,
        'childs' => [
            [
                'title' => '添加证明信',
                'icon' => '',
                'module' => 'certificate',
                'url' => 'certificate/index/add',
                'param' => '',
                'target' => '_self',
                'sort' => 0,
            ],
            [
                'title' => '修改证明信',
                'icon' => '',
                'module' => 'certificate',
                'url' => 'certificate/index/edit',
                'param' => '',
                'target' => '_self',
                'sort' => 0,
            ],
            [
                'title' => '删除证明信',
                'icon' => '',
                'module' => 'certificate',
                'url' => 'certificate/index/del',
                'param' => '',
                'target' => '_self',
                'sort' => 0,
            ],
            [
                'title' => '打印证明信',
                'icon' => '',
                'module' => 'certificate',
                'url' => 'certificate/index/pdf',
                'param' => '',
                'target' => '_self',
                'sort' => 0,
            ],

        ],
      ],
    ],
  ],
];