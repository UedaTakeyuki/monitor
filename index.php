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
  <!--<script src="node_modules/chart.js/node_modules/moment/min/moment.min.js"></script>-->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.5/Chart.min.js"></script>
  <!--<script src="node_modules/chart.js/dist/Chart.min.js"></script>-->
  <link rel="stylesheet" href="https://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.css" />
  <!--<link rel="stylesheet" href="js/jquery.mobile-1.3.1.min.css" />-->
  <script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>
  <!--<script src="node_modules/jquery/dist/jquery.min.js"></script>-->
  <script src="https://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.js"></script>
  <!--<script src="js/jquery.mobile-1.3.1.min.js"></script>-->

  <!-- VUE start -->
  <script src='https://cdnjs.cloudflare.com/ajax/libs/vue/1.0.10/vue.js'></script>
  <!--<script src='node_modules/vue/dist/vue.js'></script>-->
  <!-- VUE end -->

  <!-- BOOTSTRAP start -->
  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
  <!-- <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">-->
  <!-- Optional theme -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
  <!-- <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">-->
  <!-- Latest compiled and minified JavaScript -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
  <!-- <script src="node_modules/bootstrap/dist/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>-->
  <!-- BOOTSTRAP end -->
<?php   else: ?>
  <!--<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.1/moment.min.js"></script>-->
  <script src="node_modules/chart.js/node_modules/moment/min/moment.min.js"></script>
  <!--<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.5/Chart.min.js"></script>-->
  <script src="node_modules/chart.js/dist/Chart.min.js"></script>
  <!--<link rel="stylesheet" href="https://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.css" />-->
  <link rel="stylesheet" href="js/jquery.mobile-1.3.1.min.css" />
  <!--<script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>-->
  <script src="node_modules/jquery/dist/jquery.min.js"></script>
  <!--<script src="https://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.js"></script>-->
  <script src="js/jquery.mobile-1.3.1.min.js"></script>

  <!-- VUE start -->
  <!--<script src='https://cdnjs.cloudflare.com/ajax/libs/vue/1.0.10/vue.js'></script>-->
  <script src='node_modules/vue/dist/vue.js'></script>
  <!-- VUE end -->

  <!-- BOOTSTRAP start -->
  <!-- Latest compiled and minified CSS -->
  <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">-->
  <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
  <!-- Optional theme -->
  <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">-->
  <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap-theme.min.css">
  <!-- Latest compiled and minified JavaScript -->
  <!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>-->
  <script src="node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
  <!-- BOOTSTRAP end -->
<?php   endif; ?>

  <script>
  //--------------------------
  // chart
  //--------------------------
<?php foreach ($data_inis as $key => $value): ?>
<?php   $dini = parse_ini_file($value); ?>
<?php   if (!isset($dini["image_type"])): ?>

  var myChart_<?=$dini["dname"]?>;

<?php   endif; ?>
<?php endforeach ?>

  var chart_type = "time";
  var forced = "no";
  var timeFormat = 'MM/DD HH:mm:ss';

  Chart.defaults.global.legend.display = false;

  var gLastTime; // 最後に受け取ったデータの時間。ここで明に受け取るファイル以外のファイルの時刻チェッック用

<?php foreach ($data_inis as $key => $value): ?>
<?php   $dini = parse_ini_file($value); ?>
<?php   if (!isset($dini["image_type"])): ?>

  var gLastTime_<?=$dini["dname"]?>;

<?php   endif; ?>
<?php endforeach ?>

  var gIni = { // config.ini の設定値
    'show_data_lows': <?=$ini["show_data_lows"]; ?>
  };

  window.onload = function (){
    // IE ajax キャッシュの抑止
    $.ajaxSetup({
      cache: false,
    });

    //--------------------------
    // VUE
    //--------------------------
    /*
              var demo = new Vue({
                      el: '#demo.old',
                      data: {
                        message: '温度計測値'
                      }
                    });
    */
    var alart_vue = new Vue({
      el: '#a1',
      data: {
        message: '2016年9月7日、水警報受信',
      },
      methods: {
        release: function(){
          // 表示を消す
          $('#a1').removeClass("hidden");
          alart_vue.message = data.water;
          // alart.ini を消す
          $.ajax({
            type: "POST",
            url: "postalart.php",
            data: {
              serial_id: "<?= $_GET['serial_id']; ?>",
              name: "water",
              status: "off"
            },
            dataType: "json",
          })
          .then(
            function(data, dataType){
            },
            function(XMLHttpRequest, textStatus, errorThrown){
              console.log('Error : ' + errorThrown);
          });

          
        }
      }
    });

  // コンテキストの取得
<?php foreach ($data_inis as $key => $value): ?>
<?php   $dini = parse_ini_file($value); ?>
<?php   if (!isset($dini["image_type"])): ?>

  var ctx_<?=$dini["dname"]?> = document.getElementById("myChart_<?=$dini["dname"]?>").getContext("2d");;

<?php   endif; ?>
<?php endforeach ?>

  // イニシャルデータ
  var data = {
    labels: [],
    datasets: [
    ]
  };

  function toMyTime(dt){
    mydate = new Date(Date.parse(dt));
    return mydate.toLocaleTimeString();
  };
  function toMyDateTime(dt){
    mydate = new Date(Date.parse(dt));
    return mydate.toLocaleString();
  };
  
  // toLocalString が実装依存なので使わない
  function toMyTime2(dt){
    mydate = new Date(dt);
    str = mydate.getHours() + ":" +
    //      mydate.getMinutes(); // + ":" + mydate.getSeconds()
          mydate.getMinutes() + ":" +
          mydate.getSeconds();
    return str;
  };
  function toMyDateTime2(dt){
    mydate = new Date(dt);
/*    str = mydate.getFullYear() + "年" +
          (mydate.getMonth() + 1) + "月" +
          mydate.getDate() + "日 " +
          toMyTime2(dt);*/
/*    str = (mydate.getMonth() + 1) + "/" +
          mydate.getDate() + " " +
          toMyTime2(dt);*/
    str = mydate.getFullYear() + "/" +
          (mydate.getMonth() + 1) + "/" +
          mydate.getDate() + " " +
          toMyTime2(dt);
    return str;
  };
  function toMyDateTime2_wo_yyyy(dt){
    mydate = new Date(dt);
/*    str = mydate.getFullYear() + "年" +
          (mydate.getMonth() + 1) + "月" +
          mydate.getDate() + "日 " +
          toMyTime2(dt);*/
    str = (mydate.getMonth() + 1) + "/" +
          mydate.getDate() + " " +
          toMyTime2(dt);
/*    str = mydate.getFullYear() + "/" +
          (mydate.getMonth() + 1) + "/" +
          mydate.getDate() + " " +
          toMyTime2(dt);*/
    return str;
  };

  function setMyGraph(j_data, chart, tag, unit_str){
    chart.config.data.labels.splice(0, chart.config.data.labels.length);
    if (chart.config.data.datasets[0].data.length != 0){
      chart.config.data.datasets[0].data.splice(0, chart.config.data.datasets[0].data.length);
    }
    for (i=0; i < j_data.length; i++){
      chart.config.data.labels.push(toMyDateTime2(j_data[i].datetime));
      chart.config.data.datasets[0].data.push(j_data[i].data.toFixed(1));
    }
    // グラフ領域のタグの描画
    tag_val_string = toMyDateTime2_wo_yyyy(j_data[0].datetime) + ", " + j_data[0].data.toFixed(1) +unit_str;
    document.getElementById(tag).innerHTML = tag_val_string;

    chart.update();
  };

<?php foreach ($data_inis as $key => $value): ?>
<?php   $dini = parse_ini_file($value); ?>
<?php   if (!isset($dini["image_type"])): ?>

  function onReceiveStreamValues_<?=$dini["dname"]?>(j_data) {
    // 最後のデータの時間を保存
    gLastTime = j_data[0].datetime;
    gLastTime_<?=$dini["dname"]?> = gLastTime; 

    // グラフの更新
    setMyGraph(j_data, myLineChart_<?=$dini["dname"]?>,"<?=$dini["dname"]?>_tag", " <?=$dini["unit"]?>");
    myLineChart_<?=$dini["dname"]?>.update();
  };

<?php   endif; ?>
<?php endforeach ?>

  // time chart の時間軸のフォーマット。momentjs の display format を指定
  var time = {
    // string/callback - By default, date objects are expected. You may use a pattern string from http://momentjs.com/docs/#/parsing/string-format/ to parse a time string format, or use a callback function that is passed the label, and must return a moment() instance.
    parser: false,
    // string - By default, unit will automatically be detected.  Override with 'week', 'month', 'year', etc. (see supported time measurements)
    unit: false,

    // Number - The number of steps of the above unit between ticks
    unitStepSize: 1,

    // string - By default, no rounding is applied.  To round, set to a supported time unit eg. 'week', 'month', 'year', etc.
    round: false,

    // Moment js for each of the units. Replaces `displayFormat`
    // To override, use a pattern string from http://momentjs.com/docs/#/displaying/format/
    displayFormats: {
      'millisecond': 'SSS [ms]',
      'second': 'h:mm:ss a', // 11:20:01 AM
      'minute': 'h:mm:ss a', // 11:20:01 AM
      'hour': 'MM, D hA', // Sept 4, 5PM
//              'hour': 'MMM D, hA', // Sept 4, 5PM
      'day': 'll', // Sep 4 2015
      'week': 'll', // Week 46, or maybe "[W]WW - YYYY" ?
      'month': 'MM YYYY', // Sept 2015
//              'month': 'MMM YYYY', // Sept 2015
      'quarter': '[Q]Q - YYYY', // Q3
      'year': 'YYYY', // 2015
    },
    // Sets the display format used in tooltip generation
    tooltipFormat: '',
  };

  var options = {
//    responsive: true,
    scales: {
      xAxes: [{
        type: "time",
        time: time,
        ticks:{
          autoSkip: true,
        },
        scaleLabel: {
          display: false,
          labelString: '日時'
        }
      }, ],
      yAxes: [{
        scaleLabel: {
          display: false,
          labelString: '℃'
        },
        ticks:{
//          stepSize: 0.5,
        },
      }]
    },
  }

  function new_config(){
    config = {type: 'line', 
              data: $.extend(true, {}, data), 
              options: options};
    config.data.datasets.push($.extend(true, {}, {data: [],fill: false}));
    config.data.datasets[0].label = "ラベル";
    return config;
  }

  function setColor_onGraph(o){
    o.borderColor = "rgba(151,187,205,0.4)"
    o.backgroundColor = "rgba(151,187,205,0.5)"
    o.pointBorderColor = "rgba(151,187,205,0.7)"
    o.pointBackgroundColor = "rgba(151,187,205,0.5)"
    o.pointBorderWidth = 1;
    o.fill = true;

  //  o.lineTension = 0;
  }

  //　描画
  //   data をそのまま渡すと全グラフの data ツリーが同じになり、x, y ラベルが同じになってしまうので
  //   data オブジェクトのコピー (JQuerry の extend) を渡す
  Chart.defaults.global.animation = false;

<?php foreach ($data_inis as $key => $value): ?>
<?php   $dini = parse_ini_file($value); ?>
<?php   if (!isset($dini["image_type"])): ?>

  config = new_config();
  setColor_onGraph(config.data.datasets[0]);
  myLineChart_<?=$dini["dname"]?> = new Chart(ctx_<?php echo $dini["dname"]?>, config);
  myLineChart_<?=$dini["dname"]?>.datasets = config.data.datasets;

<?php   endif; ?>
<?php endforeach ?>

    var iv = setInterval( function() {

      $.ajax({
        type: "GET",
        url: "data.php",
        data: {serial_id: "<?=$_GET['serial_id']; ?>",
               show_data_lows: <?=$ini['show_data_lows']; ?>,
//               show_data_gnt: "<? = $ini['show_data_gnt']; ?>",
//               LastTime: gLastTime,
               ILTimes: {
              // 以下、キーはファイル名（拡張子なし）
<?php foreach ($data_inis as $key => $value): ?>
<?php   $dini = parse_ini_file($value); ?>
<?php   if (!isset($dini["image_type"])): ?>

               <?= $dini["fname"]?>: gLastTime_<?= $dini["dname"] ?>,

<?php   endif; ?>
<?php endforeach ?>
               }
        },
        dataType: "json",
      })
      .then(
        function(data, dataType){
          if(data){
<?php foreach ($data_inis as $key => $value): ?>
<?php   $dini = parse_ini_file($value); ?>
<?php   if (!isset($dini["image_type"])): ?>

            if(data.<?= $dini["fname"]?>)
              onReceiveStreamValues_<?= $dini["dname"]?>(data.<?= $dini["fname"]?>);

<?php   endif; ?>
<?php endforeach ?>
          }
        },
        function(XMLHttpRequest, textStatus, errorThrown){
          console.log('Error : ' + errorThrown);
      });


<?php foreach ($data_inis as $key => $value): ?>
<?php   $dini = parse_ini_file($value); ?>
<?php   if (isset($dini["image_type"])): ?>

      $.ajax({
        type: "GET",
        url: "pic.php",
        data: {serial_id: "<?= $_GET['serial_id']; ?>", device: "<?= $dini["dname"] ?>"},
        dataType: "json",
      })
      .then(
        function(data, dataType){
          document.getElementById("pic_file_name_<?= $dini["dname"] ?>").innerHTML = data.latest_pic_name.slice(0,4) + "年" + data.latest_pic_name.substring(4,6) + "月" + data.latest_pic_name.substring(6,8) + "日" + data.latest_pic_name.substring(8,10) + "時" + data.latest_pic_name.substring(10,12) + "分" + data.latest_pic_name.substring(12,14) + "秒";
          if (data.device == ""){
            // no device specified
            if (data.ymd == ""){
              $('#latest_pic_<?= $dini["dname"] ?>').attr('src', 'uploads/'+"<?= $_GET['serial_id'];?>"+"/"+data.latest_pic_name);
            } else {
              $('#latest_pic_<?= $dini["dname"] ?>').attr('src', 'uploads/'+"<?= $_GET['serial_id'];?>"+"/"+data.ymd+"/"+data.latest_pic_name);
            }
          } else {
            // device specified
            if (data.ymd == ""){
              $('#latest_pic_<?= $dini["dname"] ?>').attr('src', 'uploads/'+"<?= $_GET['serial_id'];?>"+"/"+data.device+"/"+data.latest_pic_name);
            } else {
              $('#latest_pic_<?= $dini["dname"] ?>').attr('src', 'uploads/'+"<?= $_GET['serial_id'];?>"+"/"+data.device+"/"+data.ymd+"/"+data.latest_pic_name);
            }
          }
//          $('#latest_pic').attr('src', 'uploads/'+"<?= $_GET['serial_id'];?>"+"/"+data.latest_pic_name);
        },
        function(XMLHttpRequest, textStatus, errorThrown){
          console.log('Error : ' + errorThrown);
      });

<?php   endif; ?>
<?php endforeach ?>

// alart.ini があれば
<?php
  $alarm_ini_file = __DIR__. "/uploads/".$_GET['serial_id']."/alart.ini";
  if (is_readable($alarm_ini_file)) {
?>
      $.ajax({
        type: "POST",
        url: "alart.php",
        data: {serial_id: "<?= $_GET['serial_id']; ?>"},
        dataType: "json",
      })
      .then(
        function(data, dataType){
          if (data.water != ""){
            $('#a1').removeClass("hidden");
            alart_vue.message = data.water;
          } else {
            $('#a1').addClass("hidden");
            alart_vue.message = "";
          }
        },
        function(XMLHttpRequest, textStatus, errorThrown){
          console.log('Error : ' + errorThrown);
        });
<?php
  }
?>
      
//    }, 250 );
    }, 1000 );

	}
  </script>
  <!--<script>
	var client; // MQTTのクライアントです
	var clientId = "clientid-test"; // ClientIDを指定します。

	function connect(){
	    var user_name = "UedaTakeyuki@github";
	    var pass = "tcJy9P8z61ztwF6K";
	    var wsurl = "ws://free.mqtt.shiguredo.jp:8080/mqtt";

	    // WebSocketURLとClientIDからMQTT Clientを作成します
	    client = new Paho.MQTT.Client(wsurl, clientId);
//	    client = new Paho.MQTT.Client("free.mqtt.shiguredo.jp",8080, "/mqtt", clientId);

	    // connectします
	    client.connect({userName: user_name, password: pass, onSuccess:onConnect, onFailure: failConnect});

	}

	// 接続が失敗したら呼び出されます
	function failConnect(e) {
	    console.log("connect failed");
	    console.log(e);
	}

	// 接続に成功したら呼び出されます
	function onConnect() {
	    console.log("onConnect");
			subscribe();
	}

	// メッセージが到着したら呼び出されるコールバック関数
	function onMessageArrived(message) {
	    console.log("onMessageArrived:"+message.payloadString);
      // データ追加
      myLineChart.addData([parseFloat(message.payloadString)], "");
      // 先頭データ削除
      myLineChart.removeData();
	}

	function subscribe(){
	    // コールバック関数を登録します
	    client.onMessageArrived = onMessageArrived;

	    var topic = "UedaTakeyuki@github/#";
	    // Subscribeします
	    client.subscribe(topic);
	}
	connect();
	//subscribe();
  </script>-->
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
  <div id="a1" class="hidden alert alert-danger" role="alert">
    水警報、{{ message }}受信<button v-on:click="release()" type="button" class="btn btn-danger">解除</button>
  </div>
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

<div data-role="footer" data-position="fixed" data-disable-page-zoom="false">
    <h4>© Atelier UEDA🐸</h4>
</div>
</div> <!-- page -->

</body>
</html>
