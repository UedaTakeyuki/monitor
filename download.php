<?php
// 参考：http://thr3a.hatenablog.com/entry/20131017/1381974853
//パス
#`tar -cvz -f /home/pi/MCC/mcc.log /var/log/nginx/error.log /usr/share/nginx/www/MCC/app/140903/takepic_auto_fromUVC.log`;
$serial_id = $_GET['serial_id'];
$name = $_GET['name'];
$fname = $name.'.csv'; //ファイル名
#$fpath = '/var/www/html/tools/151001/uploads/'.$serial_id.'/'.$fname;
$fpath = dirname(__FILE__)."/uploads/".$serial_id.'/'.$fname;

header('Content-Type: application/force-download');
header('Content-Length: '.filesize($fpath));
header('Content-disposition: attachment; filename="'.$fname.'"');
readfile($fpath);
?>