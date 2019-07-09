<?php

namespace app\system\controller;

use think\App;
use Knp\Snappy\Pdf as SnappyPDF;
use think\Response;
use Env;

class Pdf extends Admin
{
    private $snappy;

    private $html;

    public function __construct(App $app = null)
    {
        parent::__construct($app);

        $dir =Env::get('ROOT_PATH');
        $this->snappy = new SnappyPDF($dir.'vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64');
    }

    public function loadHtml($html)
    {
        $this->html=$html;
        return $this;

    }

    public function output()
    {
        if($this->html){
            return $this->snappy->getOutputFromHtml($this->html);
        }else{
            return $this->error('pdf生成出错,请重试');
        }
    }

    public function inline($filename = 'document.pdf')
    {
        return new Response($this->output(),200,array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ));
    }
}