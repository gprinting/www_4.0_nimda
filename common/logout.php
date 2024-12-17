<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
$fb = new FormBean();
$fb->removeAllSession();

header("Location: /login.html");
?>
