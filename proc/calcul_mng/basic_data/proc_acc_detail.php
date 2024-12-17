<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/set/BasicDataDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$fb = new FormBean();

$basicDAO = new BasicDataDAO();
$conn->StartTrans();

//계정 상세
$param = array();
$param["table"] = "acc_detail";
$param["col"]["acc_subject_seqno"] = $fb->form("acc_subject");
$param["col"]["name"] = $fb->form("acc_detail");
$param["col"]["note"] = $fb->form("note");

if ($fb->form("acc_detail_seqno")) {

    $param["prk"] = "acc_detail_seqno";
    $param["prkVal"] = $fb->form("acc_detail_seqno");

    $result = $basicDAO->updateData($conn, $param);

} else {

    $result = $basicDAO->insertData($conn, $param);

}

echo $result;
$conn->CompleteTrans();
$conn->close();
?>
