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

    public function testImport($file, $ar, $filename,$mergeCellIndex,$count,$fontSize=20,$rowHeight=100)
    {
        if (!file_exists($file)) {
            return array("error" => 0, 'message' => 'file not found!');
        }
        $ext = pathinfo($file, PATHINFO_EXTENSION);

        $excel_version = $ext == 'xls' ? 'Excel5' : 'Excel2007';

        Vendor("PHPExcel.PHPExcel.IOFactory");


// 2. 通过读取已有的模板创建
        $phpexcel = \PHPExcel_IOFactory::createReader("$excel_version")->load($file);
       // put(json_encode($phpexcel));
      //  var_dump($phpexcel);
        /**
         * 实例化之后的PHPExcel对象类似于一个暂存于内存中文档文件，
         * 可以对它进行操作以达到修改文档数据的目的
         */
// 设置文档属性
/*        $phpexcel->getProperties()->setCreator("Liu Jian") // 文档作者
        ->setLastModifiedBy("Liu Jian") // 最后一次修改者
        ->setTitle("Office 2003 XLS Test Document") // 标题
        ->setSubject("Office 2003 XLS Test Document") // 主题
        ->setDescription("Test document for Office 2003 XLS, generated using PHPExcel.") // 备注
        ->setKeywords("office 2003 openxml php") // 关键字
        ->setCategory("Test result file"); // 类别*/

// 默认状态下，新创建的空白文档（通过new）只有一个工作表（sheet），且它的编号（index）为0
// 可以通过如下的方式添加新的工作表
      //  $phpexcel->createSheet(1);

// 获取已有编号的工作表
        //$phpexcel->getSheet(0);

// 设置当前激活的工作表编号
       // $phpexcel->setActiveSheetIndex(0);

// 获取当前激活的工作表
        $sheet = $phpexcel->getActiveSheet(0);

// 得到工作表之后就可以操作它的单元格以修改数据了
// 修改工作表的名称
      //  $sheet->setTitle("Test");

// 设置单元格A&的值
        
        $sheet->insertNewRowBefore(7, $count);
        $attrMv=array();
        $newMv=array();
       //$money=0;
        foreach ($mergeCellIndex as $mk=>$mv)
        {
            //$newMv=reset(explode('-',$mv));
            //$mv='G8-G9-G10-G11';
            $newMv=explode('-',$mv);
            $firtIndex=current($newMv);
            $endIndx=end($newMv);
            $sheet->mergeCells($firtIndex.':'.$endIndx);// 合并单元格
            $sheet->setCellValue($firtIndex, $ar[$firtIndex]); //预先把账号设置好
            $sheet->getStyle($firtIndex)->getFont()->setBold(true)->setSize($fontSize);
            $sheet->getRowDimension(substr($endIndx,1))->setRowHeight('30'); // 行高
            $style = $sheet->getStyle($firtIndex);
           //$sheet->getColumnDimension($firtIndex)->setRowHeight(20);
            $style->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER); // 水平方向
            $attrMv=array_merge($attrMv,$newMv);
            $newMv=array();
        }
        foreach ($ar as $k=>$v)
        {
            $sign=substr($k,1);
            if(in_array($k,$attrMv))
                continue;
            $sheet->setCellValue($k, $v);
            $style = $sheet->getStyle($k);
            $sheet->getStyle($k)->getFont()->setBold(true)->setSize($fontSize);
            if(substr($k,0,1)=='G')
            {
                $sheet->getRowDimension(substr($k,1))->setRowHeight($rowHeight); // 行高
              //  $sheet->getDefaultRowDimension('G7')->setRowHeight(-1);
                $style->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER); // 水平方向
            }elseif (substr($k,0,1)=='B' && ($sign-$count==11 || $sign-$count==13 || $sign-$count==15 || $sign-$count==17 || $sign-$count==19 || $sign-$count==21))
            {
                $sheet->getRowDimension(substr($k,1))->setRowHeight('20'); // 行高
                $style->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER); // 水平方向

            }
            else
            {
                $style->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER); // 水平方向
                $sheet->getRowDimension(substr($k,1))->setRowHeight($rowHeight); // 行高
            }
        }
       // $sheet->getRowDimension('G8')->setRowHeight('90'); // 行高
// 设置第3行第5列（E3）的值
      //  $sheet->setCellValueByColumnAndRow(4, 3, date('Y-m-d h:i:s'));

// 获取单元格A5的值
       // $sheet->getCell("A5")->getValue();

// 合并单元格
       // $sheet->mergeCells("C3:G6");

// 拆分合并的单元格
       // $sheet->unmergeCells("C3:G6");

// 设置第3行的属性
       // $sheet->getRowDimension(3)->setRowHeight(100) // 行高
       // ->setVisible(true) // 是否可见，默认为true
      //  ->setRowIndex(6) // 变更行号为6
       // ->setOutlineLevel(5); // 优先级别，默认为0，参数必须是0到7

// 设置第F列的属性
// getColumnDimension("F")可以用getColumnDimensionByColumn(5)代替
     //   $sheet->getColumnDimension("F")->setWidth(200) // 列宽
     //   ->setColumnIndex("I") // 变更列号为I
     //   ->setVisible(false) // 是否可见
      //  ->setAutoSize(true); // 自动适应列宽

// 在第3行前面插入1行，该行将变成新的第3行，其它的依次下移1行
       // $sheet->insertNewRowBefore(3, 1);

// 在第C行前面插入1列，该列将变成新的第C列，其它的依次右移1列
      //  $sheet->insertNewColumnBefore("C", 1); // 方法一
      //  $sheet->insertNewColumnBeforeByIndex(2, 1); // 方法二，第C列又是第2列

// 获取单元格D3的样式对象
       // $style = $sheet->getStyle("A7"); // 等价于getStyleByColumnAndRow(3, 3)

// 设置该单元格的字体属性
      //  $style->getFont()->setBold(true)->setSize(16);//->setName("Gungsuh")->setItalic(true)->setStrikethrough(true)->setUnderline(\PHPExcel_Style_Font::UNDERLINE_DOUBLEACCOUNTING)->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLUE);
        // 是否粗体
         // 字号
         // 字体名，只适用于外文字体
         // 是否斜体
         // 是否有删除线
         // 下划线类型
         // 字体颜色

// 设置该单元格的背景填充属性
//        $style->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID) // 填充模式
  //      ->getStartColor()->setARGB(PHPExcel_Style_Color::COLOR_YELLOW); // 背景颜色

// 设置该单元格中数字的格式
      //  $style->getNumberFormat()->setFormatCode("0.00");

// 设置该单元格中文本对齐方式
      //  $style->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER); // 水平方向

        //   $style->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER); // 水平方向
         // 垂直方向
       // $sheet->setCellValue("D3", "12.3456");

// 在本地保存文档
      //  PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007')->save("output.xls");

// 输出文档到页面
/*        header('pragma:public');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$filename.'.xlsx"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        \PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007')->save('php://output');
        exit;*/
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="aa.xls"');
        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
        $objWriter = \PHPExcel_IOFactory::createWriter($phpexcel, $excel_version);
        $objWriter->save('php://output');
        exit;
    }

    //put your code here
    public function importExecl($file, $ar, $filename) {
        if (!file_exists($file)) {
            return array("error" => 0, 'message' => 'file not found!');
        }
        Vendor("PHPExcel.PHPExcel.IOFactory");
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $excel_version = $ext == 'xls' ?  'Excel5' : 'Excel2007';
        $objReader = \PHPExcel_IOFactory::createReader($excel_version);
        try {
            $PHPReader = $objReader->load($file);
           // $target = clone $PHPReader;
        } catch (Exception $e) {
            
        }
        if (!isset($PHPReader))
            return array("error" => 0, 'message' => 'read error!');
        $allWorksheets = $PHPReader->getAllSheets();
        $i = 0;
        foreach ($allWorksheets as $objWorksheet) {
            $sheetname = $objWorksheet->getTitle();
            $allRow = $objWorksheet->getHighestRow(); //how many rows
            $highestColumn = $objWorksheet->getHighestColumn(); //how many columns
            $allColumn = \PHPExcel_Cell::columnIndexFromString($highestColumn);
            $array[$i]["Title"] = $sheetname;
            $array[$i]["Cols"] = $allColumn;
            $array[$i]["Rows"] = $allRow;
            $arr = array();
            $isMergeCell = array();
            foreach ($objWorksheet->getMergeCells() as $cells) {   //merge cells
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
                   // $target->getActiveSheet()->getStyle('A1')->getFont()->setSize('28');
                    if (array_key_exists($currentColumnPosition, $ar)) {
                        $value .= $ar[$cell->getCoordinate()];
                        //$target->getActiveSheet()->getCell($currentColumnPosition)->getStyle($currentColumnPosition)->getFont()->setName('Calibri')->setSize('18');
                        //$target->getActiveSheet()->getStyle($currentColumnPosition)->getFill()->getStartColor()->setARGB('FF808080');
                        $PHPReader->getActiveSheet()->getCell($currentColumnPosition)->setValue($value);
                        $PHPReader->getActiveSheet()->getStyle($currentColumnPosition)->getFont()->setSize('18');
                        //$bb=$target->getActiveSheet()->getStyle('A1')->getFont()->getSize();
                        //  $bb=$target->getActiveSheet()->getStyle($currentColumnPosition)->getFill()->getStartColor()->setARGB('00ff99cc'); // 将背景设置为浅粉色

                    }
                if ($cell->getDataType() == \PHPExcel_Cell_DataType::TYPE_NUMERIC) {
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
                }
                $arr[$currentRow] = $row;
            }
           // $bb=$target->getActiveSheet()->getStyle('A7')->getFont()->getSize();
            $array[$i]["Content"] = $arr;
           $i++;
        }
       // unset($objWorksheet);
       // unset($PHPReader);
       // unset($PHPExcel);
//        unlink($file);
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="aa.xlsx"');
        header("Content-Disposition:attachment;filename=$filename.xlsx"); //attachment新窗口打印inline本窗口打印
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $objWriter = \PHPExcel_IOFactory::createWriter($PHPReader, $excel_version);
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
