<?php
class SavePic {
  // property
  // public $name;
  // constructor
  public function __construct() {
    // $this->name = $name;
  }
  static public function if_not_exist_then_make_folder($directory_path){
    if(!file_exists($directory_path)){
      mkdir($directory_path, 0744);
    }
  }
  static public function pic_compaction($folder, $extension, $num){
    $p=pathinfo($_SERVER['SCRIPT_FILENAME']);
    $command = "".$p['dirname']."/rmfiles.sh ".$folder.' jpeg '.$num. ' &';
 #   $logfile->log('['.__LINE__.']'.'$command = '.$command);
    `$command`;
  }
  static public function make_folder($setting){
    // $_FILES['upfile']['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
    $type = @exif_imagetype($_FILES['upfile']['tmp_name']);
    if (!in_array($type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG), true)) {
      throw new RuntimeException('画像形式が未対応です');
    }
    $today = date("Ymd");
    $serial_id_path       = './uploads/'.$_POST['serial_id'];
    $serial_id_date_path  = './uploads/'.$_POST['serial_id']. '/' . $today;
    if(isset($_POST["device"])){
      $device_path          = $serial_id_path . '/' . $_POST['device'];
      $device_date_path     = $device_path    . '/' . $today;
    }
    switch ($setting){
      case "device":
        self::if_not_exist_then_make_folder($device_path);
        return $device_path;
      case "device_date":
        self::if_not_exist_then_make_folder($device_path);
        self::if_not_exist_then_make_folder($device_date_path);
        return $device_date_path;
      case "serial_id":
        return $serial_id_path;
      case "serial_id_date":
        self::if_not_exist_then_make_folder($serial_id_date_path);
        return $serial_id_date_path;
      default:
        return $serial_id_path;      
    }
  }
  // method
  static public function save() { //$_POST, $_FILES
    $logfilename = "SavePic.out.log";
    $logfile = &Log::factory('file', $logfilename, 'TEST'); 
    $logfile->log('['.__LINE__.']'.'*** STARTED ***');
    # 設定の読み込み
    $configfile = "uploads/".$_POST['serial_id']."/SavePic.ini";
    $ini = parse_ini_file($configfile);
    // $_FILES['upfile']['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
    $type = @exif_imagetype($_FILES['upfile']['tmp_name']);
    if (!in_array($type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG), true)) {
      $logfile->log('['.__LINE__.']'.'画像形式が未対応です');
      move_uploaded_file($_FILES['upfile']['tmp_name'], 'uploadedfile');
      throw new RuntimeException('画像形式が未対応です');
    }
    // 保存する
    $pathData = pathinfo($_FILES['upfile']['name']);
    $folder = self::make_folder($ini["folder_type"]);
    $path = sprintf($folder.'/%s%s', $pathData["filename"], image_type_to_extension($type));
    $logfile->log('['.__LINE__.']'.'$path = '.$path);
    if (!move_uploaded_file($_FILES['upfile']['tmp_name'], $path)) {
      $logfile->log('['.__LINE__.']'.'ファイル保存時にエラーが発生しました');
      throw new RuntimeException('ファイル保存時にエラーが発生しました');
    }
    chmod($path, 0644);
    // 必要なら写真のコンパクションを行う
    if($ini["max_pic_num"]){
      self::pic_compaction($folder, "jpeg", $ini["max_pic_num"]);
    }
  }
}