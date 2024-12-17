<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/tab/SalesTabListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new SalesTabListDAO();
$check = 1;
$conn->StartTrans();

//증빙 유형및 정보 변경
$param = array();
$param["table"] = "public_admin";
$param["col"]["oa"] = $fb->form("oa");
$param["col"]["before_oa"] = $fb->form("before_oa");
$param["col"]["public_date"] = $fb->form("public_date");
$param["col"]["public_state"] = "완료";
$param["prk"] = "public_admin_seqno";
$param["prkVal"] = $fb->form("seqno");

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
