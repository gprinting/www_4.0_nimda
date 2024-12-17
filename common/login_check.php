<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/doc/nimda/common/common.inc");

$session = $fb->getSession();

$id = $session["id"];

if (empty($id) === true) {
    $template->reg("header_login_class", "login"); 
    $template->reg("header_login", getLogoutHtml()); 
    $template->reg("side_menu", ""); 
} else {
    $template->reg("header_login_class", "memberInfo"); 
    $template->reg("header_login", getLoginHtml($session)); 
    //$template->reg("side_menu", getAsideHtml($session)); 
}
?>
