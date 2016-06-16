<?php
require_once("Log.php");

$logfilename = "postpic.out.log";
$logfile = &Log::factory('file', $logfilename, 'TEST'); 
$logfile->log('['.__LINE__.']'.'*** STARTED ***');

// 必用に応じて log ファイルのコンパクションを行う
$p=pathinfo($_SERVER['SCRIPT_FILENAME']);
$logfile->log('['.__LINE__.']'.'$_SERVER[SCRIPT_FILENAME] = '.$_SERVER['SCRIPT_FILENAME']);
$command = "".$p['dirname']."/compaction.sh ".$logfilename;
$logfile->log('['.__LINE__.']'.'$command = '.$command);
`$command`;

// 写真のコンパクションを行う
if (isset($_POST['serial_id'])){
  $command = "".$p['dirname']."/rmfiles.sh ".$p['dirname'].'/uploads/'.$_POST['serial_id']." jpeg mv &";
  $logfile->log('['.__LINE__.']'.'$command = '.$command);
  `$command`;
}

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

        $msg = array('green', 'ファイルは正常にアップロードされました');

    } catch (RuntimeException $e) {

#        $msg = ['red', $e->getMessage()];
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