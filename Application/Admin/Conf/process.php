<?php

return array(
    'process' => array(
        0 => array(
            'is_over' => 1,
            'step_desc' => '项目终止',
            'step_role_id' => 0, //关联角色id
            'step_next' => array(
                0 => 0,
                1 => 2
            )
        ),
        1 => array(
            'is_over' => 0,
            'step_desc' => '提交申请',
            'step_role_id' => 0, //关联角色id
            'step_next' => array(
                0 => 0,
                1 => 2
            )
        ),
        2 => array(
            'is_over' => 0,
            'step_desc' => '项管部审核',
            'step_role_id' => 2, //关联角色id
            'step_next' => array(
                0 => 0,
                1 => 3
            )
        ),
        3 => array(
            'is_over' => 0,
            'step_desc' => '立项会审核',
            'step_role_id' => 2, //关联角色id
            'step_next' => array(
                0 => 0,
                1 => 4
            )
        ),
        4 => array(
            'is_over' => 0,
            'step_desc' => '项管部主管',
            'step_role_id' => 14, //关联角色id
            'step_next' => array(
                0 => 0,
                1 => 5
            )
        ),
        5 => array(
            'is_over' => 0,
            'step_desc' => '风控',
            'step_role_id' => 1, //关联角色id
            'step_next' => array(
                0 => 0,
                1 => 6
            )
        ),
    )
);

