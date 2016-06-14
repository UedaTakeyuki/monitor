<?php
define('gal03', 'gal4');
require_once("common.php");
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title></title>
  <link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.css" />
  <!-- <script src="mqttws31.js" type="text/javascript"></script>-->
  <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
  <script src="node_modules/jquery-lazyload/jquery.lazyload.js" type="text/javascript"></script>
  <script src="http://code.jquery.com/mobile/1.3.1/jquery.mobile-1.3.1.min.js"></script>
  <script>
    $(function() {
    $("img.lazy").lazyload();
    });
  </script>
</head>
<body>

<div data-role="page"> 
    
<div data-role="header" data-position="fixed">
    <h1><?php echo gal03?> マニュアル</h1>
  <!-- <a href="index.php?serial_id=<?php echo $_GET['serial_id']; ?>" data-icon="home" data-transition="fade" data-ajax="false">ホーム</a> -->
</div>

<div data-role="content">
  <h2><?php echo TITLE?> マニュアル</h2>
  <?php echo gal03 ?> はマルチロガーです。定期的に環境データを取得してSDカードに記録し、かつインターネット接続が可能であればサーバにデータを送信します。
  インターネットが利用できる環境では Web で、できない環境では回収した SDからデータを取得することで、管理者は環境データを取得することができます 
  <img src="2015-09-30_21.13.08.png" width="300" height="200">

  <h2>WiFi の設定</h2>
  <?php echo gal03 ?> は、インターネット環境が利用可能であれば取得したデータをサーバに送信します。LAN ケーブルを接続するか WiFi ルータの SSID と key を gal3 に設定することでインターネット接続が利用可能になります。
  Raspberry Pi を起動する前に、SD カードにテキストファイルで WiFi の設定を与えておき、Raspberry Pi の起動時に自身で WiFi の設定を行う
  <a href="http://qiita.com/UedaTakeyuki/items/b64c63ade185303628eb">addwpa</a>という仕組みを使い、WiFi の設定をSDカードにPCから書き込みます
  <ol>
    <li>Raspberry Pi の電源を切り、SDカードを抜きます 
      <img src="2015-08-28＿15.43.19.s.jpg" width="300" height="200">
    </li>
    <li>SDカードを Windows や Mac の PC に挿します。SDカードは "boot" という名前で認識されます 
      <img src="2015-09-28_12.30.40.png" width="300" height="200">
    </li>
    <li>addwpa.txt 一行目に設定する WiFi の SSID の値を、二行目に wpa の key の値だけを書いて保存します
      <img src="2015-09-28_12.32.01.png" width="300" height="200">
    </li>
    <li>このSDカードを Raspberry Pi に戻して再起動すると、指定した SSID と key が設定されます。設定が正常に終了すると、addwpa.txt ファイルは自動的に削除されます
    </li>
  </ol>
<h2>SDカードに保存したデータの利用</h2>
通常の Raspberry Pi の SD カードと異なり、<?php echo gal03 ?> には Mac や Windows PCと共有できる FAT32 の領域があります。取得したセンサーデータはこの中に csv ファイルとして、撮影した写真は jpg ファイルとして保存されています。
それらのデータは PC からは "boot" という名前で見える領域の以下の場所に保存されますので、必用に応じて PC 等で回収することが可能です。
<ul>
  <li>データ： /DATA/log</li>
  <li>写真： /MEDIA/photo</li>
</ul>
共有領域のサイズは 8GB の <?php echo gal03 ?> SD カードで 4GByte, 16GB のカードで 11GByte 用意してあります。  

<h2>飽差計算値</h3>
<?php echo gal03 ?> は飽差を表示します。飽差とは<a href="http://lib.ruralnet.or.jp/genno/yougo/gy087.html">空気中に水がまだ蒸発できる量を1立方メートルあたりの重さで表した量で、植物の生理に影響を与えることが知られています。</a>
</div> <!-- content -->

<!-- <div data-role="footer" data-position="fixed">
    <h4>© Atelier UEDA🐸</h4>
</div> -->
</div> <!-- page -->
</body>
</html>
