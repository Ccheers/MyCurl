<?php

include './classes/PHPExcel/IOFactory.php';
include './classes/PHPExcel.php';

class PHPOpExcel{

    /**
     * 读取文件内容
     * @param $inputFileName 文件名称
     * @param $start string 开始列
     * @param $end string 结束列
     * @throws PHPExcel_Exception
     * @return array
     */
    public static function read($inputFileName, $start='A', $end=''){
        $data = array();
        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (Exception $e) {
            die('加载文件发生错误："' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": '.$e->getMessage());
        }
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $end = empty($end) ? $highestColumn : $end;
        if($start == $end){
            //返回二维数组
            for ($row = 2; $row <= $highestRow; $row++){
                $rowData = $sheet->rangeToArray($start . $row . ':' . $end . $row, NULL, TRUE, FALSE);
                $data[] = $rowData[0][0];
            }
        }else{
            //返回三维数组
            for ($row = 2; $row <= $highestRow; $row++){
                $rowData = $sheet->rangeToArray($start . $row . ':' . $end . $row, NULL, TRUE, FALSE);
                $data[] = $rowData[0];
            }
        }
        return $data;
    }

    /**
     * 将数据导出
     * @param $file_name 导出的文件名
     * @param $title excel的标题
     * @param $data excel的数据
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
    public static function export($file_name, $title, $data){
        $objPHPExcel = new PHPExcel();

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
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($file_name);
    }

    /**
     * 将csv文件转成excel文件
     * @param $csv_path csv文件路径
     * @param $excel_path excel文件路径
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
    public static function csvToExcel($csv_path, $excel_path){
        try {
            $inputFileType = PHPExcel_IOFactory::identify($csv_path);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($csv_path);
        } catch (Exception $e) {
            die('加载文件发生错误："' . pathinfo($csv_path, PATHINFO_BASENAME) . '": '.$e->getMessage());
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($excel_path);
    }




}