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

require("sc.php");
require_once("vendor/autoload.php"); 
#require_once("Log.php");
$logfilename = "postalart.out.log";
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
  if (isset($_POST['name'])){
    $logfile->log('['.__LINE__.']'.'$_POST[name] = '.$_POST['name']);
  } else {exit;}
  if (isset($_POST['status'])){
    $logfile->log('['.__LINE__.']'.'$_POST[status] = '.$_POST['status']);
  } else {exit;}

  $alarm_ini_file = __DIR__. "/uploads/".$_POST['serial_id']."/alart.ini";
  
  # confirm alarm.ini file existance and read/write ability.
  if (!is_readable($alarm_ini_file)) {exit;}
  
  # read alarm.ini
  $ini = parse_ini_file($alarm_ini_file);

  if (isset($ini[$_POST['name']])){
    $datetime = date("Y年m月d日H時i分s秒"); # current time.
    if ($_POST['status'] == "on"){
      if ($ini[$_POST['name']] == ""){
        # emargency call if first alart
        $query = ['serial_id'=>$_POST['serial_id'],'name'=>'water'];
        $contents = file_get_contents(ALART_CALL_URL.'?'.http_build_query($query));
        #$contents = file_get_contents(ALART_CALL_URL);
      
        # set alart state.
        $ini[$_POST['name']] = $datetime;
        $logfile->log('['.__LINE__.']'.'Set '. $_POST['name']. ' ON at $datetime = '.$datetime);
      }
    } else {
      $ini[$_POST['name']] = "";
      $logfile->log('['.__LINE__.']'.'Set '. $_POST['name']. ' OFF at $datetime = '.$datetime);
    }
  }


  # update alarm.ini
  $fp = fopen($alarm_ini_file, 'w');
	foreach ($ini as $k => $i) fputs($fp, "$k=$i\n");
	fclose($fp); 

  exit();
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>Alarm</title>
</head>
<body>
  <form action="" method="POST" >
    serial_id<input type="text" name="serial_id" id="serial_id"/>
    name<input type="text" name="name" id="name">
    status<input type="text" name="status" id="status">
    <input type="submit" value="登録">
  </form>
</body>
</html>