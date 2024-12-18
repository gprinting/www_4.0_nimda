<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-09-18
 * Time: 오전 10:14
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
    $param['col'][$key] = $value;
}

$param['table'] = 'CJparcel_record';

$dao->InsertData($conn, $param);

$bun_info = explode("_",$param['col']["order_num"]);
$bun_dlvr_order_num = $bun_info[0];
$bun_group = $bun_info[1] . "_" . $bun_info[2] . "_" . $bun_info[3];
$bun_seq = $bun_info[4];

$update_invo_num = array();
$update_invo_num["invo_num"] = $param['col']["invoiceNumber"];
$update_invo_num["bun_dlvr_order_num"] = $bun_dlvr_order_num;
$update_invo_num["bun_group"] = $bun_group;
$update_invo_num["bun_seq"] = $bun_seq;

$dao->updateBunInfo($conn, $update_invo_num);

?>

