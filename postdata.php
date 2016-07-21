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
$logfilename = "postdata.out.log";
$logfile = &Log::factory('file', $logfilename, 'TEST'); 
$logfile->log('['.__LINE__.']'.'*** STARTED ***');

// 必用に応じて log ファイルのコンパクションを行う
$p=pathinfo($_SERVER['SCRIPT_FILENAME']);
$logfile->log('['.__LINE__.']'.'$_SERVER[SCRIPT_FILENAME] = '.$_SERVER['SCRIPT_FILENAME']);
$command = "".$p['dirname']."/compaction.sh ".$logfilename;
$logfile->log('['.__LINE__.']'.'$command = '.$command);
`$command`;

// XHTMLとしてブラウザに認識させる
// (IE8以下はサポート対象外ｗ)
//header('Content-Type: application/xhtml+xml; charset=utf-8');
if($_SERVER["REQUEST_METHOD"] == "POST"){
  $logfile->log('['.__LINE__.']'.'$_POST[serial_id] = '.$_POST['serial_id']);
#  $logfile->log('['.__LINE__.']'.'$_POST[show_data_lows] = '.$_POST['show_data_lows']);
  $logfile->log('['.__LINE__.']'.'$_POST[name] = '.$_POST['name']);
  $logfile->log('['.__LINE__.']'.'$_POST[datetime] = '.$_POST['datetime']);
  $logfile->log('['.__LINE__.']'.'$_POST[data] = '.$_POST['data']);

  # 設定ファイルの読み込み
  $configfile = __DIR__. "/uploads/".$_POST['serial_id']."/config.ini";
  $ini = parse_ini_file($configfile);

  # 送信データに datetime がなかった場合はサーバの受信日時を設定
  if (isset($_POST['datetime']) && $_POST['datetime'] != ""){
    $datetime = $_POST['datetime'];
  } else {
    # 2016/7/3  18:59:05
    $logfile->log('['.__LINE__.']'.'$datetime created. ');
    $datetime = date("Y/m/d H:i:s");
  }
  $logfile->log('['.__LINE__.']'.'$datetime = '.$datetime);

  # データを保存、もしくは転送
  $fp = fopen(__DIR__. "/uploads/".$_POST['serial_id']."/".$_POST['name'].'.csv', 'a');
#  fwrite($fp, $_POST['datetime'].",".$_POST['data'].PHP_EOL);
  fwrite($fp, $datetime.",".$_POST['data'].PHP_EOL);

  exit();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>データアップロード</title>
</head>
<body>
  <form action="" method="POST" >
    serial_id<input type="text" name="serial_id" id="serial_id"/>
    name<input type="text" name="name" id="name">
    datetime<input type="text" name="datetime" id="datetime">
    data<input type="text" name="data" id="data">
    <input type="submit" value="登録">
  </form>
</body>
</html>