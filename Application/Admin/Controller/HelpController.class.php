<?php

namespace Admin\Controller;

class HelpController extends CommonController {

    public function __construct() {
        parent::__construct();
    }

    /* 上传文件提示 */

    public function upload() {
        $pro_id = I('get.pro_id');
        $this->assign('pro_id', $pro_id);
        if (D('Project')->getSubStatus($pro_id)) {
            $this->error('项目已被提交，禁止使用文件夹上传功能');
        }
        $this->display();
    }

    public function uploadDocument() {
//        echo phpinfo();
       
        $key=end(array_keys(I('get.')));
        $this->assign('k',$key);
        $this->assign('v', I('get.'.$key));
//        if (D('Project')->getSubStatus($pro_id)) {
//            $this->error('项目已被提交，禁止使用文件夹上传功能');
//        }
        $this->display('upload_3');
    }
	public function upFundDocument(){
		//获取要操作的id值
		$fund_id = I('get.fund_id');
		if (empty($fund_id)) {
			$this->error('非法操作');
		}
		//获取管理员的相关信息
		$admin = session('admin');
		if (!$this->checkAuthUpload($fund_id, $admin['role_id'], $admin['admin_id'])) {
			$this->error('本阶段您没有上传权限');
		}
		//将操作对象的ID值存入到session中
		session('fund_id', $fund_id);
		//切割路径
		$paths = explode("###", rtrim($_POST['paths'], "###"));
		//操作结果存入到日志中
		upload_log($fund_id, json_encode($paths));
		
		//获取文件的数量
		$maxId = M('FundFile')->max('file_id');
		$sql=M()->getLastSql();
		$fileTree = new \Admin\Lib\FileTree($paths, $fund_id, $maxId);
		//生成相关的路径
		$fileTree->mkdir();
		//获取目录信息
		$dirs_info = $fileTree->getDirsInfo();
		$field = 'fund-' . $fund_id;
		//上传文件
		$upload_info = upload_document('/fund/attachment/', $field);
		//将生成的结果存入到数据库zhon
		foreach ($upload_info as $val) {
			$file_names = explode('-', $val['savename']);
			$file_id = $file_names[0];
			$save_data['file_id'] = $file_id;
			$save_data['fund_id'] = $fund_id;
			$save_data['path'] = '/Uploads' . $val['savepath'] . $val['savename'];
			$save_data['doc_name'] = $val['name'];
			$save_data['addtime'] = time();
			$save_data['admin_id'] = $admin['admin_id'];
			$save_data['sha1'] = $val['sha1'];
			$save_datas[] = $save_data;
		}
		if (!D('FundFile')->makeDir($fund_id, $dirs_info)) {
			$this->error('文件夹创建失败');
		}
		if (!(M('FundAttachment')->addAll($save_datas))) {
			$this->error('上传失败');
		}
		$this->success('上传成功');
		self::log('add', json_encode($save_datas));
    }


    //文件夹上传
    public function upDocument() {
    	//获取要操作的id值
        $pro_id = I('get.pro_id');
        if (empty($pro_id)) {
            $this->error('非法操作');
        }
        //获取管理员的相关信息
        $admin = session('admin');
        if (!$this->checkAuthUpload($pro_id, $admin['role_id'], $admin['admin_id'])) {
            $this->error('本阶段您没有上传权限');
        }
        //将操作对象的ID值存入到session中
        session('pro_id', $pro_id);
        //切割路径
        $paths = explode("###", rtrim($_POST['paths'], "###"));
        //操作结果存入到日志中
        upload_log($pro_id, json_encode($paths));

		//获取项目文件的相关信息
        $file_model = D('ProjectFile');
        //获取文件的数量
        $maxId = $file_model->getMaxId($pro_id);

        $fileTree = new \Admin\Lib\FileTree($paths, $pro_id, $maxId);
        //生成相关的路径
        $fileTree->mkdir();
        //获取目录信息
        $dirs_info = $fileTree->getDirsInfo();
        $field = 'pro-' . $pro_id;
        //上传文件
        $upload_info = upload_document('/project/attachment/', $field);
		//将生成的结果存入到数据库zhon
        foreach ($upload_info as $val) {
            $file_names = explode('-', $val['savename']);
            $file_id = $file_names[0];
            $save_data['file_id'] = $file_id;
            $save_data['pro_id'] = $pro_id;
            $save_data['path'] = '/Uploads' . $val['savepath'] . $val['savename'];
            $save_data['doc_name'] = $val['name'];
            $save_data['addtime'] = time();
            $save_data['admin_id'] = $admin['admin_id'];
            $save_data['sha1'] = $val['sha1'];
            $save_datas[] = $save_data;
        }
        if (!D('ProjectFile')->makeDir($pro_id, $dirs_info)) {
            $this->error('文件夹创建失败');
        }
        if (!(D('ProjectAttachment')->addAll($save_datas))) {
            $this->error('上传失败');
        }
        $this->success('上传成功');
        self::log('add', json_encode($save_datas));
//        var_dump($upload_info);
    }
    
    public function checkAutho() {
        $pro_id = I('post.pro_id');
        $admin = session('admin');
        if (!$this->checkAuthUpload($pro_id, $admin['role_id'], $admin['admin_id'])) {
            $this->error('本阶段您没有上传权限');
        }
        $this->success('可以上传');
    }

    /**
     * 查询上传的权限
     * @param type $pro_id
     * @param type $role_id
     * @return boolean
     */
    protected function checkAuthUpload($pro_id, $role_id, $admin_id) {
        if (isSupper()) {
            return true;
        }
        if (in_array($role_id, array(14, 2))) {
            return true;
        }
        $pro_info = D('Project')->findByPk($pro_id, 'pro_step,step_pid,pro_linker');
        //现在项目提交以后项目经理不能提交文档；判断权限是否在可上传的权限中；
        if ($pro_info['step_pid'] == 1 && $pro_info['pro_step'] == 1 && $pro_info['pro_linker'] == $admin_id) {
            return true;
        }
        return false;
    }
    
    public function download() {
        $pro_id = I('get.pro_id', 0);
//        var_dump($file);
        $admin = session('admin');
        if ($this->checkDownloadAuth($pro_id, $admin['role_id'], $admin['admin_id']) === false) {
            echo json_encode(array('statusCode' => 300, 'message' => '非法操作！'));
            exit();
        }
        $pro_info = D('Project')->findByPk($pro_id);
        $document_root = $_SERVER["DOCUMENT_ROOT"];
        $file = "$document_root/Uploads/project/attachment/download/pro-$pro_id.zip";
        $filePath = "/protected/pro-$pro_id.zip";
        if (!file_exists($file)) {
            $this->zip($pro_id, $file);
        }
        $filename = $pro_info['pro_title'].'.zip';
        header("Content-type: application/octet-stream");
        //处理中文文件名
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $encoded_filename = rawurlencode($filename);
        if (preg_match("/MSIE/", $ua)) {
         header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
         header("Content-Disposition: attachment; filename*=\"utf8''" . $filename . '"');
        } else {
         header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
//        header("Content-Length: ". filesize($file));
//        readfile($file);
        //apache  mod_xsendfile 让Xsendfile发送文件
//        header("X-Sendfile: $file");
        //nginx
        header('X-Accel-Redirect: '.$filePath);
    }
    
    protected function zip($pro_id, $file) {
        $map['pro_id'] = $pro_id;
        $file_list = D('ProjectAttachment')->where($map)->select();
        $doc = D('ProjectFile')->where($map)->select();
        if (empty($doc) || empty($file_list)) {
            echo json_encode(array('statusCode' => 300, 'message' => '文件包不存在文件'));
            exit();
            $this->json_error('文件包不存在文件');
        }
        $doc = array_switch_key($doc, 'file_id');
        $tree = new \Admin\Lib\Tree;
        $tree->init($doc, 'file_id');
        $zip = new \ZipArchive();
        $document_root = $_SERVER["DOCUMENT_ROOT"];
        if ($zip->open($file, \ZipArchive::CREATE) === TRUE) {
            foreach ($file_list as & $v) {
                $path = '';
                $tree->getPath($v['file_id'], 0, 'file_name', $path);
                $v['local_path'] = $path;
//                var_dump($path);
                $local_path = $path . '/' . $v['doc_name'];
                $abpath = $document_root.$v['path'];
//                var_dump($abpath, $local_path);
                $zip->addFile($abpath, $local_path);
            }
            
        }
      
        $zip->close();
    }


    
    protected function checkDownloadAuth($pro_id, $role_id, $admin_id) {
        if (isSupper()) {
            return true;
        }
        if (in_array($role_id, array(14, 2, 17, 18, 19, 20, 21, 24, 26, 28))) {
            return true;
        }
        $pro_info = D('Project')->findByPk($pro_id, 'pro_step,step_pid,pro_linker');
        //现在项目提交以后项目经理不能提交文档；判断权限是否在可上传的权限中；
        if ($pro_info['pro_linker'] == $admin_id) {
            return true;
        }
        return false;
    }
    
    public function downloadChecked() {
        $pro_id = I('get.pro_id', 0);
        $file_id = I('get.file_id', 0);
//        var_dump($file);
        $admin = session('admin');
        if ($this->checkDownloadAuth($pro_id, $admin['role_id'], $admin['admin_id']) === false) {
            echo json_encode(array('statusCode' => 300, 'message' => '非法操作！'));
            exit();
        }
        $pro_info = D('Project')->findByPk($pro_id);
        $document_root = $_SERVER["DOCUMENT_ROOT"];
        $uniqid = uniqid();
        $file = "$document_root/Uploads/project/attachment/download/tmp-$uniqid-$pro_id.zip";
        $filePath = "/protected/tmp-$uniqid-$pro_id.zip";
        $this->zipChecked($pro_id, $file_id, $file);
        $filename = $pro_info['pro_title'].'.zip';
        header("Content-type: application/octet-stream");
        //处理中文文件名
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $encoded_filename = rawurlencode($filename);
        if (preg_match("/MSIE/", $ua)) {
         header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
         header("Content-Disposition: attachment; filename*=\"utf8''" . $filename . '"');
        } else {
         header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
//        header("Content-Length: ". filesize($file));
//        readfile($file);
        //apache  mod_xsendfile 让Xsendfile发送文件
//        header("X-Sendfile: $file");
        //nginx
        header('X-Accel-Redirect: '.$filePath);
    }
    public function downloadFundChecked() {
        $fund_id = I('get.fund_id', 0);
        $file_id = I('get.file_id', 0);
//        var_dump($file);
        $admin = session('admin');
        if ($this->checkDownloadAuth($fund_id, $admin['role_id'], $admin['admin_id']) === false) {
            echo json_encode(array('statusCode' => 300, 'message' => '非法操作！'));
            exit();
        }
        $pro_info = D('FundManage')->findByPk($fund_id);
        $document_root = $_SERVER["DOCUMENT_ROOT"];
        $uniqid = uniqid();
        $file = "$document_root/Uploads/fund/attachment/download/tmp-$uniqid-$fund_id.zip";
        $filePath = "/protected/tmp-$uniqid-$fund_id.zip";
        $this->zipFundChecked($fund_id, $file_id, $file);
        $filename = $pro_info['pro_title'].'.zip';
        header("Content-type: application/octet-stream");
        //处理中文文件名
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $encoded_filename = rawurlencode($filename);
        if (preg_match("/MSIE/", $ua)) {
         header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
         header("Content-Disposition: attachment; filename*=\"utf8''" . $filename . '"');
        } else {
         header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
//        header("Content-Length: ". filesize($file));
//        readfile($file);
        //apache  mod_xsendfile 让Xsendfile发送文件
//        header("X-Sendfile: $file");
        //nginx
        header('X-Accel-Redirect: '.$filePath);
    }
    protected function zipFundChecked($fund_id, $file_id, $file) {
        $map['fund_id'] = $fund_id;
        $map['file_id'] = array('in', $file_id);
        $file_list = D('FundAttachment')->where($map)->select();
//        var_dump($file_list);exit;
        $doc = D('FundFile')->where($map)->select();
        if (empty($doc) || empty($file_list)) {
            echo json_encode(array('statusCode' => 300, 'message' => '文件包不存在文件'.D('FundAttachment')->_sql()));
            exit();
            $this->json_error('文件包不存在文件');
        }
        $doc = array_switch_key($doc, 'file_id');
        $tree = new \Admin\Lib\Tree;
        $tree->init($doc, 'file_id');
        $zip = new \ZipArchive();
        $document_root = $_SERVER["DOCUMENT_ROOT"];
        if ($zip->open($file, \ZipArchive::CREATE) === TRUE) {
            foreach ($file_list as & $v) {
                $path = '';
                $tree->getPath($v['file_id'], 0, 'file_name', $path);
                $v['local_path'] = $path;
                $local_path = $path . '/' . $v['doc_name'];
                $abpath = $document_root.$v['path'];
                $zip->addFile($abpath, $local_path);
            }
            var_dump($zip->getStatusString());
        }
        var_dump($zip->getStatusString());
        $zip->close();
    }
    protected function zipChecked($pro_id, $file_id, $file) {
        $map['pro_id'] = $pro_id;
        $map['file_id'] = array('in', $file_id);
        $file_list = D('ProjectAttachment')->where($map)->select();
//        var_dump($file_list);exit;
        $doc = D('ProjectFile')->where($map)->select();
        if (empty($doc) || empty($file_list)) {
            echo json_encode(array('statusCode' => 300, 'message' => '文件包不存在文件'.D('ProjectAttachment')->_sql()));
            exit();
            $this->json_error('文件包不存在文件');
        }
        $doc = array_switch_key($doc, 'file_id');
        $tree = new \Admin\Lib\Tree;
        $tree->init($doc, 'file_id');
        $zip = new \ZipArchive();
        $document_root = $_SERVER["DOCUMENT_ROOT"];
        if ($zip->open($file, \ZipArchive::CREATE) === TRUE) {
            foreach ($file_list as & $v) {
                $path = '';
                $tree->getPath($v['file_id'], 0, 'file_name', $path);
                $v['local_path'] = $path;
                $local_path = $path . '/' . $v['doc_name'];
                $abpath = $document_root.$v['path'];
                $zip->addFile($abpath, $local_path);
            }
            var_dump($zip->getStatusString());
        }
        var_dump($zip->getStatusString());
        $zip->close();
    }
    
    //导入基金报表（一次性方法，在导入以后最好禁用次方法）
    public function uploadFund() {
        $file = $_FILES['fund'];
//        var_dump($file);exit;
//        $filepath = $file['tmp_name'];
        if (!file_exists($file['tmp_name'])) {
            $this->json_error('file not found!');
        }
        $ext = pathinfo($file['tmp_name'], PATHINFO_EXTENSION);
        $filepath = './Uploads/fund.'.$ext;
//        var_dump($filepath);exit;
        move_uploaded_file($file['tmp_name'], $filepath);
        Vendor("PHPExcel.PHPExcel");
        Vendor("PHPExcel.PHPExcel.IOFactory");
        $excel_version = $ext == 'xls' ?  'Excel5' : 'Excel2007';
        $objReader = \PHPExcel_IOFactory::createReader($excel_version);
        try {
            $PHPReader = $objReader->load($filepath);
        } catch (Exception $e) {
            
        }
        if (!isset($PHPReader)) {
            $this->json_error('read error!');
        }
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
            for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                $row = array();
                for ($currentColumn = 0; $currentColumn < $allColumn; $currentColumn++) {
                    $cell = $objWorksheet->getCellByColumnAndRow($currentColumn, $currentRow);
                    $afCol = \PHPExcel_Cell::stringFromColumnIndex($currentColumn + 1);
                    $bfCol = \PHPExcel_Cell::stringFromColumnIndex($currentColumn - 1);
                    $col = \PHPExcel_Cell::stringFromColumnIndex($currentColumn);
                    $address = $col . $currentRow;
                    $value = $objWorksheet->getCell($address)->getValue();


                    if ($cell->getDataType() == \PHPExcel_Cell_DataType::TYPE_NUMERIC) {
                        $cellstyleformat = $cell->getStyle($cell->getCoordinate())->getNumberFormat();
                        $formatcode = $cellstyleformat->getFormatCode();
                        if (preg_match('/^([$[A-Z]*-[0-9A-F]*])*[hmsdy]/i', $formatcode)) {
                            $value =  \PHPExcel_Shared_Date::ExcelToPHP($value);
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
            $array[$i]["Content"] = $arr;
            $i++;
        }
        unset($objWorksheet);
        unset($PHPReader);
        unset($PHPExcel);
//        print_r($array);exit;
        $content = $array[0]['Content'];
//        print_r($content);
        $value = '';
        $managers = D('FundManager')->select();
        foreach ($managers as $val) {
            $manager_list[$val['fmanager_id']] = $val['real_name'];
        }
        $time = time();
        foreach ($content as $val) {
            $val[10] = array_search($val[10], $manager_list);
            $tmp = implode('\',\'', $val);
//            var_dump($tmp);
            $value .= "(' $tmp','{$time}'),";
//            var_dump($value);
        }
        
        $sql = 'INSERT INTO gt_fund_order (partnership,pay_time,begin_interest_time,done_time,customer_name,fund_title,money,fund_rate,term,deadline,fmanager_id,contract_no,id_no,bank_no,link_type,remark,performance_rate,manage_rate,addtime)';
        $values = substr($value, 0, -1);
        $sql .= "VALUES $values";
//        print_r($sql);
        $re = D('FundOrder')->execute($sql);
        if ($re) {
            $this->json_success('导入成功');
        } 
        $this->json_error('导入失败');
//        print_r($re);
    }
    
}
