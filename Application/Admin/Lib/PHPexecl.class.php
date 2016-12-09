<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Lib;

/**
 * Description of PHPexecl
 *
 * @author Administrator
 */
class PHPexecl {
    
    public function __construct() {
        Vendor("PHPExcel.PHPExcel");
    }

    //put your code here
    public function importExecl($file, $ar, $filename) {
//        spl_autoload_register(array('Think', 'autoload'));
        if (!file_exists($file)) {
            return array("error" => 0, 'message' => 'file not found!');
        }
        Vendor("PHPExcel.PHPExcel.IOFactory");
        $objReader = \PHPExcel_IOFactory::createReader('Excel5');
//        var_dump($objReader);exit;
        try {
            $PHPReader = $objReader->load($file);
            $target = clone $PHPReader;
        } catch (Exception $e) {
            
        }
        if (!isset($PHPReader))
            return array("error" => 0, 'message' => 'read error!');
        $allWorksheets = $PHPReader->getAllSheets();
        $i = 0;
        foreach ($allWorksheets as $objWorksheet) {
            $sheetname = $objWorksheet->getTitle();
            $allRow = $objWorksheet->getHighestRow(); //how many rows
//            var_dump($allRow);exit;
            $highestColumn = $objWorksheet->getHighestColumn(); //how many columns
            $allColumn = \PHPExcel_Cell::columnIndexFromString($highestColumn);
            $array[$i]["Title"] = $sheetname;
            $array[$i]["Cols"] = $allColumn;
            $array[$i]["Rows"] = $allRow;
            $arr = array();
            $isMergeCell = array();
            foreach ($objWorksheet->getMergeCells() as $cells) {//merge cells
                foreach (\PHPExcel_Cell::extractAllCellReferencesInRange($cells) as $cellReference) {
                    $isMergeCell[$cellReference] = true;
                }
            }
            for ($currentRow = 1; $currentRow <= $allRow; $currentRow++) {
                $row = array();
                for ($currentColumn = 0; $currentColumn < $allColumn; $currentColumn++) {
                    ;
                    $cell = $objWorksheet->getCellByColumnAndRow($currentColumn, $currentRow);
                    $afCol = \PHPExcel_Cell::stringFromColumnIndex($currentColumn + 1);
                    $bfCol = \PHPExcel_Cell::stringFromColumnIndex($currentColumn - 1);
                    $col = \PHPExcel_Cell::stringFromColumnIndex($currentColumn);
                    $address = $col . $currentRow;
                    $value = $objWorksheet->getCell($address)->getValue();

                    $currentColumnPosition = $cell->getCoordinate();
                    if (array_key_exists($currentColumnPosition, $ar)) {
                        $value .= $ar[$cell->getCoordinate()];
                        $target->getActiveSheet()->getCell($currentColumnPosition)->setValue($value);
                    }
//                    var_dump($currentColumnPosition);
//                    if (substr($value, 0, 1) == '=') {
//                        return array("error" => 0, 'message' => 'can not use the formula!');
//                        exit;
//                    }
                    if ($cell->getDataType() == \PHPExcel_Cell_DataType::TYPE_NUMERIC) {
//                        var_dump($cell->getStyle($cell->getCoordinate())->getNumberFormat()->getFormatCode());exit;
                        $cellstyleformat = $cell->getParent()->getStyle($cell->getCoordinate())->getNumberFormat();
                        $formatcode = $cellstyleformat->getFormatCode();
                        if (preg_match('/^([$[A-Z]*-[0-9A-F]*])*[hmsdy]/i', $formatcode)) {
                            $value = gmdate("Y-m-d", \PHPExcel_Shared_Date::ExcelToPHP($value));
                        } else {
                            $value = \PHPExcel_Style_NumberFormat::toFormattedString($value, $formatcode);
                        }
                    }
                    if ($isMergeCell[$col . $currentRow] && $isMergeCell[$afCol . $currentRow] && !empty($value)) {
                        $temp = $value;
                    } elseif ($isMergeCell[$col . $currentRow] && $isMergeCell[$col . ($currentRow - 1)] && empty($value)) {
                        $value = $arr[$currentRow - 1][$currentColumn];
                    } elseif ($isMergeCell[$col . $currentRow] && $isMergeCell[$bfCol . $currentRow] && empty($value)) {
                        $value = $temp;
                    }
                    $row[$currentColumn] = $value;
//                    var_dump($value);
                }
                $arr[$currentRow] = $row;
            }
            $array[$i]["Content"] = $arr;
            $i++;
        }
        unset($objWorksheet);
        unset($PHPReader);
        unset($PHPExcel);
//        unlink($file);
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="aa.xls"');
        header("Content-Disposition:attachment;filename=$filename.xls"); //attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($target, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    public function push($head, $data, $filename = 'Excel') {
        $objPHPExcel = new \PHPExcel();
        /* 以下是一些设置 ，什么作者  标题啊之类的 */
//        $objPHPExcel->getProperties()->setCreator("转弯的阳光")
//                ->setLastModifiedBy("转弯的阳光")
//                ->setTitle("数据EXCEL导出")
//                ->setSubject("数据EXCEL导出")
//                ->setDescription("备份数据")
//                ->setKeywords("excel")
//                ->setCategory("result file");
/*        $ascii = 65;    //A字母的ascii值
        foreach ($head as $val) {   //先制作表头
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue(chr($ascii) . 1, $val);
            $ascii++;
        }*/
        //设置表头
        $key = 0;
        //print_r($headArr);exit;
        foreach($head as $v){
            //注意，不能少了。将列数字转换为字母\
            $colum = \PHPExcel_Cell::stringFromColumnIndex($key);
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $key += 1;
        }
        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();

        foreach($data as $key => $rows){ //行写入
            $span = 0;
            foreach($rows as $keyName=>$value){// 列写入
                $j = \PHPExcel_Cell::stringFromColumnIndex($span);
                $objActSheet->setCellValue($j.$column, $value);
                $span++;
            }
            $column++;
        }

        
        /* 写入数据 */
/*        foreach ($data as $k => $row) {
            $num = $k + 2;
            $ascii = 65;
            foreach ($row as $column) {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($ascii) . $num, $column);
                $ascii++;
            }
        }*/
        //$objPHPExcel->getActiveSheet()->getStyle('o')->getNumberFormat()->setFormatCode();
      //  $objPHPExcel->getActiveSheet()->getStyle('p')->getNumberFormat()->setFormatCode();
        $objActSheet->getColumnDimension('o')->setWidth(30);//改变此处设置的长度数值
        $objActSheet->getColumnDimension('p')->setWidth(30);//改变此处设置的长度数值
        $objPHPExcel->getActiveSheet()->setTitle($filename);
        $objPHPExcel->setActiveSheetIndex(0);
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="aa.xls"');
        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    
}
