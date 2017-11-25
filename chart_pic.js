/** 
 * View chart and pic
 */
//--------------------------
// chart
//--------------------------
/* No used this valiable.
<?php foreach ($data_inis as $key => $value): ?>
<?php   $dini = parse_ini_file($value); ?>
<?php   if (!isset($dini["image_type"])): ?>

  var myChart_<?=$dini["dname"]?>;

<?php   endif; ?>
<?php endforeach ?>
*/

  var chart_type = "time";
  var forced = "no";
  var timeFormat = 'MM/DD HH:mm:ss';

  Chart.defaults.global.legend.display = false;

  var gLastTime; // 最後に受け取ったデータの時間。ここで明に受け取るファイル以外のファイルの時刻チェッック用

/* separate from .php
<?php foreach ($data_inis as $key => $value): ?>
<?php   $dini = parse_ini_file($value); ?>
<?php   if (!isset($dini["image_type"])): ?>

  var gLastTime_<?=$dini["dname"]?>;

<?php   endif; ?>
<?php endforeach ?>
*/
  var gLastTimes = [];
  gIni.sensor_devices.forEach(function(device){
    gLastTimes[device.dname] = "";
  });

  // コンテキストの取得
/* separate from .php
<?php foreach ($data_inis as $key => $value): ?>
<?php   $dini = parse_ini_file($value); ?>
<?php   if (!isset($dini["image_type"])): ?>

  var ctx_<?=$dini["dname"]?> = document.getElementById("myChart_<?=$dini["dname"]?>").getContext("2d");;

<?php   endif; ?>
<?php endforeach ?>
*/
  var ctxs = [];
  gIni.sensor_devices.forEach(function(device){
    ctxs[device.dname] = document.getElementById("myChart_"+device.dname).getContext("2d");
  });

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

/* separate from .php
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
*/
  function onReceiveStreamValues(device, j_data) {
    // 最後のデータの時間を保存
    gLastTime = j_data[0].datetime;
    gLastTimes[device.dname] = gLastTime; 

    // グラフの更新
    setMyGraph(j_data, myLineCharts[device.dname], device.dname + "_tag", device.unit);
    myLineCharts[device.dname].update();
  };

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

/* separate from .php
<?php foreach ($data_inis as $key => $value): ?>
<?php   $dini = parse_ini_file($value); ?>
<?php   if (!isset($dini["image_type"])): ?>

  config = new_config();
  setColor_onGraph(config.data.datasets[0]);
  myLineChart_<?=$dini["dname"]?> = new Chart(ctx_<?php echo $dini["dname"]?>, config);
  myLineChart_<?=$dini["dname"]?>.datasets = config.data.datasets;

<?php   endif; ?>
<?php endforeach ?>
*/
    myLineCharts = [];
    gIni.sensor_devices.forEach(function(device){
      config = new_config();
      setColor_onGraph(config.data.datasets[0]);
      myLineCharts[device.dname] = new Chart(ctxs[device.dname], config);
      myLineCharts[device.dname].datasets = config.data.datasets;
    });

    var iv = setInterval( function() {
      $.ajax({
        type: "GET",
        url: "data.php",
        data: {serial_id: gIni.serial_id,
               show_data_lows: gIni['show_data_lows'],
//               show_data_lows: <?=$ini['show_data_lows']; ?>,
//               show_data_gnt: "<? = $ini['show_data_gnt']; ?>",
//               LastTime: gLastTime,
/* separate from .php
               ILTimes: {
              // 以下、キーはファイル名（拡張子なし）
<?php foreach ($data_inis as $key => $value): ?>
<?php   $dini = parse_ini_file($value); ?>
<?php   if (!isset($dini["image_type"])): ?>

               <?= $dini["fname"]?>: gLastTime_<?= $dini["dname"] ?>,

<?php   endif; ?>
<?php endforeach ?>
               }
*/
               ILTimes: gLastTimes

        },
        dataType: "json",
      })
      .then(
        function(data, dataType){
          if(data){
/* separate from .php
<?php foreach ($data_inis as $key => $value): ?>
<?php   $dini = parse_ini_file($value); ?>
<?php   if (!isset($dini["image_type"])): ?>

            if(data.<?= $dini["fname"]?>)
              onReceiveStreamValues_<?= $dini["dname"]?>(data.<?= $dini["fname"]?>);

<?php   endif; ?>
<?php endforeach ?>
*/
            gIni.sensor_devices.forEach(function(device){
              if(data[device.fname])
                onReceiveStreamValues(device, data[device.fname]);              
            });
          }
        },
        function(XMLHttpRequest, textStatus, errorThrown){
          console.log('Error : ' + errorThrown);
      });

/* separate from .php
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
*/
      gIni.image_devices.forEach(function(device){
        $.ajax({
          type: "GET",
          url: "pic.php",
          data: {serial_id: gIni.serial_id, device: device.dname},
          dataType: "json",
        })
        .then(
          function(data, dataType){
            document.getElementById("pic_file_name_" + device.dname).innerHTML = data.latest_pic_name.slice(0,4) + "年" + data.latest_pic_name.substring(4,6) + "月" + data.latest_pic_name.substring(6,8) + "日" + data.latest_pic_name.substring(8,10) + "時" + data.latest_pic_name.substring(10,12) + "分" + data.latest_pic_name.substring(12,14) + "秒";
            if (data.device == ""){
              // no device specified
              if (data.ymd == ""){
                $('#latest_pic_' + device.dname).attr('src', 'uploads/'+gIni.serial_id+"/"+data.latest_pic_name);
              } else {
                $('#latest_pic_' + device.dname).attr('src', 'uploads/'+gIni.serial_id+"/"+data.ymd+"/"+data.latest_pic_name);
              }
            } else {
              // device specified
              if (data.ymd == ""){
                $('#latest_pic_' + device.dname).attr('src', 'uploads/'+gIni.serial_id+"/"+data.device+"/"+data.latest_pic_name);
              } else {
                $('#latest_pic_' + device.dname).attr('src', 'uploads/'+gIni.serial_id+"/"+data.device+"/"+data.ymd+"/"+data.latest_pic_name);
              }
            }
  //          $('#latest_pic').attr('src', 'uploads/'+"<?= $_GET['serial_id'];?>"+"/"+data.latest_pic_name);
          },
          function(XMLHttpRequest, textStatus, errorThrown){
            console.log('Error : ' + errorThrown);
        });
      });
//    }, 250 );
    }, 1000 );