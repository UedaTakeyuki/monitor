<?php
date_default_timezone_set("Asia/Tokyo");

ini_set( 'display_errors', 0 ); // エラー出力しない場合
#ini_set( 'display_errors', 1 ); // エラー出力する場合
require_once("vendor/autoload.php"); 
#require_once("Log.php");
$logfilename = "data.out.log";
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

$show_data_lows = 11;
if(isset($_GET['show_data_lows'])){
  $show_data_lows = $_GET['show_data_lows'];
  $logfile->log('['.__LINE__.']'.'$show_data_lows ='.$show_data_lows);
}
if(isset($_GET['LastTime'])){
  $logfile->log('['.__LINE__.']'.'$_GET[LastTime] = '.$_GET['LastTime']);
  $lastTime = jdatetotime($_GET['LastTime']);
  $logfile->log('['.__LINE__.']'.'$lastTime = '.date("'Y-m-d G:i:s'",$lastTime));
}
if(isset($_GET['ILTimes'])){
  $logfile->log('['.__LINE__.']'.'$_GET[ILTimes] = '.serialize($_GET['ILTimes']));
//  $lastTime = jdatetotime($_GET['LastTime']);
//  $logfile->log('['.__LINE__.']'.'$lastTime = '.date("'Y-M-D G:i:s'",$lastTime));
  $logfile->log('['.__LINE__.']'.'temp= '.$_GET['ILTimes']['temp']);
  $u_arr = $_GET['ILTimes'];
}
if(isset($_GET['LTime'])){
  $logfile->log('['.__LINE__.']'.'$_GET[LTime] = '.$_GET['LTime']);
  $ymdhms=explode("-", $_GET['LTime']);
  $lastTime = jdatetotime($ymdhms[0]." ".$ymdhms[1]);
  $logfile->log('['.__LINE__.']'.'$lastTime = '.date("'Y-m-d G:i:s'",$lastTime));
}

#$result = array();
$csv_file_dir = dirname(__FILE__)."/uploads/".$_GET['serial_id'];
$json['serial_id']=$_GET['serial_id'];
if ($dirhandle = opendir($csv_file_dir)) {
  // ディレクトリをループする際の正しい方法
  while (false !== ($entry = readdir($dirhandle))) {
    $csv_file_name = $csv_file_dir."/".$entry;
    $p = pathinfo($csv_file_name);
    if ( $p["extension"] == "csv" ) {
      // ファイルのタイムスタンプが lastdata より古（小さい）ければ
      // なにもつくらないで次の while に（両者 unixtime）
      // add
      /*if (isset($_GET['ILTimes'][$p["basename"]])){
        $postedLastTime = jdatetotime($_GET['ILTimes'][$p["basename"]]);
      } else {
        $postedLastTime = $lastTime);
      }*/
      $logfile->log('['.__LINE__.']'.'filename = '.$p["filename"]);
      global $u_arr, $lastTime; // なぜか SCOPE が grobal じゃない
      $logfile->log('['.__LINE__.']'.'VfK($p[filename],$_GET[ILTimes]) = '.u_VfK($p["filename"], $u_arr));
      $postedLastTimeForThisFile = u_VfK($p["filename"], $u_arr);
      if(!is_null($postedLastTimeForThisFile)){
        $postedLastTime = jdatetotime($postedLastTimeForThisFile);
      } else {
        $postedLastTime = $lastTime;
      }
      //$logfile->log('['.__LINE__.']'.'$_GET[ILTimes][$p[filename]] = '.$_GET['ILTimes'][$p["filename"]]);      
      $logfile->log('['.__LINE__.']'.'$postedLastTime = '.date("'Y-m-d G:i:s'",$postedLastTime));
      // add-ended
//      if (isset($lastTime) && filemtime($csv_file_name) <= $lastTime + 30){
      if (filemtime($csv_file_name) <= $postedLastTime + 30){
        continue;
      }
      $logfile->log('['.__LINE__.']'.'csv file name = '.$csv_file_name);
      $logfile->log('['.__LINE__.']'.'csv file timestamp = '.date("'Y-m-d G:i:s'",filemtime($csv_file_name)));
      //$logfile->log('['.__LINE__.']'.'CSV!');
      $name = $p["filename"];
      $json[$name] = array();
      $logfile->log('['.__LINE__.']'.'csv_file_name = '.$csv_file_name);
      // 末尾11行だけの仮ファイルをつくり、そこから読む
      //$temp_cmd = "tail -n 11 ".$csv_file_name." > tmp.csv";
      $temp_cmd = "tail -n ".$show_data_lows." ".$csv_file_name." > tmp.csv";
      `$temp_cmd`;
      if (($handle = fopen("tmp.csv", "r")) !== FALSE) {      
//      if (($handle = fopen($csv_file_name, "r")) !== FALSE) {
        while (($data = fgetcsv($handle)) !== FALSE) {
          array_push($json[$name], (array("datetime" => $data[0], "data" => (float)$data[1])));
        }
        fclose($handle);
        $json[$name]=array_reverse($json[$name]);
      }
    }
  }
  closedir($dirhandle);
}
/*
$json['temp'] = array();
$csv_file_name = dirname(__FILE__)."/uploads/".$_GET['serial_id']."/".$_GET['name'].".csv";
$logfile->log('['.__LINE__.']'.'csv_file_name = '.$csv_file_name);
if (($handle = fopen($csv_file_name, "r")) !== FALSE) {
#    $column_headers = fgetcsv($handle); // read the row.
#    foreach($column_headers as $header) {
#            $result[$header] = array();
#    }
  while (($data = fgetcsv($handle)) !== FALSE) {
#    $i = 0;
#    foreach($result as &$column) {
#      $column[] = $data[$i++];
#    }
  	array_push($json['temp'], (array("datetime" => $data[0], "data" => $data[1])));
  }
  fclose($handle);
}
#$json = json_encode($result);
#$json['latest_pic_name'] = `ls /var/www/html/tools/150721/uploads/$_POST{"serial_id"} | tail -n 1`;
*/

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
$json_str = json_encode( $json );
//error_log('$json_str = '.$json_str);
echo $json_str;
//	echo json_encode( $json );
	exit;
//}

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

// キーを指定して連想配列から値を取得（PHPは連想配列のキーにリテラル以外を指定できないので）
function u_VfK($key, $arr){
  global $logfile;
  $logfile->log('['.__LINE__.']'.'$key = '.$key);
  $logfile->log('['.__LINE__.']'.'$arr = '.serialize($arr));
  $logfile->log('['.__LINE__.']'.'is_array($arr) = '.is_array($arr));

  switch($key){
    case "temp":
      return $arr['temp'];
    case "humidity":
      return $arr['humidity'];
    case "CO2":
      return $arr['CO2'];
    case "lux":
      return $arr['lux'];
    case "humiditydeficit":
      return $arr['humiditydeficit'];
    default:
      return "";
  }
  // Invalid argument supplied for foreach() in /var/www/html/tools/151024/data.php on line 126
  /*
  foreach ($arr as $key_i => $val_i){
    if ($key_i == $key){
      return $val_i;
    }
  }
  */
}
?>
