<?php
    return array(
        //动态表单零部件池，name对应数据库中相应的字段
        'forms'=>array(
            1=>array('description' =>'申请部门', 'type' => 'text', 'name' => '','readonly'=>'readonly'),
            2=>array('description' =>'申请人', 'type' => 'text', 'name' => ''),
            3=>array('description' =>'申请时间', 'type' => 'text', 'name' => ''),
            4=>array('description' =>'产品名称', 'type' => 'text', 'name' => ''),
            5=>array('description' =>'满标日', 'type' => 'text', 'name' => ''),
            6=>array('description' =>'合同编号', 'type' => 'text', 'name' => ''),
            7=>array('description' =>'融资额度【万元】', 'type' => 'text', 'name' => ''),
            8=>array('description' =>'大麦居间服务费【包含在<code onchange="">0元</code>内】', 'type' => 'text', 'name' => ''),
            9=>array('description' =>'放款额【元】', 'type' => 'text', 'name' => ''),
            10=>array('description' =>'融资期限', 'type' => 'multi','sub'=>array()),
            11=>array('description' =>'类型', 'type' => 'select', 'name' => ''),
        ),
        'A'=>array(
            //行
           array(
               //列级元素
               'cols'=>array(1, 2, 3),
           ),
            array('cols' => array(4, 5, 6),),
            array('cols' => array(7, 8, 9),),
            array('cols' => array(7, 8, 9),),
            array(
                'cols'=>array(
                    //列中包含有多个元素，则用数组扩起来，然后，将这个列设置为一块
                    array(
                        'description'=>'融资期限',
                        'sub'=>array(10,11)
                    )
                )
            ),
            array(
                'cols'=>array(

                )
            )
        ),
        'B'=>array(

        )
    );
?>