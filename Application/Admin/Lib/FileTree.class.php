<?php

namespace Admin\Lib;


/**
 * Description of CalcTool
 * 用来生成文件夹关联关系，但是并不真正生成文件夹，只是将文件关联上文件夹
 * 
 */
class FileTree {
    
    private static $files;
    private $dirs;
    private $paths;
    private $maxId;
    private $fileId;
    private static $pro_id;


    public function __construct($paths, $pro_id, $maxId = 0,$fileId=0) {
        $this->paths = $paths;
        self::$pro_id = $pro_id;
        $this->maxId = $maxId;
        $this->fileId=$fileId;
    }
    
    //执行生成方法
    public function mkdir() {
        foreach($this->paths as $key => $path) {
            $this->key = $key;
            $this->generateLik($path, $this->maxId);
        }
    }
    
    /**
     * 
     * @param type $path
     * @param type $file_id
     * @return boolean
     */
    protected function generateLik($path, & $file_id) {
        if (empty($this->dirs)){
            $pid = 0;
        }
        //文件路径分组
        $path_arr = explode('/', $path);
//        $path_parts  =  pathinfo ( $path );
        //推出数组最后，也就是文件名
        $basename = array_pop($path_arr);
        $i = 0;
        foreach($path_arr as $val) {
            $key = $i++;
            $flag = false;
            //如果文件名已经存在了，返回文件id
            if (array_key_exists($val, $this->dirs)) {
                $flag = true;
                //判断是上层文件夹是否一样，不一样需要重建
                //拿到数组的前一个
                $mode = isset($path_arr[$key - 1]) ? $path_arr[$key - 1] : false;
                if ($mode == false || $this->dirs[$mode]['file_id'] == $this->dirs[$val]['pid']) {
                    $flag = false;
                    $pid = $this->dirs[$val]['file_id'];
                    $current_file_id = $pid;
                    continue;
                }
            }
            if ($basename == $val) {
                self::$files[$val] = array('file_id' => $file_id);
            } else {
                if ($flag) {
                    $this_pid = $this->dirs[$mode]['file_id'];
                    $val = $val . '_' . $this_pid;
                }
                if($this->fileId && empty($this->dirs)) //文件夹里面传文件夹 author：lmj ---- writeTime:2017-1-3
                {
                    $this->dirs[$val] = array('file_id' => $file_id + 1, 'pid' => $this->fileId);
                    $pid = $this->fileId;
                }else
                {
                    $this->dirs[$val] = array('file_id' => $file_id + 1, 'pid' => $pid);//普通的模式
                    $pid = $file_id + 1;
                }


                $file_id++;
                $current_file_id = $file_id;
            }
        }
        self::$files[$this->key] = array('file_id' => $current_file_id);
        return true;
    }
    
    public function getFilesInfo() {
        return self::$files;
    }
    
    public function getDirsInfo() {
        return $this->dirs;
    }
    
    public function generateFileName($key) {
//        var_dump($key);
//        var_dump($files);
        $file_id = self::$files[$key]['file_id'];
        $pre_file_name = $file_id . '-';
        $pre_file_name = uniqid($pre_file_name);
        return $pre_file_name;
    }
    
    public function sha1Exists($data) {
        if (D('ProjectAttachment')->sha1Exists(self::$pro_id, $data['sha1'])) {
            return true;
        }
        return false;
    }

}
