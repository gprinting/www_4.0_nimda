<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/cooperator_mng/CooperatorListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new CooperatorListDAO();
$util = new CommonUtil();

$order_detail_seqno = $fb->form("order_detail_seqno");
$order_common_seqno = $fb->form("order_common_seqno");
$invo_cpn = $fb->form("invo_cpn");
$invo_num = $fb->form("invo_num");

$check = 1;
$conn->StartTrans();

$param = array();
$param["table"] = "order_detail";
$param["col"]["state"] = $util->status2statusCode("배송중");
$param["prk"] = "order_detail_seqno";
$param["prkVal"] = $order_detail_seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "order_common";
$param["col"]["order_state"] = $util->status2statusCode("배송중");
$param["prk"] = "order_common_seqno";
$param["prkVal"] = $order_common_seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "order_dlvr";
$param["col"]["order_common_seqno"] = $order_common_seqno;
$param["col"]["invo_cpn"] = $invo_cpn;
$param["col"]["invo_num"] = $invo_num;

$rs = $dao->insertData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->close();
echo $check; 
?>
