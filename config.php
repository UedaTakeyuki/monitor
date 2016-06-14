<?php
require_once("common.php");
// エラー出力しない場合
//ini_set( 'display_errors', 0 );
// エラー出力する場合
ini_set( 'display_errors', 1 );
require_once("Log.php");
$logfile = &Log::factory('file', 'config.out.log', 'TEST'); 

// ログイン状態のチェック
session_start();

$logfilename = "config.out.log";
$logfile->log('['.__LINE__.']'.'*** STARTED ***');

// 必用に応じて log ファイルのコンパクションを行う
$p=pathinfo($_SERVER['SCRIPT_FILENAME']);
$logfile->log('['.__LINE__.']'.'$_SERVER[SCRIPT_FILENAME] = '.$_SERVER['SCRIPT_FILENAME']);
$command = "".$p['dirname']."/compaction.sh ".$logfilename;
$logfile->log('['.__LINE__.']'.'$command = '.$command);
`$command`;

// POST, GET によらず、session_id を取得
if(isset($_GET['serial_id'])){$serial_id=$_GET['serial_id'];};
if(isset($_POST['serial_id'])){$serial_id=$_POST['serial_id'];};

//if (!isset($_SESSION["USERID"])) {
if (!isset($_SESSION["LOGINS"]) || !array_key_exists($serial_id, $_SESSION["LOGINS"])) {  // ログイン成功後の戻り先(パラメタ付き)をセッション変数に保存
  //$_SESSION["return_url"]=$_SERVER["REQUEST_URI"];
  // 2015.10.31 login.php 内でリファラから取得するように変更
  // ログイン処理
  header("Location: login.php?serial_id=".$serial_id);
  exit;
}

switch($_SERVER["REQUEST_METHOD"]) {
	case "GET":
		if (!isset($_GET["serial_id"])){
			exit("serial_id 未設定");
		} else {
       $serial_id = $_GET['serial_id'];
			 $configfile = "uploads/".$_GET['serial_id']."/config.ini";
			 $ini = parse_ini_file($configfile);
		}
    # リファラを戻り先アドレスとして保存
    if(isset($_SERVER['HTTP_REFERER'])){
      ################################
      # リファラを戻り先アドレスとして保存 #
      ################################

      # リファラが login.php の時（なぜだ？）も index.php を
      if (explode("?", basename($_SERVER['HTTP_REFERER']))[0] == "login.php"){
        $_SESSION["return_url"]="index.php?serial_id=".$_GET['serial_id'];
      } else {
        $_SESSION["return_url"]=$_SERVER['HTTP_REFERER'];
      }
    } else {
      # リファラがなければ index.php を戻り先アドレスとして保存
      $_SESSION["return_url"]="index.php?serial_id=".$_GET['serial_id'];
    }
    $logfile->log('['.__LINE__.']'.'$_SESSION["return_url"] = '.$_SESSION["return_url"]);
		break;

	case "POST":
	  $logfile->log('['.__LINE__.']'.'$_POST["serial_id"] = '.$_POST["serial_id"]);
	  $logfile->log('['.__LINE__.']'.'$_POST["show_data_lows"] = '.$_POST["show_data_lows"]);
    $logfile->log('['.__LINE__.']'.'$_POST["show_data_gnt"] = '.$_POST["show_data_gnt"]);
    $logfile->log('['.__LINE__.']'.'$_POST["show_temp"] = '.$_POST["show_temp"]);
    $logfile->log('['.__LINE__.']'.'$_POST["show_hmd"] = '.$_POST["show_hmd"]);
    $logfile->log('['.__LINE__.']'.'$_POST["show_hmddft"] = '.$_POST["show_hmddft"]);
    $logfile->log('['.__LINE__.']'.'$_POST["show_co2"] = '.$_POST["show_co2"]);
    $logfile->log('['.__LINE__.']'.'$_POST["show_lux"] = '.$_POST["show_lux"]);
    $logfile->log('['.__LINE__.']'.'$_POST["show_pic"] = '.$_POST["show_pic"]);
    $logfile->log('['.__LINE__.']'.'$_POST["id"] = '.$_POST["id"]);
    $logfile->log('['.__LINE__.']'.'$_POST["pw"] = '.md5($_POST["pw"]));
		if (!isset($_POST["serial_id"])){
			exit("serial_id 未設定");
		} else {
       $serial_id = $_POST['serial_id'];
			 $configfile = "uploads/".$_POST['serial_id']."/config.ini";
			 $ini = parse_ini_file($configfile);
		}
    // 設定 show_data_lows=11
    if (isset($_POST["show_data_lows"])){$ini["show_data_lows"]=$_POST["show_data_lows"];};
    if (isset($_POST["show_data_gnt"])){$ini["show_data_gnt"]=$_POST["show_data_gnt"];};
    if (isset($_POST["show_temp"])){$ini["show_temp"]=$_POST["show_temp"];};
    if (isset($_POST["show_hmd"])){$ini["show_hmd"]=$_POST["show_hmd"];};
    if (isset($_POST["show_hmddft"])){$ini["show_hmddft"]=$_POST["show_hmddft"];};
    if (isset($_POST["show_co2"])){$ini["show_co2"]=$_POST["show_co2"];};
    if (isset($_POST["show_lux"])){$ini["show_lux"]=$_POST["show_lux"];};
    if (isset($_POST["show_pic"])){$ini["show_pic"]=$_POST["show_pic"];};
    if (isset($_POST["id"])){$ini["id"]=$_POST["id"];};
    if (isset($_POST["pw"])&& $_POST["pw"]!==""){$ini["pw"]=md5($_POST["pw"]);};

    // ini ファイルの上書き
    $fp = fopen($configfile, 'w');
		foreach ($ini as $k => $i) fputs($fp, "$k=$i\n");
		fclose($fp); 

    // index.php のロード
    //header("Location: index.php?serial_id=".$_POST["serial_id"]);
    header("Location: ".$_SESSION["return_url"]);
		break;

	default:
		// 今は DELETE, PUT に未対応
		exit("未対応");
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title><?php echo TITLE?> 設定変更</title>
  <!-- <script src="mqttws31.js" type="text/javascript"></script>-->
  <script src="Chart.js"></script>
  <link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.css" />
  <script src="m2x-2.0.3.js"></script>
<!--  <script src="jquery-1.10.2.min.js"></script> -->
  <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
  <script src="http://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.js"></script>
</head>
<body onLoad="selecter_select()">
  <script type="text/javascript">

  　function selecter_select(){
      //該当するselectのIDにセット
      $('#show_data_lows').val(<?php echo $ini["show_data_lows"]; ?>);
      $('#show_data_gnt').val("<?php echo $ini["show_data_gnt"]; ?>");
      $('#show_temp').val(<?php echo $ini["show_temp"]; ?>);
      $('#show_hmd').val(<?php echo $ini["show_hmd"]; ?>);
      $('#show_hmddft').val(<?php echo $ini["show_hmddft"]; ?>);
      $('#show_co2').val(<?php echo $ini["show_co2"]; ?>);
      $('#show_lux').val(<?php echo $ini["show_lux"]; ?>);
      $('#show_pic').val(<?php echo $ini["show_pic"]; ?>);
      //jquery mobile用の処理
      $('select').selectmenu('refresh',true);
    }
  </script>

<div data-role="page"> 
    
<div data-role="header" data-position="fixed">
    <h1><?php echo TITLE?> 設定変更</h1>
    <a href="<?php echo $_SESSION['return_url']?>" data-rel="back">戻る</a>
    <a href="logout.php?serial_id=<?php echo $serial_id?>" data-transition="fade" data-ajax="false">ログアウト</a>
</div>

<div data-role="content">
	<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" data-ajax="false" id="form_new">
		<input type="hidden" name="serial_id" id="serial_id" value="<?php echo $_GET['serial_id'] ?>" />

    <div data-role="fieldcontain">
  	  <label for="show_data_lows">グラフ表示データ数</label>
      <select name="show_data_lows" id="show_data_lows" data-native-menu="true">
      <!-- <option value="">選択してください</option> -->
        <option value="24">24データ</option>
        <option value="168">168データ</option>
    	</select>
    </div>
    <div data-role="fieldcontain">
      <label for="show_data_gnt">グラフ表示データ頻度</label>
      <select name="show_data_gnt" id="show_data_gnt" data-native-menu="true">
      <!-- <option value="">選択してください</option> -->
        <option value="direct">直接データ</option>
        <option value="hour">一時間平均値</option>
        <option value="day">一日平均値</option>
      </select>
    </div>
    <div data-role="fieldcontain">
      <label for="show_temp">温度の表示</label>
      <select name="show_temp" id="show_temp" data-native-menu="true">
      <!-- <option value="">選択してください</option> -->
        <option value="0">非表示</option>
        <option value="1">表示</option>
      </select>
      <label for="show_hmd">湿度の表示</label>
      <select name="show_hmd" id="show_hmd" data-native-menu="true">
      <!-- <option value="">選択してください</option> -->
        <option value="0">非表示</option>
        <option value="1">表示</option>
      </select>
      <label for="show_hmddft">飽差の表示</label>
      <select name="show_hmddft" id="show_hmddft" data-native-menu="true">
      <!-- <option value="">選択してください</option> -->
        <option value="0">非表示</option>
        <option value="1">表示</option>
      </select>
      <label for="show_co2">二酸化炭素濃度</label>
      <select name="show_co2" id="show_co2" data-native-menu="true">
      <!-- <option value="">選択してください</option> -->
        <option value="0">非表示</option>
        <option value="1">表示</option>
      </select>
      <label for="show_lux">照度の表示</label>
      <select name="show_lux" id="show_lux" data-native-menu="true">
      <!-- <option value="">選択してください</option> -->
        <option value="0">非表示</option>
        <option value="1">表示</option>
      </select>
      <label for="show_pic">現場写真の表示</label>
      <select name="show_pic" id="show_pic" data-native-menu="true">
      <!-- <option value="">選択してください</option> -->
        <option value="0">非表示</option>
        <option value="1">表示</option>
      </select>
    </div>
    <div data-role="fieldcontain">
      <label for="id">ログインID</label>
      <input name="id" id="id" type="text" data-native-menu="true" value="<?php echo $ini['id']?>"/>
      <label for="pw">パスワード</label>
      <input name="pw" id="pw" type="password" data-native-menu="true"/>
    </div>

<!--  	<input id="show_data_lows" name="show_data_lows" type="range"
          min="11" max="200" step="1" value="<?php echo $ini['show_data_lows'] ?>" /> -->
		<input type="submit" value="設定" />

	</form>
</div>

</div> <!-- page -->

</body>
</html>