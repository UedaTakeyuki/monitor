<?php


ini_set( 'display_errors', 0 ); // エラー出力しない場合
#ini_set( 'display_errors', 1 ); // エラー出力する場合
require_once("vendor/autoload.php"); 
#require_once("Log.php");
$logfilename = "fota.out.log";
$logfile = &Log::factory('file', $logfilename, 'TEST'); 
$logfile->log('['.__LINE__.']'.'*** STARTED ***');
$json=null;
$json['serial_id'] = $_GET['serial_id'];

// 必用に応じて log ファイルのコンパクションを行う
$p=pathinfo($_SERVER['SCRIPT_FILENAME']);
$logfile->log('['.__LINE__.']'.'$_SERVER[SCRIPT_FILENAME] = '.$_SERVER['SCRIPT_FILENAME']);
$command = "".$p['dirname']."/compaction.sh ".$logfilename;
$logfile->log('['.__LINE__.']'.'$command = '.$command);
`$command`;

# 設定の読み込み
$configfile = "uploads/".$_GET['serial_id']."/fota.ini";
$fota = parse_ini_file($configfile);

# 設定値の json への設定
$json['restart']=$fota['restart'];
$fota['restart']=null;

# 設定値の上書き
$fp = fopen($configfile, 'w');
foreach ($fota as $k => $i) fputs($fp, "$k=$i\n");
fclose($fp); 

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
$json_str = json_encode( $json );
//error_log('$json_str = '.$json_str);
echo $json_str;
//	echo json_encode( $json );
exit;
?>
