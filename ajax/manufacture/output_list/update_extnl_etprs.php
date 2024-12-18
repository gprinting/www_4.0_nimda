<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 2017-08-22
 * Time: 오전 11:23
 */

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/output_mng/OutputListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OutputListDAO();


$param = array();
$param["table"] = "sheet_typset";
$param["col"]["print_etprs"] = $fb->form("extnl_etprs");
$param["prk"] = "sheet_typset_seqno";
$param["prkVal"] = $fb->form("seqno");

$rs = $dao->updateData($conn, $param);
if($rs) {
    echo 1;
} else {
    echo 0;
}

?>