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

$new_price = $fb->form("new_price");
$member_seqno = $fb->form("member_seqno");
$year = $fb->form("year");
$mon = $fb->form("mon");

//증빙 유형및 정보 변경
$param["new_price"] = $new_price;
$param["member_seqno"] = $member_seqno;
$param["year"] = $year;
$param["mon"] = $mon;

$rs = $dao->selectTaxPrice($conn, $param);
$arr = (array) $rs;
if ($arr['fields']['cnt'] == 1) {
    $rs2 = $dao->updateTaxPrice($conn, $param);
}else{
    $rs2 = $dao->insertTaxPrice($conn, $param);
}


$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
