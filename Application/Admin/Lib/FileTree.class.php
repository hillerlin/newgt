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
    private static $pro_id;


    public function __construct($paths, $pro_id, $maxId = 0) {
        $this->paths = $paths;
        self::$pro_id = $pro_id;
        $this->maxId = $maxId;
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
        if (empty($this->dirs)) {
            $pid = 0;
        }
//        $basename = basename($path);
//        var_dump($basename);
        $path_arr = explode('/', $path);
//        var_dump($path_arr);
//        $path_parts  =  pathinfo ( $path );
        $basename = array_pop($path_arr);
        foreach($path_arr as $val) {
            if (array_key_exists($val, $this->dirs)) {
                $pid = $this->dirs[$val]['file_id'];
                $current_file_id = $pid;
                continue;
            }
//            var_dump($basename);
//            var_dump($val);
            if ($basename == $val) {
                self::$files[$val] = array('file_id' => $file_id);
            } else {
                $this->dirs[$val] = array('file_id' => $file_id + 1, 'pid' => $pid);
                $pid = $file_id + 1;
                $file_id++;
                $current_file_id = $file_id;
            }
        }
        self::$files[$this->key] = array('file_id' => $current_file_id);
//        var_dump($this->key,self::$files[$this->key] ,$basename);
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
