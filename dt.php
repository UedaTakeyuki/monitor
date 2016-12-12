<?php
/**
 * [API] FOTA.
 * 
 * FOTA(Farmware update On The Air)
 * Return FOTA settings provided for the account as JSON.
 * 
 * Requires $_GET['serial_id']
 * 
 * @author Dr. Takeyuki UEDA
 * @copyright Copyright© Atelier UEDA 2016 - All rights reserved.
 *
 */

ini_set( 'display_errors', 0 ); // エラー出力しない場合
#ini_set( 'display_errors', 1 ); // エラー出力する場合
require_once("vendor/autoload.php"); 

function mtime(){
	return substr(explode(".", (microtime(true) . ""))[1], 0, 3);
}

#require_once("Log.php");
$logfilename = "dt.out.log";
$logfile = &Log::factory('file', $logfilename, 'TEST'); 
$logfile->log('['.__LINE__.']'.mtime().'*** STARTED ***');

// 必用に応じて log ファイルのコンパクションを行う
$p=pathinfo($_SERVER['SCRIPT_FILENAME']);
$logfile->log('['.__LINE__.']'.'$_SERVER[SCRIPT_FILENAME] = '.$_SERVER['SCRIPT_FILENAME']);
$command = "".$p['dirname']."/compaction.sh ".$logfilename;
$logfile->log('['.__LINE__.']'.'$command = '.$command);
`$command`;
$logfile->log('['.__LINE__.']'.mtime().'*** STARTED 2***');
$result = "";
if(isset($_POST['sec'])&&isset($_POST['usec'])){
	$command ="sudo ./setdate_milli ".$_POST['sec']." ".$_POST['usec'];
	$logfile->log('['.__LINE__.']'.mtime().'command = '.$command);
	$result = `$command`;
	$logfile->log('['.__LINE__.']'.mtime().'result = '.$result);

	$json['result']=$result;

	header("Access-Control-Allow-Origin: *");
	header('Content-Type: application/json');
	$json_str = json_encode( $json );
	error_log('$json_str = '.$json_str);
	echo $json_str;

	exit;

}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>データアップロード</title>
  <script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>
  <script>
    var now = new Date().getTime();
    var sec = Math.floor(now / 1000);
    var usec = now % 1000;

    $.ajax({
        type: "POST",
        url: "dt.php",
        data: {sec: sec, usec: usec},
        dataType: "json",
      })
     .then(
       function(data, dataType){
         console.log('OK : ');
       },
       function(XMLHttpRequest, textStatus, errorThrown){
         console.log('Error : ' + errorThrown);
     });

  </script>
</head>
<body>
</body>
</html>
