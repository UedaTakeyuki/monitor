<?php
class GetPic {
  // property
  // public $name;
  // constructor
  public $serial_id;
  public $device;
  public function __construct($serial_id) {
    // $this->name = $name;
    $this->serial_id = $serial_id;
    $this->serial_id_path = dirname(__FILE__).'/uploads/'.$this->serial_id;
#    $this->device = $device;
#    $this->device_path = $this->serial_id_path.'/'.$this->device;
  }
  
  public function get_latest_ymd_subfolder($directory_path){
    $f = scandir($directory_path);
    $ff = preg_grep("/^[0-9]{8}$/", $f); # 20160718 and so on.
    rsort($ff, SORT_STRING);
    return $ff[0];
  }
  
  public function get_latest_pic_on_the_folder($directory_path){
    $f = scandir($directory_path);
    $ff = preg_grep("/^[0-9]{14}.jpeg/", $f); # 20160718 and so on.
    rsort($ff, SORT_STRING);
    return $ff[0];
  }

  public function get_latest_pic($device){
    # device_path の作成
    if ($device == ""){
      $this->device = "";
      $this->device_path = "";
    } else {
      $this->device = $device;
      $this->device_path = $this->serial_id_path.'/'.$device;
    }
    # 設定の読み込み
    $configfile = "uploads/".$this->serial_id."/SavePic.ini";
    $ini = parse_ini_file($configfile);
    
    # 戻り値
    $result_array = array('serial_id' => $this->serial_id, 'device' => $this->device);

    switch ($ini["folder_type"]){
      case "device":
        $result_array['ymd'] = '';
        $result_array['pic_file_name'] = $this->get_latest_pic_on_the_folder($this->device_path);
        break;
      case "device_date":
        $result_array['ymd'] = $this->get_latest_ymd_subfolder($this->device_path);
        $result_array['pic_file_name'] = $this->get_latest_pic_on_the_folder($this->device_path.'/'.$result_array['ymd']);
        break;
      case "serial_id":
        $result_array['device'] = '';
        $result_array['ymd'] = '';
        $result_array['pic_file_name'] = $this->get_latest_pic_on_the_folder($this->serial_id_path);
        break;
      case "serial_id_date":
        $result_array['device'] = '';
        $result_array['ymd'] = $this->get_latest_ymd_subfolder($this->serial_id_path);
        $result_array['pic_file_name'] = $this->get_latest_pic_on_the_folder($this->serial_id_path.'/'.$result_array['ymd']);
        break;
      default:
        break;
    }
    return $result_array;
  }
}