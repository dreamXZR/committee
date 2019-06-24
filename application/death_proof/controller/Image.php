<?php

namespace app\death_proof\controller;

use app\system\controller\Admin;
use app\common\controller\SystemUpload;
use app\death_proof\model\DeathProof;

class Image extends Admin
{
    /**
     * 展示已上传图片
     * @param SystemUpload $systemUpload
     * @return \think\response\Json
     */
    public function index(SystemUpload $systemUpload)
    {
        $id=request()->param('id/d');

        if($certificate=DeathProof::find($id)){
            $image_array=$systemUpload->getImageList($certificate,$id);
            return json($image_array);
        }
    }
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

    /**
     * 删除文件
     * @return bool
     */
    public function destory(SystemUpload $systemUpload)
    {
        $id=request()->param('id/d');
        $key=request()->param('key');

        if($certificate=DeathProof::find($id)) {

            return $systemUpload->deleteImage($certificate,$key);

        }
    }
}