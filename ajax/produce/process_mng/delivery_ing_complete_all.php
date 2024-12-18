<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/ProcessMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ProcessMngDAO();


$param = array();
$param['product_sort'] = $fb->form("product_sort"); //현재 출고실에서는 상품을 전단류, 명함류로 분류해 배송함
$param['after_yn'] = $fb->form("after_yn");
$param['keyword'] = $fb->form("keyword");
$param['theday_yn'] = $fb->form("theday_yn");
$param['after_yn'] = $fb->form("after_yn");
$param["dlvr_way"] = $fb->form("dlvr_way");
$param["dvs"] = "ALL";
$from_date = $fb->form("date_from");
$to_date = $fb->form("date_to");

if ($from_date) {
    $from_time = $fb->form("time_from");
    $from = $from_date . " " . $from_time;
}

if ($to_date) {
    $to_time = " " . $fb->form("time_to") + 1;
    $to =  $to_date . " " . $to_time;
}

$param["from"] = $from;
$param["to"] = $to;
$param["search_cnd"] = $fb->form("search_cnd");

$rs = $dao->selectDeliveryIngList($conn, $param);

$param = array();
$i = 0;
while ($rs && !$rs->EOF) {
    $param["order_detail_num"][$i++] = $rs->fields["order_detail_dvs_num"];
    $rs->MoveNext();
}

$param["state"] = "9120";

$rs1 = $dao->updateOrderState($conn, $param);

if($rs1 != null) {
    echo "1";
} else {
    echo "0";
}

$conn->close();

?>