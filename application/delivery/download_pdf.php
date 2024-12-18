<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2018-03-19
 * Time: 오전 11:07
 */

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/dprinting/DeliveryAppDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new DeliveryAppDAO();
$fb = new FormBean();

$param = array();

foreach($fb->fb as $key=>$value)
{
    $param[$key] = $value;
}

$rs = $param["path"];

$file_size = filesize($rs);

header("Pragma: public");
header("Expires: 0");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$rs\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: $file_size");

ob_clean();
flush();
readfile($rs);

?>