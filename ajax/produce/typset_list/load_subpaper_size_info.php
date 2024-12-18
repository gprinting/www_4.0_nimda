<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");

const TYPE_46_SIZE = array( 1  => array("WID" => 1090, "VERT" => 768)
,2  => array("WID" => 768,  "VERT" => 545)
,4  => array("WID" => 545,  "VERT" => 394)
,8  => array("WID" => 394,  "VERT" => 272)
,16 => array("WID" => 272,  "VERT" => 197)
,32 => array("WID" => 197,  "VERT" => 136));

// 국계열 종이 사이즈
const TYPE_GUK_SIZE = array( 1  => array("WID" => 939, "VERT" => 636)
,2  => array("WID" => 636, "VERT" => 468)
,4  => array("WID" => 468, "VERT" => 318)
,8  => array("WID" => 318, "VERT" => 234)
,16 => array("WID" => 234, "VERT" => 159)
,32 => array("WID" => 159, "VERT" => 117));

// 4*6계열 판 사이즈
const TYPE_46_BOARD_SIZE = array( /*1 => array("WID" => 1030, "VERT" => 800)
                                     ,*/2 => array("WID" => 830,  "VERT" => 645));

// 국계열 판 사이즈
const TYPE_GUK_BOARD_SIZE = array( 1 => array("WID" => 1030, "VERT" => 800)
,2 => array( 0 => array("WID" => 760, "VERT" => 635)
    ,1 => array("WID" => 670, "VERT" => 550)));

$fb = new FormBean();

$affil = $fb->form("affil");
$subpaper = $fb->form("subpaper");

//46계열 일 경우
if ($affil == "46") {
   echo TYPE_46_SIZE[$subpaper]["WID"] . "♪" . TYPE_46_SIZE[$subpaper]["VERT"];
//국계열 일 경우
} else if ($affil == "국") {
   echo TYPE_GUK_SIZE[$subpaper]["WID"] . "♪" . TYPE_GUK_SIZE[$subpaper]["VERT"];
}
?>
