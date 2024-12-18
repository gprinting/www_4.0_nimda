<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-11-08
 * Time: 오후 4:36
 */

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/dprinting/StateManagerDAO.inc');
include_once(INC_PATH . "/classes/dprinting/StateManager/StateManager.php");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new StateManagerDAO();
$fb = new FormBean();

$param = array();

foreach($fb->fb as $key=>$value)
{
    $param[$key] = $value;
}

$order_detail_file_nums = explode('|', $param['order_detail_file_nums']);

foreach($order_detail_file_nums as $order_detail_file_num) {
    $param['order_detail_file_num'] = $order_detail_file_num;
    $state = new StateManager($param);
    $state->ToNextState();
}

?>