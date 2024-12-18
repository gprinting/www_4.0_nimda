<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/PasswordEncrypt.inc");

echo password_hash(ADMIN_FLAG[0], PASSWORD_DEFAULT);
?>
