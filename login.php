<!-- http://d.hatena.ne.jp/replication/20100828/1282994791 -->
<?php
/**
 * Login.
 * 
 * Login.
 * 
 * Requires $_GET['serial_id']
 * 
 * @author Dr. Takeyuki UEDA
 * @copyright Copyright© Atelier UEDA 2016 - All rights reserved.
 *
 */
  session_start();

  require_once("common.php");
  require_once("vendor/autoload.php"); 
  # comporser は凄い http://nazonohito51.jugem.jp/?eid=51
  #  require_once("Log.php");
  $logfilename = "login.out.log";
  $logfile = &Log::factory('file', $logfilename, 'TEST'); 
  $logfile->log('['.__LINE__.']'.'*** STARTED ***');

  // 必用に応じて log ファイルのコンパクションを行う
  $p=pathinfo($_SERVER['SCRIPT_FILENAME']);
  $logfile->log('['.__LINE__.']'.'$_SERVER[SCRIPT_FILENAME] = '.$_SERVER['SCRIPT_FILENAME']);
  $command = "".$p['dirname']."/compaction.sh ".$logfilename;
  $logfile->log('['.__LINE__.']'.'$command = '.$command);
  `$command`;

  # 設定の読み込み
  if($_SERVER["REQUEST_METHOD"] == "POST"){
    if (isset($_POST['serial_id'])){
      $serial_id = $_POST['serial_id'];
    }
  }else{
    if (isset($_GET['serial_id'])){
      $serial_id = $_GET['serial_id'];
    }
    if(isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])){
      ################################
      # リファラを戻り先アドレスとして保存 #
      ################################

    /* old 
      # リファラが login.php の時（なぜだ？）も index.php を
      if (explode("?", basename($_SERVER['HTTP_REFERER']))[0] == "login.php"){
        $_SESSION["return_url"]="index.php?serial_id=".$serial_id;
      } else {
        $_SESSION["return_url"]=$_SERVER['HTTP_REFERER'];
      }
    */
      // 外部URLから index.php を開こうとして login.php にくると、index.php ではなく
      // 外部URLがリファラになる。無条件にリファラに戻るのではなく要チェック
      switch (explode("?", basename($_SERVER['HTTP_REFERER']))[0]){
        case "login.php":
          $_SESSION["return_url"]="index.php?serial_id=".$serial_id;
          break;
        case "index.php":
          $_SESSION["return_url"]="index.php?serial_id=".$serial_id;
          break;
        case "config.php":
          $_SESSION["return_url"]="config.php?serial_id=".$serial_id;
          break;
        default:
          $_SESSION["return_url"]="index.php?serial_id=".$serial_id;
          break;
      }
    } else {
      # リファラがなければ index.php を戻り先アドレスとして保存
      $_SESSION["return_url"]="index.php?serial_id=".$serial_id;
    }
    $logfile->log('['.__LINE__.']'.'$_SESSION["return_url"] = '.$_SESSION["return_url"]);
  }
  if (!isset($serial_id)){exit;}
  if (is_null($serial_id)){exit;}
  $configfile = "uploads/".$serial_id."/config.ini";
  $ini = parse_ini_file($configfile);
  
  // エラーメッセージ
  $errorMessage = "";
  $viewUserId = "";

  // ログインボタンが押された場合      
  if (isset($_POST["login"])) {
    // 認証成功
#    if ($_POST["userid"] == "mcc" && $_POST["password"] == "mcc") {
    if ($_POST["userid"] == $ini["id"] && md5($_POST["password"]) == $ini["pw"]) {
      // 戻り先（パラメタ付き）を現在のセッションから取得      
      if (isset($_SESSION["return_url"])){
        $return_url = $_SESSION["return_url"];
      } else {
        $return_url = 'index.php?serial_id='.$_POST["serial_id"];
      }
      $logfile->log('['.__LINE__.']'.'$return_url = '.$return_url);

      // 旧セッションの LOGINS を覚えておく
      if (isset($_SESSION["LOGINS"])){
        $LOGINS = $_SESSION["LOGINS"];
      } else {
        $LOGINS = NULL;
      }

      // セッションIDを新規に発行する
      session_regenerate_id(TRUE);

      // ログインユーザ ID を新セッションに保存
      $_SESSION["USERID"] = $_POST["userid"];
      if (is_null($LOGINS)){
        $_SESSION["LOGINS"] = array($_POST["serial_id"] => $_POST["userid"]);
      } else {
        $_SESSION["LOGINS"] = $LOGINS + array($_POST["serial_id"] => $_POST["userid"]);
      }

      // 現在の LOGINS をログに出力
      ob_start();
      var_dump($_SESSION["LOGINS"]);
      $result =ob_get_contents();
      ob_end_clean();
      $logfile->log('['.__LINE__.']'.'$_SESSION["LOGINS"] = '.$result);

#      if (!isset($_SESSION["serial_id"])){
#        $_SESSION["serial_id"] = array($_POST["serial_id"]);
#      } else {
#        array_push($_SESSION["serial_id"],$_POST["serial_id"]);
#      }
      #$_SESSION["serial_id"] = $_POST["serial_id"];

      // 指定された戻り先にリダイレクト
      header("Location: ".$return_url);
      // header("Location: config.php?serial_id=".$serial_id);
      exit;
    }
    else {
      // 画面に表示するため特殊文字をエスケープする
      $viewUserId = htmlspecialchars($_POST["userid"], ENT_QUOTES);
      //$logfile->log('['.__LINE__.']'.'$_POST["userid"] = '.$_POST["userid"]);

      $errorMessage = "ユーザIDあるいはパスワードに誤りがあります。";
    }
  }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo TITLE?></title>
<?php   if ($cdn): ?>
  <link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.css" />
  <link rel="stylesheet" href="https://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.css" />
  <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
  <script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>
  <script src="custom-scripting.js"></script>
  <script src="http://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.js"></script>
  <script src="https://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.js"></script>
<?php   else: ?>
  <link rel="stylesheet" href="js/jquery.mobile-1.3.1.min.css" />
  <script src="node_modules/jquery/dist/jquery.min.js"></script>
  <script src="custom-scripting.js"></script>
  <script src="js/jquery.mobile-1.3.1.min.js"></script>
<?php   endif; ?>

</head>
<body>

<div data-role="page"> 
    
<div data-role="header" data-position="fixed">
    <h1><?php echo TITLE?></h1>
    <!-- <a data-role="button" data-inline="true" href="index.usbrh2.php?serial_id=<?php echo $serial_id; ?>" data-icon="gear" data-transition="fade" data-ajax="false">設定変更</a> -->
    <a href="" data-rel="back">戻る</a>
</div>

<div data-role="content">
  <h1>ログイン</h1>
  <form id="loginForm" name="loginForm" action="<?php print($_SERVER['PHP_SELF']).'?serial_id='.$serial_id ?>" method="POST">
    <fieldset>
    <div style="color:#ff0000;"><?php echo $errorMessage ?></div>
    <input type="hidden" id="serial_id" name="serial_id" value="<?php echo $serial_id ?>" placeholder="ID">
    <input type="text" id="userid" name="userid" value="<?php echo $viewUserId ?>" placeholder="ID">
    <input type="password" id="password" name="password" value="" placeholder="PW">
    <input type="submit" id="login" name="login" value="ログイン">
  </fieldset>
  </form>
</div>

</div> <!-- page -->

</body>
</html>
