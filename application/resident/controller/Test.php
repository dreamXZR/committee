<?php

namespace app\resident\controller;

use app\system\controller\Admin;
use Knp\Snappy\Pdf;
use Env;

class Test extends Admin
{
    public function test()
    {
        $path=Env::get('root_path');
        $snappy = new Pdf($path.'vendor/h4cc/wkhtmltopdf-i386/bin/wkhtmltopdf-i386');
        try{
            $snappy->generateFromHtml("<div>11111</div>", 'test.pdf');
        }catch (\Exception $e){
            echo $e->getMessage();
        }


    }
}