<?php
/**
 * [API] Post one line data.
 * 
 * Add a line to the file.
 * Using for:
 *   vmstat infos of slider. 
 * 
 * Requires $_POST['serial_id']
 *          $_POST['filename']
 *          $_POST['textstr']
 * 
 * @author Dr. Takeyuki UEDA
 * @copyright Copyright© Atelier UEDA 2016 - All rights reserved.
 *
 */
 
 require_once("Log.php");
$logfilename = "postdata2.out.log";
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
  $logfile->log('['.__LINE__.']'.'$_POST[filename] = '.$_POST['filename']);
  $logfile->log('['.__LINE__.']'.'$_POST[textstr] = '.$_POST['textstr']);

  # 設定ファイルの読み込み
  $configfile = "uploads/".$_POST['serial_id']."/config.ini";
  $ini = parse_ini_file($configfile);

  # データを保存、もしくは転送
  $recordfilename = "./uploads/".$_POST['serial_id']."/".$_POST['filename'];
  $logfile->log('['.__LINE__.']'.'$recordfilename = '.$recordfilename);
  $fp = fopen($recordfilename, 'a');
  fwrite($fp, $_POST['textstr'].PHP_EOL);

  // 必用に応じてコンパクションを行う
  $p=pathinfo($_SERVER['SCRIPT_FILENAME']);
  $command = "".$p['dirname']."/compaction.sh ".$recordfilename;
  $logfile->log('['.__LINE__.']'.'$command = '.$command);
  `$command`;
#  }
}
?>