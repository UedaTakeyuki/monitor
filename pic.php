<?php
/**
 * Tell latest pic file name.
 * 
 * Tell latest pic file name follow by SavePic configration for the account.
 * 
 * Requires $_GET['serial_id']
 * 
 * @author Dr. Takeyuki UEDA
 * @copyright Copyright© Atelier UEDA 2016 - All rights reserved.
 *
 */

date_default_timezone_set("Asia/Tokyo");
#ini_set( 'display_errors', 0 ); // エラー出力しない場合
ini_set( 'display_errors', 1 ); // エラー出力する場合
require_once("vendor/autoload.php");
spl_autoload_register(function($class) {
  require $class . ".class.php";
});

#require_once("Log.php");
$logfilename = "pic.out.log";
$logfile = &Log::factory('file', $logfilename, 'TEST'); 
$logfile->log('['.__LINE__.']'.'*** STARTED ***');

// 必用に応じて log ファイルのコンパクションを行う
$p=pathinfo($_SERVER['SCRIPT_FILENAME']);
$logfile->log('['.__LINE__.']'.'$_SERVER[SCRIPT_FILENAME] = '.$_SERVER['SCRIPT_FILENAME']);
$command = "".$p['dirname']."/compaction.sh ".$logfilename;
$logfile->log('['.__LINE__.']'.'$command = '.$command);
`$command`;

/*
$command_str = 'ls '.dirname(__FILE__).'/uploads/'.$_GET["serial_id"].'/*.jpeg | tail -n 1';
$logfile->log('['.__LINE__.']'.'$command_str = '.$command_str);
$result = `$command_str`;
$logfile->log('['.__LINE__.']'.'$result = '.$result);
$json['latest_pic_name'] = basename($result);
*/

$json['serial_id'] = $_GET['serial_id'];
if (isset($_GET['device'])){
    $json['device']=$_GET['device'];
} else {
    $json['device']="";
}

$getpic = new GetPic($_GET['serial_id']);
$result = $getpic->get_latest_pic($json['device']);

// get_latest_pic の戻り値を json で返す
$json['latest_pic_name'] = $result['pic_file_name'];
$json['ymd'] = $result['ymd'];
$json['serial_id'] = $result['serial_id'];
$json['device'] = $result['device'];

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
$json_str = json_encode( $json );
//error_log('$json_str = '.$json_str);
echo $json_str;
//	echo json_encode( $json );
	exit;
?>