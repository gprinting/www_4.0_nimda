<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");

$fb = new FormBean();

$affil = $fb->form("affil");
$subpaper = str_replace("절", "", $fb->form("subpaper"));

if ($subpaper == "전") {
    $subpaper = 1;
}

if ($affil == "국") {
    $wid = TYPE_GUK_SIZE[$subpaper]["WID"];
    $vert = TYPE_GUK_SIZE[$subpaper]["VERT"];
} else if($affil == "46") {
    $wid = TYPE_46_SIZE[$subpaper]["WID"];
    $vert = TYPE_46_SIZE[$subpaper]["VERT"];
}

echo $wid . "*" . $vert;
?>
