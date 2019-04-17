<?php


namespace MyProject\tools;
require '../phpExcel/PHPExcel.php';

class MyPhpExcel
{
    /**
     * 将数据导出
     * @param $file_name -导出的文件名
     * @param $title -excel的标题
     * @param $data -excel的数据
     * @throws \PHPExcel_Exception
     */
    public static function export($file_name, $title, $data){
        $objPHPExcel = new \PHPExcel();

        $column = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N');

        foreach ($title as $k => $v){
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue($column[$k].'1', $v);
        }

        foreach ($data as $k => $v) {
            foreach ($title as $key => $value){
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue($column[$key].($k+2), $v[$value]);
            }
        }
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($file_name);
    }
}