<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/basic_mng/pur_etprs_mng/PurEtprsListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$purDAO = new PurEtprsListDAO();
$conn->StartTrans();

$param = array();
$param["table"] = "extnl_etprs_member";
$param["prk"] = "extnl_etprs_member_seqno";
$param["prkVal"] = $fb->form("seqno");
$result = $purDAO->deleteData($conn, $param);

if ($result) {
    echo "1";
} else {
    echo "2";
}

$conn->CompleteTrans();
$conn->close();
?>
