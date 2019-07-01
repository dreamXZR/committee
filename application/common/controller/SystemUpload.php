<?php

namespace app\common\controller;

use hisi\Dir;
use think\Controller;


class SystemUpload extends Controller
{

    /**
     * 上传文件
     * @param array $param
     * @return array|bool
     */
    public function upload(array $param=[])
    {
        $upload_file=request()->file($param['file']);
        $folder_name=$param['folder_name'] ?: 'default';

        // 文件存放路径
        $filePath = '/upload/'.$folder_name.'/';

        if(!is_dir($filePath)){
            Dir::create($filePath);
        }

        $upfile = $upload_file->rule('md5')->move('.'.$filePath);

        if ( !is_file('.'.$filePath.$upfile->getSaveName()) ) {
            echo \json_encode(['code'=>-1,'error'=>'上传失败']);
            return false;
        }

        return [
            'path'=>$folder_name.'/'.$upfile->getSaveName()
        ];
    }

    /**
     * 删除图片并更新数据库
     * @param $obj
     * @param $key
     * @return bool
     */
    public function deleteImage($obj,$key)
    {
        $images = \json_decode($obj->images);
        delete_file($images[$key]);
        $images[$key] = '';

        $obj->images = $images;
        $obj->save();

        return true;
    }

    /**
     * 获得图片列表
     * @param $obj
     * @param $id
     * @return array
     */
    public function getImageList($obj,$id)
    {
        $array=[];
        if($obj->images){
            foreach (\json_decode($obj->images) as $k=>$v){
                if($v){
                    $array['images'][]="<img src='/upload/".$v."' style='height:auto; max-width: 100%; max-height: 100%; margin-top: 0px;'>";
                    $array['delete'][]=['url'=>url('image/destory','id='.$id), 'key'=>$k];
                }
            }
        }

        return $array;

    }

    /**
     * 更新json数据
     * @param array $image_path
     * @param $obj
     * @return mixed
     */
    public static function getUpdateJson(array $image_path,$obj)
    {
        return json_merge(\json_encode($image_path),$obj->images);
    }
}