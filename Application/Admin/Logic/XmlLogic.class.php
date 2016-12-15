<?php
namespace Admin\Logic;

class XmlLogic{

    public $file;
    public function index()
    {
        //  加载XML内容
        $content = file_get_contents(dirname(__FILE__).'/'.$this->file);
        $p = xml_parser_create();
        xml_parse_into_struct($p, $content, $vals, $index);
        xml_parser_free($p);
        $tags=array('BPMN2:DEFINITIONS', 'BPMN2:DATASTORE', 'BPMN2:GROUP', 'BPMNDI:BPMNDIAGRAM', 'BPMNDI:BPMNPLANE', 'BPMNDI:BPMNEDGE', 'DI:WAYPOINT', 'BPMNDI:BPMNSHAPE', 'DC:BOUNDS', 'BPMN2:PROCESS');
        foreach($vals as $k => $v){
            if(strcasecmp($v['type'],'cdata')===0 || in_array($v['tag'],$tags))unset($vals[$k]);
        }
        $data=[];$children=[];
        foreach($vals as $kk=>$vv){
            if($vv['level']==3 && $vv['type']=='complete'){
                array_push($data,$vv);
                continue;
            }
            if($vv['type']=='open'){
                array_push($data,$vv);
                continue;
            }
            if($vv['type']=='close'){
                //print_r($children);
                $data[count($data)-1]['value']=$children;
                $children=[];
                continue;
            }
            array_push($children,$vv);
        }
        foreach ($data as $k =>$v){
            // $result[$v['attributes']['ID']]=$v;
            //$id=$v['attributes']['ID'];

            if(array_key_exists('SOURCEREF',$v['attributes']))
            {
                $data[$k]['SOURCEREF']=$v['attributes']['SOURCEREF'];
                $data[$k]['TARGETREF']=$v['attributes']['TARGETREF'];
            }
            $data[$k]['name']=$v['attributes']['NAME'];
            unset($data[$k]['attributes']);
            $result[$v['attributes']['ID']]=$data[$k];

        }

        return $result;
    }

}
