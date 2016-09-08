<?php
/**
 * [API] Post data.
 * 
 * Use HTTP POST method for posting data from slider to the monitor.
 * 
 * Requires $_POST['serial_id']
 *          $_POST['name']
 *          $_POST['data']
 * 
 * Option   $_POST['datetime'] server time is used in case not specified.
 * 
 * @author Dr. Takeyuki UEDA
 * @copyright Copyright© Atelier UEDA 2016 - All rights reserved.
 *
 */

require_once("vendor/autoload.php"); 
#require_once("Log.php");
$logfilename = "alart.out.log";
$logfile = &Log::factory('file', $logfilename, 'TEST'); 
$logfile->log('['.__LINE__.']'.'*** STARTED ***');

// 必用に応じて log ファイルのコンパクションを行う
$p=pathinfo($_SERVER['SCRIPT_FILENAME']);
$logfile->log('['.__LINE__.']'.'$_SERVER[SCRIPT_FILENAME] = '.$_SERVER['SCRIPT_FILENAME']);
$command = "".$p['dirname']."/compaction.sh ".$logfilename;
$logfile->log('['.__LINE__.']'.'$command = '.$command);
`$command`;

if($_SERVER["REQUEST_METHOD"] == "POST"){
  # confirm post parameters.
  if (isset($_POST['serial_id'])){
    $logfile->log('['.__LINE__.']'.'$_POST[serial_id] = '.$_POST['serial_id']);
  } else {exit;}

  $alarm_ini_file = __DIR__. "/uploads/".$_POST['serial_id']."/alart.ini";
  
  # confirm alarm.ini file existance and read/write ability.
  if (!is_readable($alarm_ini_file)) {exit;}
  
  # read alarm.ini
  $ini = parse_ini_file($alarm_ini_file);
  
  # 設定値の json への設定
	foreach ($ini as $k => $i){
	  $json[$k]=$i;
	}

  header("Access-Control-Allow-Origin: *");
  header('Content-Type: application/json');
  $json_str = json_encode( $json );
  echo $json_str;
  exit;
}
?>
