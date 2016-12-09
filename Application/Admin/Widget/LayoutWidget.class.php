<?php

namespace Admin\Widget;
use Think\Controller;

class LayoutWidget extends Controller {

    public function pageFooter($total) {
        $page_sizes = C('page_sizes');
        $page_default_size = C('page_default_size');
        $this->assign('total', $total);
        $this->assign('page_sizes', $page_sizes);
        $this->assign('page_default_size', $page_default_size);
        $this->display('Layout:page_footer');
    }

}
