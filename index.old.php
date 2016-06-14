<?php
  date_default_timezone_set("Asia/Tokyo");
  session_start();

  require_once("common.php");
  require_once("Log.php");
  $logfile = &Log::factory('file', 'index.out.log', 'TEST'); 
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
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title><?php echo TITLE?></title>
  <!-- <script src="mqttws31.js" type="text/javascript"></script>-->
  <script src="Chart.js"></script>
  <link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.css" />
  <script src="m2x-2.0.3.js"></script>
<!--  <script src="jquery-1.10.2.min.js"></script> -->
  <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
  <!-- <script src="custom-scripting.js"></script> -->
  <script src="http://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.js"></script>


  <script>
  //--------------------------
  // chart
  //--------------------------
  var myLineChart;
  var myChart_hmd;
  var myChart_co2;
  var myChart_lux;
  var myChart_hmddft;

//  var gTemp;   // 飽差の計算のために、現在の温度を保存
  var gLastTime; // 最後に受け取ったデータの時間。ここで明に受け取るファイル以外のファイルの時刻チェッック用
  var gLastTime_temp;   // 最後に受け取った温度の時間
  var gLastTime_hmd;    // 最後に受け取った湿度の時間
  var gLastTime_co2;    // 最後に受け取ったCO2 の時間
  var gLastTime_lux;    // 最後に受け取った照度の時間
  var gLastTime_hmddft; // 最後に受け取った飽差の時間

  var gIni = { // config.ini の設定値
    'show_data_lows': <?php echo $ini["show_data_lows"]; ?>
  };
//  alert(gIni['show_data_lows']);
//  alert(gIni.show_data_lows);

  window.onload = function (){
  // コンテキストの取得
<?php if($ini["show_temp"]){ ?>
  var ctx_tmp = document.getElementById("myChart_tmp").getContext("2d");
<?php } ?>
<?php if($ini["show_hmd"]){ ?>
  var ctx_hmd = document.getElementById("myChart_hmd").getContext("2d");
<?php } ?>
<?php if($ini["show_co2"]){ ?>
  var ctx_co2 = document.getElementById("myChart_co2").getContext("2d");
<?php } ?>
<?php if($ini["show_lux"]){ ?>
  var ctx_lux = document.getElementById("myChart_lux").getContext("2d");
<?php } ?>
<?php if($ini["show_hmddft"]){ ?>
  var ctx_hmddft = document.getElementById("myChart_hmddft").getContext("2d");
<?php } ?>
  // イニシャルデータ
  var data = {
    //labels: ["January", "February", "March", "April", "May", "June", "July"],
    //labels: ["", "", "", "", "", "", "", "", "", "", ""],
    labels: [<?php
              for ($i=0; $i<$ini["show_data_lows"]; $i++){
                echo '"", ';
              }
            ?>],
    datasets: [
        /*{
            label: "My First dataset",
            fillColor: "rgba(220,220,220,0.2)",
            strokeColor: "rgba(220,220,220,1)",
            pointColor: "rgba(220,220,220,1)",
            pointStrokeColor: "#fff",
            pointHighco2Fill: "#fff",
            pointHighco2Stroke: "rgba(220,220,220,1)",
            data: [65, 59, 80, 81, 56, 55, 40]
        },*/
        {
            label: "My Second dataset",
            fillColor: "rgba(151,187,205,0.2)",
            strokeColor: "rgba(151,187,205,1)",
            pointColor: "rgba(151,187,205,1)",
            pointStrokeColor: "#fff",
            pointHighco2Fill: "#fff",
            pointHighco2Stroke: "rgba(151,187,205,1)",
            //data: [28, 48, 40, 19, 86, 27, 90]
            //data: [00, 00, 00, 00, 00, 00, 00, 00, 00, 00, 00]
            data: [<?php
                    for ($i=0; $i<$ini["show_data_lows"]; $i++){
                      echo '00, ';
                    }?>
                  ]
        }
    ]
	};

  //--------------------------
  // m2x
  //--------------------------
  //var m2x = new M2X("ec70557571ee2dddac861076ef569f6c");

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
          mydate.getMinutes()// + ":" +
    //      mydate.getSeconds();
    return str;
  };
  function toMyDateTime2(dt){
    mydate = new Date(dt);
/*    str = mydate.getFullYear() + "年" +
          (mydate.getMonth() + 1) + "月" +
          mydate.getDate() + "日 " +
          toMyTime2(dt);*/
    str = (mydate.getMonth() + 1) + "/" +
          mydate.getDate() + " " +
          toMyTime2(dt);
    return str;
  };

  function setMyGraph(j_data, chart, tag, unit_str){
    num_graph_mems = gIni.show_data_lows; // グラフのポイント数
    /*if (j_data.length < num_graph_mems){
      num_graph_mems = j_data.length;
    }*/
    //slice_strings_JST = 4;     // 日付文字列の末尾の" JST"を削除
    //slice_strings_sec_JST = 7; // 日付文字列の末尾の秒と" JST"を削除
    for (i=0; i <= num_graph_mems -1; i++){
      console.log("i="+i+" j_data="+j_data[0+i]);
      //data.labels[<?php echo $ini["show_data_lows"]-1; ?>-i] = toMyTime2(j_data[0+i].datetime)
      if (gIni.show_data_lows < 30){
        if (i<j_data.length){
          chart.scale.xLabels[num_graph_mems -1 -i] = toMyDateTime2(j_data[0+i].datetime);
        } else {
          chart.scale.xLabels[num_graph_mems -1 -i] = "";
        }
      } else if (gIni.show_data_lows < 60){
        if (i % 3 == 0){
          if (i<j_data.length){
            chart.scale.xLabels[num_graph_mems -1 -i] = toMyDateTime2(j_data[0+i].datetime);
          } else {
            chart.scale.xLabels[num_graph_mems -1 -i] = "";
          }
        } else {
          chart.scale.xLabels[num_graph_mems -1 -i] = "";
        }
      } else {
        if (i % 6 == 0){
          if (i<j_data.length){
            chart.scale.xLabels[num_graph_mems -1 -i] = toMyDateTime2(j_data[0+i].datetime);
          } else {
            chart.scale.xLabels[num_graph_mems -1 -i] = "";
          }
        } else {
          chart.scale.xLabels[num_graph_mems -1 -i] = "";
        }
      }

      if (i<j_data.length){
        chart.datasets[0].points[num_graph_mems -1 -i].value = j_data[0+i].data.toFixed(1);
      } else {
        chart.datasets[0].points[num_graph_mems -1 -i].value = 0;        
      }
    };
    // グラフ領域のタグの描画
    tag_val_string = toMyDateTime2(j_data[0].datetime) + ", " + j_data[0].data.toFixed(1) +unit_str;
    document.getElementById(tag).innerHTML = tag_val_string;

    chart.update();
  };

  // 現在の値の表示
/*  function onReceiveStreamValues_setCurrent(data) {
//    if (data.temp){ // 基準値が取れない場合はなにもしない
      // 最後のデータの時間を保存
      //gLastTime = data.temp[0].datetime;

      // データの表示
      //cTime = gLastTime;
//    cTime = data.temp[0].datetime;
      if(data.temp){
        cTime=data.temp[0].datetime;
        cTemp = data.temp[0].data.toFixed(1); 
        document.getElementById("datetime_val_tag").innerText = cTime;
        document.getElementById("temp_val_tag").innerText = cTemp + " ℃";
//        if (data.temp[0].datetime == cTime){
//          cTemp = data.temp[0].data.toFixed(1); 
//        } else {
//          cTemp = "-";
//        }
      }
      if(data.humidity){
        cTime=data.humidity[0].datetime;
        cHumidity = data.humidity[0].data.toFixed(1); 
        document.getElementById("datetime_val_tag").innerText = cTime;
        document.getElementById("hmd_val_tag").innerText = cHumidity + " %";
//        if (data.humidity[0].datetime == cTime){
//          cHumidity = data.humidity[0].data.toFixed(1); 
//        } else {
//          cHumidity = "-";
//        }
      }
      if(data.humiditydeficit){
        cTime=data.humiditydeficit[0].datetime;
        cHumiditydeficit = data.humiditydeficit[0].data.toFixed(1); 
        document.getElementById("datetime_val_tag").innerText = cTime;
        document.getElementById("hd_val_tag").innerText = cHumiditydeficit + " g/㎥";
        switch(true){
          case cHumiditydeficit < 2.0:
          // 低すぎ
            document.getElementById("hd_val_tag").setAttribute("style","color:#ff0000;");
            break;
          case cHumiditydeficit >= 2.0 && cHumiditydeficit < 3.0:
            // 注意
            document.getElementById("hd_val_tag").setAttribute("style","color:#ffff00;");
            break;
          case cHumiditydeficit >= 3.0 && cHumiditydeficit < 6.0:
            // 適正値
            document.getElementById("hd_val_tag").setAttribute("style","color:#000000;");
            break;
          case cHumiditydeficit >= 6.0 && cHumiditydeficit < 7.0:
            // 注意
            document.getElementById("hd_val_tag").setAttribute("style","color:#ffff00;");
            break;
          default:
            // 高すぎ
            document.getElementById("hd_val_tag").setAttribute("style","color:#ff0000;");
            break;
          }
//                if (data.humidity[0].datetime == cTime){
//          cHumidity = data.humidity[0].data.toFixed(1); 
//        } else {
//          cHumidity = "-";
//        }
      }
<?php if($ini["show_co2"]){ ?>
      if (data.CO2){
//        if (data.CO2[0].datetime == cTime){
          cTime=data.CO2[0].datetime;
          cCO2 = data.CO2[0].data.toFixed(1);
          document.getElementById("datetime_val_tag").innerText = cTime;
          document.getElementById("co2_val_tag").innerText = cCO2 + " ppm";
      }
//      } else {
//        cCO2 = "-";
//      }
//      } else {
//        cCO2 = "-";
//      }
//      
      if (data.lux){
//      if (data.lux[0].datetime == cTime){
        cTime=data.lux[0].datetime;
        cLux = data.lux[0].data.toFixed(1);
        document.getElementById("datetime_val_tag").innerText = cTime;
        document.getElementById("lux_val_tag").innerText = cLux + " lux";
      }
//      } else {
//        cLux = "-";
//      }
//      } else {
//        cLux = "-";
//      }
//      
<?php } ?>
//      document.getElementById("datetime_val_tag").innerText = cTime;
//      document.getElementById("temp_val_tag").innerText = cTemp + " ℃";
//      document.getElementById("hmd_val_tag").innerText = cHumidity + " %";
<?php if($ini["show_co2"]){ ?>
//      document.getElementById("co2_val_tag").innerText = cCO2 + " ppm";
//      document.getElementById("lux_val_tag").innerText = cLux + " lux";
<?php } ?>
//      if (cTemp != "-" && cHumidity != "-" ){
//        hd = HumidityDeficit(parseFloat(cTemp),parseFloat(cHumidity));
//        document.getElementById("hd_val_tag").innerText = hd.toFixed(1) + " g/㎥";
//        switch(true){
//          case hd < 2.0:
//          // 低すぎ
//            document.getElementById("hd_val_tag").setAttribute("style","color:#ff0000;");
//            break;
//          case hd >= 2.0 && hd < 3.0:
//            // 注意
//            document.getElementById("hd_val_tag").setAttribute("style","color:#ffff00;");
//            break;
//          case hd >= 3.0 && hd < 6.0:
//            // 適正値
//            document.getElementById("hd_val_tag").setAttribute("style","color:#000000;");
//            break;
//          case hd >= 6.0 && hd < 7.0:
//            // 注意
//            document.getElementById("hd_val_tag").setAttribute("style","color:#ffff00;");
//            break;
//          default:
//            // 高すぎ
//            document.getElementById("hd_val_tag").setAttribute("style","color:#ff0000;");
//            break;
//        }
//      }
//      
//    }
  };
*/
<?php if($ini["show_temp"]){ ?>
  function onReceiveStreamValues_tmp(j_data) {
    // 最後のデータの時間を保存
    gLastTime = j_data[0].datetime;
    gLastTime_temp = gLastTime; 

    // グラフの更新
    setMyGraph(j_data, myLineChart,"temp_tag", " ℃");
    myLineChart.update();
    // 飽差の計算のために現在温度を保存
    //gTemp = j_data[0].data.toFixed(1);
  };
<?php } ?>

<?php if($ini["show_hmd"]){ ?>
  function onReceiveStreamValues_hmd(j_data) {
    // 最後のデータの時間を保存
    gLastTime = j_data[0].datetime;
    gLastTime_hmd = gLastTime; 

    // グラフの更新
    setMyGraph(j_data, myLineChart_hmd,"hmd_tag", " %");
    myLineChart_hmd.update();
    // 飽差を計算して表示
    //hd = HumidityDeficit(parseFloat(gTemp),parseFloat(j_data[0].data.toFixed(1)));
    //document.getElementById("hmd_tag").innerHTML
    // = document.getElementById("hmd_tag").innerHTML + " 飽差: " + hd.toFixed(1) + " g/m3";
  };
<?php } ?>

<?php if($ini["show_co2"]){ ?>
  function onReceiveStreamValues_co2(j_data) {
    // 最後のデータの時間を保存
    gLastTime = j_data[0].datetime;
    gLastTime_co2 = gLastTime; 

    // グラフの更新
    setMyGraph(j_data, myLineChart_co2,"co2_tag", " ppm");
    myLineChart_co2.update();
  };
<?php } ?>

<?php if($ini["show_lux"]){ ?>
  function onReceiveStreamValues_lux(j_data) {
    // 最後のデータの時間を保存
    gLastTime = j_data[0].datetime;
    gLastTime_lux = gLastTime; 

    // グラフの更新
    setMyGraph(j_data, myLineChart_lux,"lux_tag", " lux");
    myLineChart_lux.update();
  };
<?php } ?>

<?php if($ini["show_hmddft"]){ ?>
  function onReceiveStreamValues_hmddft(j_data) {
    // 最後のデータの時間を保存
    gLastTime = j_data[0].datetime;
    gLastTime_hmddft = gLastTime; 

    // グラフの更新
    setMyGraph(j_data, myLineChart_hmddft,"hmddft_tag", " g/㎥");
    myLineChart_hmddft.update();
  };
<?php } ?>

  function handleError(error) {
        var text = error.message;

        if (error.responseJSON) {
            console.log(error);
            text = JSON.stringify(error.responseJSON);
        } else {
            text = error.message;
        }
        console.log(text);
  };

  //
  // 飽差関連式
  //
  function HumidityDeficit(t,rh){ // t: 温度, rh: 相対湿度
    // http://d.hatena.ne.jp/Rion778/20121203/1354546179
    ret = (AbsoluteHumidity(t, 100) - AbsoluteHumidity(t, rh));
    console.log("HD = " + ret)
    return ret; 
  };

  function AbsoluteHumidity(t, rh){
    // http://d.hatena.ne.jp/Rion778/20121203/1354461231
    //return (2.166740 * 10^2 * rh * GofGra(t)/(100 * (t + 273.15)));
    ret = (2.166740 * 100 * rh * tetens(t)/(100 * (t + 273.15)));
    console.log("AH = " + ret);
    return ret;
  };

  // 飽和水蒸気圧
  function GofGra(t){};
  function tetens(t){
    // http://d.hatena.ne.jp/Rion778/20121126/1353861179
    console.log("T = " + t);
    console.log("TETENS 7.5*t/(t + 237.3) = " + (t + 237.3));
    ret = ( 6.11 * Math.pow(10,(7.5*t/(t + 237.3))));
    console.log("tetens = " + ret);
    return ret;
  };

  //　描画
  //   data をそのまま渡すと全グラフの data ツリーが同じになり、x, y ラベルが同じになってしまうので
  //   data オブジェクトのコピー (JQuerry の extend) を渡す
  Chart.defaults.global.animation = false;
<?php if($ini["show_temp"]){ ?>
  myLineChart = new Chart(ctx_tmp).Line($.extend(true, {}, data));
<?php } ?>
<?php if($ini["show_hmd"]){ ?>
  myLineChart_hmd = new Chart(ctx_hmd).Line($.extend(true, {}, data));
<?php } ?>
<?php if($ini["show_co2"]){ ?>
  myLineChart_co2 = new Chart(ctx_co2).Line($.extend(true, {}, data));
<?php } ?>
<?php if($ini["show_lux"]){ ?>
  myLineChart_lux = new Chart(ctx_lux).Line($.extend(true, {}, data));
<?php } ?>
<?php if($ini["show_hmddft"]){ ?>
  myLineChart_hmddft = new Chart(ctx_hmddft).Line($.extend(true, {}, data));
<?php } ?>

  // オプション設定
  //myLineChart.defaults.global.showScale = false;
  // データ追加
  //myLineChart.addData([0], "");
  // 先頭データ削除
  //myLineChart.removeData();

    var iv = setInterval( function() {
    //new Chart(ctx).Line(data, {scaleOverride: true,scaleSteps: 25,scaleStepWidth: 5,scaleStartValue: -5,scaleShowGridLines : false});
/*
      m2x.devices.streamValues("26959b8879733be0466c2bc98703a382",
                    "USBRH_temperature", {limit: 11},
                    onReceiveStreamValues_tmp,
                    handleError)
      m2x.devices.streamValues("26959b8879733be0466c2bc98703a382",
                    "USBRH_humidity", {limit: 11},
                    onReceiveStreamValues_hmd,
                    handleError)
      m2x.devices.streamValues("26959b8879733be0466c2bc98703a382",
                    "grove_co2_digital", {limit: 11},
                    onReceiveStreamValues_co2,
                    handleError) */

      $.ajax({
      type: "GET",
//      url: "data.php",
      url: "datafromdb.php",
      //data: {serial_id: "00000000c4c423ee"},
      data: {serial_id: "<?php echo $_GET['serial_id']; ?>",
             show_data_lows: <?php echo $ini['show_data_lows']; ?>,
             show_data_gnt: "<?php echo $ini['show_data_gnt']; ?>",
             LastTime: gLastTime,
             ILTimes: {
              // 以下、キーはファイル名（拡張子なし）
              temp: gLastTime_temp,
              humidity: gLastTime_hmd,
              CO2: gLastTime_co2,
              lux: gLastTime_lux,
              humiditydeficit: gLastTime_hmddft
             }
            },
      dataType: "json",
      /**
       * Ajax通信が成功した場合に呼び出されるメソッド
       */
      success: function(data, dataType) 
      {
        //console.log('temp = ' + data.temp);
        //console.log('humidity = ' + data.humidity);
        //document.getElementById("pic_file_name").innerHTML = data.latest_pic_name.slice(0,4) + "年" + data.latest_pic_name.substring(4,6) + "月" + data.latest_pic_name.substring(6,8) + "日" + data.latest_pic_name.substring(8,10) + "時" + data.latest_pic_name.substring(10,12) + "分" + data.latest_pic_name.substring(12,14) + "秒";
        //$('#latest_pic').attr('src', '/tools/150721/uploads/'+"<?php echo $_GET['serial_id'];?>"+"/"+data.latest_pic_name);
        if(data){
          //onReceiveStreamValues_setCurrent(data);
<?php if($ini["show_temp"]){ ?>
          if(data.temp)
            onReceiveStreamValues_tmp(data.temp);
<?php } ?>
<?php if($ini["show_hmd"]){ ?>
          if(data.humidity)
            onReceiveStreamValues_hmd(data.humidity);
<?php } ?>
<?php if($ini["show_co2"]){ ?>
          if(data.CO2)
            onReceiveStreamValues_co2(data.CO2);
<?php } ?>
<?php if($ini["show_lux"]){ ?>
          if(data.lux)
            onReceiveStreamValues_lux(data.lux);
<?php } ?>
<?php if($ini["show_hmddft"]){ ?>
          if(data.humiditydeficit)
            onReceiveStreamValues_hmddft(data.humiditydeficit);
<?php } ?>
        }
      },
      /**
       * Ajax通信が失敗場合に呼び出されるメソッド
       */
      error: function(XMLHttpRequest, textStatus, errorThrown) 
      {
        //通常はここでtextStatusやerrorThrownの値を見て処理を切り分けるか、単純に通信に失敗した際の処理を記述します。

        //this;
        //thisは他のコールバック関数同様にAJAX通信時のオプションを示します。

        //エラーメッセージの表示
        console.log('Error : ' + errorThrown);
      }
    });

      $.ajax({
      type: "GET",
      url: "pic.php",
      //data: {serial_id: "00000000c4c423ee"},
      data: {serial_id: "<?php echo $_GET['serial_id']; ?>"},
      dataType: "json",
      /**
       * Ajax通信が成功した場合に呼び出されるメソッド
       */
      success: function(data, dataType) 
      {
        //console.log("data.latest_pic_name = "+data.latest_pic_name);
        document.getElementById("pic_file_name").innerHTML = data.latest_pic_name.slice(0,4) + "年" + data.latest_pic_name.substring(4,6) + "月" + data.latest_pic_name.substring(6,8) + "日" + data.latest_pic_name.substring(8,10) + "時" + data.latest_pic_name.substring(10,12) + "分" + data.latest_pic_name.substring(12,14) + "秒";
//        $('#latest_pic').attr('src', '/tools/151024/uploads/'+"<?php echo $_GET['serial_id'];?>"+"/"+data.latest_pic_name);
        $('#latest_pic').attr('src', 'uploads/'+"<?php echo $_GET['serial_id'];?>"+"/"+data.latest_pic_name);
      },
      /**
       * Ajax通信が失敗場合に呼び出されるメソッド
       */
      error: function(XMLHttpRequest, textStatus, errorThrown) 
      {
        //通常はここでtextStatusやerrorThrownの値を見て処理を切り分けるか、単純に通信に失敗した際の処理を記述します。

        //this;
        //thisは他のコールバック関数同様にAJAX通信時のオプションを示します。

        //エラーメッセージの表示
        console.log('Error : ' + errorThrown);
      }
    });


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

<div data-role="page"> 
    
<div data-role="header" data-position="fixed">
  <h1><?php echo TITLE?></h1>
  <a data-role="button" data-inline="true" href="config.php?serial_id=<?php echo $_GET['serial_id']; ?>" data-icon="gear" data-transition="fade" data-ajax="false">設定変更</a>
  <div class="ui-btn-right">
    <a data-role="button" data-inline="true" href="logout.php?serial_id=<?php echo $_GET['serial_id']?>" data-transition="fade" data-ajax="false">ログアウト</a>
    <a data-role="button" data-inline="true" href="doc.php" data-rel="dialog">使用手引き</a>
  </div>
</div>

<div data-role="content">
<!-- <h3>
  <div><?php echo $ini["location"]?></div>
  <div>日時: <span id="datetime_val_tag"></div>
  <table border="1"><tr>
  <td>温度: <span id="temp_val_tag"></span></td><td>湿度: <span id="hmd_val_tag"></span></td>
  <td>飽差: <span id="hd_val_tag"></span></td>
<?php if($ini["show_co2"]){ ?>
  <td>CO2: <span id="co2_val_tag"></span></td>
  <td>照度: <span id="lux_val_tag"></span></td>
<?php } ?>
</tr></table>
</h3>-->
  <table>
    <tr>
<?php $showcount = 0; ?>
<?php if($ini["show_temp"]){ ++$showcount; ?>
      <td>
        <!-- <p>温度計測値:  <span id="temp_tag"></p> -->
        <h4><p>温度計測値:  <span id="temp_tag"></span>
        <a href="./download.php?serial_id=<?php echo $_GET['serial_id']; ?>&name=temp" rel="external">ダウンロード</a>
        </p></h4>
        <span style="font-size: 60%; padding: 20px">℃</span><br>
		    <canvas id="myChart_tmp" width="400" height="200" style="padding: 10px"></canvas>
        <!-- <a style="float: left;" href="./download.php?serial_id=<?php echo $_GET['serial_id']; ?>&name=temp" rel="external">温度ログファイルのダウンロード</a> -->
      </td>
<?php }
  if ($showcount % 2 == 0){ ?>
    </tr><tr>
<?php
  }
?>
<?php if($ini["show_hmd"]){ ++$showcount; ?>
      <td>
        <!-- <p>湿度計測値: <span id="hmd_tag"></p> -->
        <h4><p>湿度計測値:  <span id="hmd_tag"></span>
        <a href="./download.php?serial_id=<?php echo $_GET['serial_id']; ?>&name=humidity" rel="external">ダウンロード</a>
        </p></h4>
        <span style="font-size: 60%; padding: 20px">%</span><br>
        <canvas id="myChart_hmd" width="400" height="200" style="padding: 10px"></canvas>
        <!-- <a style="float: left;" href="./download.php?serial_id=<?php echo $_GET['serial_id']; ?>&name=humidity" rel="external">湿度ログファイルのダウンロード</a> -->
      </td>
<?php }
  if ($showcount % 2 == 0){ ?>
    </tr><tr>
<?php
  }
?>
<?php if($ini["show_hmddft"]){ ++$showcount; ?>
      <td>
        <h4><p>飽差計算値: <span id="hmddft_tag"></span>
        <a href="./download.php?serial_id=<?php echo $_GET['serial_id']; ?>&name=humiditydeficit" rel="external">ダウンロード</a>
        </p></h4>
        <span style="font-size: 60%; padding: 20px">g/㎥</span><br>
        <canvas id="myChart_hmddft" width="400" height="200" style="padding: 10px"></canvas>
        <!-- <a style="float: left;" href="./download.php?serial_id=<?php echo $_GET['serial_id']; ?>&name=humiditydeficit" rel="external">飽差ログファイルのダウンロード</a> -->
      </td>
<?php }
  if ($showcount % 2 == 0){ ?>
    </tr><tr>
<?php
  }
?>
<?php if($ini["show_co2"]){ ++$showcount; ?>
      <td>
        <!-- <p>CO2濃度計測値: <span id="co2_tag"></p> -->
        <h4><p>CO2濃度計測値:  <span id="co2_tag"></span>
        <a href="./download.php?serial_id=<?php echo $_GET['serial_id']; ?>&name=CO2" rel="external">ダウンロード</a>
        </p></h4>
        <span style="font-size: 60%; padding: 20px">ppm</span><br>
        <canvas id="myChart_co2" width="400" height="200" style="padding: 10px"></canvas>
        <!-- <a style="float: left;" href="./download.php?serial_id=<?php echo $_GET['serial_id']; ?>&name=CO2" rel="external">CO2濃度ログファイルのダウンロード</a> -->
      </td>
<?php }
  if ($showcount % 2 == 0){ ?>
    </tr><tr>
<?php
  }
?>
<?php if($ini["show_lux"]){ ++$showcount; ?>
      <td>
        <!-- <p>CO2濃度計測値: <span id="co2_tag"></p> -->
        <h4><p>照度計測値:  <span id="lux_tag"></span>
        <a href="./download.php?serial_id=<?php echo $_GET['serial_id']; ?>&name=lux" rel="external">ダウンロード</a>
        </p></h4>
        <span style="font-size: 60%; padding: 20px">lux</span><br>
        <canvas id="myChart_lux" width="400" height="200" style="padding: 10px"></canvas>
        <!-- <a style="float: left;" href="./download.php?serial_id=<?php echo $_GET['serial_id']; ?>&name=CO2" rel="external">CO2濃度ログファイルのダウンロード</a> -->
      </td>
<?php }
  if ($showcount % 2 == 0){ ?>
    </tr><tr>
<?php
  }
?>
<?php if($ini["show_pic"]){ ++$showcount; ?>
      <td>
        <p>現場状況: <span id="pic_file_name"></span></p>
        <img id="latest_pic" style="padding : 10px"></canvas>
      </td>
<?php }
  if ($showcount % 2 == 0){ ?>
    </tr>
<?php
  }
?>
  </table>
</div>

<!-- <div data-role="footer" data-position="fixed">
    <h4>© Atelier Grenouille</h4>
</div> ->>
</div> <!-- page -->

</body>
</html>
