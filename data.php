<?php
/**
 * [API] Get fresh sensor data.
 * 
 * Sensors shoud be specified by .dini settings for the account.
 * Return data as JSON.
 * Latest data than $_GET[ILTimes] if specified.
 * 
 * Requires $_GET['serial_id']
 * Return json[SENSOR_NAME]=array("datetime" => , "data" => )
 * 
 * @author Dr. Takeyuki UEDA
 * @copyright Copyright© Atelier UEDA 2016 - All rights reserved.
 *
 */
 
 date_default_timezone_set("Asia/Tokyo");

ini_set( 'display_errors', 0 ); // エラー出力しない場合
#ini_set( 'display_errors', 1 ); // エラー出力する場合
require_once("vendor/autoload.php"); 
#require_once("Log.php");

// log ファイルの設定
$logfilename = "data.out.log";
$logfile = &Log::factory('file', $logfilename, 'TEST'); 
$logfile->log('['.__LINE__.']'.'*** STARTED ***');

// 必用に応じて log ファイルのコンパクションを行う
$p=pathinfo($_SERVER['SCRIPT_FILENAME']);
$logfile->log('['.__LINE__.']'.'$_SERVER[SCRIPT_FILENAME] = '.$_SERVER['SCRIPT_FILENAME']);
$command = "".$p['dirname']."/compaction.sh ".$logfilename;
$logfile->log('['.__LINE__.']'.'$command = '.$command);
`$command`;

// 戻り値の準備
$json=null;
if(isset($_GET['serial_id'])){
  $json['serial_id'] = $_GET['serial_id'];
} else {
  // エラーを返す
  header("HTTP/1.1 400 Bad Request");
  exit();
}

$show_data_lows = 11;
if(isset($_GET['show_data_lows'])){
  $show_data_lows = $_GET['show_data_lows'];
  $logfile->log('['.__LINE__.']'.'$show_data_lows ='.$show_data_lows);
}

if(isset($_GET['ILTimes'])){
  $logfile->log('['.__LINE__.']'.'$_GET[ILTimes] = '.serialize($_GET['ILTimes']));
  $logfile->log('['.__LINE__.']'.'temp= '.$_GET['ILTimes']['temp']);
  $u_arr = $_GET['ILTimes'];
}

// データファイルの格納フォルダ
$csv_file_dir = dirname(__FILE__)."/uploads/".$_GET['serial_id'];
// データの設定ファイル一覧を取得
$data_inis = glob("uploads/".$_GET['serial_id']."/*.dini");

if (!isset($_GET['ILTimes'])){
  // ILTimes が空の場合、設定ファイルにあるものを全部返す
  foreach ($data_inis as $key => $value){
    $dini = parse_ini_file($value);
    get_latest_data($json, $dini["fname"], null, $show_data_lows);
  }  
} else {
  // ILTimes で指定されたデータを返す
  foreach ($_GET['ILTimes'] as $key => $value){
    get_latest_data($json, $key, $value, $show_data_lows);
  }
}

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
$json_str = json_encode( $json );
//error_log('$json_str = '.$json_str);
echo $json_str;
//	echo json_encode( $json );
	exit;
//}

function is_updated($csv_file_name, $lasttime){
  if (!file_exists($csv_file_name)){
    // データがない
    return FALSE;
  }
  if (!is_null($lasttime) &&  filemtime($csv_file_name) <= jdatetotime($lasttime) + 10){
    // 初回のデータ取得（直近時刻がない）でなく、かつ前回の送信からデータの更新がない
    return FALSE;
  }
  return TRUE;
}

function get_latest_data(&$json, $name, $lasttime, $show_data_lows){
  global $logfile, $csv_file_dir;
  $csv_file_name = $csv_file_dir."/".$name.'.csv';

  $logfile->log('['.__LINE__.']'.'csv file name = '.$csv_file_name);
  $logfile->log('['.__LINE__.']'.'csv file timestamp = '.date("'Y-m-d G:i:s'",filemtime($csv_file_name)));

  if (is_updated($csv_file_name, $lasttime)){
    $result = array();
    // 末尾n行だけの仮ファイルをつくり、そこから読む
    $temp_cmd = "tail -n ".$show_data_lows." ".$csv_file_name." > tmp.csv";
    `$temp_cmd`;
    if (($handle = fopen("tmp.csv", "r")) !== FALSE) {      
      while (($data = fgetcsv($handle)) !== FALSE) {
        array_push($result, (array("datetime" => $data[0], "data" => (float)$data[1])));
      }
      fclose($handle);
      $json[$name]=array_reverse($result);
    }
  }
}

// '2015/10/26 14:40:13' 形式の文字列を unix time に変換
function jdatetotime($str){
  $ymdhms=explode(" ", $str);
  $ymd = $ymdhms[0];
  $hms = $ymdhms[1];
  $ymd_dc = explode("/",$ymd);
  $y = $ymd_dc[0];
  $m = $ymd_dc[1];
  $d = $ymd_dc[2];
  $dtstr= $y . "-" . $m . "-" . $d . " " . $hms;
  return strtotime($dtstr);
}
?>
