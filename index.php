<?php
/**
 * Main application of Monitor.
 * 
 * Main application of Monitor.
 * 1. Confirm login
 * 2. Retrun application skelton and js apps which get fresh data and pics and refresh page.
 * 
 * Requires $_GET['serial_id']
 * 
 * @author Dr. Takeyuki UEDA
 * @copyright Copyright© Atelier UEDA 2016 - All rights reserved.
 *
 */
 
 date_default_timezone_set("Asia/Tokyo");
  session_start();

  require_once("common.php");
  require_once("vendor/autoload.php"); 
  #require_once("Log.php");
  $logfile = &Log::factory('file', 'index.out.log', 'Tls EST'); 
  $logfile->log('['.__LINE__.']'.'*** STARTED ***');

  // エラー出力しない場合
  //ini_set( 'display_errors', 0 );
  // エラー出力する場合
  //ini_set( 'display_errors', 1 );

  # 設定の読み込み
  $configfile = "uploads/".$_GET['serial_id']."/config.ini";
  $ini = parse_ini_file($configfile);

  // ログイン状態のチェック
#  if (!isset($_SESSION["USERID"]) || !isset($_SESSION["serial_id"]) || $_SESSION["serial_id"] != $_GET['serial_id']) {
  if (!isset($_SESSION["LOGINS"]) || !array_key_exists($_GET['serial_id'], $_SESSION["LOGINS"])) {
    // ログイン成功後の戻り先(パラメタ付き)をセッション変数に保存
    $_SESSION["return_url"]=$_SERVER["REQUEST_URI"];
    $logfile->log('['.__LINE__.']'.'$_SESSION["return_url"] = '.$_SESSION["return_url"]);
    // ログイン処理
    header("Location: login.php?serial_id=".$_GET['serial_id']);
    exit;
  }

  // データの設定ファイル一覧を取得
  $data_inis = glob("uploads/".$_GET['serial_id']."/*.dini");

?>

<!DOCTYPE html>
<html lang="ja" id="demo">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

 <title><?=TITLE?></title>
<?php   if ($cdn): ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.1/moment.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.5/Chart.min.js"></script>
  <link rel="stylesheet" href="https://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.css" />
  <!--<script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>-->
  <script src="https://code.jquery.com/jquery-3.0.0.js"></script>
  <script src="https://code.jquery.com/jquery-migrate-3.0.0.js"></script>
  <script src="https://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.js"></script>

  <!-- VUE start -->
  <script src='https://cdnjs.cloudflare.com/ajax/libs/vue/1.0.10/vue.js'></script>
  <!-- VUE end -->

  <!-- BOOTSTRAP start -->
  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
  <!-- Optional theme -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
  <!-- Latest compiled and minified JavaScript -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
  <!-- BOOTSTRAP end -->
<?php   else: ?>
  <!-- old path <script src="node_modules/chart.js/node_modules/moment/min/moment.min.js"></script> -->
  <script src="node_modules/moment/min/moment.min.js"></script>
  <script src="node_modules/chart.js/dist/Chart.min.js"></script>
  <link rel="stylesheet" href="js/jquery.mobile-1.3.1.min.css" />
  <script src="node_modules/jquery/dist/jquery.min.js"></script>
  <script src="js/jquery-migrate-3.0.1.min.js"></script>
  <script src="js/jquery.mobile-1.3.1.min.js"></script>

  <!-- VUE start -->
  <script src='node_modules/vue/dist/vue.js'></script>
  <!-- VUE end -->

  <!-- BOOTSTRAP start -->
  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
  <!-- Optional theme -->
  <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap-theme.min.css">
  <!-- Latest compiled and minified JavaScript -->
  <script src="node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
  <!-- BOOTSTRAP end -->
<?php   endif; ?>

  <script src="alart_vue_body.js"></script>
  <!--<script src="mqttclient.js"></script> -->
  <script>
  //--------------------------
  // globals
  //--------------------------
  var gIni = {
    // value of config.ini
    show_data_lows: <?=$ini["show_data_lows"]; ?>,
    saltstack_url: "<?= $ini['saltstack_url']; ?>",
    // value of dini
    image_devices: [],
    sensor_devices: [],
    // vue instances related image_devices
    rtmpbroadcast_vues: [],
    // serial id
    serial_id: "<?=$_GET['serial_id']?>"
  };
//  var serial_id = "<?=$_GET['serial_id']?>";
<?php foreach ($data_inis as $key => $value): ?>
<?php   $dini = parse_ini_file($value); ?>
<?php   if (isset($dini["image_type"])): ?>
  gIni.image_devices.push({dname: "<?= $dini['dname']; ?>", image_type: "<?= $dini['image_type']; ?>"});
<?php   else: ?>
  gIni.sensor_devices.push({pname: "<?= $dini['pname']; ?>", fname: "<?= $dini['fname']; ?>", dname: "<?= $dini['dname']; ?>", unit: "<?= $dini['unit']; ?>"});
<?php   endif; ?>
<?php endforeach ?>

  window.onload = function (){
    // IE ajax キャッシュの抑止
    $.ajaxSetup({
      cache: false,
    });
	}
  </script>
</head>
<body>

<!-- <div data-role="page" id="demo"> -->
<div data-role="page">
    
<div data-role="header" data-position="fixed" data-disable-page-zoom="false">

<?php   if (isset($ini["title"])): ?>
  <h1><?php echo $ini["title"]?></h1>
<?php   else: ?>
  <h1><?php echo TITLE?></h1>
<?php   endif; ?>

  <a data-role="button" data-inline="true" href="config.php?serial_id=<?php echo $_GET['serial_id']; ?>" data-icon="gear" data-transition="fade" data-ajax="false">設定変更</a>
  <div class="ui-btn-right">
    <a data-role="button" data-inline="true" href="logout.php?serial_id=<?php echo $_GET['serial_id']?>" data-transition="fade" data-ajax="false">ログアウト</a>
  </div>
</div>

<div data-role="content">

<?php
# alart.ini があれば
  $alarm_ini_file = __DIR__. "/uploads/".$_GET['serial_id']."/alart.ini";
  if (is_readable($alarm_ini_file)) {
?>
  <div id="alart" class="hidden alert alert-danger" role="alert">
    水警報、{{ message }}受信<button v-on:click="release()" type="button" class="btn btn-danger">解除</button>
  </div>
  <script>
    var alart_vue = new Vue(alart_vue_body('#alart',gIni.serial_id));
    alart_vue.check_alart();
  </script>
<?php
  }
?>
  
<div class="row">

<?php foreach ($data_inis as $key => $value): ?>
<?php   $dini = parse_ini_file($value); ?>
<?php   if (!isset($dini["image_type"])): ?>

      <div class="col-md-4 col-sm-6 col-xs-12">
        <!-- <h4><p>温度計測値:  <span id="temp_tag"></span> -->
        <h4><p> <?= $dini["pname"]?> :  <span id="<?= $dini["dname"]?>_tag"></span>
        <a href="./download.php?serial_id=<?= $_GET['serial_id']; ?>&name=<?php echo $dini["fname"]?>" rel="external">ダウンロード</a>
        </p></h4>
        <span style="font-size: 60%; padding: 20px"><?=$dini["unit"]?></span><br>
        <!--<canvas id="myChart_tmp" width="400" height="200" style="padding: 10px"></canvas>-->
        <canvas id="myChart_<?= $dini["dname"]?>" width="300" height="200" style="padding: 10px"></canvas>
      </div><!-- <div class="col-md-4 col-sm-6 col-xs-12"> -->
      
<?php   elseif (isset($dini["image_type"])): ?>

      <div class="col-md-4 col-sm-6 col-xs-12">
        <p><?= $dini["pname"] ?> <span id="pic_file_name_<?= $dini["dname"] ?>"></span></p>
        <img id="latest_pic_<?= $dini["dname"] ?>" style="padding : 10px"></canvas>
      </div><!-- <div class="col-md-4 col-sm-6 col-xs-12"> -->

<?php   endif; ?>
<?php endforeach ?>

  </div><!-- <div class="row"> -->
</div>
<script src="chart_pic.js"></script>

<div data-role="footer" data-position="fixed" data-disable-page-zoom="false">
    <h4>© Atelier UEDA🐸</h4>
</div>
</div> <!-- page -->

</body>
</html>
