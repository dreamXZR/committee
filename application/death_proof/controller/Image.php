<?php

namespace app\death_proof\controller;

use app\system\controller\Admin;
use app\common\controller\SystemUpload;
use app\death_proof\model\DeathProof;

class Image extends Admin
{
    /**
     * 上传文件
     * @param SystemUpload $systemUpload
     * @return array|bool
     */
    public function upload(SystemUpload $systemUpload)
    {
        $param=[
            'file'=>'image',
            'folder_name'=>request()->param('folder')
        ];
        return $systemUpload->upload($param);
    }
}