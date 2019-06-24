<?php

namespace app\work_proof\controller;

use app\system\controller\Admin;
use app\common\controller\SystemUpload;
use app\work_proof\model\WorkProof;

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

        if($workProof=WorkProof::find($id)){
            $image_array=$systemUpload->getImageList($workProof,$id);
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

        if($workProof=WorkProof::find($id)) {

            return $systemUpload->deleteImage($workProof,$key);

        }
    }
}