<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-11-07
 * Time: 오후 6:47
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

$sheet_typset_seqnos = explode('|', $param['sheet_typset_seqnos']);

foreach($sheet_typset_seqnos as $sheet_typset_seqno) {
    $param['sheet_typset_seqno'] = $sheet_typset_seqno;
    $state = new StateManager($param);
    $state->ToNextState();
}


?>