<?php
/**
 * Config settings.
 * 
 * 1. Confirm login.
 * 2. show FORM of config settings.
 * 3. post
 * 
 * Requires $_GET['serial_id']
 * 
 * @author Dr. Takeyuki UEDA
 * @copyright Copyright© Atelier UEDA 2016 - All rights reserved.
 *
 */
 
 require_once("common.php");
// エラー出力しない場合
//ini_set( 'display_errors', 0 );
// エラー出力する場合
ini_set( 'display_errors', 1 );
require_once("vendor/autoload.php"); 
#require_once("Log.php");
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

  <title><?= SHORT_TITLE ?> 設定変更</title>
  <!-- <script src="mqttws31.js" type="text/javascript"></script>-->
<?php   if ($cdn): ?>
  <!-- <script src="Chart.js"></script>-->
  <link rel="stylesheet" href="https://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.css" />
  <script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>
  <script src="https://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.js"></script>
<?php   else: ?>
  <link rel="stylesheet" href="js/jquery.mobile-1.3.1.min.css" />
  <script src="node_modules/jquery/dist/jquery.min.js"></script>
  <script src="js/jquery.mobile-1.3.1.min.js"></script>
<?php   endif; ?>
</head>
<body onLoad="selecter_select()">
  <script type="text/javascript">

  　function selecter_select(){
      //該当するselectのIDにセット
      $('#show_data_lows').val(<?= $ini["show_data_lows"] ?>);
      //jquery mobile用の処理
      $('select').selectmenu('refresh',true);
    }
  </script>

<div data-role="page"> 
    
<div data-role="header" data-position="fixed" data-disable-page-zoom="false">
    <h1><?= SHORT_TITLE ?> 設定変更</h1>
    <!-- <a href="<?= $_SESSION['return_url']?>" data-rel="back">戻る</a> -->
    <a href="index.php?serial_id=<?= $serial_id?>" data-rel="back">戻る</a>
    <a href="logout.php?serial_id=<?= $serial_id?>" data-transition="fade" data-ajax="false">ログアウト</a>
</div>

<div data-role="content">
	<form action="<?= $_SERVER['SCRIPT_NAME']; ?>" method="post" data-ajax="false" id="form_new">
		<input type="hidden" name="serial_id" id="serial_id" value="<?= $_GET['serial_id'] ?>" />

    <div data-role="fieldcontain">
  	  <label for="show_data_lows">グラフ表示データ数</label>
      <select name="show_data_lows" id="show_data_lows" data-native-menu="true">
      <!-- <option value="">選択してください</option> -->
        <option value="24">24データ</option>
        <option value="168">168データ</option>
    	</select>
    </div>
    <div data-role="fieldcontain">
      <label for="id">ログインID</label>
      <input name="id" id="id" type="text" data-native-menu="true" value="<?php echo $ini['id']?>"/>
      <label for="pw">パスワード</label>
      <input name="pw" id="pw" type="password" data-native-menu="true"/>
    </div>

		<input type="submit" value="設定" />

	</form>
</div>

<div data-role="footer" data-position="fixed" data-disable-page-zoom="false">
    <h4>© Atelier UEDA🐸</h4>
</div>
</div> <!-- page -->

</body>
</html>