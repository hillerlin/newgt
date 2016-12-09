<?php

namespace Admin\Lib;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class MsgTmp {

    //消息id
    private $id;
    private $replace;

    public function __get($name) {
        if (array_key_exists($name, $this->data)) {
            return $this->data [$name];
        }
        return null;
    }

    protected static function tmplate($key) {
        $arr = array(
            '1-2' => array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '项目状态变更-<code>###</code>项目，立项申请。',
                'title' => '项管审核'
            ),
            '2-3' => array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '项目状态变更-<code>###</code>项目，项管部审核通过提交风控初审。',
                'title' => '项管审核'
            ),
            '3-4' => array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '项目状态变更-<code>###</code>项目，风控初审通过提交项目立项会。',
                'title' => '项管审核'
            ),
            '4-5' => array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '项目状态变更-<code>###</code>项目，项目立项会通过提交风控部尽调。',
                'title' => '项管审核'
            ),
            '5-6' => array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '项目状态变更-<code>###</code>项目，风控部尽调通过提交风控会。',
                'title' => '项管审核'
            ),
            '6-7' => array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '项目状态变更-<code>###</code>项目，风控会通过提交投委会。',
                'title' => '项管审核'
            ),
            '7-8' => array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '项目状态变更-<code>###</code>项目，投委会通过进入合同预签。',
                'title' => '项管审核'
            ),
            '8-9' => array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '项目状态变更-<code>###</code>项目，合同预签审核通过签订合同。',
                'title' => '项管审核'
            ),
            '9-10' => array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '项目状态变更-<code>###</code>项目，合同签订通过等待放款。',
                'title' => '项管审核'
            ),
            '10-11' => array(
                'controller' => 'Project',
                'action' => 'index',
                'description' => '项目状态变更-<code>###</code>项目，放款成功。',
                'title' => '项管审核'
            ),
            '11-12' => array(
                'controller' => 'Project',
                'action' => 'finish',
                'description' => '项目状态变更-<code>###</code>项目，完结',
                'title' => '项管完结'
            ),
        );
        return $arr[$key];
    }

    protected static function templateReverse($key) {

        $arr = array(
            '3-2' => array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '项目状态变更-<code>###</code>项目，风控初审退回。',
                'title' => '项管审核'
            ),
            'x-0' => array(
                'controller' => 'Project',
                'action' => 'finish',
                'description' => '项目状态变更-<code>###</code>项目，XXX审核不通过。',
                'title' => '项管审核'
            ),
            '13-2' => array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '项目状态变更-<code>###</code>项目，项目重新发起。',
                'title' => '项管审核'
            ),
        );
        return $arr[$key];
    }

    public function workFlowTmpMsg($pro_title, $pro_step_desc) {
        $this->data['description'] = $pro_title . $this->data['description'] . $pro_step_desc;
    }

    public static function getTmplate($pro_step, $next_step, $pro_title) {
//        var_dump($pro_step);
//        var_dump($next_step);exit;
        if ($pro_step < $next_step) {
            $key = $pro_step . '-' . $next_step;
            $tmpMsg = static::tmplate($key);
        } elseif ($pro_step == 13) {
            $key = '13-2';
            $tmpMsg = static::templateReverse($key);
        }else {
            if ($next_step != 0) {
                $key = $next_step . '-' . $pro_step;
            } else {
                $key = 'x-0';
                $workflow = self::workflow($pro_step);
            }
            $tmpMsg = static::templateReverse($key);
            $tmpMsg['description'] = str_replace('XXX', $workflow, $tmpMsg['description']);
        }
        $tmpMsg['description'] = str_replace('###', $pro_title, $tmpMsg['description']);
        
        return $tmpMsg;
    }

    public static function workflow($pro_step) {
        $workFlow = array(
            2 => '项管部审核',
            3 => '风控初审',
            4 => '项目立项会',
            5 => '风控部尽调',
            6 => '风控会',
            7 => '投委会',
            8 => '合同签订',
            9 => '放款',
        );
        return $workFlow[$pro_step];
    }
    
    public function exchangePush() {
        return array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '项目交接-<code>###</code>项目，。',
                'title' => '项管审核'
            );
    }
    
    protected static function backlog($key) {
        $arr = array(
            '1-2' => array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '<code>###</code>项目申请立项---待分配跟进人。',
                'title' => '项管审核'
            ),
            '2-3' => array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '<code>###</code>项目申请风控初审---待审核。',
                'title' => '项管审核'
            ),
            '3-4' => array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '<code>###</code>项目申请项目立项会---待审核。',
                'title' => '项管审核'
            ),
            '4-5' => array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '<code>###</code>项目申请风控部尽调---待审核。',
                'title' => '项管审核'
            ),
            '5-6' => array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '<code>###</code>项目申请风控会---待审核。',
                'title' => '项管审核'
            ),
            '6-7' => array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '<code>###</code>项目申请投委会---待审核。',
                'title' => '项管审核'
            ),
            '7-8' => array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '<code>###</code>项目申请合同预签---待审核。',
                'title' => '项管审核'
            ),
            '8-9' => array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '<code>###</code>项目申请签订合同---待审核。',
                'title' => '项管审核'
            ),
            '9-10' => array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '<code>###</code>项目申请放款---待审核。',
                'title' => '项管审核'
            ),
//            '10-11' => array(
//                'controller' => 'Project',
//                'action' => 'index',
//                'description' => '<code>###</code>项目放款成功---请在”债权-放款管理“中添加放款记录',
//                'title' => '项管审核'
//            ),
//            '11-12' => array(
//                'controller' => 'Project',
//                'action' => 'finish',
//                'description' => '<code>###</code>项目申请完结---待审核',
//                'title' => '项管完结'
//            ),
            '3-2' => array(
                'controller' => 'Project',
                'action' => 'auditList',
                'description' => '<code>###</code>项目回退---待补充资料。',
                'title' => '项管审核'
            ),
        );
        return isset($arr[$key]) ? $arr[$key] : array();
    }
    
    public static function getBacklog($pro_step, $next_step, $pro_title) {
        if ($pro_step < $next_step) {
            $key = $pro_step . '-' . $next_step;
        }else {
            $key = $next_step . '-' . $pro_step;
        }
        $tmpMsg = static::backlog($key);
        if (empty($tmpMsg)) {
            return array();
        }
        $tmpMsg['description'] = str_replace('###', $pro_title, $tmpMsg['description']);
        return $tmpMsg;
    }
}
