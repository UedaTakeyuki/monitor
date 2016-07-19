<?php
require_once("../GetPic.class.php"); 

$getpic = new GetPic("00000000607f447e");
var_dump($getpic->get_latest_pic("video0"));