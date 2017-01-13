<?php
//echo phpinfo();
//$redis = new redis();
//$redis->connect('127.0.0.1','6379');
//$result=$redis->hset('aa','key3','222','898989');
//var_dump(array_walk($_GET,'_strip_tags'));
//array_walk($_GET,'_strip_tags');
//$a='{"\u5206\u914d\u8ddf\u8fdb\u4eba":{"pre":{"\/Admin\/Project\/exchange":"\u5206\u914d\u4eba\u5458","\/Admin\/Project\/edit":"\u7f16\u8f91","\/Admin\/Project\/file":"\u8d44\u6599\u5305"}},"\u53ec\u5f00\u7acb\u9879\u4f1a":{"pre":{"\/Admin\/Project\/detail":"\u67e5\u770b","\/Admin\/Project\/exchange":"\u4ea4\u63a5","\/Admin\/Project\/file":"\u8d44\u6599\u5305"}}}';
//var_dump(json_decode($a,true));


/*$a=array('a'=>111,'b'=>2222);
$html="<?php return ".var_export($a,true)."?>";
file_put_contents('test.ini.php',$html);*/

//$bb=file_get_contents('./test.ini.php');
/*$file=include './test.ini.php';
$file['a']=5566;
$html="<?php return ".var_export($file,true)."?>";
file_put_contents('test.ini.php',$html);*/
$aa='佟琦::<br/>孙耀飞::<br/>黄惠平::好的了，我是法务老大<br/>张天衡::知道你是谁了<br/>李慧琳::看看我是谁<br/>';
var_dump(explode('<br/>',$aa));
