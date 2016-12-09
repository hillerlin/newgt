<?php
namespace Home\Controller;

use Think\Controller;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class CommonController extends Controller {
    public $pageDefaultSize;
    protected $mainModel;

    public function __construct(){
        parent::__construct();
        $this->pageDefaultSize = C('page_default_size');
        $this->check_login();
    }
    
    public function check_login(){
        if(!in_array(ACTION_NAME,array('login','logout','makecode','synchronous'))){
            $member = session('member');
            if(!($member['mid'])){
                $this->redirect('index/login');
                exit;
            }
            $this->assign('member',$member);
        }
    }
    
    /**
     * 发送数组给前端
     * @param type $data
     * @return void
     */
    protected function sendData($data) {
        $this->dispatch(0, '', $data);
    }

    /**
     * 分配
     * @param type $status
     * @param type $message
     * @param type $content
     * @param type $jumpUrl
     * @return void
     */
    private function dispatch($statusCode = 200, $message = '', $closeCurrent = false, $jumpUrl = '', $forwardConfirm = '', $reload = array()) {
        $data = array(
            'statusCode' => $statusCode,    //必选。状态码(ok = 200, error = 300, timeout = 301)，可以在BJUI.init时配置三个参数的默认值。
            'closeCurrent' => $closeCurrent,        //可选。是否关闭当前窗口(navtab或dialog)。
            'message' => $message,          //可选。信息内容。
            'tabid' => isset($reload['tabid']) ? $reload['tabid'] : '',              //可选。待刷新navtab id，多个id以英文逗号分隔开，当前的navtab id不需要填写，填写后可能会导致当前navtab重复刷新。
            'dialogid' => isset($reload['dialogid']) ? $reload['dialogid'] : '',        //可选。待刷新div id，多个id以英文逗号分隔开，请不要填写当前的div id，要控制刷新当前div，请设置该div中表单的reload参数。
            'divid' => isset($reload['divid']) ? $reload['divid'] : '',              //可选。待刷新div id，多个id以英文逗号分隔开，请不要填写当前的div id，要控制刷新当前div，请设置该div中表单的reload参数。
            'forward' => $jumpUrl,          //可选。跳转到某个url。
            'forwardConfirm' => $forwardConfirm,    //可选。跳转url前的确认提示信息。
            
        );
        $this->ajaxRe($data);
    }

    protected function json_success($message = '', $jumpUrl = '',$forwardConfirm = '', $closeCurrent = false, $reload = array()) {
        $this->dispatch(200, $message, $closeCurrent, $jumpUrl, $forwardConfirm, $reload);
    }

    protected function json_error($message = '', $jumpUrl = '',$forwardConfirm = '', $closeCurrent = false, $reload = array()) {
        $this->dispatch(300, $message, $closeCurrent, $jumpUrl, $forwardConfirm, $reload);
    }

    /**
     * ajax返回数据
     * @param type $data 要返回的数据
     * @return void
     */
    protected function ajaxRe($data) {
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    }
}
