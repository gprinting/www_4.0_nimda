<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2018-01-24
 * Time: 오전 11:51
 */

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/dprinting/fakepDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$fb = new FormBean();

$param = array();
foreach($fb->fb as $key=>$value)
{
    $param[$key] = $value;
}

$dao = new fakepDAO();

$json = array();
$json["content"] = $dao->selectPathList($conn, $param);

//var_dump($json);
echo json_encode($json);

?>