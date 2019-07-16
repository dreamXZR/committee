<?php

namespace app\system\controller;

use app\common\controller\Common;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Excel extends Common
{
    /**
     * excel表的表名
     * @var
     */
    public $fileName='test.xlsx';

    /**
     * 设置表头 返回一个一维数组
     * @return array
     */
    public function headings()
    {
        return [];
    }



    /**
     * 获取标题总数
     * @return int
     */
    public function getHandingsCount()
    {
        return count($this->headings());
    }

    /**
     * 设置表头
     * @param $sheet
     */
    public function createHeadings($sheet)
    {
        $head=$this->headings();     //标题

        $count = $this->getHandingsCount();       //计算表头数量

        for ($i = 65; $i < $count + 65; $i++) {     //数字转字母从65开始，循环设置表头：
            $sheet->setCellValue(strtoupper(chr($i)) . '1', $head[$i - 65]);
        }
    }

    public function createData($sheet,$spreadsheet)
    {
        $data=$this->query();

        foreach ($data as $k=>$v){
            $spreadsheet->getActiveSheet()->fromArray(
                $this->map($v),
                NULL,
                'A'.($k+2)
            );
        }


    }



    /**
     * 导出excel表
     * 备注：此函数缺点是，表头（对应列数）不能超过26；
     *循环不够灵活，一个单元格中不方便存放两个数据库字段的值
     */
    public function export()
    {
        $filename=$this->fileName;   //表名

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $this->createHeadings($sheet);

        $this->createData($sheet,$spreadsheet);


        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename);
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        //删除清空：
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        exit;
    }

}