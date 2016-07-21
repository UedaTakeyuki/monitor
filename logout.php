<?php
/**
 * Logout.
 * 
 * Logout.
 * 
 * Requires $_GET['serial_id']
 * 
 * @author Dr. Takeyuki UEDA
 * @copyright Copyright© Atelier UEDA 2016 - All rights reserved.
 *
 */

  ini_set( 'display_errors', 0 ); // エラー出力しない場合
  #ini_set( 'display_errors', 1 ); // エラー出力する場合

  // セッションスタート
  session_start();

  // リファラを戻り先アドレスとして保存
  $return_url=$_SERVER['HTTP_REFERER'];

  //LOGINS から serial_id を削除
  unset($_SESSION["LOGINS"][$_GET['serial_id']]);

  //LOGINS が空ならセッションそのものを廃棄
  if (empty($_SESSION["LOGINS"])){
  	session_destroy();
  }

// 戻り先に戻る
//  header("Location: index.php?serial_id=".$_GET['serial_id']);
  header("Location: ".$return_url);
//  exit;
?>