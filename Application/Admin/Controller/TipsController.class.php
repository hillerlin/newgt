<?php

namespace Admin\Controller;

class TipsController extends CommonController {

    public function __construct() {
        parent::__construct();
    }

    /* 上传文件提示 */

    public function upload() {
        $this->display();
    }
    
    public function read() {
        $id = I('get.id');
        $this->json_success();
    }
}
