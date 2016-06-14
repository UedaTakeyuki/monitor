<?php
date_default_timezone_set("Asia/Tokyo");
#ini_set( 'display_errors', 0 ); // エラー出力しない場合
ini_set( 'display_errors', 1 ); // エラー出力する場合
require_once("Log.php");
$logfilename = "pic.out.log";
$logfile = &Log::factory('file', $logfilename, 'TEST'); 
$logfile->log('['.__LINE__.']'.'*** STARTED ***');

// 必用に応じて log ファイルのコンパクションを行う
$p=pathinfo($_SERVER['SCRIPT_FILENAME']);
$logfile->log('['.__LINE__.']'.'$_SERVER[SCRIPT_FILENAME] = '.$_SERVER['SCRIPT_FILENAME']);
$command = "".$p['dirname']."/compaction.sh ".$logfilename;
$logfile->log('['.__LINE__.']'.'$command = '.$command);
`$command`;

#$command_str = 'ls /var/www/html/tools/150721/uploads/'.$_GET["serial_id"].'/*.jpeg | tail -n 1';
$command_str = 'ls '.dirname(__FILE__).'/uploads/'.$_GET["serial_id"].'/*.jpeg | tail -n 1';
$logfile->log('['.__LINE__.']'.'$command_str = '.$command_str);
$result = `$command_str`;
$logfile->log('['.__LINE__.']'.'$result = '.$result);
$json['latest_pic_name'] = basename($result);
$json['serial_id'] = $_GET['serial_id'];

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
$json_str = json_encode( $json );
//error_log('$json_str = '.$json_str);
echo $json_str;
//	echo json_encode( $json );
	exit;
?>