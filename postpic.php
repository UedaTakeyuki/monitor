<?php
/**
 * [API] Post pic.
 * 
 * Use HTTP POST method for posting pic file from slider to the monitor.
 * 
 * Requires $_POST['serial_id']
 * 
 * @author Dr. Takeyuki UEDA
 * @copyright Copyright© Atelier UEDA 2016 - All rights reserved.
 *
 */
 
 require_once("vendor/autoload.php"); 
#require_once("Log.php");

spl_autoload_register(function($class) {
  require $class . ".class.php";
});

$logfilename = "postpic.out.log";
$logfile = &Log::factory('file', $logfilename, 'TEST'); 
$logfile->log('['.__LINE__.']'.'*** STARTED ***');

// 必用に応じて log ファイルのコンパクションを行う
$p=pathinfo($_SERVER['SCRIPT_FILENAME']);
$logfile->log('['.__LINE__.']'.'$_SERVER[SCRIPT_FILENAME] = '.$_SERVER['SCRIPT_FILENAME']);
$command = "".$p['dirname']."/compaction.sh ".$logfilename;
$logfile->log('['.__LINE__.']'.'$command = '.$command);
`$command`;

$logfile->log('['.__LINE__.']'.'$_POST[serial_id] = '.$_POST['serial_id']);
$logfile->log('['.__LINE__.']'.'$_POST[device] = '.$_POST['device']);
$logfile->log('['.__LINE__.']'.'$_FILES[upfile][error] = '.$_FILES['upfile']['error']);
$logfile->log('['.__LINE__.']'.'$_FILES[upfile][name] = '.$_FILES['upfile']['name']);

// 写真のコンパクションを行う
/*
if (isset($_POST['serial_id'])){
  $command = "".$p['dirname']."/rmfiles.sh ".$p['dirname'].'/uploads/'.$_POST['serial_id']." jpeg mv &";
  $logfile->log('['.__LINE__.']'.'$command = '.$command);
  `$command`;
}
*/

if (isset($_FILES['upfile']['error']) && is_int($_FILES['upfile']['error'])) {

    try {
        // $_FILES['upfile']['error'] の値を確認
        switch ($_FILES['upfile']['error']) {
            case UPLOAD_ERR_OK: // OK
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('No FILE.');
            case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズ超過
            case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過
                throw new RuntimeException('Too Big.');
            default:
                throw new RuntimeException('Something wrong...');
        }
        /*
        // $_FILES['upfile']['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
        $type = @exif_imagetype($_FILES['upfile']['tmp_name']);
        if (!in_array($type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG), true)) {
            throw new RuntimeException('画像形式が未対応です');
        }

        // 保存する
        $pathData = pathinfo($_FILES['upfile']['name']);
        $path = sprintf('./uploads/'.$_POST['serial_id'].'/%s%s', $pathData["filename"], image_type_to_extension($type));
        if (!move_uploaded_file($_FILES['upfile']['tmp_name'], $path)) {
            throw new RuntimeException('ファイル保存時にエラーが発生しました');
        }
        chmod($path, 0644);
        */
        SavePic::save();

        $msg = array('green', 'ファイルは正常にアップロードされました');

    } catch (RuntimeException $e) {

        $msg = array('red', $e->getMessage());

    }
    header('Content-Type: application/xhtml+xml; charset=utf-8');
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>post picture</title>
</head>
<body>
<?php if (isset($msg)): ?>
  <legend>結果</legend>
  <span style="color:<?=$msg[0]?>;"><?=$msg[1]?></span>
<?php endif; ?>
  <form enctype="multipart/form-data" method="post" action="">
    <legend>画像ファイルを選択</legend>
    <input type="text" name="serial_id" id="serial_id"/>
    <input type="file" name="upfile" /><br />
    <input type="submit" value="送信" />
  </form>
</body>
</html>