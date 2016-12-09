<?php
namespace Admin\Controller;

use Think\Controller;
use Admin\Logic\DepartmentLogic;
use Admin\Lib\Privilege;
use Admin\Lib\Workflow;
use Admin\Lib\MsgTmp;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class CommonController extends Controller {
    public $pageDefaultSize;
    protected $mainModel;
    protected $is_supper;
    protected $is_pmd_boss;
    protected $is_boss;

    public function __construct(){
        parent::__construct();
        $this->pageDefaultSize = C('page_default_size');
        $this->check_login();
//        $this->check_auth();
        $this->checkDataAuth();
        $this->iniParam();
    }
    
    public function check_login(){
        if(!in_array(ACTION_NAME,array('login','logout','makecode','synchronous'))){
            $admin = session('admin');
            if(!($admin['admin_id'] && $admin['admin_name'])){
                $this->redirect('index/login');
                exit;
            }
            $this->assign('admin',$admin);
        }
    }

    public function check_auth(){
        if(in_array(CONTROLLER_NAME, array('Index', 'Profile'))){
            return true;
        }

        $select_menu = D('Role')->check_auth();
        if(!$select_menu) $this->error('对不起，你没有权限');
    }
    
    protected function iniParam() {
        if (empty($this->is_supper)) {
            $this->is_supper = isSupper();
            $this->is_boss = isBoss();
            $this->is_pmd_boss = isPmdBoss();
        }
        $this->assign('is_supper', $this->is_supper);
        $this->assign('is_boss', $this->is_boss);
        $this->assign('is_pmd_boss', $this->is_pmd_boss);
    }
    
    /**
     * 1.先检查是否有文件权限规则，无则退出
     * 2.有权限规，先判断控制器是否在权限规则内
     * 3.规矩权限规则，调用权限判断类进行判断
     */
    protected function checkDataAuth() {
        $action = ACTION_NAME;
        $rules = $this->rules();
        if (!empty($rules) && array_key_exists($action, $rules)) {
            $rule = $rules[$action];
            $admin = session('admin');
            $pass = Privilege::checkDataAuth($rule['type'], $admin['role_id'], $rule['operation']);
            if (!$pass) {
                $this->json_error('你没有相应的权限');
            }
        }
//                var_dump($action);exit;
        return true;
    }
    
    /**
     * 规则设置
     * array('action_name' => array('type' => 'XX', 'operation' => 'xxx'))
     * .e.g
     * array('del'  => array('type' => 'project', 'oepration' => Privilege::DEL))
     * @return type
     */
    protected function rules() {
        return array();
    }
    
    /**
     * 发送数组给前端
     * @param type $data
     * @return void
     */
    protected function sendData($data) {
        $params = array(
            'statusCode' => 200,
            'content' => $data
        );
        $this->ajaxRe($params);
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
    
    /**
     * 记录日志
     * @param type $actions
     * @param type $function
     * @param type $message
     */
    protected static function log($actions, $message) {
        $admin = session('admin');
        adminLog($actions, $admin['admin_id'], $admin['real_name'], $message);
    }
}
